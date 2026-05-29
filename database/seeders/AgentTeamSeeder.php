<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AgentTeam;
use App\Models\Workflow;
use Illuminate\Database\Seeder;

class AgentTeamSeeder extends Seeder
{
    public function run(): void
    {
        $team = AgentTeam::query()->updateOrCreate(
            ['slug' => 'sales-pod'],
            [
                'name' => 'Sales Pod',
                'description' => 'Time simples para qualificação e decisão comercial.',
                'is_active' => true,
            ],
        );

        $researcher = Agent::query()->updateOrCreate(
            ['slug' => 'lead-researcher'],
            [
                'agent_team_id' => $team->id,
                'name' => 'Lead Researcher',
                'role' => 'pesquisador',
                'goal' => 'Entender rapidamente o contexto e dores do lead.',
                'system_prompt' => 'Responda de forma objetiva, clara e operacional.',
                'model' => config('agent_ai.ollama.model'),
                'temperature' => 0.2,
                'is_active' => true,
            ],
        );

        $closer = Agent::query()->updateOrCreate(
            ['slug' => 'lead-closer'],
            [
                'agent_team_id' => $team->id,
                'name' => 'Lead Closer',
                'role' => 'decisor',
                'goal' => 'Classificar prioridade e sugerir próximo passo.',
                'system_prompt' => 'Quando solicitado JSON, responda apenas JSON válido.',
                'model' => config('agent_ai.ollama.model'),
                'temperature' => 0.2,
                'is_active' => true,
            ],
        );

        Workflow::query()->updateOrCreate(
            ['slug' => 'lead-qualifier'],
            [
                'agent_team_id' => $team->id,
                'name' => 'Lead Qualifier',
                'description' => 'Workflow base para qualificar leads com um time simples de agentes.',
                'is_active' => true,
                'definition' => [
                    'steps' => [
                        [
                            'key' => 'summary',
                            'agent_id' => $researcher->id,
                            'prompt' => 'Resuma o lead abaixo em até 5 bullets e destaque riscos. Dados: {{lead}}',
                            'save_as' => 'lead_summary',
                            'output_format' => 'text',
                        ],
                        [
                            'key' => 'classification',
                            'agent_id' => $closer->id,
                            'system_prompt' => 'Responda apenas com JSON válido.',
                            'prompt' => '{"summary":"{{lead_summary}}","task":"Classifique o lead com score de 0 a 100 e uma recomendação curta."}',
                            'save_as' => 'lead_score',
                            'output_format' => 'json',
                        ],
                    ],
                ],
            ],
        );
    }
}
