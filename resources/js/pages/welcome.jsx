import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Flag, Gauge, Trophy } from 'lucide-react';
import PublicLayout from '../layouts/PublicLayout';

const features = [
    { icon: Gauge, number: '01', title: 'Three race modes', text: 'Choose safety-car, race, or qualifying pace. Every tier rewards precision differently.' },
    { icon: Flag, number: '02', title: 'Server-verified results', text: 'Every answer is locked and scored at race control. Your record is earned, not estimated.' },
    { icon: Trophy, number: '03', title: 'Live championships', text: 'Climb the driver table and add every point to your constructor’s championship total.' },
];

export default function Welcome() {
    return (
        <PublicLayout>
            <Head title="Formula racing knowledge, measured" />
            <main>
                <section className="relative overflow-hidden border-b border-white/10">
                    <div className="absolute inset-y-0 right-[-12%] hidden w-[58%] skew-x-[-13deg] border-l border-race-red/30 bg-race-red/8 lg:block" />
                    <div className="shell relative grid min-h-[calc(100vh-5rem)] items-center py-20 lg:grid-cols-[1.2fr_0.8fr] lg:gap-20">
                        <div>
                            <div className="inline-flex items-center gap-3 border border-white/15 bg-white/5 px-4 py-2 font-mono text-xs uppercase tracking-[0.28em] text-cyan">
                                <span className="h-2 w-2 animate-pulse rounded-full bg-cyan" /> Race control online
                            </div>
                            <h1 className="mt-8 max-w-5xl font-display text-[clamp(4.2rem,11vw,9rem)] font-black uppercase italic leading-[0.78] tracking-[-0.085em]">
                                Know the<br /><span className="text-race-red">race.</span><br />Own the grid.
                            </h1>
                            <p className="mt-9 max-w-xl text-lg leading-8 text-slate-400 sm:text-xl">
                                Ten questions. One clean lap. Put your Formula racing knowledge under pressure and prove where you belong.
                            </p>
                            <div className="mt-10 flex flex-wrap gap-4">
                                <Link href="/register" className="button-primary button-large">Take the wheel <ArrowRight size={18} /></Link>
                                <Link href="/login" className="button-ghost button-large">Driver login</Link>
                            </div>
                        </div>
                        <div className="mt-16 lg:mt-0">
                            <div className="relative border border-white/10 bg-white/[0.03] p-7 sm:p-10">
                                <div className="absolute -right-3 -top-3 h-16 w-16 border-r-2 border-t-2 border-race-red" />
                                <p className="font-mono text-xs uppercase tracking-[0.3em] text-slate-500">Race format</p>
                                <div className="mt-7 grid gap-5">
                                    {[['10', 'questions per run'], ['30', 'maximum points per answer'], ['100%', 'server-verified accuracy']].map(([value, label]) => (
                                        <div key={label} className="flex items-end justify-between border-b border-white/10 pb-5">
                                            <span className="font-display text-5xl font-black italic tracking-tight text-white">{value}</span>
                                            <span className="pb-1 text-right text-sm uppercase tracking-wider text-slate-500">{label}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section className="shell py-24">
                    <p className="font-mono text-xs uppercase tracking-[0.32em] text-race-red">Built for the obsessed</p>
                    <h2 className="mt-4 max-w-3xl font-display text-5xl font-black uppercase italic leading-none tracking-[-0.05em] sm:text-6xl">Every answer moves the championship.</h2>
                    <div className="mt-14 grid gap-px bg-white/10 md:grid-cols-3">
                        {features.map(({ icon: Icon, number, title, text }) => (
                            <article key={number} className="group bg-ink p-7 transition-colors hover:bg-panel sm:p-9">
                                <div className="flex items-center justify-between text-race-red"><Icon size={28} /><span className="font-mono text-xs text-slate-600">{number}</span></div>
                                <h3 className="mt-16 font-display text-2xl font-black uppercase italic">{title}</h3>
                                <p className="mt-4 leading-7 text-slate-400">{text}</p>
                            </article>
                        ))}
                    </div>
                </section>
            </main>
            <footer className="border-t border-white/10 py-8"><div className="shell flex flex-wrap justify-between gap-3 text-sm text-slate-500"><span>© 2026 F1Quiz Grid</span><span>Built for racing knowledge</span></div></footer>
        </PublicLayout>
    );
}
