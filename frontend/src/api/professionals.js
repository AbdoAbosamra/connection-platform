import client from './client'

export const professionalsApi = {
  /**
   * GET /professionals
   * Params: q, experience_level, availability, skills (comma-separated ids), page, per_page
   */
  list: (params) => client.get('/professionals', { params }),

  /**
   * GET /professionals/:id
   */
  get: (id) => client.get(`/professionals/${id}`),
}
