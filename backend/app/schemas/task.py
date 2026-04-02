from datetime import date, datetime

from pydantic import BaseModel, ConfigDict, Field

from app.core.enums import TaskPriority, TaskStatus


class TaskCreate(BaseModel):
    subject_id: int | None = None
    title: str = Field(min_length=1, max_length=255)
    description: str | None = None
    status: TaskStatus = TaskStatus.todo
    priority: TaskPriority = TaskPriority.medium
    estimated_minutes: int | None = Field(default=None, ge=0)
    due_date: date | None = None
    scheduled_for: date | None = None


class TaskUpdate(BaseModel):
    subject_id: int | None = None
    title: str | None = Field(default=None, min_length=1, max_length=255)
    description: str | None = None
    status: TaskStatus | None = None
    priority: TaskPriority | None = None
    estimated_minutes: int | None = Field(default=None, ge=0)
    due_date: date | None = None
    scheduled_for: date | None = None
    completed_at: datetime | None = None


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
    due_date: date | None
    scheduled_for: date | None
    completed_at: datetime | None
    created_at: datetime
    updated_at: datetime