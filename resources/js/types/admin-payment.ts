import { formatIDR } from '@/types/admin-order';

export { formatIDR };

export type AdminPaymentStatusFilter = 'all' | 'paid' | 'pending' | 'failed' | 'refunded';

export interface AdminPayment {
    id: string;
    transaction_id: string;
    pnr: string;
    booking_id: string;
    booker_name: string;
    booker_email: string;
    origin_code?: string | null;
    destination_code?: string | null;
    amount: number;
    payment_method?: string | null;
    status: string;
    paid_at?: string | null;
    expires_at?: string | null;
    created_at?: string | null;
}

export interface AdminPaymentFilters {
    q: string;
    status: string;
    method: string;
    date_from?: string | null;
    date_to?: string | null;
    sort: string;
    per_page: number;
    recon_date?: string | null;
}

export interface PaginatedPayments {
    data: AdminPayment[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
}

export interface PaymentStats {
    revenue: number;
    revenue_growth_pct: number | null;
    pending_count: number;
    pending_amount: number;
    failed_count: number;
    failed_amount: number;
    refunded_count: number;
    refunded_amount: number;
}

export interface PaymentMethodSummary {
    method: string;
    total: number;
    amount: number;
}

export interface PaymentRecon {
    date: string;
    expected: number;
    actual: number;
    discrepancy: number;
}

export const PAYMENT_BADGE_META: Record<string, { label: string; classes: string }> = {
    success: { label: 'SUCCESS', classes: 'bg-emerald-100 text-emerald-700' },
    pending: { label: 'PENDING', classes: 'bg-amber-100 text-amber-800' },
    failed: { label: 'FAILED', classes: 'bg-red-100 text-red-700' },
    expired: { label: 'EXPIRED', classes: 'bg-gray-200 text-gray-600' },
    deny: { label: 'DENIED', classes: 'bg-red-100 text-red-700' },
    refunded: { label: 'REFUNDED', classes: 'bg-purple-100 text-purple-800' },
};

export function paymentBadge(status: string): { label: string; classes: string } {
    return PAYMENT_BADGE_META[status] ?? { label: status.toUpperCase(), classes: 'bg-gray-200/70 text-gray-700' };
}

export function paymentMethodLabel(method?: string | null): string {
    if (!method) return '-';
    if (method === 'fake_gateway') return 'Fake Gateway';
    return method
        .split('_')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ');
}

export function formatCompactIDR(amount: number): string {
    const fmt = (v: number, suffix: string): string =>
        `Rp ${v.toLocaleString('id-ID', { maximumFractionDigits: 1 })}${suffix}`;
    if (Math.abs(amount) >= 1_000_000_000) {
        return fmt(amount / 1_000_000_000, 'B');
    }
    if (Math.abs(amount) >= 1_000_000) {
        return fmt(amount / 1_000_000, 'M');
    }
    return formatIDR(amount);
}
