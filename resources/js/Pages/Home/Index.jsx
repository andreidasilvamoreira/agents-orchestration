import React from 'react';
import { Card, Shell } from '../../Shared/Ui';

export default function Index({ ai, summary, teams, agents, runs, availableModels, modelsLookupError }) {
    const [selectedModel, setSelectedModel] = React.useState(ai.model);

    const summaryCards = [
        { key: 'teams', label: 'Times', value: summary.teams, icon: TeamIcon },
        { key: 'agents', label: 'Agentes', value: summary.agents, icon: AgentIcon },
        { key: 'workflows', label: 'Workflows', value: summary.workflows, icon: WorkflowIcon },
        { key: 'active_tasks', label: 'Tarefas ativas', value: summary.active_tasks, icon: TaskIcon },
    ];

    return (
        <Shell title="Início" eyebrow="Agent Orchestration">
            <div className="space-y-6">
                <div className="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
                    <Card title="Assistente de IA" collapsible>
                        <div className="flex h-full min-h-[620px] flex-col rounded-2xl border border-stone-800 bg-stone-950/70">
                            <div className="flex items-center justify-between gap-3 border-b border-stone-800 px-5 py-4">
                                <div>
                                    <p className="text-lg font-semibold text-stone-100">Assistente de IA</p>
                                    <p className="text-sm text-stone-400">Converse com a IA conectada e coordene seus agentes.</p>
                                </div>
                                <a href="#historico" className="rounded-full border border-stone-700 bg-stone-900 px-3 py-2 text-xs text-stone-300 transition hover:border-stone-500 hover:text-white">Histórico</a>
                            </div>

                            <div className="flex-1 space-y-4 px-5 py-5">
                                <div className="max-w-[85%] rounded-[24px] rounded-tl-md border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm leading-6 text-emerald-50">
                                    IA pronta para receber instruções, distribuir tarefas entre agentes e acompanhar execuções.
                                </div>
                                <div className="max-w-[90%] rounded-[24px] rounded-tl-md border border-stone-800 bg-stone-900 px-4 py-3 text-sm leading-6 text-stone-300">
                                    Exemplos: "resuma o estado dos times ativos", "quais agentes estão conectados?" ou "prepare um fluxo para atendimento comercial".
                                </div>
                                {!ai.is_connected && modelsLookupError ? (
                                    <div className="max-w-[90%] rounded-[24px] rounded-tl-md border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm leading-6 text-amber-100">
                                        {modelsLookupError}
                                    </div>
                                ) : null}
                            </div>

                            <div className="border-t border-stone-800 px-5 py-4">
                                <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-stone-800 bg-stone-950/80 p-3">
                                    <label className="flex h-11 w-11 cursor-pointer items-center justify-center rounded-xl border border-stone-700 bg-stone-900 text-stone-300 transition hover:border-stone-500 hover:text-white">
                                        <input type="file" className="hidden" />
                                        <PaperclipIcon />
                                    </label>
                                    <input
                                        type="text"
                                        placeholder="Escreva para o Assistente de IA"
                                        className="min-w-[240px] flex-1 border-0 bg-transparent px-1 text-sm text-stone-100 outline-none placeholder:text-stone-500"
                                    />
                                    <select
                                        value={selectedModel}
                                        onChange={(event) => setSelectedModel(event.target.value)}
                                        className="h-11 rounded-xl border border-stone-700 bg-stone-900 px-4 text-sm text-stone-200"
                                    >
                                        {(availableModels.length ? availableModels : [ai.model]).map((model) => (
                                            <option key={model} value={model}>{model}</option>
                                        ))}
                                    </select>
                                    <button
                                        type="button"
                                        className="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-500 text-stone-950 transition hover:bg-orange-400"
                                    >
                                        <SendIcon />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <div className="grid gap-6">
                        <Card title="Resumo" collapsible>
                            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                {summaryCards.map((card) => (
                                    <SummaryCard key={card.key} title={card.label} value={card.value} Icon={card.icon} />
                                ))}
                            </div>
                        </Card>

                        <Card title="Times" aside={<CountBadge value={teams.length} label="times" />} collapsible>
                            <div className="space-y-3">
                                {teams.length ? teams.map((team) => (
                                    <div key={team.id} className="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                        <div className="flex items-center justify-between gap-3">
                                            <div className="flex items-center gap-3">
                                                <div className="rounded-2xl border border-stone-700 bg-stone-900/80 p-3 text-orange-300">
                                                    <TeamAvatarIcon name={team.name} />
                                                </div>
                                                <div>
                                                    <p className="font-medium text-stone-100">{team.name}</p>
                                                </div>
                                            </div>
                                            <span className={`rounded-full px-3 py-1 text-xs ${team.is_active ? 'bg-emerald-500/15 text-emerald-200' : 'bg-stone-800 text-stone-300'}`}>
                                                {team.is_active ? 'Ativo' : 'Inativo'}
                                            </span>
                                        </div>
                                    </div>
                                )) : <EmptyState text="Nenhum time cadastrado." />}
                            </div>
                        </Card>

                        <Card title="Agentes conectados" aside={<CountBadge value={agents.length} label="agentes" />} collapsible>
                            <div className="space-y-3">
                                {agents.length ? agents.map((agent) => (
                                    <div key={agent.id} className="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                        <div className="flex items-center justify-between gap-3">
                                            <div className="flex items-center gap-3">
                                                <div className="rounded-2xl border border-stone-700 bg-stone-900/80 p-3 text-sky-300">
                                                    <AgentAvatarIcon name={agent.name} role={agent.role} />
                                                </div>
                                                <div>
                                                    <p className="font-medium text-stone-100">{agent.name}</p>
                                                    <p className="text-xs text-stone-400">{agent.role || 'Sem função definida'}</p>
                                                </div>
                                            </div>
                                            <StatusPill status={agent.status} />
                                        </div>
                                    </div>
                                )) : <EmptyState text="Nenhum agente ativo conectado." />}
                            </div>
                        </Card>

                        <Card title="Execuções recentes" aside={<CountBadge value={runs.length} label="execuções" />} collapsible>
                            <div className="space-y-3">
                                {runs.length ? runs.map((run) => (
                                    <div key={run.id} className="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                        <div className="flex items-center justify-between gap-3">
                                            <div>
                                                <p className="font-medium text-stone-100">{run.workflow_name || 'Workflow removido'}</p>
                                                <p className="text-xs text-stone-400">Run #{run.id} · {run.duration}</p>
                                            </div>
                                            <RunStatusPill status={run.status} />
                                        </div>
                                    </div>
                                )) : <EmptyState text="Nenhuma execução recente." />}
                            </div>
                        </Card>
                    </div>
                </div>

                <Card title="Ações rápidas" collapsible>
                    <div className="grid gap-4 xl:grid-cols-4">
                        <QuickActionCard
                            href="/teams"
                            title="Criar time"
                            description="Organize sua equipe e permissões"
                            Icon={TeamIcon}
                        />
                        <QuickActionCard
                            href="/agents"
                            title="Novo agente"
                            description="Crie um agente especializado"
                            Icon={AgentIcon}
                        />
                        <QuickActionCard
                            href="/workflows"
                            title="Novo workflow"
                            description="Modele e automatize processos"
                            Icon={WorkflowIcon}
                        />
                        <QuickActionCard
                            href="/workflows"
                            title="Ver execuções"
                            description="Acompanhe atividades e logs"
                            Icon={TaskIcon}
                        />
                    </div>
                </Card>
            </div>
        </Shell>
    );
}

function CountBadge({ value, label }) {
    return <span className="rounded-full bg-stone-800 px-3 py-1 text-xs text-stone-300">{value} {label}</span>;
}

function QuickActionCard({ href, title, description, Icon }) {
    return (
        <a
            href={href}
            className="group flex items-center justify-between gap-4 rounded-3xl border border-stone-800 bg-stone-900/70 p-5 transition hover:border-orange-400/50 hover:bg-stone-900"
        >
            <div className="flex items-center gap-4">
                <div className="rounded-2xl border border-stone-700 bg-stone-950/80 p-3 text-orange-300 transition group-hover:border-orange-400/40">
                    <Icon />
                </div>
                <div>
                    <p className="font-medium text-stone-100">{title}</p>
                    <p className="text-sm text-stone-400">{description}</p>
                </div>
            </div>
            <span className="text-2xl text-stone-500 transition group-hover:text-orange-300">›</span>
        </a>
    );
}

function SummaryCard({ title, value, Icon }) {
    return (
        <Card
            title={title}
            className="relative rounded-2xl bg-[linear-gradient(180deg,rgba(24,24,27,0.92),rgba(12,10,9,0.92))] px-5 py-5"
            hideHeader
        >
            <div className="pr-10">
                <p className="text-xs leading-4 text-stone-400">{title}</p>
                <p className="mt-1 text-2xl font-semibold leading-none text-stone-50">{value}</p>
            </div>
            <div className="absolute top-5 right-5 rounded-lg border border-stone-700 bg-stone-950/80 p-2 text-orange-300">
                <div className="h-4 w-4">
                    <Icon />
                </div>
            </div>
        </Card>
    );
}

function EmptyState({ text }) {
    return <p className="rounded-2xl border border-dashed border-stone-800 px-4 py-6 text-sm text-stone-400">{text}</p>;
}

function StatusPill({ status }) {
    const palette = {
        online: 'bg-emerald-500/15 text-emerald-200',
        ocupado: 'bg-amber-500/15 text-amber-200',
        offline: 'bg-stone-800 text-stone-300',
    };

    return <span className={`rounded-full px-3 py-1 text-xs ${palette[status] || palette.offline}`}>{status}</span>;
}

function RunStatusPill({ status }) {
    const labels = {
        completed: 'Concluída',
        running: 'Em andamento',
        pending: 'Em fila',
        failed: 'Falhou',
    };
    const palette = {
        completed: 'bg-emerald-500/15 text-emerald-200',
        running: 'bg-sky-500/15 text-sky-200',
        pending: 'bg-amber-500/15 text-amber-200',
        failed: 'bg-rose-500/15 text-rose-200',
    };

    return <span className={`rounded-full px-3 py-1 text-xs ${palette[status] || 'bg-stone-800 text-stone-300'}`}>{labels[status] || status}</span>;
}

function TeamIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 11a3 3 0 1 0 0-6a3 3 0 0 0 0 6Z" stroke="currentColor" strokeWidth="1.7" />
            <path d="M16 12a2.5 2.5 0 1 0 0-5a2.5 2.5 0 0 0 0 5Z" stroke="currentColor" strokeWidth="1.7" />
            <path d="M3.5 19a4.5 4.5 0 0 1 9 0" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
            <path d="M13.5 19a3.5 3.5 0 0 1 7 0" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
        </svg>
    );
}

function TeamAvatarIcon({ name }) {
    const lowered = (name || '').toLowerCase();

    if (lowered.includes('comercial') || lowered.includes('vendas')) {
        return <HandshakeIcon />;
    }

    if (lowered.includes('suporte') || lowered.includes('atendimento')) {
        return <HeadsetIcon />;
    }

    if (lowered.includes('marketing')) {
        return <MegaphoneIcon />;
    }

    return <TeamIcon />;
}

function AgentAvatarIcon({ name, role }) {
    const lowered = `${name || ''} ${role || ''}`.toLowerCase();

    if (lowered.includes('research') || lowered.includes('pesquisa')) {
        return <SearchIcon />;
    }

    if (lowered.includes('atendimento') || lowered.includes('suporte')) {
        return <HeadsetIcon />;
    }

    if (lowered.includes('vendas') || lowered.includes('closer')) {
        return <HandshakeIcon />;
    }

    if (lowered.includes('conteudo') || lowered.includes('marketing')) {
        return <MegaphoneIcon />;
    }

    return <AgentIcon />;
}

function AgentIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 13a4 4 0 1 0 0-8a4 4 0 0 0 0 8Z" stroke="currentColor" strokeWidth="1.7" />
            <path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
            <path d="M18 8h2M19 7v2" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
        </svg>
    );
}

function WorkflowIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="4" width="6" height="6" rx="1.5" stroke="currentColor" strokeWidth="1.7" />
            <rect x="15" y="4" width="6" height="6" rx="1.5" stroke="currentColor" strokeWidth="1.7" />
            <rect x="9" y="14" width="6" height="6" rx="1.5" stroke="currentColor" strokeWidth="1.7" />
            <path d="M9 7h6M12 10v4" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
        </svg>
    );
}

function TaskIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12.5L9 16.5L19 6.5" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M19 12v6a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h8" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
        </svg>
    );
}

function HandshakeIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 12L11 9a3 3 0 0 1 4 0l1 1a2 2 0 0 0 2.8 0L20 9" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M4 10l3-3l5 5m-3 3l1 1a2 2 0 1 0 2.8-2.8m-3.8-.2l2 2m-4-1l1.5 1.5a2 2 0 0 0 2.8-2.8L10 11" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function HeadsetIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12a7 7 0 1 1 14 0" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
            <path d="M5 13v3a2 2 0 0 0 2 2h1v-6H7a2 2 0 0 0-2 2Zm14 0v3a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2Z" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function MegaphoneIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 13V9a1 1 0 0 1 1-1h3l7-3v12l-7-3H5a1 1 0 0 1-1-1Z" stroke="currentColor" strokeWidth="1.7" strokeLinejoin="round" />
            <path d="M15 9a4 4 0 0 1 0 6M17 7a7 7 0 0 1 0 10" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
        </svg>
    );
}

function SearchIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="5" stroke="currentColor" strokeWidth="1.7" />
            <path d="M16 16l4 4" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
        </svg>
    );
}

function PaperclipIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8.5 12.5l6.7-6.7a3 3 0 1 1 4.3 4.3l-8.1 8.1a5 5 0 0 1-7.1-7.1l8.5-8.5" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function SendIcon() {
    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M21 3L10 14" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
            <path d="M21 3l-7 18l-4-7l-7-4l18-7Z" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
