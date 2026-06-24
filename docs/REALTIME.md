# Real-time (WebSockets)

Messaging and notifications update **live** over WebSockets via **Laravel
Reverb**, with the existing **polling as an automatic fallback**. If Reverb isn't
configured/running, nothing breaks — the app keeps polling.

## Architecture

```
send message ─▶ MessageService ─▶ MessageSent (ShouldBroadcast)
                                      │  PrivateChannel("conversation.{id}")
                                      ▼
in-app notify ─▶ Notification (via: …,'broadcast') ─▶ PrivateChannel("App.Models.User.{id}")
                                      │
                                      ▼  Reverb (Pusher protocol)
                         Vue + Laravel Echo  ──▶  Pinia stores update instantly
```

- **Channels** (`routes/channels.php`):
  - `conversation.{conversation}` → class-based [`ConversationChannel`](../backend/app/Broadcasting/ConversationChannel.php); only participants/admins.
  - `App.Models.User.{id}` → the user themselves (notification bell).
- **Auth**: Echo authenticates private channels at `POST /api/broadcasting/auth`,
  guarded by `auth:sanctum` (bearer token) — so realtime access can't exceed REST access.
- **Safety**: `MessageSent` is dispatched **after** the DB transaction commits;
  the frontend dedupes by message id so optimistic + broadcast copies never double up.

## Enabling it

**Backend**

```bash
cd backend
php artisan reverb:install      # publishes config/reverb.php + sets REVERB_* in .env
# set BROADCAST_CONNECTION=reverb in .env
php artisan reverb:start        # runs the WebSocket server (default :8080)
```

**Frontend** — set in `.env` (matching the backend `REVERB_*`):

```
VITE_REVERB_APP_KEY=<same as REVERB_APP_KEY>
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Leaving `VITE_REVERB_APP_KEY` empty disables realtime (polling only) — this is the
default for local dev and the test suite.

## Tested

- Backend (`tests/Feature/BroadcastingTest.php`): `MessageSent` dispatches on the
  right private channel; payload shape; `ConversationChannel` authorizes only
  participants/admins; the auth endpoint requires authentication.
- Frontend (`src/realtime/echo.spec.js`): all helpers are safe no-ops when
  realtime is disabled (progressive enhancement).

> Tests run with `BROADCAST_CONNECTION=null`, so they assert the broadcast
> *intent* (event + channel + payload) without needing a running WS server.
