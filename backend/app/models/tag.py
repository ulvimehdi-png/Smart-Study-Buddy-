from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import DateTime, ForeignKey, String, UniqueConstraint
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base

if TYPE_CHECKING:
    from app.models.task import Task
    from app.models.task_tag import TaskTag
    from app.models.user import User


class Tag(Base):
    __tablename__ = "tags"
    __table_args__ = (
        UniqueConstraint("user_id", "name", name="uq_tags_user_name"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False, index=True)

    name: Mapped[str] = mapped_column(String(50), nullable=False)
    color_hex: Mapped[str | None] = mapped_column(String(7), nullable=True)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)

    user: Mapped["User"] = relationship(back_populates="tags")
    task_tags: Mapped[list["TaskTag"]] = relationship(
        back_populates="tag",
        cascade="all, delete-orphan",
        overlaps="tasks,tags",
    )
    tasks: Mapped[list["Task"]] = relationship(
        secondary="task_tags",
        back_populates="tags",
        overlaps="task_tags,task,tag",
    )