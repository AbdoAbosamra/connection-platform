import client from './client'

export const employerApi = {
  // Aggregate dashboard/analytics stats + the international-hiring flag.
  stats: () => client.get('/employer/stats'),
}
