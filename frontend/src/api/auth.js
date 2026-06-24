import client from './client'

export const authApi = {
  register: (data)  => client.post('/auth/register', data),
  login:    (data)  => client.post('/auth/login', data),
  me:       ()      => client.get('/auth/me'),
  logout:   ()      => client.post('/auth/logout'),
  forgotPassword: (email) => client.post('/auth/forgot-password', { email }),
  resetPassword:  (data)  => client.post('/auth/reset-password', data),
}
