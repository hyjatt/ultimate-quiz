export default function FormField({ label, error, children, hint }) {
    return (
        <label className="grid gap-2 text-sm font-bold uppercase tracking-[0.12em] text-slate-300">
            <span>{label}</span>
            {children}
            {hint && <span className="text-xs font-normal normal-case tracking-normal text-slate-500">{hint}</span>}
            {error && <span className="text-xs font-semibold normal-case tracking-normal text-red-400">{error}</span>}
        </label>
    );
}
