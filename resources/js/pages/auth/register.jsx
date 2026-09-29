import { Link, useForm } from '@inertiajs/react';
import FormField from '../../components/FormField';
import AuthLayout from '../../layouts/AuthLayout';

export default function Register({ teams }) {
    const form = useForm({ username: '', email: '', team_id: '', password: '', password_confirmation: '' });

    function submit(event) {
        event.preventDefault();
        form.post('/register', { onFinish: () => form.reset('password', 'password_confirmation') });
    }

    return (
        <AuthLayout title="Join the grid" eyebrow="Super licence application" description="Create your driver identity and choose the constructor you race for.">
            <form onSubmit={submit} className="grid gap-5">
                <FormField label="Driver name" error={form.errors.username} hint="Letters, numbers, dashes, and underscores only.">
                    <input className="field" autoComplete="username" autoFocus value={form.data.username} onChange={(e) => form.setData('username', e.target.value)} required />
                </FormField>
                <FormField label="Email channel" error={form.errors.email}>
                    <input className="field" type="email" autoComplete="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required />
                </FormField>
                <FormField label="Constructor" error={form.errors.team_id}>
                    <select className="field" value={form.data.team_id} onChange={(e) => form.setData('team_id', e.target.value)} required>
                        <option value="">Choose your team</option>
                        {teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}
                    </select>
                </FormField>
                <div className="grid gap-5 sm:grid-cols-2">
                    <FormField label="Telemetry key" error={form.errors.password}><input className="field" type="password" autoComplete="new-password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required /></FormField>
                    <FormField label="Confirm key" error={form.errors.password_confirmation}><input className="field" type="password" autoComplete="new-password" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} required /></FormField>
                </div>
                <button className="button-primary button-large mt-2 w-full" disabled={form.processing}>Issue super licence</button>
            </form>
            <p className="mt-6 text-sm text-slate-400">Already registered? <Link href="/login" className="text-link">Return to login</Link></p>
        </AuthLayout>
    );
}
