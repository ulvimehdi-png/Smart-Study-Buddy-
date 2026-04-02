from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field


class SubjectCreate(BaseModel):
    code: str | None = Field(default=None, max_length=32)
    name: str = Field(min_length=1, max_length=120)
    description: str | None = None
    target_total_minutes: int | None = Field(default=None, ge=0)
    target_weekly_minutes: int | None = Field(default=None, ge=0)


class SubjectUpdate(BaseModel):
    code: str | None = Field(default=None, max_length=32)
    name: str | None = Field(default=None, min_length=1, max_length=120)
    description: str | None = None
    target_total_minutes: int | None = Field(default=None, ge=0)
    target_weekly_minutes: int | None = Field(default=None, ge=0)


class SubjectOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    code: str | None
    name: str
    description: str | None
    target_total_minutes: int | None
    target_weekly_minutes: int | None
    created_at: datetime