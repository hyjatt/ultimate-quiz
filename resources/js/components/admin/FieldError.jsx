export default function FieldError({ error }) {
    return error ? <p className="mt-1 text-xs font-semibold text-red-300">{error}</p> : null;
}
