from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import DateTime, String
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base

if TYPE_CHECKING:
    from app.models.note import Note
    from app.models.study_session import StudySession
    from app.models.subject import Subject
    from app.models.subject_resource import SubjectResource
    from app.models.tag import Tag
    from app.models.task import Task
    from app.models.user_setting import UserSetting


class User(Base):
    __tablename__ = "users"

    id: Mapped[int] = mapped_column(primary_key=True)

    username: Mapped[str] = mapped_column(String(50), unique=True, index=True, nullable=False)
    hashed_password: Mapped[str] = mapped_column(String(255), nullable=False)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        default=datetime.utcnow,
        onupdate=datetime.utcnow,
        nullable=False,
    )

    user_setting: Mapped["UserSetting | None"] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
        uselist=False,
    )
    subjects: Mapped[list["Subject"]] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
    )
    tasks: Mapped[list["Task"]] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
    )
    tags: Mapped[list["Tag"]] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
    )
    notes: Mapped[list["Note"]] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
    )
    subject_resources: Mapped[list["SubjectResource"]] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
    )
    study_sessions: Mapped[list["StudySession"]] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
    )