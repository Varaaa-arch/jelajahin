export interface Airport {
  id: string;
  code: string;
  name: string;
  city: string;
  country: string;
  timezone: string;
}

export interface Airline {
  id: string;
  code: string;
  name: string;
  logo_url?: string;
  headquarters_city: string;
}

export interface Route {
  id: string;
  airline_id: string;
  origin_airport_id: string;
  destination_airport_id: string;
  flight_number_prefix: string;
  distance_km: number;
  estimated_duration_minutes: number;
}

export interface Flight {
  estimated_duration_minutes: string;
  id: string;
  flight_number: string;
  route_id: string;
  departure_date: string;
  departure_time: string;
  arrival_time: string;
  base_price: number;
  tax_surcharge: number;
  fuel_surcharge: number;
  status: 'scheduled' | 'boarding' | 'in_flight' | 'landed' | 'cancelled';
  seats_available: number;
  airline?: Airline;
  route?: Route;
  origin?: Airport;
  destination?: Airport;
}

export interface FlightSearchParams {
  origin: string;
  destination: string;
  departure_date: string;
  return_date?: string;
  passenger_count: number;
  cabin_class?: 'economy' | 'premium_economy' | 'business' | 'first';
}

/** Seat class info dari Go API */
export interface SeatClass {
  id: string;
  name: string;
  display_name: string;
  baggage_allowance_kg: number;
  carry_on_allowance_kg: number;
}

/** Aircraft seat layout (posisi fisik kursi di pesawat) */
export interface AircraftSeat {
  id: string;
  aircraft_type_id: string;
  seat_class_id: string;
  seat_number: string;     // e.g. "1A"
  row_number: number;
  column_letter: string;
  is_exit_row: boolean;
  is_extra_legroom: boolean;
  seat_class?: SeatClass;
}

/**
 * FlightSeat = kursi spesifik pada penerbangan tertentu.
 * Ini yang dikembalikan oleh GET /api/v1/flights/:id/seats
 */
export interface FlightSeat {
  id: string;              // UUID — dipakai sebagai seat_id ke Go lock API
  flight_id: string;
  aircraft_seat_id: string;
  current_price: number;
  is_available: boolean;
  booking_id?: string | null;
  /** Akan di-join dari aircraft_seat oleh Go handler */
  seat_number?: string;    // e.g. "1A"
  row_number?: number;
  column_letter?: string;
  is_exit_row?: boolean;
  is_extra_legroom?: boolean;
  seat_class?: SeatClass;
  aircraft_seat?: AircraftSeat;
}
