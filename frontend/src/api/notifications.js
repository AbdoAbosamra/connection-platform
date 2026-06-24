import client from './client'

export const notificationsApi = {
  list: (page = 1) => client.get('/notifications', { params: { page } }),
  unreadCount: () => client.get('/notifications/unread-count'),
  markRead: (id) => client.post(`/notifications/${id}/read`),
  markAllRead: () => client.post('/notifications/read-all'),
  remove: (id) => client.delete(`/notifications/${id}`),
}
