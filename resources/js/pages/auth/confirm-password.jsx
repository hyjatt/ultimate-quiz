import { useForm } from '@inertiajs/react';
import FormField from '../../components/FormField';
import AuthLayout from '../../layouts/AuthLayout';

export default function ConfirmPassword() {
    const form = useForm({ password: '' });
    return (
        <AuthLayout title="Confirm access" eyebrow="Restricted operation" description="Re-enter your telemetry key to continue.">
            <form onSubmit={(e) => { e.preventDefault(); form.post('/user/confirm-password', { onFinish: () => form.reset('password') }); }} className="grid gap-5">
                <FormField label="Telemetry key" error={form.errors.password}><input className="field" type="password" autoFocus autoComplete="current-password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required /></FormField>
                <button className="button-primary button-large w-full" disabled={form.processing}>Confirm identity</button>
            </form>
        </AuthLayout>
    );
}
