from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field

from app.core.enums import ResourceType


class SubjectResourceCreate(BaseModel):
    subject_id: int
    title: str = Field(min_length=1, max_length=150)
    resource_type: ResourceType = ResourceType.link
    url: str | None = Field(default=None, max_length=1024)
    description: str | None = None


class SubjectResourceUpdate(BaseModel):
    title: str | None = Field(default=None, min_length=1, max_length=150)
    resource_type: ResourceType | None = None
    url: str | None = Field(default=None, max_length=1024)
    description: str | None = None


class SubjectResourceOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    subject_id: int
    title: str
    resource_type: ResourceType
    url: str | None
    description: str | None
    created_at: datetime
    updated_at: datetime