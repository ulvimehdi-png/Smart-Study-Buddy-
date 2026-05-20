# SEWT Project Repository

## Project URL
Provide a link to the main page of your application. Or if you have multiple parts in your website you can provide a list of links (i.e. not all pages are in your main navigation bar)

* [Main login page](https://a25sewt301.studev.groept.be/login)
* [Admin page](https://a25sewt301.studev.groept.be/admin)

---


### Regular user

* login: `demo`
* password: `secret`

### Admin / Developer

* login: `admin`
* password: `secret`

---

## Implemented Features

### User authentication

The application supports user registration, login, logout, and authenticated API access. Users can retrieve their current account state through the API and maintain an authenticated session while using the React frontend.

Implemented authentication-related functionality:

* login and logout
* user registration
* current-user endpoint
* authenticated API requests
* protected user profile routes
* role-based admin/developer access

### User profile management

Users can manage basic profile data from the application.

Implemented profile functionality:

* view current profile data
* update username
* update password
* upload profile image/avatar
* delete profile image/avatar
* server-side validation for profile changes
* static avatar serving through `/upload/...`

Uploaded files are stored outside the React build output and are served as static files. PHP execution is disabled for the upload directory on the production server.

### Subjects

Users can create and organize subjects or courses. Subjects are used as the main grouping mechanism for study content.

Implemented subject functionality:

* create subjects
* view subject list
* associate tasks with subjects
* associate notes with subjects
* use subjects in dashboard/progress views

### Tasks

The application includes task management for study planning and follow-up.

Implemented task functionality:

* create tasks
* view tasks
* update task information
* mark tasks as complete or incomplete
* delete tasks
* associate tasks with users and subjects
* show task data in dashboard views

### Notes

Users can create study notes and connect them to subjects and tasks.

Implemented note functionality:

* create notes
* view notes
* update notes
* delete or archive notes where supported
* associate notes with subjects
* associate notes with tasks
* use notes as study material in the application

### Study sessions and live timer functionality

The application includes study-session functionality intended for Pomodoro-style or focused study workflows.

Implemented session-related functionality:

* create live timer sessions
* retrieve live timer session state
* start, pause, resume, reset, skip, and stop timer sessions where supported
* store session-related state
* associate completed/finalized work with subjects, tasks, or notes where supported
* Mercure-based realtime/session-update infrastructure in development

### Dashboard

The dashboard gives users a summarized view of their study activity.

Implemented dashboard functionality:

* user-specific dashboard data
* study progress overview
* recent or relevant tasks
* subject/task/note-related summaries
* populated demo data for presentation and testing

### Admin / developer page

The project contains an admin/developer page for operational diagnostics and demonstration support.

Implemented admin/developer functionality:

* Mercure health check
* writable upload/avatar directory check
* latest application logs
* latest errors-only view
* current admin username and roles
* latest registered users
* links to deployed diagnostic/static reports where configured

### Static uploads

The application supports static upload serving from:
```
/upload/...
/upload/avatars/...
/upload/coverage/...
```
The upload directory is excluded from normal app deployment so user-generated and generated static files survive deployments.

# PHPUnit Testing

The backend has PHPUnit-based automated tests.

## Implemented Test Support

* PHPUnit configuration
* Symfony `WebTestCase` tests for API/controller behavior
* Local test execution without coverage
* CI test execution with coverage generation
* Generated HTML-style coverage artifacts where configured
* Test coverage publishing

## Test Coverage Publishing

The CI flow can generate PHPUnit coverage and deploy it as static files under the upload directory.

Expected coverage URL pattern:

```txt
/upload/coverage/html/index.html
```

# CI/CD and Deployment

The project includes GitLab CI/CD automation using a Makefile-driven workflow.

## Implemented Automation

* Validation stage
* Test stage
* Production artifact build
* Manual deploy stage
* rsync-based deployment
* Environment file deployment
* Doctrine migration execution
* Symfony cache clear and warmup
* Static coverage deployment
* Persistent upload directory preservation

# Docker / Compose Development Setup

The project supports local containerized development.

## Implemented Development Infrastructure

* PHP/Symfony container
* React/Vite container
* MySQL development database
* Mercure development container
* Docker Compose profiles for dev, test, coverage, and prod-like flows
* Makefile targets for common workflows

# Main Technology Stack

## Backend

* PHP
* Symfony
* Doctrine ORM
* MySQL
* PHPUnit
* Foundry fixtures/stories
* Mercure for realtime functionality

## Frontend

* React
* Vite
* Tailwind CSS
* JavaScript/JSX

## Infrastructure

* Docker / Docker Compose
* Apache
* GitLab CI/CD
* rsync deployment
* PHPUnit coverage artifacts


