from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from sqlalchemy import func, case
from datetime import date, timedelta
import models
from database import get_db

router = APIRouter(prefix="/api/dashboard", tags=["Dashboard"])

def get_study_streak_days(db: Session, user_id: int):
    # Change: Filter by models.Subject.user_id
    results = db.query(models.Session.study_date).join(models.Subject).filter(
        models.Subject.user_id == user_id, 
        models.Session.study_date.isnot(None)
    ).distinct().order_by(models.Session.study_date.desc()).all()
    
    dates = [r[0] for r in results]
    if not dates: return 0
        
    streak = 0
    expected = date.today()
    for current in dates:
        if current == expected:
            streak += 1
            expected -= timedelta(days=1)
        elif streak == 0 and current == expected - timedelta(days=1):
            streak = 1
            expected = current - timedelta(days=1)
        else:
            break
    return streak

@router.get("")
def get_dashboard(userId: int, db: Session = Depends(get_db)):
    # Change: Filter by Note.user_id
    notes_count = db.query(models.Note).filter(models.Note.user_id == userId).count()
    
    # Change: Filter by Subject.user_id
    total_min = db.query(func.coalesce(func.sum(models.Session.minutes_spent), 0)).join(models.Subject).filter(models.Subject.user_id == userId).scalar() or 0
    
    # Change: Filter by Subject.user_id
    today_min = db.query(func.coalesce(func.sum(models.Session.minutes_spent), 0)).join(models.Subject).filter(models.Subject.user_id == userId, models.Session.study_date == date.today()).scalar() or 0
    
    streak = get_study_streak_days(db, userId)
    
    # Task Progress
    prog_rows = db.query(
        models.Subject.subject_name,
        func.count(models.Task.task_id).label("total"),
        func.sum(case((models.Task.is_completed == True, 1), else_=0)).label("comp")
    ).outerjoin(models.Task, models.Subject.subject_id == models.Task.subject_id)\
     .filter(models.Subject.user_id == userId)\
     .group_by(models.Subject.subject_id, models.Subject.subject_name).order_by(models.Subject.subject_name).all()
     
    progress = []
    for r in prog_rows:
        total = float(r.total)
        comp = float(r.comp or 0)
        pct = round((comp * 100.0 / total), 1) if total > 0 else 0
        progress.append({"subject": r.subject_name, "completedTasks": int(comp), "totalTasks": int(total), "progressPercent": pct})
        
    # Change: Filter by Note.user_id
    rec_rows = db.query(models.Note, models.Subject.subject_name).outerjoin(models.Subject).filter(models.Note.user_id == userId).order_by(models.Note.created_at.desc(), models.Note.note_id.desc()).limit(5).all()
    recent_notes = [{"noteId": n.note_id, "title": n.title, "content": n.content, "createdAt": n.created_at, "subject": s} for n, s in rec_rows]
    
    return {
        "totalNotes": notes_count,
        "totalHours": round(float(total_min) / 60.0, 1),
        "todayHours": round(float(today_min) / 60.0, 1),
        "studyStreak": streak,
        "progress": progress,
        "recentNotes": recent_notes
    }