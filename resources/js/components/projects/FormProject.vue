<script setup>
import ErrorInput from '@/components/shared/ErrorInput.vue';
import RequiredLabel from '@/components/shared/RequiredLabel.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['update:modelValue']);

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value,
  });
};

const updateBudget = (value) => {
  if (value === '' || value === null || value === undefined) {
    updateField('budget', null);
    return;
  }

  const parsedValue = Number(value);

  if (Number.isNaN(parsedValue)) {
    updateField('budget', null);
    return;
  }

  updateField('budget', parsedValue < 0 ? 0 : parsedValue);
};

const statusOptions = [
  { label: 'Ativo', value: 'active' },
  { label: 'Inativo', value: 'inactive' },
];
</script>

<template>
  <div class="row q-col-gutter-md">
    <div class="col-12 col-md-6">
      <RequiredLabel label="Nome" />
      <q-input
        :model-value="props.modelValue.name"
        outlined
        dense
        maxlength="255"
        counter
        @update:model-value="(value) => updateField('name', value)" />
      <ErrorInput :error="props.errors?.name?.[0]" />
    </div>

    <div class="col-12 col-md-6">
      <RequiredLabel label="Status" />
      <q-select
        :model-value="props.modelValue.status"
        :options="statusOptions"
        emit-value
        map-options
        outlined
        dense
        @update:model-value="(value) => updateField('status', value)" />
      <ErrorInput :error="props.errors?.status?.[0]" />
    </div>

    <div class="col-12 col-md-6">
      <label class="text-weight-medium">Orçamento</label>
      <q-input
        :model-value="props.modelValue.budget"
        outlined
        dense
        type="number"
        min="0"
        step="0.01"
        prefix="R$"
        @update:model-value="updateBudget" />
      <ErrorInput :error="props.errors?.budget?.[0]" />
    </div>

    <div class="col-12">
      <label class="text-weight-medium">Descrição</label>
      <q-input
        :model-value="props.modelValue.description"
        type="textarea"
        outlined
        dense
        maxlength="5000"
        counter
        @update:model-value="(value) => updateField('description', value)" />
      <ErrorInput :error="props.errors?.description?.[0]" />
    </div>
  </div>
</template>
