import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Flight, FlightSearchParams } from '../types';
import { flightAPI } from '../api/flights';

export const useFlightStore = defineStore('flights', () => {
  const flights = ref<Flight[]>([]);
  const selectedFlight = ref<Flight | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const searchParams = ref<FlightSearchParams | null>(null);

  const searchFlights = async (params: FlightSearchParams) => {
    loading.value = true;
    error.value = null;
    try {
      searchParams.value = params;
      flights.value = await flightAPI.searchFlights(params);
    } catch (err: any) {
      error.value = err.message || 'Failed to search flights';
    } finally {
      loading.value = false;
    }
  };

  const getFlightById = async (id: string) => {
    loading.value = true;
    error.value = null;
    try {
      selectedFlight.value = await flightAPI.getFlightById(id);
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch flight';
    } finally {
      loading.value = false;
    }
  };

  const clearSelection = () => {
    selectedFlight.value = null;
    flights.value = [];
  };

  return {
    flights,
    selectedFlight,
    loading,
    error,
    searchParams,
    searchFlights,
    getFlightById,
    clearSelection,
  };
});
