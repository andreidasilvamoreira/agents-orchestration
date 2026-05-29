<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AgentTeam;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowPageController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Workflows/Index', [
            'teams' => AgentTeam::query()->orderBy('name')->get(['id', 'name']),
            'workflows' => Workflow::query()
                ->latest()
                ->with('team')
                ->get()
                ->map(fn (Workflow $workflow) => [
                    'id' => $workflow->id,
                    'agent_team_id' => $workflow->agent_team_id,
                    'name' => $workflow->name,
                    'slug' => $workflow->slug,
                    'description' => $workflow->description,
                    'is_active' => $workflow->is_active,
                    'team_name' => $workflow->team?->name,
                    'definition' => $workflow->definition,
                ]),
            'runs' => WorkflowRun::query()
                ->latest()
                ->with('workflow')
                ->limit(10)
                ->get()
                ->map(fn (WorkflowRun $run) => [
                    'id' => $run->id,
                    'status' => $run->status,
                    'workflow_name' => $run->workflow?->name,
                ]),
            'workflowDefinitionSample' => json_encode([
                'steps' => [
                    [
                        'key' => 'summary',
                        'agent_id' => 1,
                        'prompt' => 'Resuma o lead: {{lead}}',
                        'save_as' => 'lead_summary',
                        'output_format' => 'text',
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
