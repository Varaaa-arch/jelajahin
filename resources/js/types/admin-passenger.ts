export type BookingStatus = 'pending' | 'confirmed' | 'completed' | 'cancelled';

export type PaymentStatus = 'pending' | 'success' | 'failed' | 'expired' | 'deny';

export type CheckInStatus = 'not_checked_in' | 'checked_in';

export interface AdminPassenger {
    id: string;
    name: string;
    title?: string | null;
    first_name: string;
    last_name: string;
    nationality?: string | null;
    gender?: string | null;
    date_of_birth?: string | null;
    email: string;
    booker_name: string;
    identity_type?: string | null;
    identity_masked: string;
    identity_number?: string | null;
    passport_masked: string;
    passport_number?: string | null;
    pnr: string;
    flight_number: string;
    departure_date?: string | null;
    origin_code?: string | null;
    destination_code?: string | null;
    booking_status: BookingStatus;
    payment_status: string;
    payment_method?: string | null;
    check_in_status: CheckInStatus;
    seat_number?: string | null;
    seat_class?: string | null;
    baggage_allowance_kg?: number | null;
    carry_on_allowance_kg?: number | null;
    addons_baggage?: string | null;
    addons_insurance?: string | null;
    addons_meals: string[];
    special_requests?: string | null;
}

export interface AdminPassengerFilters {
    q: string;
    status: string;
    check_in: string;
    nationality: string;
    payment: string;
    sort: string;
    date_from?: string | null;
    date_to?: string | null;
    per_page: number;
}

export interface PaginatedPassengers {
    data: AdminPassenger[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
}

export const BOOKING_STATUS_META: Record<string, { label: string; classes: string }> = {
    confirmed: { label: 'Confirmed', classes: 'bg-teal/15 text-teal-dark' },
    pending: { label: 'Pending', classes: 'bg-gray-200/70 text-gray-700' },
    completed: { label: 'Completed', classes: 'bg-navy/10 text-navy' },
    cancelled: { label: 'Cancelled', classes: 'bg-red-100 text-red-700' },
};

export function formatPassengerDate(iso?: string | null): string {
    if (!iso) return '-';
    const d = new Date(`${iso}T00:00:00`);
    if (Number.isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
