import service from '@/services/ProjectService';
import { defineStore } from 'pinia';

const useProjectStore = defineStore('projects', {
  state: () => ({
    projects: [],
    meta: null,
    project: null,
    errors: null,
  }),

  getters: {
    getProjects() {
      return this.projects;
    },
    getMeta() {
      return this.meta;
    },
    getProject() {
      return this.project;
    },
    getErrors() {
      return this.errors;
    },
  },

  actions: {
    async list(params) {
      const data = await service.index(params);

      this.projects = Array.isArray(data?.data) ? data.data : [];
      this.meta = {
        current_page: Number(data?.current_page ?? 1),
        per_page: Number(data?.per_page ?? params?.limit ?? 15),
        total: Number(data?.total ?? 0),
        last_page: Number(data?.last_page ?? 1),
      };
    },

    async consult(id) {
      const data = await service.get(id);
      this.project = data?.data ?? data;
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
        this.project = data?.data ?? data;
        return this.project;
      } catch (error) {
        this.errors = error.response?.data?.errors ?? null;
        throw error;
      }
    },

    async destroy(id) {
      await service.destroy(id);
    },

    clearStore() {
      this.projects = [];
      this.meta = null;
      this.project = null;
      this.errors = null;
    },
  },
});

export default useProjectStore;
