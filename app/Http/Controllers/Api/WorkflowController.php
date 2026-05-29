<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkflowRequest;
use App\Models\Workflow;
use Illuminate\Http\JsonResponse;

class WorkflowController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Workflow::query()->with('team')->latest()->get(),
        ]);
    }

    public function store(StoreWorkflowRequest $request): JsonResponse
    {
        $workflow = Workflow::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Workflow criado com sucesso.',
            'data' => $workflow,
        ], 201);
    }

    public function show(Workflow $workflow): JsonResponse
    {
        return response()->json([
            'data' => $workflow->load('team', 'runs'),
        ]);
    }
}
