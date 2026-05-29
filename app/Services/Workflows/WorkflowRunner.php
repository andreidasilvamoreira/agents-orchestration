<?php

namespace App\Services\Workflows;

use App\Data\AiRequestData;
use App\Exceptions\WorkflowException;
use App\Models\Agent;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Models\WorkflowStepRun;
use App\Services\Ai\AiManager;
use Illuminate\Support\Arr;

class WorkflowRunner
{
    public function __construct(
        protected AiManager $aiManager,
        protected PromptTemplateRenderer $renderer,
        protected AgentResolver $agentResolver,
    ) {
    }

    public function start(Workflow $workflow, array $input = [], bool $dispatch = true): WorkflowRun
    {
        $run = $workflow->runs()->create([
            'status' => 'pending',
            'input' => $input,
            'context' => $input,
        ]);

        if ($dispatch) {
            \App\Jobs\RunWorkflowJob::dispatch($run->id);
        } else {
            $run = $this->execute($run);
        }

        return $run;
    }

    public function execute(WorkflowRun $run): WorkflowRun
    {
        $run->loadMissing('workflow.team');

        $context = $run->context ?? $run->input ?? [];
        $steps = Arr::get($run->workflow->definition, 'steps', []);

        if ($steps === []) {
            throw new WorkflowException('O workflow precisa ter ao menos um passo.');
        }

        $run->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            foreach ($steps as $index => $step) {
                $context = $this->executeStep($run, $step, $index, $context);
            }

            $run->update([
                'status' => 'completed',
                'context' => $context,
                'output' => $context,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'context' => $context,
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        }

        return $run->fresh('steps');
    }

    protected function executeStep(WorkflowRun $run, array $step, int $index, array $context): array
    {
        $stepKey = $step['key'] ?? 'step_'.($index + 1);
        $agent = $this->agentResolver->resolve($run->workflow, $step);
        $agentName = $agent->name;
        $prompt = $this->renderer->render($step['prompt'] ?? null, $context);
        $systemPrompt = $this->buildSystemPrompt($agent, $step, $context);

        if (blank($prompt)) {
            throw new WorkflowException("O passo [{$stepKey}] não possui prompt válido.");
        }

        /** @var WorkflowStepRun $stepRun */
        $stepRun = $run->steps()->create([
            'step_key' => $stepKey,
            'step_order' => $index + 1,
            'agent_name' => $agentName,
            'status' => 'running',
            'input' => $context,
            'prompt' => $prompt,
            'started_at' => now(),
        ]);

        try {
            $response = $this->aiManager->ask(new AiRequestData(
                agent: $agentName,
                prompt: $prompt,
                systemPrompt: $systemPrompt,
                context: $context,
                model: $agent->model,
                temperature: $agent->temperature,
            ));

            $parsedOutput = $this->normalizeOutput($response->content, $step['output_format'] ?? 'text');
            $saveAs = $step['save_as'] ?? $stepKey;

            data_set($context, $saveAs, $parsedOutput);
            data_set($context, "{$saveAs}_meta", [
                'agent' => $agentName,
                'model' => $response->model,
                'temperature' => $agent->temperature,
            ]);

            $stepRun->update([
                'status' => 'completed',
                'output' => [
                    'content' => $parsedOutput,
                    'raw' => $response->raw,
                    'model' => $response->model,
                    'temperature' => $agent->temperature,
                ],
                'finished_at' => now(),
            ]);

            return $context;
        } catch (\Throwable $exception) {
            $stepRun->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }

    protected function normalizeOutput(string $content, string $format): mixed
    {
        if ($format !== 'json') {
            return trim($content);
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WorkflowException('A IA retornou JSON inválido para um passo configurado como json.');
        }

        return $decoded;
    }

    protected function buildSystemPrompt(Agent $agent, array $step, array $context): ?string
    {
        $parts = array_filter([
            $agent->role ? "Papel: {$agent->role}" : null,
            $agent->goal ? "Objetivo: {$agent->goal}" : null,
            $agent->system_prompt,
            $this->renderer->render($step['system_prompt'] ?? null, $context),
        ]);

        return $parts === [] ? null : implode("\n\n", $parts);
    }
}
