<?php

namespace App\Http\Controllers\Web;

use App\Contracts\AiDriver;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentTeam;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AgentPageController extends Controller
{
    public function __invoke(AiDriver $aiDriver): Response
    {
        $availableModels = [];
        $modelsLookupError = null;

        try {
            $availableModels = $aiDriver->availableModels();
        } catch (Throwable) {
            $modelsLookupError = 'Nao foi possivel consultar os modelos disponiveis no provider local.';
        }

        return Inertia::render('Agents/Index', [
            'teams' => AgentTeam::query()->orderBy('name')->get(['id', 'name']),
            'agents' => Agent::query()
                ->latest()
                ->with('team')
                ->get()
                ->map(fn (Agent $agent) => [
                    'id' => $agent->id,
                    'agent_team_id' => $agent->agent_team_id,
                    'name' => $agent->name,
                    'slug' => $agent->slug,
                    'role' => $agent->role,
                    'goal' => $agent->goal,
                    'system_prompt' => $agent->system_prompt,
                    'model' => $agent->model,
                    'temperature' => $agent->temperature,
                    'is_active' => $agent->is_active,
                    'team_name' => $agent->team?->name,
                ]),
            'availableModels' => $availableModels,
            'modelsLookupError' => $modelsLookupError,
        ]);
    }
}
