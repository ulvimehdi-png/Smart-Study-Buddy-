from pydantic import BaseModel
from typing import Optional

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