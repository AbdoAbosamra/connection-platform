import client from './client'

export const jobsApi = {
  // Public
  list:   (params) => client.get('/jobs', { params }),
  get:    (slug)   => client.get(`/jobs/${slug}`),

  // Job seeker
  recommended: ()        => client.get('/job-seeker/jobs/recommended'),
  saved:       ()        => client.get('/job-seeker/jobs/saved'),
  save:        (id)      => client.post(`/job-seeker/jobs/${id}/save`),
  unsave:      (id)      => client.delete(`/job-seeker/jobs/${id}/save`),
  apply:       (id, data)=> client.post(`/job-seeker/jobs/${id}/apply`, data, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),

  // Employer
  myJobs:       (params) => client.get('/employer/jobs', { params }),
  getJob:       (id)     => client.get(`/employer/jobs/${id}`),
  createJob:    (data)   => client.post('/employer/jobs', data),
  updateJob:    (id, d)  => client.put(`/employer/jobs/${id}`, d),
  deleteJob:    (id)     => client.delete(`/employer/jobs/${id}`),
  toggleStatus: (id)     => client.patch(`/employer/jobs/${id}/toggle-status`),
}
