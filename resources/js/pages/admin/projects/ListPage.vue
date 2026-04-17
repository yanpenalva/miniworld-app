<script setup>
import TableSync from '@/components/projects/TableSync.vue';
import ActionButton from '@/components/shared/ActionButton.vue';
import PageTopTitle from '@/components/shared/PageTopTitle.vue';
import SearchInput from '@/components/shared/SearchInput.vue';
import useProjectConfigListPage from '@/composables/Project/useProjectConfigListPage';
import useProjectListPage from '@/composables/Project/useProjectListPage';
import { onMounted } from 'vue';

const { columns } = useProjectConfigListPage();

const {
  loading,
  pagination,
  rows,
  fetchProjects,
  handleSearch,
  updatePagination,
  onConsult,
  onEdit,
  onDelete,
  goToCreate,
} = useProjectListPage();

onMounted(async () => {
  await fetchProjects();
});
</script>

<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <PageTopTitle title="Projetos" />
      <ActionButton icon="add" color="primary" label="Novo" @click-action="goToCreate" />
    </div>

    <div class="q-mb-md">
      <SearchInput @update-search="handleSearch" @trigger-search="handleSearch" />
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
