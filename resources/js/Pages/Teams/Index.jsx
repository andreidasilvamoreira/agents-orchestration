import React from 'react';
import { router, useForm } from '@inertiajs/react';
import { Card, Shell, TextArea, TextInput } from '../../Shared/Ui';

export default function Index(props) {
    const { teams } = props;
    const create = useForm({ name: '', slug: '', description: '', is_active: true });
    const [isCreating, setIsCreating] = React.useState(false);
    const [editingId, setEditingId] = React.useState(null);
    const editing = teams.find((team) => team.id === editingId) || null;
    const edit = useForm({ name: '', slug: '', description: '', is_active: true });

    React.useEffect(() => {
        if (!editing) return;
        edit.setData({
            name: editing.name ?? '',
            slug: editing.slug ?? '',
            description: editing.description ?? '',
            is_active: !!editing.is_active,
        });
    }, [editingId]);

    return (
        <Shell title="Times">
            <Card
                title="Times cadastrados"
                aside={(
                    <div className="flex items-center gap-2">
                        <span className="rounded-full bg-stone-800 px-3 py-1 text-xs text-stone-300">{teams.length} times</span>
                        <button
                            type="button"
                            className="cursor-pointer rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-400 hover:shadow-lg hover:shadow-orange-500/20"
                            onClick={() => setIsCreating(true)}
                        >
                            Criar time
                        </button>
                    </div>
                )}
            >
                <div className="space-y-3">
                    {teams.map((team) => (
                        <div key={team.id} className="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <p className="font-medium">{team.name}</p>
                                        <span className={`rounded-full px-3 py-1 text-xs ${team.is_active ? 'bg-emerald-500/15 text-emerald-200' : 'bg-stone-800 text-stone-300'}`}>
                                            {team.is_active ? 'Ativo' : 'Inativo'}
                                        </span>
                                    </div>
                                    <p className="text-xs text-stone-400">{team.slug}</p>
                                </div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="rounded-full bg-stone-800 px-3 py-1 text-xs">{team.agents_count} agentes</span>
                                    <span className="rounded-full bg-stone-800 px-3 py-1 text-xs">{team.workflows_count} workflows</span>
                                </div>
                            </div>
                            {team.description ? <p className="mt-2 text-sm text-stone-300">{team.description}</p> : null}
                            <div className="mt-4 flex gap-2">
                                <button type="button" className="cursor-pointer rounded-2xl border border-stone-700 px-3 py-2 text-xs text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white" onClick={() => setEditingId(team.id)}>Editar</button>
                                <button
                                    type="button"
                                    className="cursor-pointer rounded-2xl border border-rose-500/40 px-3 py-2 text-xs text-rose-200 transition hover:border-rose-400 hover:bg-rose-500/10 hover:text-rose-100"
                                    onClick={() => {
                                        const message = team.agents_count > 0
                                            ? `Excluir este time? ${team.agents_count} agente(s) vinculado(s) tambem serao removidos.`
                                            : 'Excluir este time?';

                                        if (window.confirm(message)) {
                                            router.delete(`/teams/${team.id}`);
                                        }
                                    }}
                                >
                                    Excluir
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            </Card>

            {isCreating ? (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                    <div className="w-full max-w-2xl rounded-3xl border border-stone-700 bg-stone-950 p-5">
                        <div className="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p className="text-lg font-semibold">Criar time</p>
                                <p className="text-xs text-stone-400">Cadastre um novo time para agrupar agentes e workflows.</p>
                            </div>
                            <button className="cursor-pointer rounded-2xl border border-stone-700 px-3 py-2 text-xs text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white" onClick={() => setIsCreating(false)}>Fechar</button>
                        </div>
                        <form onSubmit={(e) => { e.preventDefault(); create.post('/teams', { onSuccess: () => setIsCreating(false) }); }} className="space-y-3">
                            <TextInput value={create.data.name} onChange={(e) => create.setData('name', e.target.value)} placeholder="Nome do time" />
                            <TextInput value={create.data.slug} onChange={(e) => create.setData('slug', e.target.value)} placeholder="Slug ex: comercial-ai" />
                            <TextArea rows={4} value={create.data.description} onChange={(e) => create.setData('description', e.target.value)} placeholder="Qual a funcao desse time?" />
                            <label className="flex items-center gap-2 text-sm text-stone-300">
                                <input type="checkbox" checked={create.data.is_active} onChange={(e) => create.setData('is_active', e.target.checked)} />
                                Time ativo
                            </label>
                            <button className="w-full cursor-pointer rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-400 hover:shadow-lg hover:shadow-orange-500/20">Salvar time</button>
                        </form>
                    </div>
                </div>
            ) : null}

            {editing ? (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                    <div className="w-full max-w-2xl rounded-3xl border border-stone-700 bg-stone-950 p-5">
                        <div className="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p className="text-lg font-semibold">Editar time</p>
                                <p className="text-xs text-stone-400">{editing.name}</p>
                            </div>
                            <button className="cursor-pointer rounded-2xl border border-stone-700 px-3 py-2 text-xs text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white" onClick={() => setEditingId(null)}>Fechar</button>
                        </div>
                        <form onSubmit={(e) => { e.preventDefault(); edit.put(`/teams/${editing.id}`, { onSuccess: () => setEditingId(null) }); }} className="space-y-3">
                            <TextInput value={edit.data.name} onChange={(e) => edit.setData('name', e.target.value)} />
                            <TextInput value={edit.data.slug} onChange={(e) => edit.setData('slug', e.target.value)} />
                            <TextArea rows={4} value={edit.data.description} onChange={(e) => edit.setData('description', e.target.value)} />
                            <label className="flex items-center gap-2 text-sm text-stone-300">
                                <input type="checkbox" checked={edit.data.is_active} onChange={(e) => edit.setData('is_active', e.target.checked)} />
                                Time ativo
                            </label>
                            <button className="w-full cursor-pointer rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-400 hover:shadow-lg hover:shadow-orange-500/20">Salvar alteracoes</button>
                        </form>
                    </div>
                </div>
            ) : null}
        </Shell>
    );
}
