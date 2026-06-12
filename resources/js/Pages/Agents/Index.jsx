import React from 'react';
import { router, useForm } from '@inertiajs/react';
import { Card, Shell, TextArea, TextInput, Select } from '../../Shared/Ui';

export default function Index(props) {
    const { teams, agents, availableModels, modelsLookupError } = props;
    const modelOptionsId = 'agent-model-options';
    const create = useForm({ agent_team_id: '', name: '', slug: '', role: '', goal: '', system_prompt: '', model: '', temperature: '', is_active: true });
    const [editingId, setEditingId] = React.useState(null);
    const editing = agents.find((agent) => agent.id === editingId) || null;
    const edit = useForm({ agent_team_id: '', name: '', slug: '', role: '', goal: '', system_prompt: '', model: '', temperature: '', is_active: true });

    React.useEffect(() => {
        if (!editing) return;
        edit.setData({
            agent_team_id: editing.agent_team_id ?? '',
            name: editing.name ?? '',
            slug: editing.slug ?? '',
            role: editing.role ?? '',
            goal: editing.goal ?? '',
            system_prompt: editing.system_prompt ?? '',
            model: editing.model ?? '',
            temperature: editing.temperature ?? '',
            is_active: !!editing.is_active,
        });
    }, [editingId]);

    return (
        <Shell title="Agentes">
            <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <Card title="Criar agente">
                    <form onSubmit={(e) => { e.preventDefault(); create.post('/agents'); }} className="space-y-3">
                        <Select value={create.data.agent_team_id} onChange={(e) => create.setData('agent_team_id', e.target.value)}>
                            <option value="">Selecione o time</option>
                            {teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}
                        </Select>
                        <TextInput value={create.data.name} onChange={(e) => create.setData('name', e.target.value)} placeholder="Nome do agente" />
                        <TextInput value={create.data.slug} onChange={(e) => create.setData('slug', e.target.value)} placeholder="Slug ex: pesquisador-leads" />
                        <TextInput value={create.data.role} onChange={(e) => create.setData('role', e.target.value)} placeholder="Papel" />
                        <TextArea rows={2} value={create.data.goal} onChange={(e) => create.setData('goal', e.target.value)} placeholder="Objetivo" />
                        <TextArea rows={4} value={create.data.system_prompt} onChange={(e) => create.setData('system_prompt', e.target.value)} placeholder="Prompt base" />
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="space-y-2">
                                <TextInput value={create.data.model} onChange={(e) => create.setData('model', e.target.value)} placeholder="Modelo opcional: usar padrao global" list={modelOptionsId} />
                                {modelsLookupError ? <p className="text-xs text-amber-300">{modelsLookupError}</p> : null}
                                {!modelsLookupError && availableModels.length ? <p className="text-xs text-stone-400">Sugestoes do driver atual disponiveis no autocomplete.</p> : null}
                            </div>
                            <div className="space-y-2">
                                <TextInput type="number" min="0" max="2" step="0.1" value={create.data.temperature} onChange={(e) => create.setData('temperature', e.target.value)} placeholder="Temperature" />
                                <p className="text-xs text-stone-400">Faixa permitida: 0.0 a 2.0</p>
                            </div>
                        </div>
                        <label className="flex items-center gap-2 text-sm text-stone-300">
                            <input type="checkbox" checked={create.data.is_active} onChange={(e) => create.setData('is_active', e.target.checked)} />
                            Agente ativo
                        </label>
                        <button className="cursor-pointer rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-sky-300 hover:shadow-lg hover:shadow-sky-500/20">Salvar agente</button>
                    </form>
                </Card>
                <Card title="Agentes cadastrados" aside={<span className="rounded-full bg-stone-800 px-3 py-1 text-xs text-stone-300">{agents.length} agentes</span>}>
                    <div className="space-y-3">
                        {agents.map((agent) => (
                            <div key={agent.id} className="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <p className="font-medium">{agent.name}</p>
                                        <p className="text-xs text-stone-400">{agent.team_name} · {agent.slug}</p>
                                    </div>
                                    {agent.role ? <span className="rounded-full bg-sky-500/15 px-3 py-1 text-xs text-sky-200">{agent.role}</span> : null}
                                </div>
                                {agent.goal ? <p className="mt-2 text-sm text-stone-300">{agent.goal}</p> : null}
                                <div className="mt-4 flex gap-2">
                                    <button type="button" className="cursor-pointer rounded-2xl border border-stone-700 px-3 py-2 text-xs text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white" onClick={() => setEditingId(agent.id)}>Editar</button>
                                    <button type="button" className="cursor-pointer rounded-2xl border border-rose-500/40 px-3 py-2 text-xs text-rose-200 transition hover:border-rose-400 hover:bg-rose-500/10 hover:text-rose-100" onClick={() => { if (window.confirm('Excluir este agente?')) router.delete(`/agents/${agent.id}`); }}>Excluir</button>
                                </div>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>

            {editing ? (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                    <div className="w-full max-w-2xl rounded-3xl border border-stone-700 bg-stone-950 p-5">
                        <div className="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p className="text-lg font-semibold">Editar agente</p>
                                <p className="text-xs text-stone-400">{editing.name}</p>
                            </div>
                            <button className="cursor-pointer rounded-2xl border border-stone-700 px-3 py-2 text-xs text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white" onClick={() => setEditingId(null)}>Fechar</button>
                        </div>
                        <form onSubmit={(e) => { e.preventDefault(); edit.put(`/agents/${editing.id}`, { onSuccess: () => setEditingId(null) }); }} className="space-y-3">
                            <Select value={edit.data.agent_team_id} onChange={(e) => edit.setData('agent_team_id', e.target.value)}>
                                {teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}
                            </Select>
                            <TextInput value={edit.data.name} onChange={(e) => edit.setData('name', e.target.value)} />
                            <TextInput value={edit.data.slug} onChange={(e) => edit.setData('slug', e.target.value)} />
                            <TextInput value={edit.data.role} onChange={(e) => edit.setData('role', e.target.value)} />
                            <TextArea rows={2} value={edit.data.goal} onChange={(e) => edit.setData('goal', e.target.value)} />
                            <TextArea rows={4} value={edit.data.system_prompt} onChange={(e) => edit.setData('system_prompt', e.target.value)} />
                            <div className="grid gap-3 sm:grid-cols-2">
                                <TextInput value={edit.data.model} onChange={(e) => edit.setData('model', e.target.value)} placeholder="Modelo opcional: usar padrao global" list={modelOptionsId} />
                                <TextInput type="number" min="0" max="2" step="0.1" value={edit.data.temperature} onChange={(e) => edit.setData('temperature', e.target.value)} />
                            </div>
                            <label className="flex items-center gap-2 text-sm text-stone-300">
                                <input type="checkbox" checked={edit.data.is_active} onChange={(e) => edit.setData('is_active', e.target.checked)} />
                                Agente ativo
                            </label>
                            <button className="w-full cursor-pointer rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-sky-300 hover:shadow-lg hover:shadow-sky-500/20">Salvar alteracoes</button>
                        </form>
                    </div>
                </div>
            ) : null}

            <datalist id={modelOptionsId}>
                {availableModels.map((model) => <option key={model} value={model} />)}
            </datalist>
        </Shell>
    );
}
