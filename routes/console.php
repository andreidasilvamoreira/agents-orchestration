<?php

use App\Models\Workflow;
use App\Services\Workflows\WorkflowRunner;
use Illuminate\Support\Facades\Artisan;

Artisan::command('workflow:run {workflow : ID ou slug do workflow} {--input= : JSON com o input inicial}', function (WorkflowRunner $runner) {
    $identifier = $this->argument('workflow');
    $input = $this->option('input') ? json_decode($this->option('input'), true) : [];

    if ($this->option('input') && json_last_error() !== JSON_ERROR_NONE) {
        $this->error('O --input precisa ser um JSON válido.');

        return self::FAILURE;
    }

    $workflow = Workflow::query()
        ->when(is_numeric($identifier), fn ($query) => $query->whereKey($identifier))
        ->when(! is_numeric($identifier), fn ($query) => $query->where('slug', $identifier))
        ->first();

    if (! $workflow) {
        $this->error('Workflow não encontrado.');

        return self::FAILURE;
    }

    $run = $runner->start($workflow, $input, false);

    $this->info("Execução {$run->id} concluída com status {$run->status}.");
    $this->line(json_encode($run->output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return self::SUCCESS;
})->purpose('Executa um workflow com input opcional em JSON');
