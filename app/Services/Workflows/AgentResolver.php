<?php

namespace App\Services\Workflows;

use App\Exceptions\WorkflowException;
use App\Models\Agent;
use App\Models\Workflow;

class AgentResolver
{
    public function resolve(Workflow $workflow, array $step): Agent
    {
        $agentId = $step['agent_id'] ?? null;
        $agentSlug = $step['agent_slug'] ?? null;

        if (! $agentId && ! $agentSlug) {
            throw new WorkflowException('Cada passo do workflow precisa informar agent_id ou agent_slug.');
        }

        $query = Agent::query()->where('is_active', true);

        if ($workflow->agent_team_id) {
            $query->where('agent_team_id', $workflow->agent_team_id);
        }

        $agent = $query->where(function ($builder) use ($agentId, $agentSlug) {
            if ($agentId) {
                $builder->whereKey($agentId);
            }

            if ($agentSlug) {
                $method = $agentId ? 'orWhere' : 'where';
                $builder->{$method}('slug', $agentSlug);
            }
        })->first();

        if (! $agent) {
            throw new WorkflowException('Agente do passo não encontrado ou inativo.');
        }

        return $agent;
    }
}
