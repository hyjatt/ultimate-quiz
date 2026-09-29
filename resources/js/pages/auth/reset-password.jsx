import { useForm } from '@inertiajs/react';
import FormField from '../../components/FormField';
import AuthLayout from '../../layouts/AuthLayout';

export default function ResetPassword({ token, email }) {
    const form = useForm({ token, email, password: '', password_confirmation: '' });
    return (
        <AuthLayout title="Set a new key" eyebrow="Security protocol" description="Choose a new telemetry key for your driver account.">
            <form onSubmit={(e) => { e.preventDefault(); form.post('/reset-password', { onFinish: () => form.reset('password', 'password_confirmation') }); }} className="grid gap-5">
                <FormField label="Email channel" error={form.errors.email}><input className="field" type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required /></FormField>
                <FormField label="New telemetry key" error={form.errors.password}><input className="field" type="password" autoFocus autoComplete="new-password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required /></FormField>
                <FormField label="Confirm key" error={form.errors.password_confirmation}><input className="field" type="password" autoComplete="new-password" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} required /></FormField>
                <button className="button-primary button-large w-full" disabled={form.processing}>Reset telemetry key</button>
            </form>
        </AuthLayout>
    );
}
