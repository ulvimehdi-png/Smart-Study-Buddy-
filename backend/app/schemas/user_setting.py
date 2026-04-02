from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field

from app.core.enums import SessionType, WeekStartDay


class UserSettingCreate(BaseModel):
    timezone: str = Field(default="UTC", max_length=64)
    week_start_day: WeekStartDay = WeekStartDay.monday
    default_session_type: SessionType = SessionType.manual
    default_session_minutes: int = Field(default=50, ge=1, le=1440)
    default_break_minutes: int = Field(default=10, ge=0, le=240)


class UserSettingUpdate(BaseModel):
    timezone: str | None = Field(default=None, max_length=64)
    week_start_day: WeekStartDay | None = None
    default_session_type: SessionType | None = None
    default_session_minutes: int | None = Field(default=None, ge=1, le=1440)
    default_break_minutes: int | None = Field(default=None, ge=0, le=240)


class UserSettingOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    user_id: int
    timezone: str
    week_start_day: WeekStartDay
    default_session_type: SessionType
    default_session_minutes: int
    default_break_minutes: int
    created_at: datetime
    updated_at: datetime