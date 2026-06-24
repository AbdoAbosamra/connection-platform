import { describe, it, expect } from 'vitest'
import { isRealtimeEnabled, getEcho, disconnectEcho, leaveChannel } from '@/realtime/echo'

describe('realtime echo (progressive enhancement)', () => {
  it('is disabled when no Reverb key is configured', () => {
    // No VITE_REVERB_APP_KEY in the test env.
    expect(isRealtimeEnabled()).toBe(false)
  })

  it('getEcho returns null when realtime is disabled', () => {
    expect(getEcho()).toBeNull()
  })

  it('disconnect and leave are safe no-ops when disabled', () => {
    expect(() => disconnectEcho()).not.toThrow()
    expect(() => leaveChannel('conversation.1')).not.toThrow()
  })
})
