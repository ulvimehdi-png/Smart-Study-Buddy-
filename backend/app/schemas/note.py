from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field

from app.core.enums import NoteSourceType


class NoteCreate(BaseModel):
    subject_id: int | None = None
    task_id: int | None = None
    title: str = Field(min_length=1, max_length=150)
    content: str = Field(min_length=1)
    source_type: NoteSourceType = NoteSourceType.manual
    external_url: str | None = Field(default=None, max_length=1024)


class NoteUpdate(BaseModel):
    subject_id: int | None = None
    task_id: int | None = None
    title: str | None = Field(default=None, min_length=1, max_length=150)
    content: str | None = Field(default=None, min_length=1)
    source_type: NoteSourceType | None = None
    external_url: str | None = Field(default=None, max_length=1024)


class NoteOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    subject_id: int | None
    task_id: int | None
    title: str
    content: str
    source_type: NoteSourceType
    external_url: str | None
    created_at: datetime
    updated_at: datetime