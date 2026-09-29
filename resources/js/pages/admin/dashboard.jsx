import { Link } from '@inertiajs/react';
import { Activity, BookOpenCheck, CheckCircle2, Gauge, ShieldAlert, Users } from 'lucide-react';
import AdminLayout from '../../layouts/AdminLayout';

export default function Dashboard({ metrics, questionsByDifficulty, recentActivity }) {
    const cards = [
        ['Players', metrics.players, Users, 'text-white'],
        ['Suspended', metrics.suspendedPlayers, ShieldAlert, 'text-race-red'],
        ['Active questions', metrics.activeQuestions, BookOpenCheck, 'text-cyan'],
        ['Attempts today', metrics.attemptsToday, Activity, 'text-white'],
        ['Completed', metrics.completedAttempts, CheckCircle2, 'text-cyan'],
        ['Average accuracy', `${metrics.averageAccuracy}%`, Gauge, 'text-white'],
    ];

    return (
        <AdminLayout title="Control tower">
            <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                {cards.map(([label, value, Icon, tone]) => <article key={label} className="admin-metric"><div className="flex items-center justify-between"><span className="metric-label">{label}</span><Icon size={18} className={tone} /></div><p className={`mt-7 font-display text-4xl font-black italic ${tone}`}>{value}</p></article>)}
            </section>
            <div className="mt-7 grid gap-6 xl:grid-cols-[1fr_1.4fr]">
                <section className="admin-panel p-6">
                    <div className="flex items-center justify-between"><h2 className="admin-section-title">Question bank</h2><Link href="/admin/questions" className="text-link text-xs uppercase">Manage</Link></div>
                    <div className="mt-6 grid gap-3">
                        {['easy', 'medium', 'hard'].map((difficulty) => <div key={difficulty} className="flex items-center justify-between border-b border-white/6 pb-3"><span className="font-mono text-xs uppercase tracking-widest text-slate-400">{difficulty}</span><strong className="font-display text-2xl italic">{questionsByDifficulty[difficulty] ?? 0}</strong></div>)}
                    </div>
                </section>
                <section className="admin-panel overflow-hidden">
                    <div className="border-b border-white/10 p-6"><h2 className="admin-section-title">Recent operations</h2></div>
                    <div className="divide-y divide-white/6">{recentActivity.length ? recentActivity.map((item) => <div key={item.id} className="grid gap-1 px-6 py-4 sm:grid-cols-[1fr_auto]"><div><p className="font-bold">{item.action.replaceAll('.', ' ')}</p><p className="text-sm text-slate-500">by {item.actor}</p></div><time className="font-mono text-[10px] text-slate-600">{new Date(item.created_at).toLocaleString()}</time></div>) : <p className="p-6 text-sm text-slate-500">No administrative activity yet.</p>}</div>
                </section>
            </div>
        </AdminLayout>
    );
}
