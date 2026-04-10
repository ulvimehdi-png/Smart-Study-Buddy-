from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from sqlalchemy import func
import models, schemas
from database import get_db
from utils import get_subject_id

router = APIRouter(prefix="/api/tasks", tags=["Tasks"])

@router.get("/")
def get_tasks(userId: int, db: Session = Depends(get_db)):
    results = db.query(models.Task, models.Subject.subject_name)\
        .outerjoin(models.Subject, models.Task.subject_id == models.Subject.subject_id)\
        .filter(models.Task.user_id == userId)\
        .order_by(models.Task.is_completed.asc(), models.Task.created_at.desc()).all()
        
    return [{"taskId": task.task_id, "title": task.title, "description": task.description, "isCompleted": task.is_completed, "dueDate": task.due_date, "createdAt": task.created_at, "completedAt": task.completed_at, "subject": subj} for task, subj in results]

@router.post("/")
def create_task(task: schemas.TaskData, db: Session = Depends(get_db)):
    sub_id = get_subject_id(db, task.userId, task.subject)
    new_task = models.Task(
        user_id=task.userId, subject_id=sub_id, title=task.title, 
        description=task.description, due_date=task.dueDate
    )
    db.add(new_task)
    db.commit()
    return {"success": True}

@router.put("/{task_id}/toggle")
def toggle_task(task_id: int, userId: int, db: Session = Depends(get_db)):
    # FIX: Changed models.Task.id to models.Task.user_id
    task = db.query(models.Task).filter(models.Task.task_id == task_id, models.Task.user_id == userId).first()
    if task:
        task.is_completed = not task.is_completed
        task.completed_at = func.now() if task.is_completed else None
        db.commit()
    return {"success": True}

@router.delete("/{task_id}")
def delete_task(task_id: int, userId: int, db: Session = Depends(get_db)):
    # FIX: Changed models.Task.id to models.Task.user_id
    db.query(models.Task).filter(models.Task.task_id == task_id, models.Task.user_id == userId).delete()
    db.commit()
    return {"success": True}