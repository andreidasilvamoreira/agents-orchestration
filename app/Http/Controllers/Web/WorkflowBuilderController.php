<?php

namespace App\Http\Controllers\Web;

use App\Contracts\AiDriver;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentTeam;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class WorkflowBuilderController extends Controller
{
    public function __invoke(Request $request, AiDriver $aiDriver): View
    {
        $selectedWorkflow = $request->filled('workflow')
            ? Workflow::query()->with('team')->find($request->integer('workflow'))
            : Workflow::query()->with('team')->latest()->first();

        $connected = true;

        try {
            $aiDriver->availableModels();
        } catch (Throwable) {
            $connected = false;
        }

        $teams = AgentTeam::query()->latest()->with(['agents' => fn ($query) => $query->where('is_active', true)->orderBy('name')])->get();
        $agents = Agent::query()->where('is_active', true)->with('team')->orderBy('name')->get();
        $workflows = Workflow::query()->with('team')->latest()->get();

        return view('workflow-builder', [
            'teams' => $teams,
            'agents' => $agents,
            'workflows' => $workflows,
            'selectedWorkflow' => $selectedWorkflow,
            'builderPayload' => [
                'agents' => $agents->map(fn (Agent $agent) => [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'slug' => $agent->slug,
                    'role' => $agent->role,
                    'team_id' => $agent->agent_team_id,
                    'team_name' => $agent->team?->name,
                    'goal' => $agent->goal,
                ])->values(),
                'workflows' => $workflows->map(fn (Workflow $workflow) => [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'slug' => $workflow->slug,
                    'agent_team_id' => $workflow->agent_team_id,
                    'description' => $workflow->description,
                ])->values(),
                'selectedWorkflow' => $selectedWorkflow ? [
                    'id' => $selectedWorkflow->id,
                    'name' => $selectedWorkflow->name,
                    'slug' => $selectedWorkflow->slug,
                    'description' => $selectedWorkflow->description,
                    'agent_team_id' => $selectedWorkflow->agent_team_id,
                    'is_active' => $selectedWorkflow->is_active,
                    'definition' => $selectedWorkflow->definition,
                ] : null,
            ],
            'ai' => [
                'model' => $this->currentModelName(),
                'is_connected' => $connected,
                'status_label' => $connected ? 'Conectada e funcionando' : 'Offline',
            ],
        ]);
    }

    protected function currentModelName(): string
    {
        return match (config('agent_ai.driver')) {
            'ollama' => (string) config('agent_ai.ollama.model'),
            'openai_compatible' => (string) config('agent_ai.openai_compatible.model'),
            'codex_cli' => (string) config('agent_ai.codex_cli.model'),
            default => 'modelo-nao-configurado',
        };
    }
}
