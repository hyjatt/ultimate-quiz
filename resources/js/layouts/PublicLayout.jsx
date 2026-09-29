import { Link, usePage } from '@inertiajs/react';
import Brand from '../components/Brand';

export default function PublicLayout({ children }) {
    const user = usePage().props.auth?.user;

    return (
        <div className="min-h-screen bg-grid text-white">
            <header className="border-b border-white/10 bg-ink/90 backdrop-blur">
                <div className="shell flex h-20 items-center justify-between">
                    <Brand />
                    <nav className="flex items-center gap-3" aria-label="Primary navigation">
                        {user ? (
                            <Link href="/dashboard" className="button-primary">Pit wall</Link>
                        ) : (
                            <>
                                <Link href="/login" className="button-ghost">Log in</Link>
                                <Link href="/register" className="button-primary">Join grid</Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>
            {children}
        </div>
    );
}
