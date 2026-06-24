import client from './client'

const prefix = (role) => (role === 'employer' ? '/employer' : '/job-seeker')

export const messagesApi = {
  // Paginated conversation list for the authed user
  conversations: (role, page = 1) =>
    client.get(`${prefix(role)}/conversations`, { params: { page } }),

  // Aggregate unread count for nav badge
  unreadCount: (role) =>
    client.get(`${prefix(role)}/conversations/unread-count`),

  // Paginated messages in a conversation
  messages: (role, convId, page = 1) =>
    client.get(`${prefix(role)}/conversations/${convId}/messages`, { params: { page } }),

  // Send a message (supports FormData for attachments)
  send: (role, convId, data) => {
    // If data contains a file, send as multipart/form-data
    if (data.attachment instanceof File) {
      const fd = new FormData()
      fd.append('body', data.body)
      fd.append('attachment', data.attachment)
      return client.post(`${prefix(role)}/conversations/${convId}/messages`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    return client.post(`${prefix(role)}/conversations/${convId}/messages`, data)
  },

  // Mark all messages in a conversation as read
  markRead: (role, convId) =>
    client.post(`${prefix(role)}/conversations/${convId}/read`),

  // Employer only: open / find conversation with a job seeker
  initiate: (data) =>
    client.post('/employer/conversations', data),
}
