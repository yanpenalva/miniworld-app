<script setup>
import PageWrapper from '@/pages/admin/PageWrapper.vue';
import useAuthStore from '@/store/useAuthStore';
import { computed } from 'vue';

const authStore = useAuthStore();

const userName = computed(() => authStore.getUser?.name || 'Visitante');
const isGuest = computed(() => authStore.getRoles?.some(({ slug }) => slug === 'guest'));

const cards = [
  {
    icon: 'folder_open',
    title: 'Gestão de Projetos',
    description:
      'Crie e gerencie projetos com controle de status, orçamento e progresso de tarefas em tempo real.',
    color: 'primary',
  },
  {
    icon: 'task_alt',
    title: 'Controle de Tarefas',
    description:
      'Organize tarefas por projeto, defina predecessoras, datas e acompanhe o que foi concluído.',
    color: 'green-6',
  },
  {
    icon: 'security',
    title: 'Acesso Seguro',
    description:
      'Autenticação via Sanctum com controle granular de permissões por perfil de usuário.',
    color: 'amber-8',
  },
];
</script>

<template>
  <PageWrapper>
    <template v-if="!isGuest" #title>
      <q-card class="q-mb-md">
        <q-card-section>
          <div class="text-h5 text-weight-medium">Mini Mundo</div>
          <div class="text-caption text-grey-7">
            Bem-vindo, {{ userName }}. Plataforma de gestão de projetos e tarefas.
          </div>
        </q-card-section>
      </q-card>
    </template>

    <template #content>
      <q-card flat bordered>
        <q-card-section>
          <div
            v-if="isGuest"
            class="column items-center q-py-xl q-px-md text-center bg-grey-1"
            style="border-radius: 8px">
            <q-icon name="info" size="64px" color="primary" class="q-mb-md" />
            <div class="text-h6 q-mb-sm">
              Olá {{ userName }}, seu perfil é <strong>Visitante</strong>.
            </div>
            <div class="text-subtitle2">
              Solicite alteração de perfil para visualizar os recursos disponíveis no
              sistema.
            </div>
          </div>

          <div v-else class="row q-col-gutter-md q-pt-md">
            <q-card
              v-for="card in cards"
              :key="card.title"
              class="col-xs-12 col-sm-4"
              bordered
              flat
              style="border-radius: 12px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05)">
              <q-card-section class="column items-center text-center q-gutter-sm">
                <q-icon :name="card.icon" size="48px" :color="card.color" />
                <div class="text-h6 text-weight-medium">{{ card.title }}</div>
                <div class="text-body2 text-grey-7 q-mt-xs">{{ card.description }}</div>
              </q-card-section>
            </q-card>
          </div>
        </q-card-section>
      </q-card>
    </template>
  </PageWrapper>
</template>
