export type FlightDbStatus = 'scheduled' | 'boarding' | 'in_flight' | 'landed' | 'cancelled';

export type FlightStatusTab = 'semua' | 'aktif' | 'nonaktif' | 'terjadwal';

export type FlightSortKey =
    | 'terbaru'
    | 'terlama'
    | 'okupansi'
    | 'harga_tertinggi'
    | 'harga_terendah';

export interface AdminFlight {
    id: string;
    flight_number: string;
    airline_id?: string | null;
    airline_code: string;
    airline_name: string;
    origin_code: string;
    origin_city: string;
    destination_code: string;
    destination_city: string;
    route_id: string;
    aircraft_id: string;
    departure_date: string;
    departure_time: string;
    arrival_time: string;
    base_price: number;
    status: FlightDbStatus;
    seats_total: number;
    seats_booked: number;
    seats_available: number;
    occupancy_pct: number;
}

export interface AdminFlightFilters {
    q: string;
    airline_id?: string | null;
    status_tab: FlightStatusTab;
    sort: FlightSortKey;
    date_from?: string | null;
    date_to?: string | null;
    occupancy_min: number;
}

export interface PaginatedFlights {
    data: AdminFlight[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
}

export interface FlightOption {
    id: string;
    code: string;
    name: string;
}

export interface FlightRouteOption {
    id: string;
    airline_id: string;
    origin_airport_id: string;
    destination_airport_id: string;
    flight_number_prefix: string;
    airline?: { id: string; code: string; name: string };
    origin_airport?: { id: string; code: string; city: string };
    destination_airport?: { id: string; code: string; city: string };
}

export interface FlightAircraftOption {
    id: string;
    aircraft_type_id: string;
    airline_id: string;
    registration_number: string;
}

export const STATUS_META: Record<FlightDbStatus, { label: string; classes: string }> = {
    scheduled: { label: 'Terjadwal', classes: 'bg-gray-100 text-gray-700' },
    boarding: { label: 'Boarding', classes: 'bg-teal/10 text-teal-dark' },
    in_flight: { label: 'Aktif', classes: 'bg-teal/10 text-teal-dark' },
    landed: { label: 'Nonaktif', classes: 'bg-gray-100 text-gray-500' },
    cancelled: { label: 'Batal', classes: 'bg-red-50 text-red-700' },
};

export function occupancyTone(pct: number): { bar: string; text: string } {
    if (pct >= 70) return { bar: 'bg-navy', text: 'text-gray-900' };
    if (pct >= 40) return { bar: 'bg-amber-400', text: 'text-gray-700' };
    return { bar: 'bg-red-600', text: 'text-red-600' };
}

export function formatFlightDate(iso: string): string {
    if (!iso) return '-';
    const d = new Date(`${iso}T00:00:00`);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
