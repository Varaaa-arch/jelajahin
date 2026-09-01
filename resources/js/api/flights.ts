import httpClient from '../utils/http'
import type { Flight, FlightSearchParams, ApiResponse } from '../types'

export const flightAPI = {
  searchFlights: async (params: FlightSearchParams): Promise<Flight[]> => {
    const response = await httpClient.get<ApiResponse<Flight[]>>('/api/v1/flights/search', { 
      params: {
        origin: params.origin,
        destination: params.destination,
        departure_date: params.departure_date,
      }
    })
    return response.data.data || []
  },

  getFlightById: async (id: string): Promise<Flight> => {
    const response = await httpClient.get<ApiResponse<Flight>>(`/api/v1/flights/${id}`)
    return response.data.data!
  },

  getFlightAvailableSeats: async (flightId: string) => {
    const response = await httpClient.get(`/api/v1/flights/${flightId}/seats`)
    return response.data.data
  },
}
