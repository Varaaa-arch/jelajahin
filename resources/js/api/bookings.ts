import httpClient from '../utils/http';
import type { Booking, BookingRequest, ApiResponse } from '../types';

export const bookingAPI = {
  createBooking: async (data: BookingRequest): Promise<Booking> => {
    const response = await httpClient.post<ApiResponse<Booking>>('/api/bookings', data);
    return response.data.data!;
  },

  getBookingByPNR: async (pnr: string): Promise<Booking> => {
    const response = await httpClient.get<ApiResponse<Booking>>(`/api/bookings/${pnr}`);
    return response.data.data!;
  },

  getMyBookings: async (): Promise<Booking[]> => {
    const response = await httpClient.get<ApiResponse<Booking[]>>('/api/bookings/me');
    return response.data.data || [];
  },

  cancelBooking: async (bookingId: string): Promise<Booking> => {
    const response = await httpClient.patch<ApiResponse<Booking>>(`/api/bookings/${bookingId}/cancel`);
    return response.data.data!;
  },
};
