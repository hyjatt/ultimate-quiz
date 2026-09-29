import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight, Flag, Gauge, Medal, Trophy, Zap } from 'lucide-react';
import AppLayout from '../layouts/AppLayout';

function TeamDot({ color }) {
    return <span className="inline-block h-3 w-1.5 skew-x-[-15deg]" style={{ backgroundColor: color || '#64748b' }} aria-hidden="true" />;
}

function Standings({ title, rows, constructor = false }) {
    return (
        <section className="panel overflow-hidden">
            <div className="flex items-center justify-between border-b border-white/10 px-5 py-5 sm:px-7">
                <h2 className="font-display text-xl font-black uppercase italic">{title}</h2>
                <span className="font-mono text-[10px] uppercase tracking-[0.25em] text-slate-600">Top 10</span>
            </div>
            <div className="divide-y divide-white/5">
                {rows.map((row) => (
                    <div key={constructor ? row.name : row.id} className={`grid grid-cols-[3rem_1fr_auto] items-center gap-3 px-5 py-4 sm:px-7 ${row.isCurrentUser ? 'bg-cyan/8' : ''}`}>
                        <span className="font-mono text-sm text-slate-500">P{row.position}</span>
                        <div className="flex min-w-0 items-center gap-3"><TeamDot color={constructor ? row.color : row.team?.color} /><span className="truncate font-bold">{constructor ? row.name : row.username}</span></div>
                        <span className="font-display text-lg font-black italic">{row.points} <small className="font-sans text-[10px] not-italic text-slate-500">PTS</small></span>
                    </div>
                ))}
            </div>
        </section>
    );
}

export default function Dashboard({ driver, leaderboard, constructors }) {
    const stats = [
        { icon: Medal, label: 'Driver class', value: driver.rank, tone: 'text-cyan' },
        { icon: Trophy, label: 'Championship', value: `P${driver.position}`, tone: 'text-race-red' },
        { icon: Gauge, label: 'Career points', value: driver.points, tone: 'text-white' },
    ];

    return (
        <AppLayout title="Pit wall">
            <Head title="Pit wall" />
            <section className="relative overflow-hidden border border-white/10 bg-panel p-6 sm:p-9">
                <div className="absolute right-[-7rem] top-[-9rem] h-80 w-80 rounded-full bg-race-red/15 blur-3xl" />
                <div className="relative flex flex-col justify-between gap-10 lg:flex-row lg:items-end">
                    <div>
                        <p className="font-mono text-xs uppercase tracking-[0.3em] text-slate-500">Current driver</p>
                        <h2 className="mt-3 font-display text-5xl font-black uppercase italic tracking-[-0.05em] sm:text-7xl">{driver.username}</h2>
                        <div className="mt-5 inline-flex items-center gap-3 border border-white/10 bg-black/20 px-4 py-2 text-sm uppercase tracking-wider text-slate-300"><TeamDot color={driver.team?.color} /> {driver.team?.name || 'Independent'}</div>
                    </div>
                    <Link href="/quiz" className="button-primary button-large shrink-0">Start race <Zap size={18} /></Link>
                </div>
            </section>

            <section className="mt-6 grid gap-3 md:grid-cols-3">
                {stats.map(({ icon: Icon, label, value, tone }) => (
                    <article key={label} className="metric-card">
                        <div className="flex items-center justify-between"><span className="metric-label">{label}</span><Icon size={18} className={tone} /></div>
                        <p className={`mt-7 font-display text-3xl font-black uppercase italic tracking-tight ${tone}`}>{value}</p>
                    </article>
                ))}
            </section>

            <div className="mt-8 grid gap-6 xl:grid-cols-2">
                <Standings title="Driver standings" rows={leaderboard} />
                <Standings title="Constructor standings" rows={constructors.slice(0, 10)} constructor />
            </div>

            <section className="mt-8 flex flex-col gap-5 border border-race-red/30 bg-race-red/8 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
                <div className="flex items-start gap-4"><Flag className="mt-1 text-race-red" /><div><h2 className="font-display text-2xl font-black uppercase italic">Ready for another lap?</h2><p className="mt-1 text-slate-400">Ten questions stand between you and the next rank.</p></div></div>
                <Link href="/quiz" className="button-ghost shrink-0">Enter race control <ArrowUpRight size={18} /></Link>
            </section>
        </AppLayout>
    );
}
