import { Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import FieldError from '../../../components/admin/FieldError';
import AdminLayout from '../../../layouts/AdminLayout';

export default function PlayerEdit({ player, teams }) {
    const form = useForm({ username: player.username, email: player.email, team_id: player.team_id ?? '' });
    const submit = (event) => { event.preventDefault(); form.put(`/admin/players/${player.id}`); };
    return <AdminLayout title={`Edit ${player.username}`} actions={<Link href={`/admin/players/${player.id}`} className="button-ghost">Cancel</Link>}><form onSubmit={submit} className="admin-panel max-w-2xl p-6 sm:p-8"><div className="grid gap-5"><label className="admin-field-label">Username<input className="field mt-2" value={form.data.username} onChange={(e) => form.setData('username', e.target.value)} /><FieldError error={form.errors.username} /></label><label className="admin-field-label">Email<input type="email" className="field mt-2" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} /><FieldError error={form.errors.email} /></label><label className="admin-field-label">Team<select className="field mt-2" value={form.data.team_id} onChange={(e) => form.setData('team_id', e.target.value)}>{teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}</select><FieldError error={form.errors.team_id} /></label></div><button disabled={form.processing} className="button-primary mt-7"><Save size={16} /> Save player</button></form></AdminLayout>;
}
