import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { User } from '../types';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const isAuthenticated = computed(() => user.value !== null);

  const setUser = (userData: User) => {
    user.value = userData;
    localStorage.setItem('auth_token', 'token_here');
  };

  const logout = () => {
    user.value = null;
    localStorage.removeItem('auth_token');
  };

  return {
    user,
    isAuthenticated,
    setUser,
    logout,
  };
});
