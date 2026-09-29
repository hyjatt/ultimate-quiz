import { Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import Pagination from '../../../components/admin/Pagination';
import StatusBadge from '../../../components/admin/StatusBadge';
import AdminLayout from '../../../layouts/AdminLayout';

export default function PlayersIndex({ players, teams, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [teamId, setTeamId] = useState(filters.team_id ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const filter = (event) => { event.preventDefault(); router.get('/admin/players', { search, team_id: teamId, status }, { preserveState: true, replace: true }); };

    return <AdminLayout title="Players"><form onSubmit={filter} className="admin-filter-bar"><label className="relative grow"><Search className="absolute left-3 top-3.5 text-slate-600" size={16} /><input className="field pl-10" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Username or email" /></label><select className="field sm:w-48" value={teamId} onChange={(e) => setTeamId(e.target.value)}><option value="">All teams</option>{teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}</select><select className="field sm:w-40" value={status} onChange={(e) => setStatus(e.target.value)}><option value="">All states</option><option value="active">Active</option><option value="suspended">Suspended</option></select><button className="button-primary">Filter</button></form><div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Driver</th><th>Team</th><th>Points</th><th>Attempts</th><th>Status</th><th /></tr></thead><tbody>{players.data.map((player) => <tr key={player.id}><td><p className="font-bold">{player.username}</p><small className="text-slate-500">{player.email}</small></td><td>{player.team?.name ?? 'Independent'}</td><td>{player.points}</td><td>{player.quiz_attempts_count}</td><td><StatusBadge active={!player.suspended_at}>{player.suspended_at ? 'Suspended' : 'Active'}</StatusBadge></td><td className="text-right"><Link href={`/admin/players/${player.id}`} className="text-link text-xs uppercase">Inspect</Link></td></tr>)}{!players.data.length && <tr><td colSpan="6" className="py-12 text-center text-slate-500">No players match these filters.</td></tr>}</tbody></table></div><Pagination links={players.links} /></AdminLayout>;
}
