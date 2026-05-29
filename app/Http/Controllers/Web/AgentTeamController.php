<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AgentTeam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentTeamController extends Controller
{
    protected function rules(?AgentTeam $team = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('agent_teams', 'slug')->ignore($team?->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        AgentTeam::query()->create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Time criado com sucesso.');
    }

    public function update(Request $request, AgentTeam $team): RedirectResponse
    {
        $validated = $request->validate($this->rules($team));

        $team->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('status', 'Time atualizado com sucesso.');
    }

    public function destroy(AgentTeam $team): RedirectResponse
    {
        $team->delete();

        return back()->with('status', 'Time excluído com sucesso.');
    }
}
