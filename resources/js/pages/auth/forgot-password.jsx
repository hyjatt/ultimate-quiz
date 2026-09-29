import { Link, useForm } from '@inertiajs/react';
import FormField from '../../components/FormField';
import AuthLayout from '../../layouts/AuthLayout';

export default function ForgotPassword({ status }) {
    const form = useForm({ email: '' });
    return (
        <AuthLayout title="Recover access" eyebrow="Radio recovery" description="Enter your registered email and race control will send a reset link.">
            {status && <div className="notice-success">{status}</div>}
            <form onSubmit={(e) => { e.preventDefault(); form.post('/forgot-password'); }} className="grid gap-5">
                <FormField label="Email channel" error={form.errors.email}><input className="field" type="email" autoComplete="email" autoFocus value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required /></FormField>
                <button className="button-primary button-large w-full" disabled={form.processing}>Send recovery link</button>
            </form>
            <p className="mt-6 text-sm"><Link href="/login" className="text-link">Return to driver login</Link></p>
        </AuthLayout>
    );
}
