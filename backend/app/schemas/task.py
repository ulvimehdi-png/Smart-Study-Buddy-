from datetime import datetime
from typing import Literal

from pydantic import BaseModel, ConfigDict, Field

TaskStatus = Literal["todo", "in_progress", "done"]
TaskPriority = Literal["low", "medium", "high"]


class TaskCreate(BaseModel):
    subject_id: int | None = None
    title: str = Field(min_length=1, max_length=255)
    description: str | None = None
    status: TaskStatus = "todo"
    priority: TaskPriority = "medium"
    estimated_minutes: int | None = Field(default=None, ge=0)


class TaskUpdate(BaseModel):
    subject_id: int | None = None
    title: str | None = Field(default=None, min_length=1, max_length=255)
    description: str | None = None
    status: TaskStatus | None = None
    priority: TaskPriority | None = None
    estimated_minutes: int | None = Field(default=None, ge=0)


class TaskOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    subject_id: int | None
    title: str
    description: str | None
    status: TaskStatus
    priority: TaskPriority
    estimated_minutes: int | None
    completed_at: datetime | None
    created_at: datetime