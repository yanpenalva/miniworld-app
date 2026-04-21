<script setup>
import Pagination from '@/components/shared/Pagination.vue';
import { formatCurrencyBRL } from '@/utils/formatCurrency';
import { ref } from 'vue';

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  pagination: {
    type: Object,
    default: () => ({}),
  },
  columns: {
    type: Array,
    default: () => [],
  },
  rows: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['updatePagination', 'onConsult', 'onEdit', 'onDelete']);

const itemDelete = ref(null);
const confirmRowDelete = ref(false);

const labels = {
  loading: 'Carregando...',
  noData: 'Nenhum registro encontrado',
  confirmDeleteTitle: 'Tem certeza que deseja excluir este projeto?',
  yes: 'Sim',
  no: 'Não',
  details: 'Ver detalhes',
  edit: 'Editar',
  delete: 'Excluir',
};

const deleteRow = (row) => {
  confirmRowDelete.value = true;
  itemDelete.value = row;
};

const confirmDeleteRow = (status) => {
  confirmRowDelete.value = false;

  if (status) {
    emit('onDelete', itemDelete.value);
  }

  itemDelete.value = null;
};

const getStatusLabel = (status) => {
  if (status === 'active') return 'Ativo';
  if (status === 'inactive') return 'Inativo';
  return '-';
};

const getStatusColor = (status) => {
  if (status === 'active') return 'positive';
  if (status === 'inactive') return 'negative';
  return 'grey';
};

const progressColor = (percentage) => {
  if (percentage >= 100) return 'positive';
  if (percentage >= 50) return 'warning';
  return 'negative';
};

const getCellValue = (row, col) => {
  return typeof col.field === 'function' ? col.field(row) : (row[col.field] ?? '-');
};

const shouldShowTooltip = (value) => {
  return typeof value === 'string' && value.length > 40;
};
</script>

<template>
  <q-table
    class="table-default-data-table"
    flat
    bordered
    binary-state-sort
    row-key="id"
    :rows="props.rows"
    :columns="props.columns"
    :rows-per-page-options="[10, 15, 25, 50, 100]"
    :loading="props.loading"
    :loading-label="labels.loading"
    :pagination="props.pagination"
    :no-data-label="props.loading ? '' : labels.noData"
    @update:pagination="emit('updatePagination', $event)"
    @request="emit('updatePagination', $event)">
    <template #header="scope">
      <q-dialog v-model="confirmRowDelete" persistent>
        <q-card>
          <q-card-section class="confirm-dialog-title">
            <span>
              <strong>{{ labels.confirmDeleteTitle }}</strong>
            </span>
          </q-card-section>

          <q-card-actions align="center" class="confirm-dialog-actions">
            <q-btn
              v-close-popup
              outline
              :label="labels.yes"
              color="primary"
              @click="confirmDeleteRow(true)" />
            <q-btn
              v-close-popup
              :label="labels.no"
              color="primary"
              @click="confirmDeleteRow(false)" />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <q-tr :props="scope">
        <q-th v-for="col in scope.cols" :key="col.name" :props="scope">
          {{ col.label }}
        </q-th>
      </q-tr>
    </template>

    <template #body="scope">
      <q-tr :props="scope">
        <q-td v-for="col in scope.cols" :key="col.name" :props="scope">
          <div v-if="col.name === 'action'" class="actions-container">
            <q-btn
              v-if="col.methods?.onConsult"
              dense
              flat
              round
              color="primary"
              icon="visibility"
              :title="labels.details"
              @click="emit('onConsult', scope.row)" />
            <q-btn
              v-if="col.methods?.onEdit"
              dense
              flat
              round
              color="primary"
              icon="edit"
              :title="labels.edit"
              @click="emit('onEdit', scope.row)" />
            <q-btn
              v-if="col.methods?.onDelete"
              dense
              flat
              round
              color="negative"
              icon="delete"
              :title="labels.delete"
              @click="deleteRow(scope.row)" />
          </div>

          <q-badge
            v-else-if="col.name === 'status'"
            :color="getStatusColor(scope.row.status)"
            text-color="white"
            class="q-px-sm q-py-xs">
            {{ getStatusLabel(scope.row.status) }}
          </q-badge>

          <span v-else-if="col.name === 'budget'">
            {{ formatCurrencyBRL(scope.row.budget) }}
          </span>

          <div v-else-if="col.name === 'progress'" class="progress-cell">
            <div class="text-caption q-mb-xs">
              {{ scope.row.progress?.completed ?? 0 }}/{{
                scope.row.progress?.total ?? 0
              }}
              ({{ scope.row.progress?.percentage ?? 0 }}%)
            </div>
            <q-linear-progress
              rounded
              size="8px"
              :value="(scope.row.progress?.percentage ?? 0) / 100"
              :color="progressColor(scope.row.progress?.percentage ?? 0)" />
          </div>

          <div
            v-else-if="col.name === 'name' || col.name === 'description'"
            class="truncate-cell">
            <span class="truncate-text">
              {{ getCellValue(scope.row, col) }}
            </span>
            <q-tooltip
              v-if="shouldShowTooltip(getCellValue(scope.row, col))"
              anchor="top middle"
              self="bottom middle"
              max-width="320px">
              {{ getCellValue(scope.row, col) }}
            </q-tooltip>
          </div>

          <span v-else>
            {{ getCellValue(scope.row, col) }}
          </span>
        </q-td>
      </q-tr>
    </template>

    <template #pagination="scope">
      <Pagination :scope="scope" />
    </template>
  </q-table>
</template>

<style lang="sass" scoped>
.table-default-data-table
  .q-table__top,
  thead tr:first-child th
    background-color: #064C7E
    color: #ffffff
    font-weight: bold
  thead tr th
    position: sticky
    z-index: 1
  thead tr:first-child th
    top: 0

.actions-container
  display: flex
  justify-content: center
  align-items: center
  gap: 8px

.confirm-dialog-title
  display: flex
  justify-content: center
  align-items: center
  text-align: center
  padding: 16px

.confirm-dialog-actions
  display: flex
  justify-content: center
  gap: 12px

.progress-cell
  min-width: 120px

.truncate-cell
  position: relative
  max-width: 220px

.truncate-text
  display: block
  max-width: 220px
  overflow: hidden
  text-overflow: ellipsis
  white-space: nowrap

.q-btn
  transition: transform 0.15s ease
  &:hover
    transform: scale(1.1)
</style>
