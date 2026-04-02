from datetime import datetime, timezone

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.api.deps import get_current_user, get_db
from app.models.subject import Subject
from app.models.task import Task
from app.models.user import User
from app.schemas.task import TaskCreate, TaskOut, TaskUpdate

router = APIRouter(prefix="/tasks", tags=["tasks"])


def _validate_subject_access(subject_id: int | None, current_user: User, db: Session) -> None:
    if subject_id is None:
        return

    subject = db.scalar(
        select(Subject).where(
            Subject.id == subject_id,
            Subject.user_id == current_user.id,
        )
    )
    if not subject:
        raise HTTPException(status_code=404, detail="Subject not found")


@router.post("", response_model=TaskOut, status_code=status.HTTP_201_CREATED)
def create_task(
    payload: TaskCreate,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    _validate_subject_access(payload.subject_id, current_user, db)

    task = Task(
        user_id=current_user.id,
        subject_id=payload.subject_id,
        title=payload.title,
        description=payload.description,
        status=payload.status,
        priority=payload.priority,
        estimated_minutes=payload.estimated_minutes,
        completed_at=datetime.now(timezone.utc) if payload.status == "done" else None,
    )
    db.add(task)
    db.commit()
    db.refresh(task)
    return task


@router.get("", response_model=list[TaskOut])
def list_tasks(
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    tasks = db.scalars(
        select(Task)
        .where(Task.user_id == current_user.id)
        .order_by(Task.created_at.desc())
    ).all()
    return list(tasks)


@router.get("/{task_id}", response_model=TaskOut)
def get_task(
    task_id: int,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    task = db.scalar(
        select(Task).where(
            Task.id == task_id,
            Task.user_id == current_user.id,
        )
    )
    if not task:
        raise HTTPException(status_code=404, detail="Task not found")
    return task


@router.put("/{task_id}", response_model=TaskOut)
def update_task(
    task_id: int,
    payload: TaskUpdate,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    task = db.scalar(
        select(Task).where(
            Task.id == task_id,
            Task.user_id == current_user.id,
        )
    )
    if not task:
        raise HTTPException(status_code=404, detail="Task not found")

    data = payload.model_dump(exclude_unset=True)

    if "subject_id" in data:
        _validate_subject_access(data["subject_id"], current_user, db)

    for key, value in data.items():
        setattr(task, key, value)

    if task.status == "done" and task.completed_at is None:
        task.completed_at = datetime.now(timezone.utc)
    elif task.status != "done":
        task.completed_at = None

    db.commit()
    db.refresh(task)
    return task


@router.delete("/{task_id}")
def delete_task(
    task_id: int,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    task = db.scalar(
        select(Task).where(
            Task.id == task_id,
            Task.user_id == current_user.id,
        )
    )
    if not task:
        raise HTTPException(status_code=404, detail="Task not found")

    db.delete(task)
    db.commit()
    return {"detail": "Task deleted"}