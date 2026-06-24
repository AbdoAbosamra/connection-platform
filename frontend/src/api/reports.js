import client from './client'

export const reportsApi = {
  // type: 'job' | 'user', id: number
  create: ({ type, id, reason, details }) =>
    client.post('/reports', { type, id, reason, details }),
}
