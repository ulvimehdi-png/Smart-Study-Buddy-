from __future__ import annotations

from datetime import date, datetime
from typing import TYPE_CHECKING

from sqlalchemy import Date, DateTime, ForeignKey, Index, Integer, String, Text
from sqlalchemy import Enum as SQLEnum
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base
from app.core.enums import TaskPriority, TaskStatus

if TYPE_CHECKING:
    from app.models.note import Note
    from app.models.study_session import StudySession
    from app.models.subject import Subject
    from app.models.tag import Tag
    from app.models.task_tag import TaskTag
    from app.models.user import User


class Task(Base):
    __tablename__ = "tasks"
    __table_args__ = (
        Index("idx_tasks_status", "status"),
        Index("idx_tasks_priority", "priority"),
        Index("idx_tasks_due_date", "due_date"),
        Index("idx_tasks_scheduled_for", "scheduled_for"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)

    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False, index=True)
    subject_id: Mapped[int | None] = mapped_column(ForeignKey("subjects.id"), nullable=True, index=True)

    title: Mapped[str] = mapped_column(String(255), nullable=False)
    description: Mapped[str | None] = mapped_column(Text, nullable=True)

    status: Mapped[TaskStatus] = mapped_column(
        SQLEnum(TaskStatus, name="task_status"),
        default=TaskStatus.todo,
        nullable=False,
    )
    priority: Mapped[TaskPriority] = mapped_column(
        SQLEnum(TaskPriority, name="task_priority"),
        default=TaskPriority.medium,
        nullable=False,
    )

    estimated_minutes: Mapped[int | None] = mapped_column(Integer, nullable=True)
    due_date: Mapped[date | None] = mapped_column(Date, nullable=True)
    scheduled_for: Mapped[date | None] = mapped_column(Date, nullable=True)
    completed_at: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        default=datetime.utcnow,
        onupdate=datetime.utcnow,
        nullable=False,
    )

    user: Mapped["User"] = relationship(back_populates="tasks")
    subject: Mapped["Subject | None"] = relationship(back_populates="tasks")

    task_tags: Mapped[list["TaskTag"]] = relationship(
        back_populates="task",
        cascade="all, delete-orphan",
        overlaps="tags,tasks",
    )
    tags: Mapped[list["Tag"]] = relationship(
        secondary="task_tags",
        back_populates="tasks",
        overlaps="task_tags,task,tag",
    )

    notes: Mapped[list["Note"]] = relationship(
        back_populates="task",
        passive_deletes=True,
    )
    study_sessions: Mapped[list["StudySession"]] = relationship(
        back_populates="task",
        passive_deletes=True,
    )