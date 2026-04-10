from sqlalchemy import Column, Integer, String, Boolean, Text, Date, DateTime, ForeignKey
from sqlalchemy.sql import func
from database import Base

class User(Base):
    __tablename__ = "users"
    id = Column(Integer, primary_key=True, index=True) 
    username = Column(String(50), unique=True, index=True)
    hashed_password = Column(String(255))

class Subject(Base):
    __tablename__ = "subjects"
    subject_id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id")) # References User.id
    subject_name = Column(String(100))

class Note(Base):
    __tablename__ = "notes"
    note_id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id")) # References User.id
    subject_id = Column(Integer, ForeignKey("subjects.subject_id"))
    title = Column(String(255))
    content = Column(Text)
    source_type = Column(String(50), default="manual")
    file_name = Column(String(255), nullable=True)
    created_at = Column(DateTime, server_default=func.now())

class Task(Base):
    __tablename__ = "tasks"
    task_id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id")) # References User.id
    subject_id = Column(Integer, ForeignKey("subjects.subject_id"))
    title = Column(String(255))
    description = Column(Text, nullable=True)
    is_completed = Column(Boolean, default=False)
    due_date = Column(Date, nullable=True)
    created_at = Column(DateTime, server_default=func.now())
    completed_at = Column(DateTime, nullable=True)

class Session(Base):
    __tablename__ = "sessions"
    session_id = Column(Integer, primary_key=True, index=True)
    subject_id = Column(Integer, ForeignKey("subjects.subject_id"))
    session_type = Column(String(50))
    priority = Column(Integer, default=1)
    session_date = Column(Date, server_default=func.current_date())
    duration = Column(Integer)
    study_date = Column(Date, server_default=func.current_date())
    minutes_spent = Column(Integer)
    session_note = Column(Text, nullable=True)