from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field

from app.core.enums import SessionType, StudySessionStatus


class StudySessionCreate(BaseModel):
    subject_id: int
    task_id: int | None = None
    note_id: int | None = None
    status: StudySessionStatus = StudySessionStatus.active
    session_type: SessionType = SessionType.manual
    started_at: datetime
    ended_at: datetime | None = None
    planned_minutes: int | None = Field(default=None, ge=0, le=1440)
    actual_focus_minutes: int | None = Field(default=None, ge=0, le=1440)
    break_minutes: int = Field(default=0, ge=0, le=1440)
    distraction_count: int = Field(default=0, ge=0, le=1000)
    session_note: str | None = Field(default=None, max_length=255)


class StudySessionUpdate(BaseModel):
    task_id: int | None = None
    note_id: int | None = None
    status: StudySessionStatus | None = None
    session_type: SessionType | None = None
    started_at: datetime | None = None
    ended_at: datetime | None = None
    planned_minutes: int | None = Field(default=None, ge=0, le=1440)
    actual_focus_minutes: int | None = Field(default=None, ge=0, le=1440)
    break_minutes: int | None = Field(default=None, ge=0, le=1440)
    distraction_count: int | None = Field(default=None, ge=0, le=1000)
    session_note: str | None = Field(default=None, max_length=255)


class StudySessionOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    subject_id: int
    task_id: int | None
    note_id: int | None
    status: StudySessionStatus
    session_type: SessionType
    started_at: datetime
    ended_at: datetime | None
    planned_minutes: int | None
    actual_focus_minutes: int | None
    break_minutes: int
    distraction_count: int
    session_note: str | None
    created_at: datetime
    updated_at: datetime