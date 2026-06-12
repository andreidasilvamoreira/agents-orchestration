<?php

namespace App\Http\Controllers\Web;

use App\Contracts\AiDriver;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentTeam;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(AiDriver $aiDriver): Response
    {
        $agents = Agent::query()->latest()->with('team')->get();
        $availableModels = [];
        $modelsLookupError = null;

        try {
            $availableModels = $aiDriver->availableModels();
        } catch (Throwable $exception) {
            $modelsLookupError = 'Não foi possível consultar os modelos disponíveis no driver configurado.';
        }

        $teams = AgentTeam::query()->latest()->withCount('agents')->get();
        $workflows = Workflow::query()->latest()->with('team')->get();
        $runs = WorkflowRun::query()->latest()->with('workflow')->limit(10)->get();
        $connected = $modelsLookupError === null;
        $activeRuns = WorkflowRun::query()
            ->whereIn('status', ['pending', 'running'])
            ->with('workflow')
            ->get();
        $activeTasksCount = $activeRuns->count();
        $busyAgentIds = $activeRuns
            ->flatMap(function (WorkflowRun $run) {
                $steps = Arr::get($run->workflow?->definition, 'steps', []);

                return collect($steps)->pluck('agent_id')->filter();
            })
            ->map(fn (mixed $agentId) => (int) $agentId)
            ->unique()
            ->values()
            ->all();

        return Inertia::render('Home/Index', [
            'ai' => [
                'driver' => config('agent_ai.driver'),
                'model' => $this->currentModelName(),
                'is_connected' => $connected,
                'status_label' => $connected ? 'Conectada e funcionando' : 'Offline',
            ],
            'summary' => [
                'teams' => $teams->count(),
                'agents' => $agents->count(),
                'workflows' => $workflows->count(),
                'active_tasks' => $activeTasksCount,
            ],
            'teams' => $teams->take(4)->map(fn (AgentTeam $team) => [
                'id' => $team->id,
                'name' => $team->name,
                'is_active' => $team->is_active,
            ])->values(),
            'agents' => $agents->where('is_active', true)->take(4)->map(fn (Agent $agent) => [
                'id' => $agent->id,
                'name' => $agent->name,
                'role' => $agent->role,
                'status' => in_array($agent->id, $busyAgentIds, true) ? 'ocupado' : 'online',
            ])->values(),
            'runs' => $runs->take(4)->map(fn (WorkflowRun $run) => [
                'id' => $run->id,
                'status' => $run->status,
                'workflow_name' => $run->workflow?->name,
                'duration' => $this->formatDuration($run),
            ])->values(),
            'availableModels' => $availableModels,
            'modelsLookupError' => $modelsLookupError,
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

    protected function formatDuration(WorkflowRun $run): string
    {
        $start = $run->started_at ?? $run->created_at;
        $end = $run->finished_at ?? now();

        if (!$start) {
            return 'Sem tempo';
        }

        $seconds = max(0, $start->diffInSeconds($end));

        if ($seconds < 60) {
            return $seconds.'s';
        }

        if ($seconds < 3600) {
            return floor($seconds / 60).'min';
        }

        return floor($seconds / 3600).'h '.floor(($seconds % 3600) / 60).'min';
    }
}
