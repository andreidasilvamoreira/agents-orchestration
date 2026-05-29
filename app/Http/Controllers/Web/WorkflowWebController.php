<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunWorkflowRequest;
use App\Http\Requests\StoreWorkflowRequest;
use App\Models\Workflow;
use App\Services\Workflows\WorkflowRunner;
use Illuminate\Http\RedirectResponse;

class WorkflowWebController extends Controller
{
    public function store(StoreWorkflowRequest $request): RedirectResponse
    {
        Workflow::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Workflow criado com sucesso.');
    }

    public function update(StoreWorkflowRequest $request, Workflow $workflow): RedirectResponse
    {
        $workflow->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('workflows.builder', ['workflow' => $workflow->id])
            ->with('status', 'Workflow atualizado com sucesso.');
    }

    public function run(RunWorkflowRequest $request, Workflow $workflow, WorkflowRunner $runner): RedirectResponse
    {
        $input = $request->validated('input', []);

        $runner->start($workflow, $input, false);

        return back()->with('status', 'Workflow executado com sucesso.');
    }
}
