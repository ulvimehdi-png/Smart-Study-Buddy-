from enum import Enum


class WeekStartDay(str, Enum):
    monday = "monday"
    sunday = "sunday"


class SessionType(str, Enum):
    manual = "manual"
    pomodoro = "pomodoro"
    review = "review"
    reading = "reading"
    practice = "practice"


class TaskStatus(str, Enum):
    todo = "todo"
    in_progress = "in_progress"
    done = "done"
    cancelled = "cancelled"


class TaskPriority(str, Enum):
    low = "low"
    medium = "medium"
    high = "high"


class NoteSourceType(str, Enum):
    manual = "manual"
    upload = "upload"
    import_ = "import"
    link = "link"


class ResourceType(str, Enum):
    link = "link"
    file = "file"
    book = "book"
    video = "video"
    article = "article"
    other = "other"


class StudySessionStatus(str, Enum):
    active = "active"
    completed = "completed"
    cancelled = "cancelled"