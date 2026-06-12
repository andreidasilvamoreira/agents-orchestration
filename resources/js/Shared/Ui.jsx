import React from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';

export function Shell({ title, children, eyebrow = 'Agent Orchestration', description = null }) {
    const { flash = {} } = usePage().props;

    return (
        <>
            <Head title={title} />
            <div className="mx-auto w-[95%] px-4 py-8 sm:px-6 lg:px-8">
                <header className="mb-8 grid gap-4 rounded-3xl border border-stone-800 bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.22),_transparent_28%),linear-gradient(135deg,_rgba(28,25,23,1),_rgba(12,10,9,1))] p-6 shadow-2xl shadow-black/30">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div className="max-w-3xl">
                            <p className="mb-2 text-sm uppercase tracking-[0.25em] text-orange-300">{eyebrow}</p>
                            {title ? <h1 className="text-3xl font-semibold text-stone-50">{title}</h1> : null}
                            {description ? <p className="mt-3 text-sm leading-6 text-stone-300">{description}</p> : null}
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <AiStatusPill />
                            <NavLink href="/" matchPrefixes={['/']} activeStyle="muted">Início</NavLink>
                            <NavLink href="/workflows/builder" matchPrefixes={['/workflows/builder']}>Construtor visual</NavLink>
                            <SettingsMenu />
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

export function NavLink({ href, children, matchPrefixes = [], activeStyle = 'default', exact = false }) {
    const current = isCurrentPath(href, matchPrefixes, exact);
    const className = current
        ? activeStyle === 'muted'
            ? 'cursor-pointer rounded-2xl border border-stone-700 px-4 py-3 text-sm text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white'
            : 'cursor-pointer rounded-2xl bg-stone-100 px-4 py-3 text-sm font-medium text-stone-950 transition hover:bg-white'
        : 'cursor-pointer rounded-2xl border border-stone-700 px-4 py-3 text-sm text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white';

    return <a href={href} className={className}>{children}</a>;
}

function SettingsMenu() {
    const active = isCurrentPath('/teams', ['/agents', '/workflows'], false);
    const buttonClassName = active
        ? 'cursor-pointer rounded-2xl bg-stone-100 px-4 py-3 text-sm font-medium text-stone-950 transition hover:bg-white'
        : 'cursor-pointer rounded-2xl border border-stone-700 px-4 py-3 text-sm text-stone-200 transition hover:border-stone-500 hover:bg-stone-900 hover:text-white';

    return (
        <details className="group relative">
            <summary className={`${buttonClassName} flex list-none items-center gap-2 [&::-webkit-details-marker]:hidden`}>
                <span>Configurações</span>
                <svg className="h-4 w-4 transition group-open:rotate-180" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M4 6L8 10L12 6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
            </summary>
            <div className="absolute right-0 z-20 mt-2 min-w-56 rounded-3xl border border-stone-800 bg-stone-950/98 p-2 shadow-2xl shadow-black/40">
                <MenuItem href="/teams" title="Times" />
                <MenuItem href="/agents" title="Agentes" />
                <MenuItem href="/workflows" title="Workflows" />
            </div>
        </details>
    );
}

function MenuItem({ href, title }) {
    const active = isCurrentPath(href, [], true);

    return (
        <a
            href={href}
            className={`block rounded-2xl px-4 py-3 transition ${active ? 'bg-stone-100 text-stone-950' : 'text-stone-200 hover:bg-stone-900 hover:text-white'}`}
        >
            <p className="text-sm font-medium">{title}</p>
        </a>
    );
}

function isCurrentPath(href, matchPrefixes = [], exact = false) {
    if (typeof window === 'undefined') {
        return false;
    }

    return exact
        ? window.location.pathname === href
        : [href, ...matchPrefixes].some((prefix) => prefix === '/' ? window.location.pathname === '/' : window.location.pathname.startsWith(prefix));
}

export function AiStatusPill() {
    const { ai = null } = usePage().props;

    if (!ai) {
        return null;
    }

    return (
        <div className="flex items-center gap-3 rounded-2xl border border-stone-700 bg-stone-900/80 px-4 py-3 text-sm text-stone-200">
            <span className={`h-2.5 w-2.5 rounded-full ${ai.is_connected ? 'bg-emerald-400 shadow-[0_0_12px_rgba(74,222,128,0.8)]' : 'bg-stone-500'}`}></span>
            <div className="flex items-center gap-2 leading-tight">
                <p className="font-medium text-stone-100">{ai.model}</p>
                <p className="text-xs text-stone-400">{ai.status_label}</p>
            </div>
        </div>
    );
}

export function Card({ title, children, aside, collapsible = false, defaultCollapsed = false, className = '', bodyClassName = '', hideHeader = false }) {
    const [isCollapsed, setIsCollapsed] = React.useState(defaultCollapsed);

    return (
        <section className={`rounded-3xl border border-stone-800 bg-stone-900/80 p-5 ${className}`}>
            {!hideHeader ? (
                <div className={`flex items-center justify-between gap-3 ${isCollapsed ? '' : 'mb-4'}`}>
                    <div className="flex items-center gap-3">
                        <h2 className="text-lg font-semibold">{title}</h2>
                        {aside}
                    </div>
                    {collapsible ? (
                        <button
                            type="button"
                            aria-expanded={!isCollapsed}
                            onClick={() => setIsCollapsed((current) => !current)}
                            className="rounded-2xl border border-stone-700 bg-stone-950/80 p-2 text-stone-300 transition hover:border-stone-500 hover:text-white"
                        >
                            <svg className={`h-4 w-4 transition-transform ${isCollapsed ? 'rotate-180' : ''}`} viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M4 10L8 6L12 10" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    ) : null}
                </div>
            ) : null}
            {!isCollapsed ? <div className={bodyClassName}>{children}</div> : null}
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
