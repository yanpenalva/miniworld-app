import router from '@/routes';
import useAuthStore from '@/store/useAuthStore';
import notify from '@/utils/notify';
import axios from 'axios';
import { Loading } from 'quasar';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 10000,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
  },
});

http.interceptors.response.use(
  (response) => response,
  async (error) => {
    Loading.hide();

    if (!error.response) {
      notify('Ocorreu um erro de rede inesperado.', 'negative');
      return Promise.reject(error);
    }

    const timeoutMessage =
      'Não foi possível carregar esta página corretamente, verifique sua conexão com a internet e tente novamente';

    if (error.code === 'ECONNABORTED') {
      notify(timeoutMessage, 'negative');
      return Promise.reject({ message: timeoutMessage });
    }

    const { status, data } = error.response;
    const isValidData = data && typeof data === 'object' && !(data instanceof Blob);
    const message = isValidData ? data?.message || 'Erro inesperado' : 'Erro inesperado';

    handleErrorResponse(status, message, isValidData ? data : {}, error.config);

    return Promise.reject(error);
  },
);

function handleErrorResponse(status, message, data, config) {
  const authStore = useAuthStore();

  const isAuthRoute = config.url.includes('/api/v1/login');

  const logoutAndRedirect = () => {
    authStore.logout();
    router.push({ name: 'login' });
  };

  switch (status) {
    case 401: {
      notify(message, 'negative');
      const isInvalidCredentials =
        isAuthRoute || message === 'Usuário ou senha inválidos';
      if (!isInvalidCredentials) logoutAndRedirect();
      break;
    }

    case 403: {
      const errorMessage = data?.error || data?.errors;
      const blockedErrors = ['Usuário não ativado', 'Usuário inativo'];
      if (blockedErrors.includes(errorMessage)) {
        notify(message, 'negative');
      }
      break;
    }

    case 404: {
      const errorMessage404 = data?.message?.includes('No query results for model')
        ? 'Nenhum registro foi encontrado'
        : data?.message;
      notify(errorMessage404, 'negative');
      break;
    }

    case 408:
      notify('Tempo de solicitação esgotado', 'negative');
      window.location.reload();
      break;

    case 419:
      notify(
        'Sessão expirada. Por favor, atualize a página e tente novamente.',
        'negative',
      );
      router.replace('/');
      break;

    case 422: {
      if (data?.message) {
        notify(data.message, 'negative');
        return;
      }
      const errors422 = data?.errors || {};
      Object.values(errors422)
        .slice(0, 8)
        .forEach((msg) => notify(msg.toString(), 'negative'));
      break;
    }

    case 429:
      notify(message, 'negative');
      logoutAndRedirect();
      break;

    case 500:
      notify(
        'Não foi possível concluir a operação no momento. Tente novamente mais tarde ou contate o suporte se o problema persistir.',
        'negative',
      );
      break;

    default: {
      const customMessage =
        message === 'This action is unauthorized.'
          ? 'Você não tem permissão para acessar este recurso'
          : message;
      notify(customMessage, 'negative');
      if (message === 'Unauthorized.' || message === 'This action is unauthorized.') {
        router.replace('/admin/inicio');
      }
      break;
    }
  }
}

export default http;
