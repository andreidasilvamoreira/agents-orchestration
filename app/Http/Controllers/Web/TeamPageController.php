<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AgentTeam;
use Inertia\Inertia;
use Inertia\Response;

class TeamPageController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Teams/Index', [
            'teams' => AgentTeam::query()
                ->latest()
                ->withCount(['agents', 'workflows'])
                ->get()
                ->map(fn (AgentTeam $team) => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'description' => $team->description,
                    'is_active' => $team->is_active,
                    'agents_count' => $team->agents_count,
                    'workflows_count' => $team->workflows_count,
                ]),
        ]);
    }
}
