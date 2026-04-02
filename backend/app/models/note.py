from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import DateTime, ForeignKey, Index, String
from sqlalchemy import Enum as SQLEnum
from sqlalchemy.dialects.mysql import MEDIUMTEXT
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base
from app.core.enums import NoteSourceType

if TYPE_CHECKING:
    from app.models.note_attachment import NoteAttachment
    from app.models.study_session import StudySession
    from app.models.subject import Subject
    from app.models.task import Task
    from app.models.user import User


class Note(Base):
    __tablename__ = "notes"
    __table_args__ = (
        Index("idx_notes_source_type", "source_type"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)

    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False, index=True)
    subject_id: Mapped[int | None] = mapped_column(ForeignKey("subjects.id"), nullable=True, index=True)
    task_id: Mapped[int | None] = mapped_column(ForeignKey("tasks.id"), nullable=True, index=True)

    title: Mapped[str] = mapped_column(String(150), nullable=False)
    content: Mapped[str] = mapped_column(MEDIUMTEXT, nullable=False)

    source_type: Mapped[NoteSourceType] = mapped_column(
        SQLEnum(NoteSourceType, name="note_source_type"),
        default=NoteSourceType.manual,
        nullable=False,
    )
    external_url: Mapped[str | None] = mapped_column(String(1024), nullable=True)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        default=datetime.utcnow,
        onupdate=datetime.utcnow,
        nullable=False,
    )

    user: Mapped["User"] = relationship(back_populates="notes")
    subject: Mapped["Subject | None"] = relationship(back_populates="notes")
    task: Mapped["Task | None"] = relationship(back_populates="notes")

    attachments: Mapped[list["NoteAttachment"]] = relationship(
        back_populates="note",
        cascade="all, delete-orphan",
    )
    study_sessions: Mapped[list["StudySession"]] = relationship(
        back_populates="note",
        passive_deletes=True,
    )