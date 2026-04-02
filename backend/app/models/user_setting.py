from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import DateTime, ForeignKey, String
from sqlalchemy import Enum as SQLEnum
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.core.database import Base
from app.core.enums import SessionType, WeekStartDay

if TYPE_CHECKING:
    from app.models.user import User


class UserSetting(Base):
    __tablename__ = "user_settings"

    user_id: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE", onupdate="CASCADE"),
        primary_key=True,
    )

    timezone: Mapped[str] = mapped_column(String(64), default="UTC", nullable=False)
    week_start_day: Mapped[WeekStartDay] = mapped_column(
        SQLEnum(WeekStartDay, name="week_start_day"),
        default=WeekStartDay.monday,
        nullable=False,
    )
    default_session_type: Mapped[SessionType] = mapped_column(
        SQLEnum(SessionType, name="default_session_type"),
        default=SessionType.manual,
        nullable=False,
    )
    default_session_minutes: Mapped[int] = mapped_column(default=50, nullable=False)
    default_break_minutes: Mapped[int] = mapped_column(default=10, nullable=False)

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime,
        default=datetime.utcnow,
        onupdate=datetime.utcnow,
        nullable=False,
    )

    user: Mapped["User"] = relationship(back_populates="user_setting")