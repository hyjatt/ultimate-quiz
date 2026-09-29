import { router, useForm } from '@inertiajs/react';
import { Plus, Save, Trash2 } from 'lucide-react';
import FieldError from '../../../components/admin/FieldError';
import AdminLayout from '../../../layouts/AdminLayout';

function RankRow({ rank }) {
    const form = useForm({ name: rank.name, min_points: rank.min_points });
    const save = (event) => { event.preventDefault(); form.put(`/admin/ranks/${rank.id}`, { preserveScroll: true }); };
    const remove = () => { if (window.confirm('Delete this rank threshold?')) router.delete(`/admin/ranks/${rank.id}`, { preserveScroll: true }); };
    return <form onSubmit={save} className="grid gap-3 border-b border-white/7 p-4 sm:grid-cols-[1fr_12rem_auto] sm:items-start"><label><span className="sr-only">Rank name</span><input className="field" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} /><FieldError error={form.errors.name} /></label><label><span className="sr-only">Minimum points</span><input type="number" min="0" className="field" value={form.data.min_points} onChange={(e) => form.setData('min_points', e.target.value)} /><FieldError error={form.errors.min_points} /></label><div className="flex gap-2"><button className="icon-button" aria-label={`Save ${rank.name}`}><Save size={16} /></button>{rank.min_points !== 0 && <button type="button" onClick={remove} className="icon-button text-red-300" aria-label={`Delete ${rank.name}`}><Trash2 size={16} /></button>}</div></form>;
}

export default function RanksIndex({ ranks }) {
    const create = useForm({ name: '', min_points: '' });
    const submit = (event) => { event.preventDefault(); create.post('/admin/ranks', { preserveScroll: true, onSuccess: () => create.reset() }); };
    return <AdminLayout title="Driver ranks"><p className="mb-5 max-w-2xl text-sm text-slate-400">Ranks are calculated live from career points. The baseline rank must remain at zero points.</p><form onSubmit={submit} className="admin-panel mb-6 grid gap-3 p-5 sm:grid-cols-[1fr_12rem_auto] sm:items-start"><label><span className="admin-field-label">New rank</span><input className="field mt-2" value={create.data.name} onChange={(e) => create.setData('name', e.target.value)} placeholder="Rank name" /><FieldError error={create.errors.name} /></label><label><span className="admin-field-label">Minimum points</span><input type="number" min="0" className="field mt-2" value={create.data.min_points} onChange={(e) => create.setData('min_points', e.target.value)} /><FieldError error={create.errors.min_points} /></label><button className="button-primary sm:mt-6"><Plus size={16} /> Add rank</button></form><section className="admin-panel overflow-hidden"><div className="grid gap-3 border-b border-white/10 px-4 py-3 font-mono text-[10px] uppercase tracking-widest text-slate-600 sm:grid-cols-[1fr_12rem_auto]"><span>Rank</span><span>Threshold</span><span>Actions</span></div>{ranks.map((rank) => <RankRow key={rank.id} rank={rank} />)}</section></AdminLayout>;
}
