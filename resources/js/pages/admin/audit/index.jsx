import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import Pagination from '../../../components/admin/Pagination';
import AdminLayout from '../../../layouts/AdminLayout';

export default function AuditIndex({ logs, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const submit = (event) => { event.preventDefault(); router.get('/admin/audit-logs', { search }, { preserveState: true, replace: true }); };
    return <AdminLayout title="Audit log"><form onSubmit={submit} className="admin-filter-bar"><label className="relative grow"><Search className="absolute left-3 top-3.5 text-slate-600" size={16} /><input className="field pl-10" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Action or administrator" /></label><button className="button-primary">Filter</button></form><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Time</th><th>Administrator</th><th>Action</th><th>Subject</th><th>IP</th><th>Changes</th></tr></thead><tbody>{logs.data.map((log) => <tr key={log.id}><td className="whitespace-nowrap">{new Date(log.created_at).toLocaleString()}</td><td>{log.actor?.username ?? 'System'}</td><td className="font-semibold">{log.action}</td><td>{log.subject_type ? `${log.subject_type.split('\\').pop()} #${log.subject_id}` : 'Batch operation'}</td><td className="font-mono text-xs">{log.ip_address ?? '—'}</td><td><details><summary className="cursor-pointer text-xs text-cyan">Inspect</summary><pre className="mt-2 max-w-md overflow-auto whitespace-pre-wrap text-[10px] text-slate-400">{JSON.stringify({ before: log.before, after: log.after }, null, 2)}</pre></details></td></tr>)}{!logs.data.length && <tr><td colSpan="6" className="py-12 text-center text-slate-500">No audit entries match.</td></tr>}</tbody></table></div><Pagination links={logs.links} /></AdminLayout>;
}
