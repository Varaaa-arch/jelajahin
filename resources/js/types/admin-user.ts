export interface AdminUser {
    id: string;
    name: string;
    email: string;
    initial: string;
    avatar?: string | null;
    provider?: string | null;
    role: string;
    status: string;
    verified: boolean;
    bookings_count: number;
    joined_at?: string | null;
    is_self: boolean;
}

export interface AdminUserFilters {
    q: string;
    role: string;
    status: string;
    sort: string;
    per_page: number;
}

export interface PaginatedUsers {
    data: AdminUser[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
}

export const USER_ROLE_META: Record<string, { label: string; classes: string }> = {
    admin: { label: 'Admin', classes: 'bg-teal/15 text-teal-dark' },
    user: { label: 'Customer', classes: 'bg-slate-100 text-slate-600' },
};

export const USER_STATUS_META: Record<string, { label: string; dot: string; classes: string }> = {
    active: { label: 'Active', dot: 'bg-emerald-600', classes: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    inactive: { label: 'Inactive', dot: 'bg-slate-500', classes: 'bg-slate-100 text-slate-600 border-slate-200' },
    suspended: { label: 'Suspended', dot: 'bg-red-600', classes: 'bg-red-50 text-red-700 border-red-200' },
};

export function roleMeta(role: string): { label: string; classes: string } {
    return USER_ROLE_META[role] ?? { label: role, classes: 'bg-slate-100 text-slate-600' };
}

export function userStatusMeta(status: string): { label: string; dot: string; classes: string } {
    return USER_STATUS_META[status] ?? { label: status, dot: 'bg-slate-500', classes: 'bg-slate-100 text-slate-600 border-slate-200' };
}

export function formatJoinDate(iso?: string | null): string {
    if (!iso) return '-';
    const d = new Date(`${iso}T00:00:00`);
    if (Number.isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
