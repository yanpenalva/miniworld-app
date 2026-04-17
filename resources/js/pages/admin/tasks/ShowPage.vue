<script setup>
import ActionButton from '@/components/shared/ActionButton.vue';
import PageTopTitle from '@/components/shared/PageTopTitle.vue';
import FormView from '@/components/tasks/FormView.vue';
import useTaskStore from '@/store/useTaskStore';
import { onBeforeMount } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const taskStore = useTaskStore();

const goBack = () => {
  router.push({ name: 'listTasks' });
};

onBeforeMount(async () => {
  await taskStore.consult(route.params.id);
});
</script>

<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <PageTopTitle title="Detalhes da Tarefa" />
      <ActionButton
        icon="arrow_back"
        color="secondary"
        label="Voltar"
        @click-action="goBack" />
    </div>

    <FormView :task="taskStore.getTask" />
  </q-page>
</template>
