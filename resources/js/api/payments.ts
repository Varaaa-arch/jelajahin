import httpClient from '../utils/http';
import type { Payment, PaymentRequest, ApiResponse } from '../types';

export const paymentAPI = {
  createPayment: async (data: PaymentRequest): Promise<Payment> => {
    const response = await httpClient.post<ApiResponse<Payment>>('/api/payments', data);
    return response.data.data!;
  },

  getPaymentStatus: async (paymentId: string): Promise<Payment> => {
    const response = await httpClient.get<ApiResponse<Payment>>(`/api/payments/${paymentId}`);
    return response.data.data!;
  },
};
