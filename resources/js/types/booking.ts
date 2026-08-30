export interface Passenger {
  id?: string;
  booking_id?: string;
  title: string;
  first_name: string;
  last_name: string;
  date_of_birth: string;
  gender: string;
  identity_type: string;
  identity_number: string;
  nationality: string;
  passport_number?: string;
  passport_expiry?: string;
  flight_seat_id?: string;
}

export interface Booking {
  id: string;
  pnr_code: string;
  user_id: string;
  flight_id: string;
  base_amount: number;
  discount_amount: number;
  tax_amount: number;
  total_price: number;
  passenger_count: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  booking_date: string;
  expiration_date?: string;
  confirmed_at?: string;
  cancelled_at?: string;
  passengers?: Passenger[];
}

export interface BookingRequest {
  flight_id: string;
  passengers: Passenger[];
  promo_code?: string;
  special_requests?: string;
}
