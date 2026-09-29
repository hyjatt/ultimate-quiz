import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { KeyRound, MailWarning, Save, ShieldCheck } from 'lucide-react';
import FormField from '../../components/FormField';
import AppLayout from '../../layouts/AppLayout';

export default function Profile({ teams, stats, history, mustVerifyEmail }) {
    const user = usePage().props.auth.user;
    const profile = useForm({ username: user.username, email: user.email, team_id: user.team_id || '' });
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });

    function updateProfile(event) {
        event.preventDefault();
        profile.patch('/profile', { preserveScroll: true });
    }

    function updatePassword(event) {
        event.preventDefault();
        password.put('/profile/password', { preserveScroll: true, onSuccess: () => password.reset() });
    }

    return (
        <AppLayout title="Driver profile">
            <Head title="Driver profile" />
            <div className="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <div className="grid gap-6">
                    <section className="panel p-6 sm:p-8">
                        <p className="section-kicker">Driver identification</p>
                        <h2 className="section-title">Paddock credentials</h2>
                        {mustVerifyEmail && !user.email_verified_at && (
                            <div className="mt-5 flex gap-3 border border-amber-400/30 bg-amber-400/10 p-4 text-sm text-amber-100"><MailWarning className="shrink-0" size={19} /><span>Your email is unverified. <Link href="/email/verification-notification" method="post" as="button" className="underline">Send another verification link.</Link></span></div>
                        )}
                        <form onSubmit={updateProfile} className="mt-7 grid gap-5">
                            <FormField label="Driver name" error={profile.errors.username}><input className="field" value={profile.data.username} onChange={(e) => profile.setData('username', e.target.value)} required /></FormField>
                            <FormField label="Email channel" error={profile.errors.email}><input className="field" type="email" value={profile.data.email} onChange={(e) => profile.setData('email', e.target.value)} required /></FormField>
                            <FormField label="Constructor allegiance" error={profile.errors.team_id}><select className="field" value={profile.data.team_id} onChange={(e) => profile.setData('team_id', e.target.value)} required><option value="">Choose your team</option>{teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}</select></FormField>
                            <button className="button-primary justify-self-start" disabled={profile.processing}><Save size={17} /> Save profile</button>
                        </form>
                    </section>
                    <section className="panel p-6 sm:p-8">
                        <div className="flex items-start gap-3"><KeyRound className="mt-1 text-race-red" /><div><p className="section-kicker">Security protocol</p><h2 className="section-title">Change telemetry key</h2></div></div>
                        <form onSubmit={updatePassword} className="mt-7 grid gap-5">
                            <FormField label="Current key" error={password.errors.current_password}><input className="field" type="password" autoComplete="current-password" value={password.data.current_password} onChange={(e) => password.setData('current_password', e.target.value)} required /></FormField>
                            <div className="grid gap-5 sm:grid-cols-2">
                                <FormField label="New key" error={password.errors.password}><input className="field" type="password" autoComplete="new-password" value={password.data.password} onChange={(e) => password.setData('password', e.target.value)} required /></FormField>
                                <FormField label="Confirm key" error={password.errors.password_confirmation}><input className="field" type="password" autoComplete="new-password" value={password.data.password_confirmation} onChange={(e) => password.setData('password_confirmation', e.target.value)} required /></FormField>
                            </div>
                            <button className="button-ghost justify-self-start" disabled={password.processing}><ShieldCheck size={17} /> Update key</button>
                        </form>
                    </section>
                </div>
                <aside className="grid content-start gap-6">
                    <div className="grid grid-cols-2 gap-3">
                        <div className="metric-card"><span className="metric-label">Grands Prix</span><strong className="mt-6 block font-display text-4xl font-black italic">{stats.totalRaces}</strong></div>
                        <div className="metric-card"><span className="metric-label">Avg. accuracy</span><strong className="mt-6 block font-display text-4xl font-black italic text-cyan">{stats.averageAccuracy}%</strong></div>
                    </div>
                    <section className="panel overflow-hidden">
                        <div className="border-b border-white/10 p-6"><p className="section-kicker">Recent telemetry</p><h2 className="section-title">Last five races</h2></div>
                        {history.length ? <div className="divide-y divide-white/5">{history.map((race) => <div key={race.id} className="grid grid-cols-[1fr_auto] gap-3 px-6 py-4"><div><span className={`difficulty difficulty-${race.difficulty}`}>{race.difficulty}</span><time className="mt-2 block text-xs text-slate-500">{new Date(race.completed_at).toLocaleString()}</time></div><div className="text-right"><strong className="font-display text-xl italic">+{race.points_awarded}</strong><span className="block text-xs text-slate-500">{race.accuracy}% accuracy</span></div></div>)}</div> : <p className="p-6 text-sm text-slate-500">No completed races yet. Your telemetry will appear here.</p>}
                    </section>
                </aside>
            </div>
        </AppLayout>
    );
}
