import http from './AxiosClient';

async function authenticate(credentials) {
  return await http.post('/api/v1/login', credentials);
}

async function unauthenticate() {
  return await http.post('/api/v1/logout');
}

async function myProfile() {
  return await http.get('/api/v1/auth/my-profile');
}

async function register(data) {
  return await http.post('/api/v1/register', data);
}

export { authenticate, myProfile, register, unauthenticate };
