import { Link, useForm } from '@inertiajs/react';
import { FileCheck2, Upload } from 'lucide-react';
import FieldError from '../../../components/admin/FieldError';
import AdminLayout from '../../../layouts/AdminLayout';

export default function QuestionImport({ preview, token }) {
    const upload = useForm({ file: null });
    const apply = useForm({ token });
    const submitPreview = (event) => { event.preventDefault(); upload.post('/admin/questions/import/preview', { forceFormData: true }); };
    const submitImport = () => apply.post('/admin/questions/import');

    return (
        <AdminLayout title="Import questions" actions={<Link href="/admin/questions" className="button-ghost">Back to bank</Link>}>
            <section className="admin-panel max-w-4xl p-6 sm:p-8">
                <h2 className="admin-section-title">CSV preview</h2>
                <p className="mt-2 text-sm text-slate-400">Required columns: prompt, difficulty, option_a, option_b, option_c, option_d, correct_option, is_active. Invalid rows block the import; duplicates are skipped.</p>
                <form onSubmit={submitPreview} className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-start"><label className="grow"><input type="file" accept=".csv,text/csv" className="field file:mr-4 file:border-0 file:bg-transparent file:text-sm file:font-bold file:text-cyan" onChange={(e) => upload.setData('file', e.target.files[0])} /><FieldError error={upload.errors.file} /></label><button disabled={upload.processing} className="button-primary"><Upload size={16} /> Preview</button></form>
                {upload.progress && <div className="mt-3 h-1 bg-white/10"><div className="h-full bg-cyan" style={{ width: `${upload.progress.percentage}%` }} /></div>}
            </section>
            {preview && <section className="mt-6"><div className="grid gap-3 sm:grid-cols-3"><div className="admin-metric"><span className="metric-label">New</span><p className="mt-3 font-display text-3xl font-black text-cyan">{preview.new_count}</p></div><div className="admin-metric"><span className="metric-label">Duplicates</span><p className="mt-3 font-display text-3xl font-black">{preview.duplicate_count}</p></div><div className="admin-metric"><span className="metric-label">Invalid</span><p className="mt-3 font-display text-3xl font-black text-race-red">{preview.invalid_count}</p></div></div>
                <div className="admin-table-wrap mt-5"><table className="admin-table"><thead><tr><th>Line</th><th>Prompt</th><th>Difficulty</th><th>Result</th></tr></thead><tbody>{preview.rows.map((row) => <tr key={row.line}><td>{row.line}</td><td>{row.data.prompt ?? 'Malformed row'}{row.errors.length > 0 && <ul className="mt-1 text-xs text-red-300">{row.errors.map((error) => <li key={error}>{error}</li>)}</ul>}</td><td>{row.data.difficulty ?? '—'}</td><td className="uppercase">{row.status}</td></tr>)}</tbody></table></div>
                <button onClick={submitImport} disabled={!preview.can_apply || apply.processing} className="button-primary mt-5"><FileCheck2 size={16} /> Import {preview.new_count} questions</button>
            </section>}
        </AdminLayout>
    );
}
