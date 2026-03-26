from fastapi import FastAPI, Depends, HTTPException, Response, Request
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
from sqlalchemy import text
import bcrypt
import uuid
from pydantic import BaseModel
from database import get_db

app = FastAPI()

# This allows your React app to talk to your Python app and share cookies
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:3000"], 
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