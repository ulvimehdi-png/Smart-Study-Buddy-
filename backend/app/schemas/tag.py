from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field


class TagCreate(BaseModel):
    name: str = Field(min_length=1, max_length=50)
    color_hex: str | None = Field(default=None, pattern=r"^#[0-9A-Fa-f]{6}$")


class TagUpdate(BaseModel):
    name: str | None = Field(default=None, min_length=1, max_length=50)
    color_hex: str | None = Field(default=None, pattern=r"^#[0-9A-Fa-f]{6}$")


class TagOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    name: str
    color_hex: str | None
    created_at: datetime


class TaskTagCreate(BaseModel):
    task_id: int
    tag_id: int


class TaskTagOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    task_id: int
    tag_id: int
    created_at: datetime