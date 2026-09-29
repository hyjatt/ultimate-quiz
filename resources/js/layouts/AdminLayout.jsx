import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    BookOpenCheck,
    Gauge,
    LogOut,
    Menu,
    Medal,
    ShieldCheck,
    Trophy,
    Users,
    UsersRound,
    X,
} from 'lucide-react';
import { useState } from 'react';

const links = [
    { href: '/admin', label: 'Overview', icon: Gauge, exact: true },
    { href: '/admin/questions', label: 'Questions', icon: BookOpenCheck },
    { href: '/admin/players', label: 'Players', icon: Users },
    { href: '/admin/attempts', label: 'Attempts', icon: Activity },
    { href: '/admin/teams', label: 'Teams', icon: UsersRound },
    { href: '/admin/ranks', label: 'Ranks', icon: Trophy },
    { href: '/admin/audit-logs', label: 'Audit log', icon: ShieldCheck },
];

export default function AdminLayout({ title, eyebrow = 'Race control', actions, children }) {
    const { auth, flash, errors } = usePage().props;
    const [open, setOpen] = useState(false);
    const path = typeof window === 'undefined' ? '' : window.location.pathname;
    const navigation = auth.user.role === 'superadmin'
        ? [...links, { href: '/admin/administrators', label: 'Administrators', icon: Medal }]
        : links;

    return (
        <div className="min-h-screen bg-admin-grid text-white">
            <Head title={title} />
            <aside className={`fixed inset-y-0 left-0 z-50 w-72 border-r border-white/10 bg-[#090c12]/98 transition-transform lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="flex h-20 items-center justify-between border-b border-white/10 px-6">
                    <Link href="/admin" className="flex items-center gap-3">
                        <span className="grid h-10 w-10 place-items-center bg-race-red font-display text-xl font-black italic">RC</span>
                        <span><b className="block font-display text-lg uppercase italic">Race Control</b><small className="font-mono text-[9px] uppercase tracking-[0.28em] text-slate-500">F1Quiz admin</small></span>
                    </Link>
                    <button className="icon-button lg:hidden" onClick={() => setOpen(false)} aria-label="Close navigation"><X size={18} /></button>
                </div>
                <nav className="grid gap-1 p-4" aria-label="Admin navigation">
                    {navigation.map(({ href, label, icon: Icon, exact }) => {
                        const active = exact ? path === href : path.startsWith(href);
                        return <Link key={href} href={href} prefetch onClick={() => setOpen(false)} className={`admin-nav-link ${active ? 'admin-nav-link-active' : ''}`}><Icon size={18} />{label}</Link>;
                    })}
                </nav>
                <div className="absolute inset-x-4 bottom-4 border border-white/10 bg-white/3 p-4">
                    <p className="truncate font-bold">{auth.user.username}</p>
                    <p className="mt-1 font-mono text-[10px] uppercase tracking-widest text-cyan">{auth.user.role}</p>
                    <Link href="/logout" method="post" as="button" className="mt-4 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-white"><LogOut size={15} /> Sign out</Link>
                </div>
            </aside>

            <div className="lg:pl-72">
                <header className="sticky top-0 z-30 flex h-20 items-center gap-4 border-b border-white/10 bg-ink/90 px-4 backdrop-blur-xl sm:px-8">
                    <button className="icon-button lg:hidden" onClick={() => setOpen(true)} aria-label="Open navigation"><Menu size={18} /></button>
                    <div className="min-w-0 flex-1"><p className="font-mono text-[10px] uppercase tracking-[0.3em] text-cyan">{eyebrow}</p><h1 className="truncate font-display text-2xl font-black uppercase italic sm:text-3xl">{title}</h1></div>
                    {actions}
                </header>
                <main className="p-4 sm:p-8">
                    {flash?.status && <div className="notice-success">{flash.status}</div>}
                    {errors?.login && <div className="mb-5 border-l-4 border-race-red bg-race-red/10 p-4 text-sm text-red-200">{errors.login}</div>}
                    {children}
                </main>
            </div>
            {open && <button className="fixed inset-0 z-40 bg-black/70 lg:hidden" onClick={() => setOpen(false)} aria-label="Close navigation overlay" />}
        </div>
    );
}
