import client from './client'

export const interviewsApi = {
  // Employer
  schedule: (applicationId, data) =>
    client.post(`/employer/applications/${applicationId}/interviews`, data),
  reschedule: (interviewId, data) =>
    client.patch(`/employer/interviews/${interviewId}`, data),
  cancelByEmployer: (interviewId) =>
    client.delete(`/employer/interviews/${interviewId}`),

  // Job seeker
  mine: (page = 1) => client.get('/job-seeker/interviews', { params: { page } }),
  confirm: (interviewId) =>
    client.patch(`/job-seeker/interviews/${interviewId}/confirm`),
  decline: (interviewId) =>
    client.patch(`/job-seeker/interviews/${interviewId}/cancel`),
}
