import { Link, router, useForm } from '@inertiajs/react';
import { Save, Trash2 } from 'lucide-react';
import FieldError from '../../../components/admin/FieldError';
import StatusBadge from '../../../components/admin/StatusBadge';
import AdminLayout from '../../../layouts/AdminLayout';

export default function QuestionForm({ question = null, revisions = [] }) {
    const editing = Boolean(question);
    const form = useForm({
        prompt: question?.prompt ?? '',
        difficulty: question?.difficulty ?? 'easy',
        option_a: question?.options?.a ?? '',
        option_b: question?.options?.b ?? '',
        option_c: question?.options?.c ?? '',
        option_d: question?.options?.d ?? '',
        correct_option: question?.correct_option ?? 'a',
        is_active: question?.is_active ?? true,
    });
    const submit = (event) => {
        event.preventDefault();
        if (editing) form.put(`/admin/questions/${question.id}`);
        else form.post('/admin/questions');
    };
    const remove = () => {
        if (window.confirm('Delete this unused question permanently?')) router.delete(`/admin/questions/${question.id}`);
    };

    return (
        <AdminLayout title={editing ? `Question #${question.id}` : 'New question'} actions={<Link href="/admin/questions" className="button-ghost">Back to bank</Link>}>
            {editing && question.attempt_questions_count > 0 && <div className="mb-5 border-l-4 border-cyan bg-cyan/8 p-4 text-sm text-cyan">This question has been used {question.attempt_questions_count} times. Saving creates a new revision and archives this version.</div>}
            <form onSubmit={submit} className="admin-panel max-w-4xl p-5 sm:p-8">
                <label className="admin-field-label">Prompt<textarea className="field mt-2 min-h-32" value={form.data.prompt} onChange={(e) => form.setData('prompt', e.target.value)} /></label><FieldError error={form.errors.prompt} />
                <div className="mt-6 grid gap-5 sm:grid-cols-2">
                    <label className="admin-field-label">Difficulty<select className="field mt-2" value={form.data.difficulty} onChange={(e) => form.setData('difficulty', e.target.value)}><option value="easy">Easy</option><option value="medium">Medium</option><option value="hard">Hard</option></select></label>
                    <label className="admin-field-label">Correct option<select className="field mt-2" value={form.data.correct_option} onChange={(e) => form.setData('correct_option', e.target.value)}>{['a', 'b', 'c', 'd'].map((option) => <option key={option} value={option}>Option {option.toUpperCase()}</option>)}</select></label>
                </div>
                <div className="mt-6 grid gap-4 sm:grid-cols-2">{['a', 'b', 'c', 'd'].map((option) => <label key={option} className="admin-field-label">Option {option.toUpperCase()}<input className="field mt-2" value={form.data[`option_${option}`]} onChange={(e) => form.setData(`option_${option}`, e.target.value)} /><FieldError error={form.errors[`option_${option}`]} /></label>)}</div>
                <label className="mt-6 flex items-center gap-3 text-sm font-bold"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} className="h-5 w-5 accent-[#40f1d2]" /> Active in quiz selection</label>
                <div className="mt-8 flex flex-wrap gap-3"><button disabled={form.processing} className="button-primary"><Save size={16} /> {form.processing ? 'Saving…' : 'Save question'}</button>{editing && question.attempt_questions_count === 0 && <button type="button" onClick={remove} className="button-danger"><Trash2 size={16} /> Delete</button>}</div>
            </form>
            {editing && (question.revised_from || revisions.length > 0) && <section className="admin-panel mt-6 max-w-4xl p-6"><h2 className="admin-section-title">Revision chain</h2><div className="mt-4 grid gap-3">{question.revised_from && <Link className="flex items-center justify-between border border-white/8 p-3 hover:border-cyan/40" href={`/admin/questions/${question.revises_question_id}/edit`}><span>Previous: {question.revised_from.prompt}</span><StatusBadge active={false}>Previous</StatusBadge></Link>}{revisions.map((revision) => <Link key={revision.id} className="flex items-center justify-between border border-white/8 p-3 hover:border-cyan/40" href={`/admin/questions/${revision.id}/edit`}><span>Revision #{revision.id}: {revision.prompt}</span><StatusBadge active={revision.is_active} /></Link>)}</div></section>}
        </AdminLayout>
    );
}
