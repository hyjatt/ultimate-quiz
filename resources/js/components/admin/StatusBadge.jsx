export default function StatusBadge({ active, children }) {
    return <span className={`status-badge ${active ? 'status-badge-active' : 'status-badge-muted'}`}>{children ?? (active ? 'Active' : 'Archived')}</span>;
}
