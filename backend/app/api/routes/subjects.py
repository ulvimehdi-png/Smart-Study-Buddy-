from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.api.deps import get_current_user, get_db
from app.models.subject import Subject
from app.models.user import User
from app.schemas.subject import SubjectCreate, SubjectOut, SubjectUpdate

router = APIRouter(prefix="/subjects", tags=["subjects"])


@router.post("", response_model=SubjectOut, status_code=status.HTTP_201_CREATED)
def create_subject(
    payload: SubjectCreate,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    subject = Subject(
        user_id=current_user.id,
        code=payload.code,
        name=payload.name,
        description=payload.description,
        target_total_minutes=payload.target_total_minutes,
        target_weekly_minutes=payload.target_weekly_minutes,
    )
    db.add(subject)
    db.commit()
    db.refresh(subject)
    return subject


@router.get("", response_model=list[SubjectOut])
def list_subjects(
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    subjects = db.scalars(
        select(Subject)
        .where(Subject.user_id == current_user.id)
        .order_by(Subject.created_at.desc())
    ).all()
    return list(subjects)


@router.get("/{subject_id}", response_model=SubjectOut)
def get_subject(
    subject_id: int,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    subject = db.scalar(
        select(Subject).where(
            Subject.id == subject_id,
            Subject.user_id == current_user.id,
        )
    )
    if not subject:
        raise HTTPException(status_code=404, detail="Subject not found")
    return subject


@router.put("/{subject_id}", response_model=SubjectOut)
def update_subject(
    subject_id: int,
    payload: SubjectUpdate,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    subject = db.scalar(
        select(Subject).where(
            Subject.id == subject_id,
            Subject.user_id == current_user.id,
        )
    )
    if not subject:
        raise HTTPException(status_code=404, detail="Subject not found")

    for key, value in payload.model_dump(exclude_unset=True).items():
        setattr(subject, key, value)

    db.commit()
    db.refresh(subject)
    return subject


@router.delete("/{subject_id}")
def delete_subject(
    subject_id: int,
    db: Session = Depends(get_db),
    current_user: User = Depends(get_current_user),
):
    subject = db.scalar(
        select(Subject).where(
            Subject.id == subject_id,
            Subject.user_id == current_user.id,
        )
    )
    if not subject:
        raise HTTPException(status_code=404, detail="Subject not found")

    db.delete(subject)
    db.commit()
    return {"detail": "Subject deleted"}