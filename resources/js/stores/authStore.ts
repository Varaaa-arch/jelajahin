import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { User } from '../types';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const isAuthenticated = computed(() => user.value !== null);

  const setUser = (userData: User) => {
    user.value = userData;
    if (userData.id) {
      localStorage.setItem('user_id', String(userData.id));
    }
  };

  const logout = () => {
    user.value = null;
    localStorage.removeItem('user_id');
  };

  return {
    user,
    isAuthenticated,
    setUser,
    logout,
  };
});
