import { Link, useForm } from '@inertiajs/react';
import FormField from '../../components/FormField';
import AuthLayout from '../../layouts/AuthLayout';

export default function Login({ canResetPassword, status }) {
    const form = useForm({ login: '', password: '', remember: false });

    function submit(event) {
        event.preventDefault();
        form.post('/login', { onFinish: () => form.reset('password') });
    }

    return (
        <AuthLayout title="Driver login" eyebrow="Pit wall access" description="Identify yourself to reconnect with your race engineer.">
            {status && <div className="notice-success">{status}</div>}
            <form onSubmit={submit} className="grid gap-5">
                <FormField label="Email or driver name" error={form.errors.login}>
                    <input className="field" name="login" autoComplete="username" autoFocus value={form.data.login} onChange={(e) => form.setData('login', e.target.value)} required />
                </FormField>
                <FormField label="Telemetry key" error={form.errors.password}>
                    <input className="field" type="password" name="password" autoComplete="current-password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required />
                </FormField>
                <label className="flex items-center gap-3 text-sm text-slate-400"><input type="checkbox" className="h-4 w-4 accent-red-500" checked={form.data.remember} onChange={(e) => form.setData('remember', e.target.checked)} /> Keep radio contact active</label>
                <button className="button-primary button-large mt-2 w-full" disabled={form.processing}>Enter pit wall</button>
            </form>
            <div className="mt-6 flex flex-wrap justify-between gap-3 text-sm text-slate-400">
                {canResetPassword && <Link href="/forgot-password" className="text-link">Lost your key?</Link>}
                <span>New driver? <Link href="/register" className="text-link">Join the grid</Link></span>
            </div>
        </AuthLayout>
    );
}
