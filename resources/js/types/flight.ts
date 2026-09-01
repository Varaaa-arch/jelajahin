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
