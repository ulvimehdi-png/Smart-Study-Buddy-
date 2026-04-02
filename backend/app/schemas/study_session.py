from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field


class StudySessionStart(BaseModel):
    subject_id: int
    task_id: int | None = None
    started_at: datetime | None = Field(
        default=None,
        description="Must be timezone-aware if provided"
    )


class StudySessionEnd(BaseModel):
    ended_at: datetime | None = None
    actual_focus_minutes: int | None = Field(default=None, ge=0)


class StudySessionOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    subject_id: int
    task_id: int | None
    started_at: datetime
    ended_at: datetime | None
    actual_focus_minutes: int | None
    created_at: datetime