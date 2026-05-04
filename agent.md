# Project Guide

## Overview

This repository contains a full-stack study planner/productivity app named `StudyBuddy`.

Primary user-facing features currently implemented:

- username/password authentication
- task management with completion tracking
- note taking linked to subjects
- automatic subject creation/deduplication from task/note input
- dashboard metrics for notes, study time, streaks, and task progress
- leaderboard and achievement points
- profile editing and profile image upload
- a realtime Pomodoro-style timer built with Symfony Cache + Mercure

The project is split into:

- `frontend/`: React 19 + Vite SPA
- `backend/`: Symfony 8 API with Doctrine ORM
- `docker/`: local development environment for MySQL, PHP, Node, Mailpit, and Mercure

## Tech Stack

### Frontend

- React 19
- React Router 7
- Vite 8
- Tailwind CSS 4
- `motion` for animations
- `lucide-react` for icons
- Mercure subscription via `EventSource`

Important frontend entry points:

- `frontend/src/App.jsx`: route map
- `frontend/src/lib/auth.js`: auth context and session bootstrap
- `frontend/src/lib/useTimer.js`: timer state orchestration
- `frontend/src/lib/liveTimerSessionApi.js`: timer API client
- `frontend/src/lib/mercure.js`: Mercure helpers

### Backend

- PHP 8.4+
- Symfony 8
- Doctrine ORM + Doctrine Migrations
- Symfony Security with `json_login`
- Mercure bundle for realtime events
- Nelmio CORS bundle
- Zenstruck Foundry + fixtures
- PHPUnit 13

Important backend areas:

- `backend/src/Controller/`: HTTP JSON API
- `backend/src/Entity/`: domain model
- `backend/src/Service/`: domain helpers
- `backend/src/LiveTimerSession/`: realtime timer subsystem
- `backend/src/DataFixtures/` and `backend/src/Story/`: seed data

### Infrastructure

Docker Compose services in `docker/compose.yaml`:

- `database`: MySQL 9.6, exposed on `localhost:3307`
- `php`: Symfony app, exposed on `localhost:8000`
- `node`: Vite dev server, exposed on `localhost:5173`
- `mercure`: Mercure hub, exposed on `localhost:3000`
- `mailer`: Mailpit, exposed on `localhost:8025`

## Product Structure

### Frontend routes

Defined in `frontend/src/App.jsx`:

- `/`: home page
- `/login`: login/register page
- `/timer`: protected realtime/local timer page
- `/tasks`: protected tasks page
- `/dashboard`: protected dashboard
- `/notes`: protected notes page
- `/leaderboard`: protected points leaderboard
- `/user`: protected user profile page
- `/test`: test/demo page

Protected routes use `frontend/src/pages/ProtectedRoute.jsx`, which waits for `authChecked` before redirecting.

### API routes

Main implemented controllers:

- `SecurityController`
  - `POST /api/register`
  - `POST /api/login`
  - `POST /api/logout`
  - `GET /api/user`
  - `PATCH /api/user/profile`
  - `PATCH /api/user/password`
  - `POST /api/user/profile-image`
  - `DELETE /api/user/profile-image`
- `TasksController`
  - `GET /api/tasks`
  - `POST /api/tasks`
  - `PUT /api/tasks/{id}/toggle`
  - `DELETE /api/tasks/{id}`
- `NotesController`
  - `GET /api/notes`
  - `POST /api/notes`
  - `DELETE /api/notes/{id}`
- `SubjectsController`
  - `GET /api/subjects`
  - `DELETE /api/subjects/{id}`
- `DashboardController`
  - `GET /api/dashboard`
- `AchievementController`
  - `GET /api/achievements/leaderboard`
  - `GET /api/achievements/mine`
- `LiveTimerSession` controllers
  - create/get/delete live sessions
  - start/pause/skip/reset/change-config/stop style commands

Security rules in `backend/config/packages/security.yaml`:

- `/api/login` and `/api/register` are public
- all other `/api/*` routes require full authentication

## Authentication Model

- Login uses Symfony `json_login` with username/password JSON payloads.
- Frontend always sends `credentials: "include"`.
- Session bootstrap happens by calling `GET /api/user`.
- Auth state is held client-side in `AuthProvider`.

Notes:

- The backend includes Lexik JWT as a dependency, but the currently active login flow is session-style `json_login`, not an exposed JWT token API.
- `frontend/src/lib/auth.js` names the first login field `email`, but it is sent as `username`.

## Core Domain Model

Key entities in `backend/src/Entity/`:

- `User`: username, optional email/display name, profile image path, points, roles, settings
- `Settings`: per-user settings container
- `Subject`: user-owned study subject; soft deletable
- `Task`: belongs to user, optional subject, status/priority/dates, soft deletable
- `Note`: belongs to user, optional subject/task, content/source type, soft deletable
- `Session`: durable study/focus session history
- `SessionEntry`: per-session granular entries
- `Achievement` and `UserAchievement`: gamification system
- `Tag`, `TaskTag`, `NoteTag`, `SubjectTag`, `ResourceTag`: tagging model
- `Resource`: modeled in backend but not obviously surfaced in current frontend routes

Cross-cutting entity conventions:

- many entities use `TimestampableTrait`
- several user content entities use `ArchivableSoftDeletableTrait`
- deletes in current controllers are usually soft deletes, not hard deletes

Enums used by the domain include:

- `TaskStatus`
- `TaskPriority`
- `NoteSourceType`
- `ResourceType`
- `WeekStartDay`

## Business Rules Already in Code

- Subjects are auto-created from task/note creation input through `SubjectService`.
- Subject lookup is case-insensitive and restores soft-deleted subjects if reused.
- Task completion currently awards 10 points; uncompleting removes 10 points.
- Achievements, once earned, are not revoked when points drop.
- Dashboard metrics are user-scoped and based on non-deleted notes/tasks plus stored `Session` history.
- Profile image uploads allow jpeg/png/webp/gif and reject files over 2 MB.

Important caveat from code comments:

- `SubjectService` assumes subject names are unique per user, but the database does not currently enforce a `(user_id, name)` unique constraint.

## Realtime Timer Architecture

The timer exists in two layers:

- older local UI timer behavior in the frontend
- backend-owned realtime timer in `backend/src/LiveTimerSession/`

Current live timer design:

- client sends explicit commands to the backend
- backend stores ephemeral live session state in Symfony Cache
- backend projects state to transport JSON
- Mercure broadcasts the full projection on state transitions
- frontend derives the visible per-second countdown locally from `phaseEndsAt`
- durable `Session` and `SessionEntry` records are only written when the live session is finalized

Important timer files:

- `backend/src/LiveTimerSession/README.md`
- `backend/src/LiveTimerSession/Controller/`
- `backend/src/LiveTimerSession/Service/`
- `frontend/src/lib/useTimer.js`
- `frontend/src/lib/liveTimerSessionApi.js`

Current limitation documented in code/docs:

- live timer sessions are owner-only right now; sharing/observer permissions are planned but not finished

## Local Development

Primary documented workflow:

```bash
make -C docker dev
make -C docker down
make -C docker reset
make -C docker logs
make -C docker bash
make -C docker console
make -C docker install
make -C docker migrate
```

Expected local endpoints from `docker/compose.yaml`:

- frontend: `http://localhost:5173`
- backend: `http://localhost:8000`
- mercure: `http://localhost:3000/.well-known/mercure`
- mailpit UI: `http://localhost:8025`
- mysql: `localhost:3307`

Notes:

- `frontend/vite.config.js` proxies `/api` and `/uploads` to the PHP container.
- `docker/Makefile` currently prints swapped/wrong URLs in `make dev`; trust `docker/compose.yaml` instead.
- Mercure depends on `MERCURE_JWT_SECRET`.
- CORS is controlled by `CORS_ALLOW_ORIGIN`.

Optional Docker-local env vars can be placed in `docker/.env`:

- `UID`
- `GID`

## Fixtures and Seed Data

Backend fixture docs live in `backend/README.md`.

Useful commands:

```bash
APP_ENV=dev php bin/console doctrine:database:drop --if-exists --force
APP_ENV=dev php bin/console doctrine:database:create
APP_ENV=dev php bin/console doctrine:migrations:migrate -n
APP_ENV=dev php bin/console foundry:load-fixtures --group=minimal
```

Available fixture groups/stories mentioned in the repo:

- `minimal`
- `base`
- `dev`
- `demo`
- `ui`
- `large`

Relevant files:

- `backend/src/DataFixtures/`
- `backend/src/Story/`
- `backend/src/Factory/`

## Testing and Quality

Frontend:

- build: `npm run build`
- lint: `npm run lint`

Backend:

- tests: `php bin/phpunit`

Important caveat:

- the existing controller tests in `backend/tests/Controller/` look like default skeleton tests and still target paths such as `/task`, `/note`, and `/dashboard`, which do not match the implemented `/api/...` routes. Treat them as stale until updated.

## Files Worth Reading First

If you need to work on this project, start here:

1. `README.md`
2. `docker/README.md`
3. `frontend/src/App.jsx`
4. `frontend/src/lib/auth.js`
5. `frontend/src/lib/useTimer.js`
6. `backend/src/Controller/`
7. `backend/src/Entity/`
8. `backend/src/LiveTimerSession/README.md`

## Current Gaps / Inconsistencies Visible in Repo

- top-level `README.md` still contains template placeholders
- frontend `README.md` is mostly the default Vite template
- backend test suite is stale relative to the real API
- Docker Makefile status output is inaccurate
- JWT dependency exists even though the active auth flow is session-oriented
- `Resource` domain model exists but is not clearly surfaced in the current SPA routes
- some files and migrations are already modified/untracked in the working tree, so inspect local changes before broad refactors

## Working Assumptions For Future Agents

- Prefer preserving existing API shapes because frontend pages fetch raw controller JSON directly.
- When changing tasks/notes/subjects, check both controller output and dashboard aggregation logic.
- When changing auth/profile behavior, verify both `SecurityController` and `frontend/src/lib/auth.js`.
- When changing timer behavior, check backend state transitions, Mercure publishing, and frontend projection derivation together.
- Be careful with soft-delete semantics; many queries explicitly filter `deletedAt IS NULL`.
