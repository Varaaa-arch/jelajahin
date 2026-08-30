export interface PaymentMethod {
  id: string;
  name: string;
  category: 'card' | 'transfer' | 'ewallet';
}

export interface Payment {
  id: string;
  booking_id: string;
  payment_method_id: string;
  amount: number;
  status: 'pending' | 'processing' | 'success' | 'failed' | 'expired';
  payment_date?: string;
  gateway_response?: Record<string, any>;
}

export interface PaymentRequest {
  booking_id: string;
  payment_method_id: string;
  amount: number;
}
