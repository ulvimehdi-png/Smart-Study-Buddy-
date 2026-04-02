from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import DateTime, ForeignKey, Index, SmallInteger, String
from sqlalchemy import Enum as SQLEnum
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base
from app.core.enums import SessionType, StudySessionStatus

if TYPE_CHECKING:
    from app.models.note import Note
    from app.models.subject import Subject
    from app.models.task import Task
    from app.models.user import User


class StudySession(Base):
    __tablename__ = "study_sessions"
    __table_args__ = (
        Index("idx_study_sessions_status", "status"),
        Index("idx_study_sessions_session_type", "session_type"),
        Index("idx_study_sessions_started_at", "started_at"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)

    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False, index=True)
    subject_id: Mapped[int] = mapped_column(ForeignKey("subjects.id"), nullable=False, index=True)
    task_id: Mapped[int | None] = mapped_column(ForeignKey("tasks.id"), nullable=True, index=True)
    note_id: Mapped[int | None] = mapped_column(ForeignKey("notes.id"), nullable=True, index=True)

    status: Mapped[StudySessionStatus] = mapped_column(
        SQLEnum(StudySessionStatus, name="study_session_status"),
        default=StudySessionStatus.active,
        nullable=False,
    )
    session_type: Mapped[SessionType] = mapped_column(
        SQLEnum(SessionType, name="study_session_type"),
        default=SessionType.manual,
        nullable=False,
    )

    started_at: Mapped[datetime] = mapped_column(DateTime, nullable=False)
    ended_at: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)

    planned_minutes: Mapped[int | None] = mapped_column(SmallInteger, nullable=True)
    actual_focus_minutes: Mapped[int | None] = mapped_column(SmallInteger, nullable=True)
    break_minutes: Mapped[int] = mapped_column(SmallInteger, default=0, nullable=False)
    distraction_count: Mapped[int] = mapped_column(SmallInteger, default=0, nullable=False)

    session_note: Mapped[str | None] = mapped_column(String(255), nullable=True)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        default=datetime.utcnow,
        onupdate=datetime.utcnow,
        nullable=False,
    )

    user: Mapped["User"] = relationship(back_populates="study_sessions")
    subject: Mapped["Subject"] = relationship(back_populates="study_sessions")
    task: Mapped["Task | None"] = relationship(back_populates="study_sessions")
    note: Mapped["Note | None"] = relationship(back_populates="study_sessions")