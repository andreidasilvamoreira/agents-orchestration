<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 grid gap-4 rounded-3xl border border-stone-800 bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.22),_transparent_28%),linear-gradient(135deg,_rgba(28,25,23,1),_rgba(12,10,9,1))] p-6 shadow-2xl shadow-black/30">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="max-w-3xl">
                        <p class="mb-2 text-sm uppercase tracking-[0.25em] text-orange-300">Dify simples em Laravel</p>
                        <h1 class="text-3xl font-semibold text-stone-50">Times de agentes + workflows</h1>
                        <p class="mt-3 text-sm leading-6 text-stone-300">
                            Configure um time, cadastre agentes com papel e prompt base, e monte workflows que encadeiam esses agentes.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-orange-500/30 bg-orange-500/10 px-4 py-3 text-sm text-orange-100">
                        Driver atual: <span class="font-semibold">{{ config('agent_ai.driver') }}</span>
                    </div>
                </div>
                <div>
                    <a href="{{ route('workflows.builder') }}" class="inline-flex rounded-2xl border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm font-medium text-sky-100">
                        Abrir Visual Workflow Orchestration
                    </a>
                </div>
                @if (session('status'))
                    <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                        {{ $errors->first() }}
                    </div>
                @endif
            </header>

            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <section class="grid gap-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
                            <h2 class="mb-4 text-lg font-semibold">Criar time</h2>
                            <form method="POST" action="{{ route('teams.store') }}" class="space-y-3">
                                @csrf
                                <input name="name" placeholder="Nome do time" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                                <input name="slug" placeholder="Slug ex: comercial-ai" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                                <textarea name="description" rows="3" placeholder="Qual a função desse time?" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm"></textarea>
                                <label class="flex items-center gap-2 text-sm text-stone-300">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-stone-600 bg-stone-900">
                                    Time ativo
                                </label>
                                <button class="rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-stone-950">Salvar time</button>
                            </form>
                        </div>

                        <div class="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
                            <h2 class="mb-4 text-lg font-semibold">Criar agente</h2>
                            <form method="POST" action="{{ route('agents.store') }}" class="space-y-3">
                                @csrf
                                <select name="agent_team_id" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm">
                                    <option value="">Selecione o time</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                                <input name="name" placeholder="Nome do agente" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                                <input name="slug" placeholder="Slug ex: pesquisador-leads" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                                <input name="role" placeholder="Papel ex: pesquisador" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                                <textarea name="goal" rows="2" placeholder="Objetivo principal do agente" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm"></textarea>
                                <textarea name="system_prompt" rows="4" placeholder="Prompt base do agente" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm"></textarea>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <select name="model" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm">
                                            <option value="">Modelo opcional: usar padrão global</option>
                                            @foreach ($availableModels as $model)
                                                <option value="{{ $model }}">{{ $model }}</option>
                                            @endforeach
                                        </select>
                                        @if ($modelsLookupError)
                                            <p class="text-xs text-amber-300">{{ $modelsLookupError }}</p>
                                        @elseif ($availableModels === [])
                                            <p class="text-xs text-stone-400">Nenhum modelo listado pelo provider atual.</p>
                                        @endif
                                    </div>
                                    <div class="space-y-2">
                                        <input name="temperature" type="number" step="0.1" min="0" max="2" placeholder="Temperature" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                                        <p class="text-xs text-stone-400">Faixa permitida: 0.0 a 2.0</p>
                                    </div>
                                </div>
                                <button class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-stone-950">Salvar agente</button>
                            </form>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
                        <h2 class="mb-4 text-lg font-semibold">Criar workflow</h2>
                        <form method="POST" action="{{ route('workflows.store') }}" class="space-y-3">
                            @csrf
                            <div class="grid gap-3 md:grid-cols-2">
                                <input name="name" placeholder="Nome do workflow" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                                <input name="slug" placeholder="Slug ex: qualificar-lead" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                            </div>
                            <select name="agent_team_id" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm">
                                <option value="">Selecione o time dono do workflow</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                            <textarea name="description" rows="2" placeholder="O que esse fluxo faz?" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm"></textarea>
                            <textarea name="definition" rows="14" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 font-mono text-sm">{{ $workflowDefinitionSample }}</textarea>
                            <label class="flex items-center gap-2 text-sm text-stone-300">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded border-stone-600 bg-stone-900">
                                Workflow ativo
                            </label>
                            <p class="text-xs leading-5 text-stone-400">
                                Cada passo referencia um agente por `agent_id`. O prompt pode usar variáveis como `@{{lead}}` e resultados anteriores como `@{{lead_summary}}`.
                            </p>
                            <button class="rounded-2xl bg-emerald-400 px-4 py-3 text-sm font-semibold text-stone-950">Salvar workflow</button>
                        </form>
                    </div>
                </section>

                <aside class="grid gap-6">
                    <div class="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
                        <h2 class="mb-4 text-lg font-semibold">Times</h2>
                        <div class="space-y-3">
                            @forelse ($teams as $team)
                                <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium">{{ $team->name }}</p>
                                            <p class="text-xs text-stone-400">{{ $team->slug }}</p>
                                        </div>
                                        <span class="rounded-full bg-stone-800 px-3 py-1 text-xs">{{ $team->agents_count }} agentes</span>
                                    </div>
                                    @if ($team->description)
                                        <p class="mt-2 text-sm text-stone-300">{{ $team->description }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-stone-400">Nenhum time ainda.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
                        <h2 class="mb-4 text-lg font-semibold">Agentes</h2>
                        <div class="space-y-3">
                            @forelse ($agents as $agent)
                                <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium">{{ $agent->name }}</p>
                                            <p class="text-xs text-stone-400">{{ $agent->team?->name }} · {{ $agent->slug }}</p>
                                        </div>
                                        @if ($agent->role)
                                            <span class="rounded-full bg-sky-500/15 px-3 py-1 text-xs text-sky-200">{{ $agent->role }}</span>
                                        @endif
                                    </div>
                                    @if ($agent->goal)
                                        <p class="mt-2 text-sm text-stone-300">{{ $agent->goal }}</p>
                                    @endif
                                    <div class="mt-4 flex gap-2">
                                        <button
                                            type="button"
                                            class="rounded-2xl border border-stone-700 px-3 py-2 text-xs text-stone-200"
                                            onclick="document.getElementById('agent-edit-{{ $agent->id }}').showModal()"
                                        >
                                            Editar
                                        </button>
                                        <form method="POST" action="{{ route('agents.destroy', $agent) }}" onsubmit="return confirm('Excluir este agente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-2xl border border-rose-500/40 px-3 py-2 text-xs text-rose-200">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <dialog id="agent-edit-{{ $agent->id }}" class="mx-auto my-auto w-full max-w-2xl rounded-3xl border border-stone-700 bg-stone-950 p-0 text-stone-100 backdrop:bg-black/70">
                                    <form method="dialog" class="flex items-center justify-between border-b border-stone-800 px-5 py-4">
                                        <div>
                                            <p class="text-lg font-semibold">Editar agente</p>
                                            <p class="text-xs text-stone-400">{{ $agent->name }}</p>
                                        </div>
                                        <button class="rounded-2xl border border-stone-700 px-3 py-2 text-xs text-stone-200">Fechar</button>
                                    </form>

                                    <form method="POST" action="{{ route('agents.update', $agent) }}" class="space-y-3 px-5 py-5">
                                        @csrf
                                        @method('PUT')
                                        <select name="agent_team_id" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm">
                                            @foreach ($teams as $team)
                                                <option value="{{ $team->id }}" @selected($agent->agent_team_id === $team->id)>{{ $team->name }}</option>
                                            @endforeach
                                        </select>
                                        <input name="name" value="{{ $agent->name }}" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm" />
                                        <input name="slug" value="{{ $agent->slug }}" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm" />
                                        <input name="role" value="{{ $agent->role }}" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm" />
                                        <textarea name="goal" rows="2" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm">{{ $agent->goal }}</textarea>
                                        <textarea name="system_prompt" rows="4" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm">{{ $agent->system_prompt }}</textarea>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div class="space-y-2">
                                                <select name="model" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm">
                                                    <option value="">Modelo opcional: usar padrão global</option>
                                                    @foreach ($availableModels as $model)
                                                        <option value="{{ $model }}" @selected($agent->model === $model)>{{ $model }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="space-y-2">
                                                <input name="temperature" type="number" step="0.1" min="0" max="2" value="{{ $agent->temperature }}" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm" />
                                                <p class="text-xs text-stone-400">Faixa permitida: 0.0 a 2.0</p>
                                            </div>
                                        </div>
                                        <label class="flex items-center gap-2 text-sm text-stone-300">
                                            <input type="checkbox" name="is_active" value="1" @checked($agent->is_active) class="rounded border-stone-600 bg-stone-900">
                                            Agente ativo
                                        </label>
                                        <button class="w-full rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-stone-950">Salvar alterações</button>
                                    </form>
                                </dialog>
                            @empty
                                <p class="text-sm text-stone-400">Nenhum agente ainda.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
                        <h2 class="mb-4 text-lg font-semibold">Workflows</h2>
                        <div class="space-y-4">
                            @forelse ($workflows as $workflow)
                                <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                    <div class="mb-3">
                                        <p class="font-medium">{{ $workflow->name }}</p>
                                        <p class="text-xs text-stone-400">{{ $workflow->team?->name ?? 'Sem time' }} · {{ $workflow->slug }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('workflows.run', $workflow) }}" class="space-y-3">
                                        @csrf
                                        <textarea name="input" rows="5" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 font-mono text-xs">{{ $runInputSample }}</textarea>
                                        <button class="rounded-2xl bg-orange-400 px-4 py-3 text-sm font-semibold text-stone-950">Executar agora</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-stone-400">Nenhum workflow ainda.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
                        <h2 class="mb-4 text-lg font-semibold">Últimas execuções</h2>
                        <div class="space-y-3">
                            @forelse ($runs as $run)
                                <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium">{{ $run->workflow?->name }}</p>
                                            <p class="text-xs text-stone-400">Run #{{ $run->id }}</p>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs {{ $run->status === 'completed' ? 'bg-emerald-500/15 text-emerald-200' : ($run->status === 'failed' ? 'bg-rose-500/15 text-rose-200' : 'bg-stone-800 text-stone-200') }}">
                                            {{ $run->status }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-stone-400">Nenhuma execução ainda.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </body>
</html>
