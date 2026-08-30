import httpClient from '../utils/http';
import type { Flight, FlightSearchParams, ApiResponse } from '../types';

export const flightAPI = {
  searchFlights: async (params: FlightSearchParams): Promise<Flight[]> => {
    const response = await httpClient.get<ApiResponse<Flight[]>>('/api/flights', { params });
    return response.data.data || [];
  },

  getFlightById: async (id: string): Promise<Flight> => {
    const response = await httpClient.get<ApiResponse<Flight>>(`/api/flights/${id}`);
    return response.data.data!;
  },

  getFlightAvailableSeats: async (flightId: string) => {
    const response = await httpClient.get(`/api/flights/${flightId}/seats`);
    return response.data.data;
  },
};
