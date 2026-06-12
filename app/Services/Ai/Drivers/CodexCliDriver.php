<?php

namespace App\Services\Ai\Drivers;

use App\Contracts\AiDriver;
use App\Data\AiRequestData;
use App\Data\AiResponseData;
use App\Exceptions\WorkflowException;
use Illuminate\Support\Facades\Process;
use Throwable;

class CodexCliDriver implements AiDriver
{
    public function __construct(
        protected array $config,
    ) {}

    public function generate(AiRequestData $request): AiResponseData
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'codex-last-message-');

        if ($outputFile === false) {
            throw new WorkflowException('Nao foi possivel preparar o arquivo temporario para a resposta do Codex CLI.');
        }

        $command = $this->buildCommand($request, $outputFile);

        try {
            $result = Process::path($this->workingDirectory())
                ->timeout($this->config['timeout'])
                ->input($this->buildPrompt($request))
                ->run($command);
        } catch (Throwable $exception) {
            @unlink($outputFile);

            throw new WorkflowException('Falha ao executar o Codex CLI: '.$exception->getMessage(), previous: $exception);
        }

        $content = trim((string) @file_get_contents($outputFile));
        @unlink($outputFile);

        if ($result->failed()) {
            $details = trim($result->errorOutput()) ?: trim($result->output()) ?: 'sem detalhes adicionais';

            throw new WorkflowException("Codex CLI retornou erro: {$details}");
        }

        if ($content === '') {
            $content = trim($result->output());
        }

        return new AiResponseData(
            content: $content,
            raw: [
                'stdout' => $result->output(),
                'stderr' => $result->errorOutput(),
                'exit_code' => $result->exitCode(),
            ],
            model: $request->model ?: ($this->config['model'] ?: null),
        );
    }

    public function availableModels(): array
    {
        return collect($this->config['available_models'] ?? [])
            ->prepend($this->config['model'] ?: null)
            ->filter(fn (?string $model) => filled($model))
            ->unique()
            ->values()
            ->all();
    }

    protected function buildCommand(AiRequestData $request, string $outputFile): array
    {
        $command = [
            $this->config['binary'],
            'exec',
            '--skip-git-repo-check',
            '--ephemeral',
            '--color',
            'never',
            '-a',
            'never',
            '-C',
            $this->workingDirectory(),
            '-o',
            $outputFile,
        ];

        if (filled($request->model ?: $this->config['model'])) {
            $command[] = '-m';
            $command[] = $request->model ?: $this->config['model'];
        }

        if (filled($this->config['profile'])) {
            $command[] = '-p';
            $command[] = $this->config['profile'];
        }

        if (filled($this->config['sandbox'])) {
            $command[] = '-s';
            $command[] = $this->config['sandbox'];
        }

        if ($this->config['bypass_approvals_and_sandbox']) {
            $command[] = '--dangerously-bypass-approvals-and-sandbox';
        }

        $command[] = '-';

        return $command;
    }

    protected function buildPrompt(AiRequestData $request): string
    {
        $parts = [
            'Voce esta executando como um agente dentro de um workflow automatizado.',
            'Entregue apenas o conteudo final da resposta, sem prefacios.',
            "Agente: {$request->agent}",
        ];

        if (filled($request->systemPrompt)) {
            $parts[] = "Instrucoes do agente:\n{$request->systemPrompt}";
        }

        $parts[] = "Tarefa do passo:\n{$request->prompt}";

        return implode("\n\n", $parts);
    }

    protected function workingDirectory(): string
    {
        return $this->config['working_directory'] ?: base_path();
    }
}
