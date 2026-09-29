import { Head, Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import Brand from '../components/Brand';

export default function AuthLayout({ title, eyebrow, description, children }) {
    return (
        <div className="relative min-h-screen overflow-hidden bg-ink text-white">
            <Head title={title} />
            <div className="absolute inset-0 bg-grid opacity-70" />
            <div className="absolute -right-24 top-0 h-full w-1/2 skew-x-[-12deg] bg-race-red/10" />
            <main className="relative mx-auto grid min-h-screen max-w-7xl items-center px-5 py-10 lg:grid-cols-[1fr_1.05fr] lg:gap-20 lg:px-10">
                <section className="hidden lg:block">
                    <Brand />
                    <p className="mt-24 font-mono text-xs uppercase tracking-[0.35em] text-cyan">Driver access protocol</p>
                    <h1 className="mt-5 max-w-xl font-display text-7xl font-black uppercase italic leading-[0.87] tracking-[-0.07em]">
                        Earn your place on the grid.
                    </h1>
                    <p className="mt-7 max-w-lg text-lg leading-8 text-slate-400">
                        Build a race record, chase the championship, and measure every answer against the clockwork precision of the paddock.
                    </p>
                </section>
                <section className="panel mx-auto w-full max-w-xl p-6 sm:p-10">
                    <Link href="/" className="mb-8 inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white">
                        <ChevronLeft size={16} /> Back to the paddock
                    </Link>
                    <div className="lg:hidden"><Brand /></div>
                    <p className="mt-8 font-mono text-xs uppercase tracking-[0.3em] text-race-red lg:mt-0">{eyebrow}</p>
                    <h2 className="mt-3 font-display text-4xl font-black uppercase italic tracking-[-0.04em]">{title}</h2>
                    <p className="mt-3 text-slate-400">{description}</p>
                    <div className="mt-8">{children}</div>
                </section>
            </main>
        </div>
    );
}
