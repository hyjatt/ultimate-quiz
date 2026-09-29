import { useForm, usePage } from '@inertiajs/react';
import { Save } from 'lucide-react';
import FieldError from '../../../components/admin/FieldError';
import StatusBadge from '../../../components/admin/StatusBadge';
import AdminLayout from '../../../layouts/AdminLayout';

function AdministratorRow({ administrator, currentUserId }) {
    const form = useForm({ role: administrator.role, suspended: Boolean(administrator.suspended_at), suspension_reason: administrator.suspension_reason ?? '' });
    const self = administrator.id === currentUserId;
    const save = (event) => { event.preventDefault(); form.put(`/admin/administrators/${administrator.id}`, { preserveScroll: true }); };
    return <form onSubmit={save} className="grid gap-3 border-b border-white/7 p-5 xl:grid-cols-[1.1fr_11rem_9rem_1fr_auto] xl:items-start"><div><p className="font-bold">{administrator.username}{self && <span className="ml-2 text-xs text-cyan">YOU</span>}</p><p className="text-sm text-slate-500">{administrator.email}</p></div><select className="field" value={form.data.role} disabled={self} onChange={(e) => form.setData('role', e.target.value)}><option value="admin">Admin</option><option value="superadmin">Superadmin</option></select><label className="flex min-h-12 items-center gap-2 text-sm"><input type="checkbox" checked={form.data.suspended} disabled={self} onChange={(e) => form.setData('suspended', e.target.checked)} /><StatusBadge active={!form.data.suspended}>{form.data.suspended ? 'Suspended' : 'Active'}</StatusBadge></label><div><input className="field" value={form.data.suspension_reason} disabled={!form.data.suspended || self} onChange={(e) => form.setData('suspension_reason', e.target.value)} placeholder="Suspension reason" /><FieldError error={form.errors.suspension_reason} /></div><button disabled={self || form.processing} className="icon-button" aria-label={`Save ${administrator.username}`}><Save size={16} /></button></form>;
}

export default function AdministratorsIndex({ administrators }) {
    const { auth } = usePage().props;
    return <AdminLayout title="Administrators"><div className="mb-5 border-l-4 border-cyan bg-cyan/8 p-4 text-sm text-slate-300">New administrator accounts are created securely on the server with <code className="text-cyan">php artisan admin:create</code>.</div><section className="admin-panel overflow-hidden"><div className="hidden gap-3 border-b border-white/10 px-5 py-3 font-mono text-[10px] uppercase tracking-widest text-slate-600 xl:grid xl:grid-cols-[1.1fr_11rem_9rem_1fr_auto]"><span>Account</span><span>Role</span><span>Status</span><span>Reason</span><span>Save</span></div>{administrators.map((administrator) => <AdministratorRow key={administrator.id} administrator={administrator} currentUserId={auth.user.id} />)}</section></AdminLayout>;
}
