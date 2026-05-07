import client from './client'

const prefix = (role) => role === 'employer' ? '/employer' : '/job-seeker'

export const messagesApi = {
  conversations: (role)            => client.get(`${prefix(role)}/conversations`),
  messages:      (role, convId)    => client.get(`${prefix(role)}/conversations/${convId}/messages`),
  send:          (role, convId, d) => client.post(`${prefix(role)}/conversations/${convId}/messages`, d),
  initiate:      (data)            => client.post('/employer/conversations', data),
}
