<script setup>
import FormView from '@/components/projects/FormView.vue';
import ActionButton from '@/components/shared/ActionButton.vue';
import PageTopTitle from '@/components/shared/PageTopTitle.vue';
import useProjectStore from '@/store/useProjectStore';
import { onBeforeMount } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const projectStore = useProjectStore();

const goBack = () => {
  router.push({ name: 'listProjects' });
};

onBeforeMount(async () => {
  await projectStore.consult(route.params.id);
});
</script>

<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <PageTopTitle title="Detalhes do Projeto" />
      <ActionButton
        icon="arrow_back"
        color="secondary"
        label="Voltar"
        @click-action="goBack" />
    </div>

    <FormView :project="projectStore.getProject" />
  </q-page>
</template>
