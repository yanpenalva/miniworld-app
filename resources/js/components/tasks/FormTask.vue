<script setup>
import ErrorInput from '@/components/shared/ErrorInput.vue';
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  projectOptions: {
    type: Array,
    default: () => [],
  },
  predecessorOptions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['update:modelValue']);

const statusOptions = [
  { label: 'Concluída', value: 'completed' },
  { label: 'Não concluída', value: 'not_completed' },
];

const endDateMin = computed(() => props.modelValue.start_date || undefined);

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value,
  });
};

const handleStartDateChange = (value) => {
  const nextValue = value || null;
  const currentEndDate = props.modelValue.end_date;

  emit('update:modelValue', {
    ...props.modelValue,
    start_date: nextValue,
    end_date:
      currentEndDate && nextValue && currentEndDate < nextValue ? null : currentEndDate,
  });
};
</script>

<template>
  <div class="row q-col-gutter-md">
    <div class="col-12">
      <q-input
        :model-value="props.modelValue.description"
        type="textarea"
        outlined
        dense
        maxlength="5000"
        counter
        label="Descrição *"
        placeholder="Informe a descrição da tarefa"
        @update:model-value="(value) => updateField('description', value)" />
      <ErrorInput :error="props.errors?.description?.[0]" />
    </div>

    <div class="col-12 col-md-6">
      <q-select
        :model-value="props.modelValue.project_id"
        :options="props.projectOptions"
        emit-value
        map-options
        outlined
        dense
        label="Projeto *"
        @update:model-value="(value) => updateField('project_id', value)" />
      <ErrorInput :error="props.errors?.project_id?.[0]" />
    </div>

    <div class="col-12 col-md-6">
      <q-select
        :model-value="props.modelValue.predecessor_task_id"
        :options="props.predecessorOptions"
        emit-value
        map-options
        clearable
        outlined
        dense
        label="Tarefa predecessora"
        @update:model-value="
          (value) => updateField('predecessor_task_id', value ?? null)
        " />
      <ErrorInput :error="props.errors?.predecessor_task_id?.[0]" />
    </div>

    <div class="col-12 col-md-4">
      <q-select
        :model-value="props.modelValue.status"
        :options="statusOptions"
        emit-value
        map-options
        outlined
        dense
        label="Status *"
        @update:model-value="(value) => updateField('status', value)" />
      <ErrorInput :error="props.errors?.status?.[0]" />
    </div>

    <div class="col-12 col-md-4">
      <q-input
        :model-value="props.modelValue.start_date"
        type="date"
        outlined
        dense
        label="Data de início"
        @update:model-value="handleStartDateChange" />
      <ErrorInput :error="props.errors?.start_date?.[0]" />
    </div>

    <div class="col-12 col-md-4">
      <q-input
        :model-value="props.modelValue.end_date"
        type="date"
        outlined
        dense
        :min="endDateMin"
        label="Data de fim"
        @update:model-value="(value) => updateField('end_date', value || null)" />
      <ErrorInput :error="props.errors?.end_date?.[0]" />
    </div>
  </div>
</template>
