from datetime import datetime, timezone

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.api.deps import get_current_user, get_db
from app.models.study_session import StudySession
from app.models.subject import Subject
from app.models.task import Task
from app.models.user import User
from app.schemas.study_session import StudySessionEnd, StudySessionOut, StudySessionStart

router = APIRouter(prefix="/study-sessions", tags=["study-sessions"])


@router.post("/start", response_model=StudySessionOut, status_code=status.HTTP_201_CREATED)
def start_session(
    payload: StudySessionStart,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    subject = db.scalar(
        select(Subject).where(
            Subject.id == payload.subject_id,
            Subject.user_id == current_user.id,
        )
    )
    if not subject:
        raise HTTPException(status_code=404, detail="Subject not found")

    if payload.task_id is not None:
        task = db.scalar(
            select(Task).where(
                Task.id == payload.task_id,
                Task.user_id == current_user.id,
            )
        )
        if not task:
            raise HTTPException(status_code=404, detail="Task not found")
        if task.subject_id is not None and task.subject_id != payload.subject_id:
            raise HTTPException(status_code=400, detail="Task does not belong to this subject")

    active = db.scalar(
        select(StudySession).where(
            StudySession.user_id == current_user.id,
            StudySession.ended_at.is_(None),
        )
    )
    if active:
        raise HTTPException(status_code=400, detail="You already have an active study session")
    
    started_at = payload.started_at or datetime.now(timezone.utc)
    if started_at.tzinfo is None:
        started_at = started_at.replace(tzinfo=timezone.utc)

    session = StudySession(
        user_id=current_user.id,
        subject_id=payload.subject_id,
        task_id=payload.task_id,
        started_at=started_at,
    )
    db.add(session)
    db.commit()
    db.refresh(session)
    return session


@router.post("/{session_id}/end", response_model=StudySessionOut)
def end_session(
    session_id: int,
    payload: StudySessionEnd,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    session = db.scalar(
        select(StudySession).where(
            StudySession.id == session_id,
            StudySession.user_id == current_user.id,
        )
    )
    if not session:
        raise HTTPException(status_code=404, detail="Study session not found")

    if session.ended_at is not None:
        raise HTTPException(status_code=400, detail="Study session already ended")

    # Make ended_at timezone-aware
    ended_at = payload.ended_at or datetime.now(timezone.utc)
    started_at = session.started_at
    if started_at.tzinfo is None:
        started_at = started_at.replace(tzinfo=timezone.utc)

    if ended_at < started_at:
        raise HTTPException(status_code=400, detail="ended_at cannot be before started_at")

    # Compute actual focus minutes safely
    actual_focus_minutes = payload.actual_focus_minutes
    if actual_focus_minutes is None:
        actual_focus_minutes = max(
            0,
            int((ended_at - started_at).total_seconds() // 60),  # use timezone-aware started_at
        )

    session.ended_at = ended_at
    session.actual_focus_minutes = actual_focus_minutes

    db.commit()
    db.refresh(session)
    return session


@router.get("", response_model=list[StudySessionOut])
def list_sessions(
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    sessions = db.scalars(
        select(StudySession)
        .where(StudySession.user_id == current_user.id)
        .order_by(StudySession.started_at.desc())
    ).all()
    return list(sessions)


@router.get("/active", response_model=StudySessionOut | None)
def get_active_session(
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    session = db.scalar(
        select(StudySession).where(
            StudySession.user_id == current_user.id,
            StudySession.ended_at.is_(None),
        )
    )
    return session