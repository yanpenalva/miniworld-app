<script setup>
import ActionButton from '@/components/shared/ActionButton.vue';
import PageTopTitle from '@/components/shared/PageTopTitle.vue';
import FormTask from '@/components/tasks/FormTask.vue';
import useTaskFormPage from '@/composables/Task/useTaskFormPage';
import useTaskStore from '@/store/useTaskStore';
import { Notify } from 'quasar';
import { onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const taskStore = useTaskStore();

const { form, projectOptions, predecessorOptions, loadDependencies, buildPayload } =
  useTaskFormPage();

const goBack = () => {
  router.push({ name: 'listTasks' });
};

onBeforeMount(async () => {
  await loadDependencies();
});

const submit = async () => {
  try {
    await taskStore.store(buildPayload());

    Notify.create({
      type: 'positive',
      message: 'Tarefa criada com sucesso.',
    });

    router.push({ name: 'listTasks' });
  } catch {}
};
</script>

<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <PageTopTitle title="Nova Tarefa" />
      <ActionButton
        icon="arrow_back"
        color="secondary"
        label="Voltar"
        @click-action="goBack" />
    </div>

    <q-card flat bordered class="q-mt-md">
      <q-card-section>
        <FormTask
          v-model="form"
          :errors="taskStore.getErrors || {}"
          :project-options="projectOptions"
          :predecessor-options="predecessorOptions" />
      </q-card-section>

      <q-card-actions align="right">
        <ActionButton icon="save" label="Salvar" color="primary" @click-action="submit" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>
