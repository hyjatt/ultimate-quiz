import { fireEvent, render, screen } from '@testing-library/react';
import React from 'react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import AdminLayout from './AdminLayout';

let role = 'admin';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children, href, method: _method, as: _as, prefetch: _prefetch, ...props }) => <a href={href} {...props}>{children}</a>,
    usePage: () => ({ props: { auth: { user: { id: 1, username: 'RaceDirector', role } }, flash: {}, errors: {} } }),
}));

describe('Admin layout', () => {
    afterEach(() => {
        role = 'admin';
    });

    it('shows the operational navigation and responsive drawer controls', () => {
        render(<AdminLayout title="Control tower"><p>Telemetry ready</p></AdminLayout>);

        expect(screen.getByRole('heading', { name: 'Control tower' })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /questions/i })).toHaveAttribute('href', '/admin/questions');
        expect(screen.queryByRole('link', { name: /administrators/i })).not.toBeInTheDocument();
        fireEvent.click(screen.getByRole('button', { name: /open navigation/i }));
        expect(screen.getByRole('button', { name: /^close navigation$/i })).toBeInTheDocument();
    });

    it('shows administrator management only to superadmins', () => {
        role = 'superadmin';

        render(<AdminLayout title="Control tower"><p>Telemetry ready</p></AdminLayout>);

        expect(screen.getByRole('link', { name: /administrators/i })).toHaveAttribute('href', '/admin/administrators');
    });
});
