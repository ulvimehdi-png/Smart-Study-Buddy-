from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import DateTime, ForeignKey, Index, String, Text
from sqlalchemy import Enum as SQLEnum
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base
from app.core.enums import ResourceType

if TYPE_CHECKING:
    from app.models.subject import Subject
    from app.models.user import User


class SubjectResource(Base):
    __tablename__ = "subject_resources"
    __table_args__ = (
        Index("idx_subject_resources_type", "resource_type"),
    )

    id: Mapped[int] = mapped_column(primary_key=True)

    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False, index=True)
    subject_id: Mapped[int] = mapped_column(ForeignKey("subjects.id"), nullable=False, index=True)

    title: Mapped[str] = mapped_column(String(150), nullable=False)
    resource_type: Mapped[ResourceType] = mapped_column(
        SQLEnum(ResourceType, name="resource_type"),
        default=ResourceType.link,
        nullable=False,
    )
    url: Mapped[str | None] = mapped_column(String(1024), nullable=True)
    description: Mapped[str | None] = mapped_column(Text, nullable=True)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        default=datetime.utcnow,
        onupdate=datetime.utcnow,
        nullable=False,
    )

    user: Mapped["User"] = relationship(back_populates="subject_resources")
    subject: Mapped["Subject"] = relationship(back_populates="subject_resources")