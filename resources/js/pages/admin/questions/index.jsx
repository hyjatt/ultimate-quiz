import { Link, router } from '@inertiajs/react';
import { Download, FileUp, Plus, Search } from 'lucide-react';
import { useState } from 'react';
import Pagination from '../../../components/admin/Pagination';
import StatusBadge from '../../../components/admin/StatusBadge';
import AdminLayout from '../../../layouts/AdminLayout';

export default function QuestionsIndex({ questions, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [difficulty, setDifficulty] = useState(filters.difficulty ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const submit = (event) => {
        event.preventDefault();
        router.get('/admin/questions', { search, difficulty, status }, { preserveState: true, replace: true });
    };
    const exportUrl = `/admin/questions/export?${new URLSearchParams({ search, difficulty, status }).toString()}`;

    return (
        <AdminLayout title="Question bank" actions={<Link href="/admin/questions/create" className="button-primary"><Plus size={16} /> New question</Link>}>
            <div className="mb-5 flex flex-wrap gap-3"><Link href="/admin/questions/import" className="button-ghost"><FileUp size={16} /> Import CSV</Link><a href={exportUrl} className="button-ghost"><Download size={16} /> Export CSV</a></div>
            <form onSubmit={submit} className="admin-filter-bar">
                <label className="relative grow"><Search className="absolute left-3 top-3.5 text-slate-600" size={16} /><input className="field pl-10" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search prompts" /></label>
                <select className="field sm:w-44" value={difficulty} onChange={(e) => setDifficulty(e.target.value)}><option value="">All difficulties</option><option value="easy">Easy</option><option value="medium">Medium</option><option value="hard">Hard</option></select>
                <select className="field sm:w-40" value={status} onChange={(e) => setStatus(e.target.value)}><option value="">All states</option><option value="active">Active</option><option value="archived">Archived</option></select>
                <button className="button-primary">Filter</button>
            </form>
            <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Question</th><th>Difficulty</th><th>Usage</th><th>Status</th><th /></tr></thead><tbody>
                {questions.data.map((question) => <tr key={question.id}><td><p className="max-w-2xl font-semibold">{question.prompt}</p>{question.revises_question_id && <small className="text-slate-600">Revision of #{question.revises_question_id}</small>}</td><td className="uppercase">{question.difficulty}</td><td>{question.attempt_questions_count}</td><td><StatusBadge active={question.is_active} /></td><td className="text-right"><Link href={`/admin/questions/${question.id}/edit`} className="text-link text-xs uppercase">Edit</Link></td></tr>)}
                {!questions.data.length && <tr><td colSpan="5" className="py-12 text-center text-slate-500">No questions match these filters.</td></tr>}
            </tbody></table></div>
            <Pagination links={questions.links} />
        </AdminLayout>
    );
}
