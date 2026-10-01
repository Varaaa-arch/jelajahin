export type OrderStatus =
    | 'pending'
    | 'awaiting_confirmation'
    | 'confirmed'
    | 'completed'
    | 'cancelled'
    | 'refund_requested'
    | 'refunded';

export type PaymentStatus =
    | 'pending'
    | 'success'
    | 'failed'
    | 'expired'
    | 'deny'
    | 'refunded';

export interface AdminOrderPassenger {
    id: string;
    name: string;
    first_name: string;
    last_name: string;
    seat_number?: string | null;
    seat_class?: string | null;
}

export interface AdminOrder {
    id: string;
    pnr: string;
    booker_name: string;
    booker_email: string;
    passenger_count: number;
    passengers: AdminOrderPassenger[];
    route_id?: string | null;
    origin_code: string;
    destination_code: string;
    flight_id: string;
    flight_number: string;
    airline_code: string;
    airline_name: string;
    departure_date?: string | null;
    departure_time?: string | null;
    arrival_time?: string | null;
    booking_date?: string | null;
    total_price: number;
    payment_status: string;
    payment_method?: string | null;
    order_status: OrderStatus;
    special_requests?: string | null;
    addons_baggage?: string | null;
    addons_insurance?: string | null;
    addons_meals: string[];
}

export interface AdminOrderFilters {
    q: string;
    order_status: string;
    payment_status: string;
    airline_id?: string | null;
    route_id?: string | null;
    date_from?: string | null;
    date_to?: string | null;
    sort: string;
    per_page: number;
}

export interface PaginatedOrders {
    data: AdminOrder[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
}

export interface AirlineOption {
    id: string;
    code: string;
    name: string;
}

export interface RouteOption {
    id: string;
    label: string;
    origin_code?: string | null;
    destination_code?: string | null;
    airline_id?: string | null;
    airline_name?: string | null;
}

export interface RescheduleFlightOption {
    id: string;
    flight_number: string;
    route_id: string;
    origin_code?: string | null;
    destination_code?: string | null;
    airline_name?: string | null;
    departure_date?: string | null;
    departure_time?: string | null;
    seats_available: number;
}

export const ORDER_STATUS_META: Record<string, { label: string; classes: string }> = {
    confirmed: { label: 'Confirmed', classes: 'bg-teal/15 text-teal-dark' },
    pending: { label: 'Pending', classes: 'bg-amber-100 text-amber-800' },
    awaiting_confirmation: { label: 'Awaiting Approval', classes: 'bg-orange-100 text-orange-800' },
    completed: { label: 'Completed', classes: 'bg-navy/10 text-navy' },
    cancelled: { label: 'Cancelled', classes: 'bg-red-100 text-red-700' },
    refund_requested: { label: 'Refund Requested', classes: 'bg-orange-100 text-orange-800' },
    refunded: { label: 'Refunded', classes: 'bg-purple-100 text-purple-800' },
};

export const PAYMENT_STATUS_META: Record<string, { label: string; classes: string }> = {
    success: { label: 'Paid', classes: 'bg-emerald-50 text-emerald-700' },
    pending: { label: 'Unpaid', classes: 'bg-amber-100 text-amber-800' },
    failed: { label: 'Unpaid', classes: 'bg-amber-100 text-amber-800' },
    expired: { label: 'Unpaid', classes: 'bg-amber-100 text-amber-800' },
    deny: { label: 'Unpaid', classes: 'bg-amber-100 text-amber-800' },
    refunded: { label: 'Refunded', classes: 'bg-red-50 text-red-700' },
};

export const ORDER_TRANSITIONS: Record<string, string[]> = {
    pending: ['cancelled'],
    awaiting_confirmation: ['confirmed', 'cancelled'],
    confirmed: ['completed', 'cancelled', 'refund_requested'],
    refund_requested: ['refunded', 'cancelled', 'confirmed'],
    completed: [],
    cancelled: [],
    refunded: [],
};

export function formatIDR(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount).replace('Rp', 'Rp ');
}

export function formatOrderDate(iso?: string | null): string {
    if (!iso) return '-';
    const d = new Date(`${iso}T00:00:00`);
    if (Number.isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

export interface AdminOrderPayment {
    id: string;
    payment_method?: string | null;
    amount: number;
    status: string;
    transaction_id: string;
    paid_at?: string | null;
    expires_at?: string | null;
    created_at?: string | null;
}

export interface AdminOrderDetailPassenger extends AdminOrderPassenger {
    type: string;
    ticket_number?: string | null;
}

export interface AdminOrderPrice {
    base: number;
    discount: number;
    tax: number;
    addons: number;
    total: number;
}

export interface AdminOrderDetail extends AdminOrder {
    passengers: AdminOrderDetailPassenger[];
    type_counts: { adult: number; child: number; infant: number };
    aircraft_label: string;
    aircraft_registration?: string | null;
    duration_label: string;
    origin_city: string;
    destination_city: string;
    placed_at?: string | null;
    price: AdminOrderPrice;
    payments: AdminOrderPayment[];
    invoice_number?: string | null;
}
