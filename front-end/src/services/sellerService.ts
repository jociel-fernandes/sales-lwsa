import http from './http'

type Seller = {
  id?: number
  name: string
  email: string
  phone?: string | null
}

export default {
  // list with pagination and optional search q param
  async list(page = 1, q = '') {
    const params: Record<string, unknown> = { page }
    if (q) params.search = q // backend expects 'search' param
    const res = await http.get('/api/sellers', { params })
    // backend returns standard Laravel pagination
    return res.data
  },

  async get(id: number) {
    const res = await http.get(`/api/sellers/${id}`)
    return res.data.data || res.data
  },

  async me() {
    const res = await http.get('/api/sellers/me')
    return res.data
  },

  async create(payload: Seller) {
    const res = await http.post('/api/sellers', payload)
    return res.data
  },

  async update(id: number, payload: Partial<Seller>) {
    const res = await http.put(`/api/sellers/${id}`, payload)
    return res.data
  },

  async remove(id: number) {
    const res = await http.delete(`/api/sellers/${id}`)
    return res.data
  },
  async resendCommissionEmail(id: number, date?: string) {
    const payload: Record<string, unknown> = {}
    if (date) payload.date = date
    const res = await http.post(`/api/sellers/${id}/resend-commission-email`, payload)
    return res.data
  },
}
