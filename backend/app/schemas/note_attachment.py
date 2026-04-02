from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field

class NoteAttachmentCreate(BaseModel):
    note_id: int
    original_filename: str = Field(min_length=1, max_length=255)
    storage_path: str = Field(min_length=1, max_length=512)
    mime_type: str | None = Field(default=None, max_length=100)
    size_bytes: int | None = Field(default=None, ge=0)


class NoteAttachmentOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    note_id: int
    original_filename: str
    storage_path: str
    mime_type: str | None
    size_bytes: int | None
    created_at: datetime