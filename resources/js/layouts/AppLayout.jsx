import { Link, usePage } from '@inertiajs/react';
import { LayoutDashboard, LogOut, Menu, UserRound, X, Zap } from 'lucide-react';
import { useState } from 'react';
import Brand from '../components/Brand';

const links = [
    { href: '/dashboard', label: 'Pit wall', icon: LayoutDashboard },
    { href: '/quiz', label: 'Race', icon: Zap },
    { href: '/profile', label: 'Driver', icon: UserRound },
];

export default function AppLayout({ title, children }) {
    const { auth, flash } = usePage().props;
    const [open, setOpen] = useState(false);
    const path = typeof window === 'undefined' ? '' : window.location.pathname;

    return (
        <div className="min-h-screen bg-grid text-white">
            <header className="sticky top-0 z-40 border-b border-white/10 bg-ink/90 backdrop-blur-xl">
                <div className="shell flex h-20 items-center justify-between">
                    <Brand />
                    <nav className="hidden items-center gap-1 md:flex" aria-label="Driver navigation">
                        {links.map(({ href, label, icon: Icon }) => (
                            <Link key={href} href={href} className={`nav-link ${path === href ? 'nav-link-active' : ''}`}>
                                <Icon size={16} /> {label}
                            </Link>
                        ))}
                    </nav>
                    <div className="hidden items-center gap-4 md:flex">
                        <span className="text-right">
                            <span className="block text-xs uppercase tracking-widest text-slate-500">Driver</span>
                            <span className="font-bold">{auth.user.username}</span>
                        </span>
                        <Link href="/logout" method="post" as="button" className="icon-button" aria-label="Log out">
                            <LogOut size={18} />
                        </Link>
                    </div>
                    <button className="icon-button md:hidden" onClick={() => setOpen(!open)} aria-expanded={open} aria-label="Toggle navigation">
                        {open ? <X /> : <Menu />}
                    </button>
                </div>
                {open && (
                    <nav className="shell grid gap-2 border-t border-white/10 py-4 md:hidden">
                        {links.map(({ href, label, icon: Icon }) => (
                            <Link key={href} href={href} onClick={() => setOpen(false)} className="nav-link">
                                <Icon size={18} /> {label}
                            </Link>
                        ))}
                        <Link href="/logout" method="post" as="button" className="nav-link text-left"><LogOut size={18} /> Log out</Link>
                    </nav>
                )}
            </header>
            <main className="shell py-8 sm:py-12">
                <div className="mb-9 flex items-end justify-between gap-4">
                    <div>
                        <p className="font-mono text-xs uppercase tracking-[0.32em] text-cyan">Live race control</p>
                        <h1 className="mt-2 font-display text-4xl font-black uppercase italic tracking-[-0.04em] sm:text-5xl">{title}</h1>
                    </div>
                    <div className="hidden h-px flex-1 bg-gradient-to-r from-white/20 to-transparent sm:block" />
                </div>
                {flash?.status && <div className="mb-6 border-l-4 border-cyan bg-cyan/10 px-5 py-4 text-sm text-cyan">{flash.status}</div>}
                {children}
            </main>
        </div>
    );
}
