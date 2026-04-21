<script setup>
import FormProject from '@/components/projects/FormProject.vue';
import ActionButton from '@/components/shared/ActionButton.vue';
import PageTopTitle from '@/components/shared/PageTopTitle.vue';
import useProjectStore from '@/store/useProjectStore';
import { Notify } from 'quasar';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const projectStore = useProjectStore();

const form = ref({
  name: '',
  description: '',
  status: 'active',
  budget: null,
});

const goBack = () => {
  router.push({ name: 'listProjects' });
};

const submit = async () => {
  try {
    await projectStore.store(form.value);

    Notify.create({
      type: 'positive',
      message: 'Projeto criado com sucesso.',
    });

    router.push({ name: 'listProjects' });
  } catch {}
};
</script>

<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <PageTopTitle title="Novo Projeto" />
      <ActionButton
        icon="arrow_back"
        color="secondary"
        label="Voltar"
        @click-action="goBack" />
    </div>

    <q-card flat bordered class="q-mt-md">
      <q-card-section>
        <FormProject v-model="form" :errors="projectStore.getErrors || {}" />
      </q-card-section>

      <q-card-actions align="right">
        <ActionButton icon="save" label="Salvar" color="primary" @click-action="submit" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>
