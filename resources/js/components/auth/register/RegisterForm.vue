<script setup>
import { useRegisterForm } from '@/composables/Authenticate/useRegisterForm';

const { formData, showFields, show, isLoading, onSubmit } = useRegisterForm();
</script>

<template>
  <q-form class="form-register--padding" @submit.prevent="onSubmit">
    <label class="text-weight-medium text--font-13">CPF</label>
    <q-input
      v-model="formData.cpf"
      class="input-color-blue input--margin-bottom"
      filled
      placeholder="Digite seu CPF"
      lazy-rules
      mask="###.###.###-##"
      unmasked-value />

    <label class="text-weight-medium text--font-13">Nome Completo</label>
    <q-input
      v-model="formData.name"
      class="input-color-blue input--margin-bottom"
      filled
      placeholder="Digite seu nome"
      lazy-rules
      maxlength="80" />

    <label class="text-weight-medium text--font-13">E-mail</label>
    <q-input
      v-model="formData.email"
      class="input-color-blue input--margin-bottom"
      filled
      placeholder="Digite seu e-mail"
      lazy-rules />

    <label class="text-weight-medium text--font-13">Senha</label>
    <q-input
      v-model="formData.password"
      :type="show.isPassword ? 'password' : 'text'"
      class="input-color-blue input--margin-bottom"
      filled
      placeholder="Digite sua senha"
      lazy-rules>
      <template #append>
        <q-icon
          :name="show.isPassword ? 'visibility_off' : 'visibility'"
          class="cursor-pointer"
          @click="show.isPassword = !show.isPassword" />
      </template>
    </q-input>

    <label class="text-weight-medium text--font-13">Repetir Senha</label>
    <q-input
      v-model="formData.password_confirmation"
      :type="show.isPasswordConfirmation ? 'password' : 'text'"
      class="input-color-blue input--margin-bottom"
      filled
      placeholder="Confirme sua senha"
      lazy-rules>
      <template #append>
        <q-icon
          :name="show.isPasswordConfirmation ? 'visibility_off' : 'visibility'"
          class="cursor-pointer"
          @click="show.isPasswordConfirmation = !show.isPasswordConfirmation" />
      </template>
    </q-input>

    <div class="q-mt-xs">
      <q-btn
        :loading="isLoading"
        label="Cadastrar"
        type="submit"
        color="secondary"
        class="full-width" />
    </div>
  </q-form>
</template>
