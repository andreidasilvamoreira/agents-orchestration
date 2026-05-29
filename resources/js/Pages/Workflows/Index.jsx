import React from 'react';
import { router, useForm } from '@inertiajs/react';
import { Card, Select, Shell, TextArea, TextInput } from '../../Shared/Ui';

export default function Index(props) {
    const { teams, workflows, runs, workflowDefinitionSample, runInputSample } = props;
    const form = useForm({ name: '', slug: '', agent_team_id: '', description: '', definition: workflowDefinitionSample, is_active: true });

    return (
        <Shell title="Workflows">
            <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <Card title="Criar workflow">
                    <form onSubmit={(e) => { e.preventDefault(); form.post('/workflows'); }} className="space-y-3">
                        <TextInput value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Nome do workflow" />
                        <TextInput value={form.data.slug} onChange={(e) => form.setData('slug', e.target.value)} placeholder="Slug ex: qualificar-lead" />
                        <Select value={form.data.agent_team_id} onChange={(e) => form.setData('agent_team_id', e.target.value)}>
                            <option value="">Selecione o time dono do workflow</option>
                            {teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}
                        </Select>
                        <TextArea rows={2} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} placeholder="O que esse fluxo faz?" />
                        <TextArea rows={12} value={form.data.definition} onChange={(e) => form.setData('definition', e.target.value)} className="font-mono text-sm" />
                        <label className="flex items-center gap-2 text-sm text-stone-300">
                            <input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} />
                            Workflow ativo
                        </label>
                        <button className="cursor-pointer rounded-2xl bg-emerald-400 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-emerald-300 hover:shadow-lg hover:shadow-emerald-500/20">Salvar workflow</button>
                    </form>
                </Card>
                <div className="grid gap-6">
                    <Card title="Workflows cadastrados" aside={<a href="/workflows/builder" className="cursor-pointer rounded-2xl border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm text-sky-100 transition hover:border-sky-400/50 hover:bg-sky-500/20 hover:text-white">Abrir builder visual</a>}>
                        <div className="space-y-4">
                            {workflows.map((workflow) => (
                                <div key={workflow.id} className="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                    <div className="mb-3">
                                        <p className="font-medium">{workflow.name}</p>
                                        <p className="text-xs text-stone-400">{workflow.team_name || 'Sem time'} · {workflow.slug}</p>
                                    </div>
                                    <button
                                        type="button"
                                        className="cursor-pointer rounded-2xl bg-orange-400 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-300 hover:shadow-lg hover:shadow-orange-500/20"
                                        onClick={() => router.post(`/workflows/${workflow.id}/run`, { input: JSON.parse(runInputSample) })}
                                    >
                                        Executar agora
                                    </button>
                                </div>
                            ))}
                        </div>
                    </Card>
                    <Card title="Ultimas execucoes">
                        <div className="space-y-3">
                            {runs.map((run) => (
                                <div key={run.id} className="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <div>
                                            <p className="font-medium">{run.workflow_name}</p>
                                            <p className="text-xs text-stone-400">Run #{run.id}</p>
                                        </div>
                                        <span className="rounded-full bg-stone-800 px-3 py-1 text-xs">{run.status}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Card>
                </div>
            </div>
        </Shell>
    );
}
