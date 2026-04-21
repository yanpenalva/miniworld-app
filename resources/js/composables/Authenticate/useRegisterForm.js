import UserService from '@/services/UserService';
import { Notify } from 'quasar';
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';

export function useRegisterForm() {
  const show = ref({ isPassword: true, isPasswordConfirmation: true });

  const formData = ref({
    role: { name: 'Administrador', value: 'administrator' },
    name: '',
    email: '',
    cpf: '',
    registration: '',
    password: '',
    password_confirmation: '',
  });

  const showFields = ref({
    nameEmail: true,
    registration: false,
    cpf: true,
  });

  const isLoading = ref(false);
  const isSearchCompleted = ref(false);
  const router = useRouter();

  const resetFormData = () => {
    formData.value = {
      role: formData.value.role,
      name: '',
      email: '',
      cpf: '',
      registration: '',
      password: '',
      password_confirmation: '',
    };
  };

  const onSubmit = async () => {
    isLoading.value = true;
    try {
      const data = await UserService.register({
        ...formData.value,
        role: formData.value.role.value,
      });

      if (data) {
        Notify.create({
          message:
            'Cadastro realizado com sucesso! Acesse seu e-mail para concluir o processo e obter maiores informações.',
          color: 'positive',
          position: 'top-right',
        });
        resetFormData();
        router.push('/');
      }
    } finally {
      isLoading.value = false;
    }
  };

  watch(() => formData.value.role.value, resetFormData, { immediate: true });

  return {
    formData,
    showFields,
    show,
    isLoading,
    isSearchCompleted,
    onSubmit,
  };
}
