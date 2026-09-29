import { router, useForm } from '@inertiajs/react';
import { Plus, Save, Trash2 } from 'lucide-react';
import FieldError from '../../../components/admin/FieldError';
import StatusBadge from '../../../components/admin/StatusBadge';
import AdminLayout from '../../../layouts/AdminLayout';

function TeamRow({ team }) {
    const form = useForm({ name: team.name, color: team.color, is_active: team.is_active });
    const save = (event) => { event.preventDefault(); form.put(`/admin/teams/${team.id}`, { preserveScroll: true }); };
    const remove = () => { if (window.confirm('Delete this unused team?')) router.delete(`/admin/teams/${team.id}`, { preserveScroll: true }); };
    return <form onSubmit={save} className="grid gap-3 border-b border-white/7 p-4 lg:grid-cols-[1fr_10rem_8rem_auto] lg:items-start"><label><span className="sr-only">Name</span><input className="field" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} /><FieldError error={form.errors.name} /></label><label><span className="sr-only">Color</span><input type="color" className="field h-[3.15rem] p-2" value={form.data.color} onChange={(e) => form.setData('color', e.target.value)} /></label><label className="flex min-h-12 items-center gap-2 text-sm"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} /> <StatusBadge active={form.data.is_active} /></label><div className="flex gap-2"><button className="icon-button" aria-label={`Save ${team.name}`}><Save size={16} /></button>{team.users_count === 0 && <button type="button" onClick={remove} className="icon-button text-red-300" aria-label={`Delete ${team.name}`}><Trash2 size={16} /></button>}</div></form>;
}

export default function TeamsIndex({ teams }) {
    const create = useForm({ name: '', color: '#40F1D2', is_active: true });
    const submit = (event) => { event.preventDefault(); create.post('/admin/teams', { preserveScroll: true, onSuccess: () => create.reset('name') }); };
    return <AdminLayout title="Teams"><form onSubmit={submit} className="admin-panel mb-6 grid gap-3 p-5 lg:grid-cols-[1fr_10rem_auto] lg:items-start"><label><span className="admin-field-label">New team</span><input className="field mt-2" value={create.data.name} onChange={(e) => create.setData('name', e.target.value)} placeholder="Constructor name" /><FieldError error={create.errors.name} /></label><label><span className="admin-field-label">Color</span><input type="color" className="field mt-2 h-[3.15rem] p-2" value={create.data.color} onChange={(e) => create.setData('color', e.target.value)} /></label><button className="button-primary lg:mt-6"><Plus size={16} /> Add team</button></form><section className="admin-panel overflow-hidden"><div className="grid gap-3 border-b border-white/10 px-4 py-3 font-mono text-[10px] uppercase tracking-widest text-slate-600 lg:grid-cols-[1fr_10rem_8rem_auto]"><span>Team</span><span>Color</span><span>Status</span><span>Actions</span></div>{teams.map((team) => <TeamRow key={team.id} team={team} />)}</section></AdminLayout>;
}
