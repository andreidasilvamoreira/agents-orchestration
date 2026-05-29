<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} · Builder</title>
        @vite(['resources/css/app.css', 'resources/js/builder.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <div id="workflow-builder-root" class="mx-auto max-w-[1600px] px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-stone-800 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.16),_transparent_28%),linear-gradient(135deg,_rgba(28,25,23,1),_rgba(12,10,9,1))] p-6">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-sky-300">Visual Workflow Orchestration</p>
                    <h1 class="mt-2 text-3xl font-semibold">Builder visual de workflows</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-300">
                        Arraste agentes para o canvas, conecte em sequência e edite prompts por passo. O builder salva um fluxo linear compatível com o motor atual.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="rounded-2xl border border-stone-700 px-4 py-3 text-sm text-stone-200">Voltar ao painel</a>
                    <form method="GET" action="{{ route('workflows.builder') }}">
                        <select name="workflow" onchange="this.form.submit()" class="rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm">
                            <option value="">Novo workflow</option>
                            @foreach ($workflows as $workflow)
                                <option value="{{ $workflow->id }}" @selected($selectedWorkflow?->id === $workflow->id)>{{ $workflow->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="builder-shell">
                <aside class="builder-card">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Agentes disponíveis</h2>
                        <span class="rounded-full bg-stone-800 px-3 py-1 text-xs text-stone-300">{{ $agents->count() }} agentes</span>
                    </div>
                    <p class="mb-4 text-sm text-stone-400">Arraste um agente para o canvas. Cada nó vira um passo do workflow.</p>
                    <div data-agent-palette class="space-y-4">
                        @foreach ($teams as $team)
                            <section>
                                <div class="mb-2 flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-stone-200">{{ $team->name }}</h3>
                                    <span class="text-xs text-stone-500">{{ $team->agents->count() }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($team->agents as $agent)
                                        <article
                                            data-agent-item
                                            data-agent-id="{{ $agent->id }}"
                                            draggable="true"
                                            class="cursor-grab rounded-2xl border border-stone-800 bg-stone-950/90 p-3 transition hover:border-sky-500/50 hover:bg-stone-950"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-medium">{{ $agent->name }}</p>
                                                    <p class="mt-1 text-xs text-stone-500">{{ $agent->slug }}</p>
                                                </div>
                                                @if ($agent->role)
                                                    <span class="rounded-full bg-sky-500/10 px-2 py-1 text-[11px] text-sky-200">{{ $agent->role }}</span>
                                                @endif
                                            </div>
                                            @if ($agent->goal)
                                                <p class="mt-2 text-xs leading-5 text-stone-400">{{ $agent->goal }}</p>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </aside>

                <main class="builder-card">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">Canvas</h2>
                            <p class="text-sm text-stone-400">Use <strong>Ctrl + scroll do mouse</strong> para zoom. Arraste o fundo com o botão direito para navegar. Use o botão esquerdo no agente para movê-lo. Clique em qualquer ponto azul do nó de origem e depois em qualquer ponto laranja do destino. Clique direito em um nó para removê-lo do canvas.</p>
                            <p data-connect-hint class="mt-2 text-xs text-sky-200">Nenhuma conexão em andamento.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-clear-canvas class="rounded-2xl border border-stone-700 px-4 py-3 text-sm text-stone-200">Limpar canvas</button>
                        </div>
                    </div>
                    <div data-canvas-viewport class="builder-canvas-viewport">
                        <div data-canvas class="builder-canvas builder-canvas-stage">
                            <svg data-edges-layer class="builder-edges-layer"></svg>
                            <div data-nodes-layer class="builder-nodes-layer"></div>
                            <div data-empty-state class="absolute inset-0 flex items-center justify-center text-center text-sm text-stone-400">
                                Arraste agentes para começar seu fluxo.
                            </div>
                        </div>
                    </div>
                </main>

                <aside class="builder-card">
                    <form data-builder-form method="POST" action="{{ route('workflows.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <textarea name="definition" class="hidden"></textarea>

                        <div>
                            <p class="text-sm uppercase tracking-[0.22em] text-stone-500" data-form-title>Novo workflow</p>
                            <h2 class="mt-2 text-lg font-semibold">Dados do fluxo</h2>
                        </div>

                        <input name="name" placeholder="Nome do workflow" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                        <input name="slug" placeholder="Slug ex: atendimento-comercial" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm" />
                        <textarea name="description" rows="3" placeholder="Descreva o objetivo do workflow" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm"></textarea>
                        <select name="agent_team_id" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm">
                            <option value="">Time do workflow</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        <label class="flex items-center gap-2 text-sm text-stone-300">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-stone-600 bg-stone-900">
                            Workflow ativo
                        </label>

                        <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                            <h3 class="text-sm font-semibold text-stone-100">Passo selecionado</h3>
                            <div data-selected-hint class="mt-3 text-sm leading-6 text-stone-400">
                                Selecione um nó no canvas para editar o prompt, o `save_as` e o formato de saída.
                            </div>

                            <div data-selected-step class="mt-3 hidden space-y-3">
                                <div>
                                    <p data-selected-title class="text-sm font-medium text-stone-100"></p>
                                </div>
                                <input data-step-field="key" placeholder="Chave do passo" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm" />
                                <input data-step-field="save_as" placeholder="Salvar como" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm" />
                                <select data-step-field="output_format" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm">
                                    <option value="text">text</option>
                                    <option value="json">json</option>
                                </select>
                                <textarea data-step-field="prompt" rows="6" placeholder="Prompt do passo" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 font-mono text-sm"></textarea>
                                <textarea data-step-field="system_prompt" rows="4" placeholder="System prompt opcional do passo" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 font-mono text-sm"></textarea>
                                <button type="button" data-remove-step class="w-full rounded-2xl border border-rose-500/40 px-4 py-3 text-sm text-rose-200">Remover passo</button>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-sky-500/20 bg-sky-500/8 px-4 py-3 text-xs leading-6 text-sky-100">
                            O builder atual suporta um fluxo linear: um passo inicial, uma entrada por nó e uma saída por nó.
                        </div>

                        <button class="w-full rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-stone-950">Salvar workflow visual</button>
                    </form>
                </aside>
            </div>
        </div>

        <script id="workflow-builder-payload" type="application/json">@json($builderPayload)</script>

        <div data-delete-modal class="builder-delete-modal hidden">
            <div class="builder-delete-card">
                <p class="text-lg font-semibold text-stone-100">Remover agente do canvas</p>
                <p data-delete-text class="mt-3 text-sm leading-6 text-stone-300"></p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" data-delete-cancel class="rounded-2xl border border-stone-700 px-4 py-3 text-sm text-stone-200">Cancelar</button>
                    <button type="button" data-delete-confirm class="rounded-2xl bg-rose-500 px-4 py-3 text-sm font-semibold text-white">Remover</button>
                </div>
            </div>
        </div>
    </body>
</html>
