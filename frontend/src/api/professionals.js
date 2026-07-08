import client from './client'

export const professionalsApi = {
  /**
   * GET /professionals
   * Basic params:    q, experience_level, availability, skills (csv ids),
   *                  industry, education_level, remote_experience_min,
   *                  salary_min, salary_max, page, per_page
   * Advanced params: country, time_zone, languages (csv), contract_type,
   *                  has_portfolio, has_certifications, has_security_clearance
   */
  list: (params) => client.get('/professionals', { params }),

  /**
   * GET /professionals/:id
   */
  get: (id) => client.get(`/professionals/${id}`),

  /**
   * GET /skills — typeahead for the Skills filter.
   */
  skills: (search) => client.get('/skills', { params: { search } }),
}
