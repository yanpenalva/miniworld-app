import service from '@/services/TaskService';
import { defineStore } from 'pinia';

const useTaskStore = defineStore('tasks', {
  state: () => ({
    tasks: [],
    meta: null,
    task: null,
    errors: null,
  }),

  getters: {
    getTasks() {
      return this.tasks;
    },
    getMeta() {
      return this.meta;
    },
    getTask() {
      return this.task;
    },
    getErrors() {
      return this.errors;
    },
  },

  actions: {
    async list(params) {
      const payload = await service.index(params);

      this.tasks = Array.isArray(payload?.data)
        ? payload.data
        : Array.isArray(payload)
          ? payload
          : [];

      this.meta = payload?.meta
        ? {
            current_page: Number(payload.meta.current_page ?? 1),
            per_page: Number(payload.meta.per_page ?? params?.limit ?? 10),
            total: Number(payload.meta.total ?? this.tasks.length),
            last_page: Number(payload.meta.last_page ?? 1),
          }
        : {
            current_page: 1,
            per_page: Number(params?.limit ?? 10),
            total: this.tasks.length,
            last_page: 1,
          };
    },

    async consult(id) {
      const data = await service.get(id);
      this.task = data?.data ?? data;
    },

    async store(params) {
      try {
        this.errors = null;
        return await service.store(params);
      } catch (error) {
        this.errors = error.response?.data?.errors ?? null;
        throw error;
      }
    },

    async update(id, params) {
      try {
        this.errors = null;
        const data = await service.update(id, params);
        this.task = data?.data ?? data;
        return this.task;
      } catch (error) {
        this.errors = error.response?.data?.errors ?? null;
        throw error;
      }
    },

    async updateStatus(id, params) {
      try {
        this.errors = null;
        const data = await service.updateStatus(id, params);
        this.task = data?.data ?? data;
        return this.task;
      } catch (error) {
        this.errors = error.response?.data?.errors ?? null;
        throw error;
      }
    },

    async destroy(id) {
      await service.destroy(id);
    },

    clearStore() {
      this.tasks = [];
      this.meta = null;
      this.task = null;
      this.errors = null;
    },
  },
});

export default useTaskStore;
