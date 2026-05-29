<?php

namespace App\Http\Controllers\Web;

use App\Contracts\AiDriver;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentTeam;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(AiDriver $aiDriver): View
    {
        $agents = Agent::query()->latest()->with('team')->get();
        $availableModels = [];
        $modelsLookupError = null;

        try {
            $availableModels = $aiDriver->availableModels();
        } catch (Throwable $exception) {
            $modelsLookupError = 'Não foi possível consultar os modelos disponíveis no provider local.';
        }

        return view('dashboard', [
            'teams' => AgentTeam::query()->latest()->withCount('agents')->get(),
            'agents' => $agents,
            'workflows' => Workflow::query()->latest()->with('team')->get(),
            'runs' => WorkflowRun::query()->latest()->with('workflow')->limit(10)->get(),
            'availableModels' => $availableModels,
            'modelsLookupError' => $modelsLookupError,
            'workflowDefinitionSample' => json_encode([
                'steps' => [
                    [
                        'key' => 'summary',
                        'agent_id' => $agents->first()?->id,
                        'prompt' => 'Resuma o lead: {{lead}}',
                        'save_as' => 'lead_summary',
                        'output_format' => 'text',
                    ],
                    [
                        'key' => 'decision',
                        'agent_id' => $agents->skip(1)->first()?->id ?? $agents->first()?->id,
                        'prompt' => '{"summary":"{{lead_summary}}","task":"Decida se o lead vale a pena."}',
                        'save_as' => 'decision',
                        'output_format' => 'json',
                    ],
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'runInputSample' => json_encode([
                'lead' => [
                    'company' => 'Acme',
                    'segment' => 'logistica',
                    'pain' => 'reprocesso alto',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
