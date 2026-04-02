
# StudyBuddy Backend

AGAIN PARTIALLY AI GENERATED README

## Description / Overview

The StudyBuddy backend is a **FastAPI** application that provides the API layer for the StudyBuddy platform.

It is responsible for:

- user registration and login
- JWT-based authentication
- subject management
- task management
- study session tracking
- database persistence
- testing infrastructure for backend verification and extension

The backend is organized around the following core domain entities:

- `User`
- `Subject`
- `Task`
- `StudySession`

Together, these models define the base structure for a study planning and study-tracking application.

---

## Demo

xxx

---

## Installation

### 1. Clone the repository

```bash
git clone <your-repository-url>
cd studybuddy/backend
```

### 2. Create and activate a virtual environment
**Linux/MacOS**
```bash
python -m venv .venv
source .venv/bin/activate
```
**Windows PowerShell**
```ps
python -m venv .venv
.venv\Scripts\Activate.ps1
```

### 3. Install dependencies
```python
pip install -r requirements.txt
```

### 4. Create the database
**Example:**
```sql
CREATE DATABASE studybuddy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'studybuddy'@'localhost' IDENTIFIED BY 'change-me';
GRANT ALL PRIVILEGES ON studybuddy.* TO 'studybuddy'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Create a .env file
**Example:**
```python
APP_NAME=StudyBuddy API
APP_ENV=dev
SECRET_KEY=change-this-to-a-long-random-secret
ACCESS_TOKEN_EXPIRE_MINUTES=1440
DATABASE_URL=mysql+pymysql://studybuddy:change-me@127.0.0.1:3306/studybuddy
```

### 6. Run database migrations
- If Alembic is configured:
```python
alembic upgrade head
```
- If migrations still need to be generated:
```baspythonh
alembic revision --autogenerate -m "initial schema"
alembic upgrade head
```
### 7. Start dev server
```python
uvicorn app.main:app --reload
```

---

## Usage