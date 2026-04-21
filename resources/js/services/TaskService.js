import http from './AxiosClient';

const route = 'api/v1/tasks';

const index = async (params) => {
  const { data } = await http.get(route, { params });
  return data;
};

const get = async (id) => {
  const { data } = await http.get(`${route}/${id}`);
  return data;
};

const store = async (params) => {
  const { data } = await http.post(route, params);
  return data;
};

const update = async (id, params) => {
  const { data } = await http.put(`${route}/${id}`, params);
  return data;
};

const updateStatus = async (id, params) => {
  const { data } = await http.patch(`${route}/${id}/status`, params);
  return data;
};

const destroy = async (id) => {
  return await http.delete(`${route}/${id}`);
};

export default {
  index,
  get,
  store,
  update,
  updateStatus,
  destroy,
};
