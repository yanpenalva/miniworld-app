<script setup>
import { formatCurrencyBRL } from '@/utils/formatCurrency';

const props = defineProps({
  project: {
    type: Object,
    default: () => ({}),
  },
});

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
</script>

<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-6">
          <div class="text-caption text-grey-7">Nome</div>
          <div class="text-body1">{{ props.project?.name ?? '-' }}</div>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-caption text-grey-7">Status</div>
          <q-badge
            :color="getStatusColor(props.project?.status)"
            text-color="white"
            class="q-px-sm q-py-xs">
            {{ getStatusLabel(props.project?.status) }}
          </q-badge>
        </div>

        <div class="col-12 col-md-6">
          <div class="text-caption text-grey-7">Orçamento</div>
          <div class="text-body1">{{ formatCurrencyBRL(props.project?.budget) }}</div>
        </div>

        <div class="col-12">
          <div class="text-caption text-grey-7">Descrição</div>
          <div class="text-body1">{{ props.project?.description ?? '-' }}</div>
        </div>

        <div class="col-12">
          <div class="text-caption text-grey-7">Progresso das tarefas</div>
          <div class="text-caption q-mt-xs q-mb-xs">
            {{ props.project?.progress?.completed ?? 0 }}/{{
              props.project?.progress?.total ?? 0
            }}
            ({{ props.project?.progress?.percentage ?? 0 }}%)
          </div>
          <q-linear-progress
            rounded
            size="10px"
            :value="(props.project?.progress?.percentage ?? 0) / 100"
            :color="progressColor(props.project?.progress?.percentage ?? 0)" />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
