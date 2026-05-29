<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunWorkflowRequest;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Services\Workflows\WorkflowRunner;
use Illuminate\Http\JsonResponse;

class WorkflowRunController extends Controller
{
    public function store(RunWorkflowRequest $request, Workflow $workflow, WorkflowRunner $runner): JsonResponse
    {
        $run = $runner->start(
            workflow: $workflow,
            input: $request->validated('input', []),
            dispatch: $request->boolean('dispatch', true),
        );

        return response()->json([
            'message' => 'Execução iniciada.',
            'data' => $run->fresh(),
        ], 202);
    }

    public function show(WorkflowRun $run): JsonResponse
    {
        return response()->json([
            'data' => $run->load('workflow', 'steps'),
        ]);
    }
}
