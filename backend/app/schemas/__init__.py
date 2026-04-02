from app.schemas.note import NoteCreate, NoteOut, NoteUpdate
from app.schemas.note_attachment import NoteAttachmentCreate, NoteAttachmentOut
from app.schemas.study_session import StudySessionCreate, StudySessionOut, StudySessionUpdate
from app.schemas.subject import SubjectCreate, SubjectOut, SubjectUpdate
from app.schemas.subject_resource import (
    SubjectResourceCreate,
    SubjectResourceOut,
    SubjectResourceUpdate,
)
from app.schemas.tag import TagCreate, TagOut, TagUpdate, TaskTagCreate, TaskTagOut
from app.schemas.task import TaskCreate, TaskOut, TaskUpdate
from app.schemas.user import Token, UserCreate, UserLogin, UserOut
from app.schemas.user_setting import UserSettingCreate, UserSettingOut, UserSettingUpdate