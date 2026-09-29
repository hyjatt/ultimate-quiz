import { Link } from '@inertiajs/react';

function label(value) {
    return value.replace('&laquo;', '‹').replace('&raquo;', '›');
}

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    return (
        <nav className="mt-6 flex flex-wrap gap-2" aria-label="Pagination">
            {links.map((link, index) => link.url ? (
                <Link key={`${link.label}-${index}`} href={link.url} preserveScroll className={`pagination-link ${link.active ? 'pagination-link-active' : ''}`}>{label(link.label)}</Link>
            ) : (
                <span key={`${link.label}-${index}`} className="pagination-link opacity-35">{label(link.label)}</span>
            ))}
        </nav>
    );
}
