# 2526-TEAM-301 -- StudyBuddy

# Frontend -- React + Vite
This template provides a minimal setup to get React working in Vite with HMR and some ESLint rules.

Currently, two official plugins are available:

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react) uses [Oxc](https://oxc.rs)
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react-swc) uses [SWC](https://swc.rs/)

## React Compiler

The React Compiler is enabled on this template. See [this documentation](https://react.dev/learn/react-compiler) for more information.

Note: This will impact Vite dev & build performances.

## Expanding the ESLint configuration

If you are developing a production application, we recommend using TypeScript with type-aware lint rules enabled. Check out the [TS template](https://github.com/vitejs/vite/tree/main/packages/create-vite/template-react-ts) for information on how to integrate TypeScript and [`typescript-eslint`](https://typescript-eslint.io) in your project.

# StudyBuddy Backend
## Description / Overview
A **FastAPI** 
## Demo (optional but powerful)
Add a GIF, image, or link to a live demo. Visuals grab attention.
## Installation
Step-by-step instructions to get the project running.
## Usage
Show how to run it or use it. Include example commands or screenshots.
## Features
List key features so users know what to expect.
## Tech Stack / Built With
Mention the languages, frameworks, and tools you used.
## Contributing (optional)
If you’re open to collaboration, include guidelines.
## License
Let people know how they can use or reuse your code.
## Credits / Acknowledgments (optional)
Shout out anyone who helped or inspired the project.

## Repository Layout

backend

|app/
│ ├── api/
│ │ ├── deps.py # Dependency functions (DB, auth, current user)
│ │ └── routes/
│ │ ├── study_sessions.py # CRUD endpoints for study sessions
│ │ ├── subjects.py # CRUD endpoints for subjects
│ │ └── tasks.py # CRUD endpoints for tasks
│ ├── core/
│ │ ├── config.py # Project configuration
│ │ └── database.py # SQLAlchemy Base and Session setup
│ ├── models/
│ │ ├── study_session.py
│ │ ├── subject.py
│ │ ├── task.py
│ │ └── user.py
│ ├── schemas/
│ │ ├── study_session.py # Pydantic schemas for validation
│ │ ├── subject.py
│ │ ├── task.py
│ │ └── user.py
│ └── main.py # FastAPI application entry point
├── alembic/ # DB migrations folder
├── tests/ # Pytest test cases
│ ├── test_study_sessions.py
│ ├── test_subjects.py
│ └── test_tasks.py
├── requirements.txt # Python dependencies
├── scripts/
│ ├── dev.sh # Start backend + frontend dev
│ ├── reset_db.sh # Reset database (dev only)
│ └── migrate.sh # Run Alembic migrations
├── .env # Environment variables
├── Makefile # Automation for setup, tests, migrations
└── README.md # This file

# StudyBuddy

StudyBuddy is a student-focused planning and study-tracking application built for the Software and Web Technology course. The project is split into two main parts:

- **Backend**: FastAPI API for authentication, subject management, task tracking, and study session logging
- **Frontend**: web client for interacting with the StudyBuddy platform

The goal of the system is to help students organize courses, plan work, track study time, and measure progress over time.

---

## Project Structure

```text
studybuddy/
├── backend/
│   ├── app/
│   ├── tests/
│   ├── alembic/
│   ├── requirements.txt
│   └── README.md
├── frontend/
│   └── README.md
└── README.md
```
---

# StudyBuddy

StudyBuddy is a full-stack study planning and study-tracking application built for the Software and Web Technology course. It is designed to help students manage subjects, organize tasks, track study sessions, and build a more structured academic workflow.

The project is split into two major parts:

- **Backend**: FastAPI-based REST API for authentication, subjects, tasks, and study session tracking
- **Frontend**: user-facing web application for interacting with the platform

---

## Description / Overview

StudyBuddy is intended as a focused academic productivity platform. The system allows a user to:

- register and authenticate
- create and manage subjects
- create and manage study tasks
- track study sessions
- build a foundation for progress analytics and dashboard-based insights

The repository is organized into a frontend and backend so that the API layer and client layer remain clearly separated and independently maintainable.

---

## Demo

Add one or more of the following here:

- GIF showing the main user flow
- screenshots of login, dashboard, subjects, and tasks
- link to a deployed frontend
- link to backend API docs

Example placeholders:

- Frontend: `http://localhost:5173`
- Backend docs: `http://127.0.0.1:8000/docs`

---

## Installation

See the dedicated setup guides:

- [Backend setup](./backend/README.md)
- [Frontend setup](./frontend/README.md)

---

## Usage

Typical workflow:

1. Start the backend API
2. Start the frontend application
3. Register a new account
4. Create subjects
5. Add tasks
6. Start and end study sessions
7. Review progress and tracked work

---

## Features

- user authentication
- subject management
- task management
- study session tracking
- backend test setup with fixtures
- split frontend/backend architecture
- extensible structure for future dashboard and analytics features

---

## Tech Stack / Built With

### Backend
- Python
- FastAPI
- Uvicorn
- SQLAlchemy
- Alembic
- PyMySQL
- Pydantic
- bcrypt
- python-jose
- pytest
- httpx

### Frontend
- [Replace with actual frontend stack]
- Example: React / Vue / Svelte
- Example: Vite / Next.js / Nuxt
- Example: Tailwind CSS / CSS Modules / SCSS

---

## Contributing

If collaboration is allowed, use a standard contribution workflow:

1. Fork the repository
2. Create a feature branch
3. Make focused changes
4. Add or update tests where relevant
5. Submit a pull request

---

## License

Choose the license that matches the project policy.

Examples:

- MIT
- Apache-2.0
- GPL-3.0
- Academic / private repository only

Example placeholder:

This project is currently distributed for academic use only unless stated otherwise.

---

## Credits / Acknowledgments

- Software and Web Technology course staff
- project teammates and collaborators
- FastAPI, SQLAlchemy, Alembic, and other open-source maintainers

---

## Project Structure

```text
studybuddy/
├── backend/
│   ├── app/
│   ├── tests/
│   ├── alembic/
│   ├── requirements.txt
│   └── README.md
├── frontend/
│   └── README.md
└── README.md