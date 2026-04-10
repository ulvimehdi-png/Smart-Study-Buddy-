from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import models
from database import engine
from routers import auth, notes, tasks, dashboard

# Automatically create database tables if they don't exist
models.Base.metadata.create_all(bind=engine)

app = FastAPI(title="Study Dashboard API")

# Allow the React frontend to talk to this backend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:3000", "http://localhost:5173", "http://127.0.0.1:5173"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Bring in all the organized routes from our other files!
app.include_router(auth.router)
app.include_router(notes.router)
app.include_router(tasks.router)
app.include_router(dashboard.router)

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)