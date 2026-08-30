import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { Booking, Passenger, BookingRequest } from '../types';
import { bookingAPI } from '../api/bookings';

export const useBookingStore = defineStore('booking', () => {
  const currentBooking = ref<Booking | null>(null);
  const passengers = ref<Passenger[]>([]);
  const selectedSeats = ref<string[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const addPassenger = (passenger: Passenger) => {
    passengers.value.push(passenger);
  };

  const removePassenger = (index: number) => {
    passengers.value.splice(index, 1);
  };

  const selectSeat = (seatId: string) => {
    if (!selectedSeats.value.includes(seatId)) {
      selectedSeats.value.push(seatId);
    }
  };

  const deselectSeat = (seatId: string) => {
    selectedSeats.value = selectedSeats.value.filter(s => s !== seatId);
  };

  const createBooking = async (flightId: string, promoCode?: string) => {
    loading.value = true;
    error.value = null;
    try {
      const bookingRequest: BookingRequest = {
        flight_id: flightId,
        passengers: passengers.value,
        promo_code: promoCode,
      };
      currentBooking.value = await bookingAPI.createBooking(bookingRequest);
    } catch (err: any) {
      error.value = err.message || 'Failed to create booking';
    } finally {
      loading.value = false;
    }
  };

  const resetBooking = () => {
    currentBooking.value = null;
    passengers.value = [];
    selectedSeats.value = [];
  };

  return {
    currentBooking,
    passengers,
    selectedSeats,
    loading,
    error,
    addPassenger,
    removePassenger,
    selectSeat,
    deselectSeat,
    createBooking,
    resetBooking,
  };
});
