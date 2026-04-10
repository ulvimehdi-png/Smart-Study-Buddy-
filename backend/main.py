from fastapi import FastAPI, Depends, HTTPException, Response, Request
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
from sqlalchemy import text
import bcrypt
import uuid
from pydantic import BaseModel
from database import get_db
from typing import Optional
from datetime import date, timedelta

app = FastAPI()

# This allows your React app to talk to your Python app and share cookies
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost:3000",
        "http://localhost:5173",
        "http://127.0.0.1:5173"
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# A simple dictionary to remember who is logged in (SessionID -> Username)
active_sessions = {}

# Data models for what the frontend sends us
class UserCreate(BaseModel):
    username: str
    password: str
    
class NoteData(BaseModel):
    userId: int
    title: str
    content: str
    subject: Optional[str] = None
    sourceType: Optional[str] = None
    fileName: Optional[str] = None

class TaskData(BaseModel):
    userId: int
    title: str
    subject: Optional[str] = None
    description: Optional[str] = None
    dueDate: Optional[str] = None

class SessionData(BaseModel):
    userId: int
    subject: str
    minutesStudied: int
    sessionNote: Optional[str] = None

@app.post("/register")
def register(user: UserCreate, db: Session = Depends(get_db)):
    # Scramble (hash) the password so it's safe to store
    hashed_pw = bcrypt.hashpw(user.password.encode('utf-8'), bcrypt.gensalt())
    
    try:
        db.execute(
            text("INSERT INTO users (username, hashed_password) VALUES (:u, :p)"),
            {"u": user.username, "p": hashed_pw.decode('utf-8')}
        )
        db.commit()
        return {"message": "User created successfully"}
    except Exception:
        raise HTTPException(status_code=400, detail="Username already exists")

@app.post("/login")
def login(user: UserCreate, response: Response, db: Session = Depends(get_db)):
    # Find the user in the database
    result = db.execute(
        text("SELECT hashed_password FROM users WHERE username = :u"),
        {"u": user.username}
    ).fetchone()

    if not result:
        raise HTTPException(status_code=400, detail="Incorrect username or password")

    # Check if the password matches the scrambled password in the database
    stored_hash = result[0].encode('utf-8')
    if not bcrypt.checkpw(user.password.encode('utf-8'), stored_hash):
        raise HTTPException(status_code=400, detail="Incorrect username or password")

    # Create a random session ID and remember it
    session_id = str(uuid.uuid4())
    active_sessions[session_id] = user.username

    # Give the session ID to the user's browser as a cookie
    response.set_cookie(key="session_id", value=session_id, httponly=True)
    return {"message": "Logged in successfully"}

@app.get("/user")
def get_user(request: Request):
    # Used by the frontend on refresh to restore auth state from cookie session.
    session_id = request.cookies.get("session_id")

    if session_id not in active_sessions:
        raise HTTPException(status_code=401, detail="Not logged in")

    return {"user": active_sessions[session_id]}

@app.get("/dashboard")
def get_dashboard(request: Request):
    # Check if the browser sent a valid session cookie
    session_id = request.cookies.get("session_id")
    
    if session_id not in active_sessions:
        raise HTTPException(status_code=401, detail="Not logged in")
        
    username = active_sessions[session_id]
    return {"message": f"Welcome to your secret dashboard, {username}!"}

@app.post("/logout")
def logout(request: Request, response: Response):
    # Forget the session and delete the cookie
    session_id = request.cookies.get("session_id")
    if session_id in active_sessions:
        del active_sessions[session_id]
    
    response.delete_cookie("session_id")
    return {"message": "Logged out"}


@app.get("/api/notes")
def get_notes(userId: int, db: Session = Depends(get_db)):
    # The SQL command to grab notes from the database
    sql = """
        SELECT n.note_id, n.title, n.content, n.source_type, n.file_name, n.created_at, s.subject_name
        FROM notes n
        LEFT JOIN subjects s ON n.subject_id = s.subject_id
        WHERE n.user_id = :u
        ORDER BY n.created_at DESC, n.note_id DESC
    """
    
    # Run the command
    rows = db.execute(text(sql), {"u": userId}).fetchall()
    
    # Format the data exactly how your frontend expects it
    notes = []
    for row in rows:
        notes.append({
            "noteId": row[0],
            "title": row[1],
            "content": row[2],
            "sourceType": row[3],
            "fileName": row[4],
            "createdAt": row[5],
            "subject": row[6],
        })
        
    return notes

def get_subject_id(db: Session, user_id: int, subject_name: str):
    if not subject_name or not subject_name.strip():
        return None
    clean_name = subject_name.strip()
    
    # Check if subject already exists
    row = db.execute(text("SELECT subject_id FROM subjects WHERE user_id = :u AND subject_name = :s"), {"u": user_id, "s": clean_name}).fetchone()
    if row: 
        return row[0]
        
    # If it doesn't exist, create it
    db.execute(text("INSERT INTO subjects (user_id, subject_name) VALUES (:u, :s)"), {"u": user_id, "s": clean_name})
    db.commit()
    row = db.execute(text("SELECT subject_id FROM subjects WHERE user_id = :u AND subject_name = :s"), {"u": user_id, "s": clean_name}).fetchone()
    return row[0]


@app.get("/api/notes")
def get_notes(userId: int, db: Session = Depends(get_db)):
    sql = """
        SELECT n.note_id, n.title, n.content, n.source_type, n.file_name, n.created_at, s.subject_name
        FROM notes n LEFT JOIN subjects s ON n.subject_id = s.subject_id
        WHERE n.user_id = :u ORDER BY n.created_at DESC
    """
    rows = db.execute(text(sql), {"u": userId}).fetchall()
    return [{"noteId": r[0], "title": r[1], "content": r[2], "sourceType": r[3], "fileName": r[4], "createdAt": r[5], "subject": r[6]} for r in rows]

@app.post("/api/notes")
def create_note(note: NoteData, db: Session = Depends(get_db)):
    sub_id = get_subject_id(db, note.userId, note.subject)
    sql = "INSERT INTO notes (user_id, subject_id, title, content, source_type, file_name) VALUES (:u, :s, :t, :c, :src, :f)"
    db.execute(text(sql), {"u": note.userId, "s": sub_id, "t": note.title, "c": note.content, "src": note.sourceType or "manual", "f": note.fileName})
    db.commit()
    return {"success": True}

@app.delete("/api/notes/{note_id}")
def delete_note(note_id: int, userId: int, db: Session = Depends(get_db)):
    db.execute(text("DELETE FROM notes WHERE note_id = :n AND user_id = :u"), {"n": note_id, "u": userId})
    db.commit()
    return {"success": True}


@app.get("/api/tasks")
def get_tasks(userId: int, db: Session = Depends(get_db)):
    sql = """
        SELECT t.task_id, t.title, t.description, t.is_completed, t.due_date, t.created_at, t.completed_at, s.subject_name
        FROM tasks t LEFT JOIN subjects s ON t.subject_id = s.subject_id
        WHERE t.user_id = :u ORDER BY t.is_completed ASC, t.created_at DESC
    """
    rows = db.execute(text(sql), {"u": userId}).fetchall()
    return [{"taskId": r[0], "title": r[1], "description": r[2], "isCompleted": bool(r[3]), "dueDate": r[4], "createdAt": r[5], "completedAt": r[6], "subject": r[7]} for r in rows]

@app.post("/api/tasks")
def create_task(task: TaskData, db: Session = Depends(get_db)):
    sub_id = get_subject_id(db, task.userId, task.subject)
    sql = "INSERT INTO tasks (user_id, subject_id, title, description, is_completed, due_date) VALUES (:u, :s, :t, :d, FALSE, :due)"
    db.execute(text(sql), {"u": task.userId, "s": sub_id, "t": task.title, "d": task.description, "due": task.dueDate})
    db.commit()
    return {"success": True}

@app.put("/api/tasks/{task_id}/toggle")
def toggle_task(task_id: int, userId: int, db: Session = Depends(get_db)):
    sql = """UPDATE tasks SET is_completed = NOT is_completed, 
             completed_at = CASE WHEN is_completed = 0 THEN CURRENT_TIMESTAMP ELSE NULL END 
             WHERE task_id = :t AND user_id = :u"""
    db.execute(text(sql), {"t": task_id, "u": userId})
    db.commit()
    return {"success": True}

@app.delete("/api/tasks/{task_id}")
def delete_task(task_id: int, userId: int, db: Session = Depends(get_db)):
    db.execute(text("DELETE FROM tasks WHERE task_id = :t AND user_id = :u"), {"t": task_id, "u": userId})
    db.commit()
    return {"success": True}
    

@app.post("/api/study-sessions")
def create_study_session(session: SessionData, db: Session = Depends(get_db)):
    sub_id = get_subject_id(db, session.userId, session.subject)
    if not sub_id: 
        return {"success": False, "message": "Subject required"}
        
    sql = """INSERT INTO sessions (subject_id, session_type, priority, session_date, duration, study_date, minutes_spent, session_note) 
             VALUES (:s, 'exercise', 1, CURRENT_DATE(), :m, CURRENT_DATE(), :m, :n)"""
    db.execute(text(sql), {"s": sub_id, "m": session.minutesStudied, "n": session.sessionNote})
    db.commit()
    return {"success": True}

def get_study_streak_days(db: Session, user_id: int):
    # This helper calculates how many days in a row the user studied
    sql = """
        SELECT DISTINCT se.study_date 
        FROM sessions se JOIN subjects s ON se.subject_id = s.subject_id 
        WHERE s.user_id = :u AND se.study_date IS NOT NULL
        ORDER BY se.study_date DESC
    """
    rows = db.execute(text(sql), {"u": user_id}).fetchall()
    dates = [row[0] for row in rows]
    
    if not dates:
        return 0
        
    streak = 0
    expected = date.today()
    for current in dates:
        if current == expected:
            streak += 1
            expected -= timedelta(days=1)
        elif streak == 0 and current == expected - timedelta(days=1):
            streak = 1
            expected = current - timedelta(days=1)
        else:
            break
    return streak

@app.get("/api/dashboard")
def get_dashboard(userId: int, db: Session = Depends(get_db)):
    # 1. Count Total Notes
    notes_count = db.execute(text("SELECT COUNT(*) FROM notes WHERE user_id = :u"), {"u": userId}).scalar() or 0
    
    # 2. Calculate Total Hours
    sql_total = "SELECT COALESCE(SUM(se.minutes_spent), 0) / 60.0 FROM sessions se JOIN subjects s ON se.subject_id = s.subject_id WHERE s.user_id = :u"
    total_hours = float(db.execute(text(sql_total), {"u": userId}).scalar() or 0.0)
    
    # 3. Calculate Today's Hours
    sql_today = "SELECT COALESCE(SUM(se.minutes_spent), 0) / 60.0 FROM sessions se JOIN subjects s ON se.subject_id = s.subject_id WHERE s.user_id = :u AND se.study_date = CURRENT_DATE()"
    today_hours = float(db.execute(text(sql_today), {"u": userId}).scalar() or 0.0)
    
    # 4. Get Study Streak
    streak = get_study_streak_days(db, userId)
    
    # 5. Calculate Task Progress Percentages
    sql_prog = """
        SELECT s.subject_name, COUNT(t.task_id), COALESCE(SUM(CASE WHEN t.is_completed = TRUE THEN 1 ELSE 0 END), 0)
        FROM subjects s LEFT JOIN tasks t ON s.subject_id = t.subject_id
        WHERE s.user_id = :u GROUP BY s.subject_id, s.subject_name ORDER BY s.subject_name
    """
    prog_rows = db.execute(text(sql_prog), {"u": userId}).fetchall()
    progress = []
    for r in prog_rows:
        # Convert Decimal from DB into standard float before math
        total = float(r[1])
        comp = float(r[2])
        
        pct = round((comp * 100.0 / total), 1) if total > 0 else 0
        progress.append({"subject": r[0], "completedTasks": int(comp), "totalTasks": int(total), "progressPercent": float(pct)})
           
    # 6. Get the 5 most recent notes
    sql_rec = """
        SELECT n.note_id, n.title, n.content, n.created_at, s.subject_name
        FROM notes n LEFT JOIN subjects s ON n.subject_id = s.subject_id
        WHERE n.user_id = :u ORDER BY n.created_at DESC, n.note_id DESC LIMIT 5
    """
    rec_rows = db.execute(text(sql_rec), {"u": userId}).fetchall()
    recent_notes = [{"noteId": r[0], "title": r[1], "content": r[2], "createdAt": r[3], "subject": r[4]} for r in rec_rows]
    
    # Send all this data back to the frontend
    return {
        "totalNotes": notes_count,
        "totalHours": total_hours,
        "todayHours": today_hours,
        "studyStreak": streak,
        "progress": progress,
        "recentNotes": recent_notes
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)