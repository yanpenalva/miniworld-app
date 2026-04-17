<script setup>
const props = defineProps({
  task: {
    type: Object,
    default: () => ({}),
  },
});

const getStatusLabel = (status) => {
  if (status === 'completed') return 'Concluída';
  if (status === 'not_completed') return 'Não concluída';
  return '-';
};

const getStatusColor = (status) => {
  if (status === 'completed') return 'positive';
  if (status === 'not_completed') return 'warning';
  return 'grey';
};

const formatDate = (value, formattedValue) => formattedValue || value || '-';
</script>

<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="row q-col-gutter-md">
        <div class="col-12">
          <div class="text-caption text-grey-7">Descrição</div>
          <div class="text-body1">{{ props.task?.description ?? '-' }}</div>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-caption text-grey-7">Projeto</div>
          <div class="text-body1">{{ props.task?.project?.name ?? '-' }}</div>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-caption text-grey-7">Status</div>
          <q-badge
            :color="getStatusColor(props.task?.status)"
            text-color="white"
            class="q-px-sm q-py-xs">
            {{ getStatusLabel(props.task?.status) }}
          </q-badge>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-caption text-grey-7">Data de início</div>
          <div class="text-body1">
            {{ formatDate(props.task?.start_date, props.task?.start_date_formatted) }}
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-caption text-grey-7">Data de fim</div>
          <div class="text-body1">
            {{ formatDate(props.task?.end_date, props.task?.end_date_formatted) }}
          </div>
        </div>

        <div class="col-12">
          <div class="text-caption text-grey-7">Tarefa predecessora</div>
          <div class="text-body1">{{ props.task?.predecessor?.description ?? '-' }}</div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
