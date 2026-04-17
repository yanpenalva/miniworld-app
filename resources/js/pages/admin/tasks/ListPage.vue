<script setup>
import ActionButton from '@/components/shared/ActionButton.vue';
import PageTopTitle from '@/components/shared/PageTopTitle.vue';
import SearchInput from '@/components/shared/SearchInput.vue';
import TableSync from '@/components/tasks/TableSync.vue';
import useTaskConfigListPage from '@/composables/Task/useTaskConfigListPage';
import useTaskListPage from '@/composables/Task/useTaskListPage';
import { onMounted } from 'vue';

const { columns } = useTaskConfigListPage();

const {
  loading,
  pagination,
  rows,
  statusOptions,
  projectOptions,
  statusFilter,
  projectFilter,
  fetchTasks,
  handleSearch,
  handleStatusFilter,
  handleProjectFilter,
  updatePagination,
  onConsult,
  onEdit,
  onDelete,
  goToCreate,
} = useTaskListPage();

onMounted(async () => {
  await fetchTasks();
});
</script>

<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <PageTopTitle title="Tarefas" />
      <ActionButton icon="add" color="primary" label="Novo" @click-action="goToCreate" />
    </div>

    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <SearchInput @update-search="handleSearch" @trigger-search="handleSearch" />
      </div>

      <div class="col-12 col-md-4">
        <q-select
          :model-value="statusFilter"
          :options="statusOptions"
          emit-value
          map-options
          outlined
          dense
          label="Filtrar por status"
          @update:model-value="handleStatusFilter" />
      </div>

      <div class="col-12 col-md-4">
        <q-select
          :model-value="projectFilter"
          :options="projectOptions"
          emit-value
          map-options
          clearable
          outlined
          dense
          label="Filtrar por projeto"
          @update:model-value="handleProjectFilter" />
      </div>
    </div>

    <TableSync
      :loading="loading"
      :pagination="pagination"
      :columns="columns"
      :rows="rows"
      @update-pagination="updatePagination"
      @on-consult="onConsult"
      @on-edit="onEdit"
      @on-delete="onDelete" />
  </q-page>
</template>
