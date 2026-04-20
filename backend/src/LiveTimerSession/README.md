
---

{{ Proofread and edited, but please be aware that 
this file was generated with AI and may contain mistakes. }}

---
# LiveTimerSession

Realtime Pomodoro-style timer reference feature built with Symfony, Symfony Cache, and Mercure.

This module exists as a backend-owned realtime feature (example, for now) for the codebase. 
It shows how to implement a multi-client live feature without introducing a separate websocket backend.

---
## Why this exists

The original timer page is a local UI-first timer.

`LiveTimerSession` is the reference implementation for a backend-owned realtime timer:
- the browser sends explicit commands
- Symfony applies the state transition
- ephemeral live state is stored in cache
- Mercure broadcasts the resulting projection
- only finalized session results are written to MySQL

---

## Core architecture

The feature is split into four responsibilities:

1. **HTTP command/query boundary**
    - controllers receive requests
    - DTOs validate payloads
    - owner checks are enforced at the boundary

2. **Ephemeral live session state**
    - the active timer lives in Symfony Cache
    - this state is temporary runtime state, not durable domain history

3. **Projection + realtime fanout**
    - the backend produces a full projection for the current session
    - Mercure publishes that projection to subscribed clients

4. **Persistence on finalization**
    - when the session is stopped and saved, the live state is converted into durable `Session` / `SessionEntry` data and persisted to MySQL

---

## Request flow

### Read/query flow

- client requests the current live session
- backend loads the live state from cache
- backend projects the state into the transport shape returned to the browser
- browser renders from that projection

### Command flow

- client sends an explicit command such as start, pause, resume, skip phase, reset phase, change config, or stop and save
- backend loads the current live state
- backend applies the transition
- backend persists the updated live state back into cache
- backend projects the updated state
- backend publishes the projection through Mercure
- subscribed clients receive the full updated projection

### Finalization flow

- client sends `stop-and-save`
- backend loads the live state
- backend resolves the effective timer state
- backend converts it into durable session data
- backend persists the result to MySQL
- backend publishes a deleted event for the live session
- backend removes the ephemeral live session from cache

---

## State model

The feature intentionally separates state into three layers.

### 1. Durable domain state

Persisted in MySQL:
- `sessions`
- `session_entries`

This is historical data that should remain after the live timer is finished.

### 2. Ephemeral live state

Stored in Symfony Cache while the timer is active.

This state contains the runtime information needed to drive the live timer, such as:
- owner user id
- status
- phase
- configured work/break durations
- either `phaseEndsAt` or `remainingMs`
- accumulated focus and break time
- related subject/task ids
- timestamps such as `startedAt` / `updatedAt`

This state is temporary and should disappear when the live session is deleted or finalized.

### 3. Realtime transport state

Published through Mercure as a projection.

The browser does not receive raw cache internals or mutate cache state directly. 
It receives a projection intended for transport and rendering.

---

## Ownership model

The authenticated user who creates a live session is the only user allowed to:
- load it
- mutate it
- delete it
- stop and save it

So live timer sessions are currently **owner-only**.

==> MUST BE CHANGED, THIS IS THE NEXT PART TO IMPLEMENT

(A future extension could add read-only share 
tokens for observers while keeping commands owner-only.
After this, also implement other permissions to (not) share when desied.) 

---

## Command model

The browser is not allowed to send full timer state back as authority.

Instead, it sends explicit commands.

Supported command categories:

- create session
- start
- pause
- resume
- skip phase
- reset phase
- change config
- stop and save
- delete session

This keeps ownership of business rules in the backend and 
makes invalid state transitions much easier to prevent.

---

## Projection model

Mercure publishes **full projections** on **discrete state transitions**.

The server does **not** send per-second updates.

Instead:
- when the timer changes, the backend publishes a full projection
- when the timer is running, the projection exposes `phaseEndsAt`
- when the timer is not running, the projection exposes `remainingMs`
- the frontend derives the visible countdown locally from the latest projection

This is the key scalability and simplicity decision in the feature:
- backend owns transitions
- frontend owns visual ticking between transitions

==> other timer/sessions options will have to be added/configured since
right now it is solely pomodoro

---

## Why Symfony Cache is used for the live timer

The live timer is runtime state, not durable business history and thus
should not be store in MySQL to avoid mixing temporary execution state
with durable session history.

That would make the domain model noisier and create cleanup concerns 
for data that should naturally expire or be deleted after finalization.

Symfony Cache is a better fit because the live timer:
- is short-lived
- is mutable
- is only relevant while active
- does not need durable history until finalized

---

## On the use of Mercure

Mercure is a provides for this feature:
- backend-owned transitions
- low-latency fanout to one or more viewers
- simple browser subscriptions via `EventSource`
- no custom websocket protocol (→ SSE)
- no permanently open bidirectional application channel

This feature is fundamentally command-driven, not stream-driven.

---

## Structure

Again, up for change when refining, changing and adding to this feauture.

```text
backend/src/LiveTimerSession/
├── Controller
│   ├── AbstractSessionController.php
│   ├── SessionActionController.php
│   └── SessionController.php
├── DTO
│   ├── Command
│   │   ├── ChangeConfig.php
│   │   ├── CreateSession.php
│   │   ├── EmptyCommand.php
│   │   └── StopSession.php
│   └── DurationDto.php
├── Enum
│   ├── SessionPhase.php
│   ├── SessionStatus.php
│   └── SessionType.php
├── README.md
└── Service
    ├── Finalizer.php
    ├── Projector.php
    ├── Publisher.php
    ├── StateHydrator.php
    ├── State.php
    ├── Store.php
    └── TimeService.php
```
