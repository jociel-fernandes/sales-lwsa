import http from './http';

export default {
  async list() {
    const res = await http.get('/api/settings');
    return res.data;
  },

  async store(payload: FormData | Record<string, unknown>) {
    const res = await http.post('/api/settings', payload);
    return res.data;
  },

  async update(id: number | string, payload: FormData | Record<string, unknown>) {
    const res = await http.put(`/api/settings/${id}`, payload);
    return res.data;
  },
};
