import client from './client'

export const billingApi = {
  plans: () => client.get('/subscription-plans'),
  current: () => client.get('/employer/subscription'),
  subscribe: (planId, billingPeriod) =>
    client.post('/employer/subscription', { plan_id: planId, billing_period: billingPeriod }),
  cancel: () => client.delete('/employer/subscription'),
}
