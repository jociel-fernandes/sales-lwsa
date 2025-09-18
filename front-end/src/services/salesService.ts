import http from './http'

type Sale = {
  id?: number
  seller_id?: number | null
  date: string
  value: number
  description?: string
}

export default {
  async list(page = 1, params: Record<string, unknown> = {}) {
    const p = { page, ...params }
    const res = await http.get('/api/sales', { params: p })
    return res.data
  },

  async get(id: number) {
    const res = await http.get(`/api/sales/${id}`)
    return res.data
  },

  async create(payload: Sale) {
    const res = await http.post('/api/sales', payload)
    return res.data
  },

  async update(id: number, payload: Partial<Sale>) {
    const res = await http.put(`/api/sales/${id}`, payload)
    return res.data
  },

  async remove(id: number) {
    const res = await http.delete(`/api/sales/${id}`)
    return res.data
  },
}
