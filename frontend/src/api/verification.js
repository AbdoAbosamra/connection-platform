import client from './client'

export const verificationApi = {
  status: () => client.get('/employer/verification'),
  payment: () => client.post('/employer/verification/payment'),
  linkedInUrl: () => client.get('/employer/verification/linkedin/redirect'),
}
