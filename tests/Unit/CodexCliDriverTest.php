<?php

namespace Tests\Unit;

use App\Data\AiRequestData;
use App\Exceptions\WorkflowException;
use App\Services\Ai\Drivers\CodexCliDriver;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class CodexCliDriverTest extends TestCase
{
    public function test_it_runs_codex_exec_and_returns_the_last_message(): void
    {
        Process::fake(function ($process) {
            $command = $process->command;
            $outputPath = $command[array_search('-o', $command, true) + 1];

            file_put_contents($outputPath, "Resposta final\n");

            return Process::result('', 'stderr de diagnostico');
        });

        $driver = new CodexCliDriver([
            'binary' => '/usr/bin/codex',
            'model' => 'gpt-5.4',
            'timeout' => 120,
            'working_directory' => '/tmp/workspace',
            'profile' => null,
            'sandbox' => 'workspace-write',
            'bypass_approvals_and_sandbox' => false,
            'available_models' => ['gpt-5.4', 'gpt-5.3'],
        ]);

        $response = $driver->generate(new AiRequestData(
            agent: 'pesquisador',
            prompt: 'Responda com um resumo.',
            systemPrompt: 'Seja objetivo.',
        ));

        $this->assertSame('Resposta final', $response->content);
        $this->assertSame('gpt-5.4', $response->model);
        $this->assertSame("stderr de diagnostico\n", $response->raw['stderr']);

        Process::assertRan(function ($process) {
            return $process->path === '/tmp/workspace'
                && in_array('exec', $process->command, true)
                && in_array('-m', $process->command, true)
                && in_array('gpt-5.4', $process->command, true)
                && $process->input === "Voce esta executando como um agente dentro de um workflow automatizado.\n\nEntregue apenas o conteudo final da resposta, sem prefacios.\n\nAgente: pesquisador\n\nInstrucoes do agente:\nSeja objetivo.\n\nTarefa do passo:\nResponda com um resumo.";
        });
    }

    public function test_it_throws_a_workflow_exception_when_codex_fails(): void
    {
        Process::fake(function ($process) {
            $command = $process->command;
            $outputPath = $command[array_search('-o', $command, true) + 1];

            file_put_contents($outputPath, '');

            return Process::result('', 'falha de autenticacao', 1);
        });

        $driver = new CodexCliDriver([
            'binary' => '/usr/bin/codex',
            'model' => 'gpt-5.4',
            'timeout' => 120,
            'working_directory' => '/tmp/workspace',
            'profile' => null,
            'sandbox' => 'workspace-write',
            'bypass_approvals_and_sandbox' => false,
            'available_models' => [],
        ]);

        $this->expectException(WorkflowException::class);
        $this->expectExceptionMessage('Codex CLI retornou erro: falha de autenticacao');

        $driver->generate(new AiRequestData(
            agent: 'executor',
            prompt: 'Teste',
        ));
    }

    public function test_it_exposes_configured_models(): void
    {
        $driver = new CodexCliDriver([
            'binary' => '/usr/bin/codex',
            'model' => 'gpt-5.4',
            'timeout' => 120,
            'working_directory' => '/tmp/workspace',
            'profile' => null,
            'sandbox' => 'workspace-write',
            'bypass_approvals_and_sandbox' => false,
            'available_models' => ['gpt-5.4', 'gpt-5.3', ''],
        ]);

        $this->assertSame(['gpt-5.4', 'gpt-5.3'], $driver->availableModels());
    }
}
