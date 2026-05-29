<?php

namespace App\Jobs;

use App\Models\WorkflowRun;
use App\Services\Workflows\WorkflowRunner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunWorkflowJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $workflowRunId,
    ) {
    }

    public function handle(WorkflowRunner $runner): void
    {
        $run = WorkflowRun::query()->findOrFail($this->workflowRunId);

        $runner->execute($run);
    }
}
