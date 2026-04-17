import PasswordService from '@/services/PasswordService';
import { defineStore } from 'pinia';

const usePasswordStore = defineStore('password', {
  state: () => ({}),
  persist: {},
  getters: {},
  actions: {
    async sendPasswordReset(params) {
      await PasswordService.resetPassword(params);
    },

    async requestPasswordRecovery(params) {
      await PasswordService.requestPasswordRecovery(params);
    },
  },
});

export default usePasswordStore;
