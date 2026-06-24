/**
 * Real-time transport (Laravel Echo + Reverb).
 *
 * This is a *progressive enhancement*: if the VITE_REVERB_* env vars are absent
 * (e.g. local dev without a WebSocket server, or tests) every helper here is a
 * safe no-op and the app keeps working via polling. When Reverb is configured
 * and running, the stores upgrade to live updates automatically.
 */
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echoInstance = null

export function isRealtimeEnabled() {
  return Boolean(import.meta.env.VITE_REVERB_APP_KEY)
}

/**
 * Lazily create (or return) the Echo client. Returns null when realtime is
 * disabled so callers can simply `getEcho()?.private(...)`.
 */
export function getEcho() {
  if (!isRealtimeEnabled()) return null
  if (echoInstance) return echoInstance

  // pusher-js is the protocol Reverb speaks.
  window.Pusher = Pusher

  const apiBase = import.meta.env.VITE_API_BASE_URL || '/api'
  const token = localStorage.getItem('token')

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    // Private channels authenticate against the Sanctum-guarded endpoint with
    // the bearer token, mirroring REST access control.
    authEndpoint: `${apiBase}/broadcasting/auth`,
    auth: { headers: { Authorization: token ? `Bearer ${token}` : '' } },
  })

  return echoInstance
}

/** Tear down the connection (on logout / session change). */
export function disconnectEcho() {
  if (echoInstance) {
    try {
      echoInstance.disconnect()
    } catch {
      /* ignore */
    }
    echoInstance = null
  }
}

/** Leave a single channel without tearing down the whole connection. */
export function leaveChannel(name) {
  echoInstance?.leave(name)
}
