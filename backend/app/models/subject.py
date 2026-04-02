from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import Boolean, DateTime, ForeignKey, Index, Integer, String, Text, UniqueConstraint
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base

if TYPE_CHECKING:
    from app.models.note import Note
    from app.models.study_session import StudySession
    from app.models.subject_resource import SubjectResource
    from app.models.task import Task
    from app.models.user import User


class Subject(Base):
    __tablename__ = "subjects"
    __table_args__ = (
        UniqueConstraint("user_id", "code", name="uq_subjects_user_code"),
        Index("idx_subjects_is_archived", "is_archived"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False, index=True)

    code: Mapped[str | None] = mapped_column(String(32), nullable=True)
    name: Mapped[str] = mapped_column(String(120), nullable=False)
    description: Mapped[str | None] = mapped_column(Text, nullable=True)
    color_hex: Mapped[str | None] = mapped_column(String(7), nullable=True)

    is_archived: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    archived_at: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)

    target_total_minutes: Mapped[int | None] = mapped_column(Integer, nullable=True)
    target_weekly_minutes: Mapped[int | None] = mapped_column(Integer, nullable=True)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        default=datetime.utcnow,
        onupdate=datetime.utcnow,
        nullable=False,
    )

    user: Mapped["User"] = relationship(back_populates="subjects")
    tasks: Mapped[list["Task"]] = relationship(
        back_populates="subject",
        passive_deletes=True,
    )
    notes: Mapped[list["Note"]] = relationship(
        back_populates="subject",
        passive_deletes=True,
    )
    subject_resources: Mapped[list["SubjectResource"]] = relationship(
        back_populates="subject",
        cascade="all, delete-orphan",
    )
    study_sessions: Mapped[list["StudySession"]] = relationship(
        back_populates="subject",
        cascade="all, delete-orphan",
    )