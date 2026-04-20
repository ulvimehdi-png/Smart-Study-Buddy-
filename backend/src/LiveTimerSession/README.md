## Realtime timer reference feature (Symfony + Mercure)

This project contains a separate `TimerMercure` reference page that demonstrates a clean realtime feature pattern using Symfony, Symfony Cache, and Mercure.

### Why this exists

The original timer page is a local UI-first timer.  
`TimerMercure` is a backend-owned realtime example intended to show how to build multi-client live features in this codebase without introducing a second backend.

### Architecture

- **React page** sends HTTP commands
- **Symfony controller/services** apply domain transitions
- **Symfony Cache** stores ephemeral live session state
- **Mercure** broadcasts the updated projection to subscribers
- **MySQL/Doctrine** stores only finalized session data

### State split

There are three layers of state:

1. **Durable domain data**  
   Persisted in MySQL (`sessions`, optionally `session_entries`)

2. **Ephemeral live state**  
   Stored in Symfony Cache while the timer is active

3. **Realtime transport**  
   Mercure distributes projections to connected clients

### Command model

The browser never sends full live state snapshots back as authority.  
Instead, it sends explicit commands such as:

- create
- start
- pause
- resume
- skip phase
- reset phase
- change config
- stop and save

Symfony applies the command, stores the updated live state, projects it, and publishes the projection through Mercure.

### Projection model

Mercure publishes **full projections** on **discrete state transitions only**.

No per-second server updates are sent.

The client derives the visible countdown from the latest projected `phaseEndsAt` / `remainingMs` values.

### Ownership model

For now, live timer sessions are **owner-only**.

The user who creates a live timer session is the only user allowed to:
- load it
- mutate it
- delete it
- stop and save it

This keeps the feature simple and safe.

A future extension could add a separate read-only share token for multi-user viewing while keeping commands owner-only.

### Why Mercure instead of a custom WebSocket server

Mercure fits this feature because the timer needs:
- explicit backend-owned state transitions
- low-latency fanout to multiple viewers
- no continuous bidirectional socket stream
- a simple subscriber model in the browser via `EventSource`

### Why the live timer is not stored in MySQL

The live timer state is temporary and should expire automatically.

Persisting it permanently would mix transient UI/runtime state with real domain data.

Only the finalized result is saved to MySQL when the session is stopped and committed.

### Files of interest

- `backend/src/Controller/LiveTimerSessionController.php`
- `backend/src/Service/LiveTimerSessionStore.php`
- `backend/src/Service/LiveTimerSessionManager.php`
- `backend/src/Service/LiveTimerSessionProjector.php`
- `backend/src/Service/LiveTimerSessionPublisher.php`
- `backend/src/Service/LiveTimerSessionFinalizer.php`
- `frontend/src/lib/mercure.js`
- `frontend/src/lib/liveTimerSessionApi.js`
- `frontend/src/features/liveTimerSession/useLiveTimerSession.js`
- `frontend/src/pages/TimerMercurePage.jsx`


That makes the architecture very easy to defend in review.

---

# 4. What is actually left after this

Once ownership, tests, and README are in, the feature is effectively finished.

The only things left would be optional refinements:

- add note/distraction inputs to the UI for stop-and-save
- attach subject/task selection in the page
- add read-only share tokens if you want real multi-user viewing
- add better UI polish

But architecturally, it is done.

---

# 5. Recommended order right now

Do it in this order:

1. add `ownerUserId` and controller ownership checks
2. verify the feature still works in-browser
3. add the projector and manager unit tests
4. add the functional controller test
5. add the README section and diagram

That is the clean finishing pass.

```mermaid
flowchart LR
    A[React TimerMercure Page] -->|HTTP command| B[Symfony Controller]
    B --> C[LiveTimerSessionManager]
    C --> D[Symfony Cache]
    C --> E[LiveTimerSessionProjector]
    E --> F[Mercure Hub]
    F -->|SSE/EventSource| A
    B -->|stop-and-save| G[Doctrine/MySQL sessions]
