import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../layouts/AuthLayout';

export default function VerifyEmail({ status }) {
    const form = useForm({});
    return (
        <AuthLayout title="Verify your radio" eyebrow="Email confirmation" description="We sent a verification link to your email. Confirm it before entering the championship.">
            {status === 'verification-link-sent' && <div className="notice-success">A fresh verification link is on its way.</div>}
            <button onClick={() => form.post('/email/verification-notification')} className="button-primary button-large w-full" disabled={form.processing}>Resend verification email</button>
            <Link href="/logout" method="post" as="button" className="button-ghost mt-4 w-full">Log out</Link>
        </AuthLayout>
    );
}
