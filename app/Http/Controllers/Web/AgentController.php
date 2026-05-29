<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    protected function rules(?Agent $agent = null): array
    {
        return [
            'agent_team_id' => ['required', 'exists:agent_teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('agents', 'slug')->ignore($agent?->id)],
            'role' => ['nullable', 'string', 'max:255'],
            'goal' => ['nullable', 'string'],
            'system_prompt' => ['nullable', 'string'],
            'model' => ['nullable', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Agent::query()->create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Agente criado com sucesso.');
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate($this->rules($agent));

        $agent->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('status', 'Agente atualizado com sucesso.');
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $agent->delete();

        return back()->with('status', 'Agente excluído com sucesso.');
    }
}
