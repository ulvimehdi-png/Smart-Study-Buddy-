from fastapi import APIRouter, Depends, HTTPException, Response, Request
from sqlalchemy.orm import Session
import bcrypt
import uuid
import models, schemas
from database import get_db

router = APIRouter(tags=["Auth"])

# Store active sessions in memory
active_sessions = {}

@router.post("/register")
def register(user: schemas.UserCreate, db: Session = Depends(get_db)):
    hashed_pw = bcrypt.hashpw(user.password.encode('utf-8'), bcrypt.gensalt())
    
    existing_user = db.query(models.User).filter(models.User.username == user.username).first()
    if existing_user:
        raise HTTPException(status_code=400, detail="Username already exists")
        
    new_user = models.User(username=user.username, hashed_password=hashed_pw.decode('utf-8'))
    db.add(new_user)
    db.commit()
    return {"message": "User created successfully"}

@router.post("/login")
def login(user: schemas.UserCreate, response: Response, db: Session = Depends(get_db)):
    db_user = db.query(models.User).filter(models.User.username == user.username).first()

    if not db_user or not bcrypt.checkpw(user.password.encode('utf-8'), db_user.hashed_password.encode('utf-8')):
        raise HTTPException(status_code=400, detail="Incorrect username or password")

    session_id = str(uuid.uuid4())
    active_sessions[session_id] = user.username
    response.set_cookie(key="session_id", value=session_id, httponly=True)
    return {"message": "Logged in successfully"}

@router.get("/user")
def get_user(request: Request):
    session_id = request.cookies.get("session_id")
    if session_id not in active_sessions:
        raise HTTPException(status_code=401, detail="Not logged in")
    return {"user": active_sessions[session_id]}

@router.post("/logout")
def logout(request: Request, response: Response):
    session_id = request.cookies.get("session_id")
    if session_id in active_sessions:
        del active_sessions[session_id]
    response.delete_cookie("session_id")
    return {"message": "Logged out"}