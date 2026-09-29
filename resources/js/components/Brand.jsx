import { Link } from '@inertiajs/react';

export default function Brand({ compact = false }) {
    return (
        <Link href="/" className="group inline-flex items-center gap-3" aria-label="F1Quiz home">
            <span className="grid h-10 w-10 place-items-center bg-race-red font-display text-sm font-black italic text-white shadow-[5px_5px_0_#40f1d2] transition-transform group-hover:-translate-y-0.5">
                F1
            </span>
            {!compact && (
                <span className="font-display text-xl font-black uppercase italic tracking-[-0.04em] text-white">
                    Quiz<span className="text-race-red">Grid</span>
                </span>
            )}
        </Link>
    );
}
