from fastapi import FastAPI

from app.api.routes.auth import router as auth_router
from app.api.routes.subjects import router as subjects_router
from app.api.routes.tasks import router as tasks_router
from app.api.routes.study_sessions import router as study_sessions_router
from app.core.config import get_settings
from app.core.database import Base, engine
from app.models import User, Subject, Task, StudySession  # noqa: F401

#from fastapi.middleware.cors import CORSMiddleware

#app.add_middleware(
#    CORSMiddleware,
#    allow_origins=[
#        settings.FRONTEND_ORIGIN,
#        "http://localhost:3000",
#        "http://localhost:5173",
#        "http://127.0.0.1:5173",
#    ],
#    allow_credentials=True,
#    allow_methods=["*"],
#    allow_headers=["*"],
#)

settings = get_settings()

app = FastAPI(title=settings.app_name)

Base.metadata.create_all(bind=engine)

app.include_router(auth_router)
app.include_router(subjects_router)
app.include_router(tasks_router)
app.include_router(study_sessions_router)


@app.get("/")
def root():
    return {"message": "StudyBuddy API running"}

