export interface ReportFilters {
    date_from: string;
    date_to: string;
}

export interface ReportSummary {
    revenue: number;
    bookings: number;
    refunded: number;
    avg_ticket: number;
}

export interface RevenueTrendPoint {
    labels: string[];
    values: number[];
    unit: string;
}

export interface BookingStatusSlice {
    status: string;
    label: string;
    count: number;
    pct: number;
    color: string;
}

export interface TopRoute {
    route: string;
    codes: string;
    airline: string;
    bookings: number;
    revenue: number;
}

export interface TopFlight {
    rank: number;
    route: string;
    flightNo: string;
    bookings: number;
}

export interface PaymentSummaryRow {
    status: string;
    total: number;
    amount: number;
}

export interface OccupancyRow {
    flight_number: string;
    departure_date?: string | null;
    booked: number;
    total: number;
    pct: number;
}

export interface OccupancyReport {
    average_pct: number | null;
    flights: OccupancyRow[];
}
