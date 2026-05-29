import React from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

export function Shell({ title, children }) {
    const { flash = {} } = usePage().props;

    return (
        <>
            <Head title={title} />
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <header className="mb-8 grid gap-4 rounded-3xl border border-stone-800 bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.22),_transparent_28%),linear-gradient(135deg,_rgba(28,25,23,1),_rgba(12,10,9,1))] p-6 shadow-2xl shadow-black/30">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div className="max-w-3xl">
                            <p className="mb-2 text-sm uppercase tracking-[0.25em] text-orange-300">Dify simples em Laravel</p>
                            <h1 className="text-3xl font-semibold text-stone-50">{title}</h1>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <NavLink href="/teams">Times</NavLink>
                            <NavLink href="/agents">Agentes</NavLink>
                            <NavLink href="/workflows">Workflows</NavLink>
                            <a href="/workflows/builder" className="cursor-pointer rounded-2xl border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm font-medium text-sky-100 transition hover:border-sky-400/50 hover:bg-sky-500/20 hover:text-white">Builder visual</a>
                        </div>
                    </div>
                    {flash.status ? (
                        <div className="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                            {flash.status}
                        </div>
                    ) : null}
                </header>
                {children}
            </div>
        </>
    );
}

export function NavLink({ href, children }) {
    const current = typeof window !== 'undefined' ? window.location.pathname === href : false;
    const className = current
        ? 'cursor-pointer rounded-2xl bg-stone-100 px-4 py-3 text-sm font-medium text-stone-950 transition hover:bg-white'
        : 'cursor-pointer rounded-2xl border border-stone-700 px-4 py-3 text-sm text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white';

    return <Link href={href} className={className}>{children}</Link>;
}

export function Card({ title, children, aside }) {
    return (
        <section className="rounded-3xl border border-stone-800 bg-stone-900/80 p-5">
            <div className="mb-4 flex items-center justify-between gap-3">
                <h2 className="text-lg font-semibold">{title}</h2>
                {aside}
            </div>
            {children}
        </section>
    );
}

export function TextInput(props) {
    return <input {...props} className={`w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm ${props.className || ''}`} />;
}

export function TextArea(props) {
    return <textarea {...props} className={`w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm ${props.className || ''}`} />;
}

export function Select(props) {
    return <select {...props} className={`w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm ${props.className || ''}`} />;
}

export function useShared() {
    return { router, useForm };
}
