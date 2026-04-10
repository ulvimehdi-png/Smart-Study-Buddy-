from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
import models, schemas
from database import get_db
from utils import get_subject_id

router = APIRouter(prefix="/api/notes", tags=["Notes"])

@router.get("/")
def get_notes(userId: int, db: Session = Depends(get_db)):
    results = db.query(models.Note, models.Subject.subject_name)\
        .outerjoin(models.Subject, models.Note.subject_id == models.Subject.subject_id)\
        .filter(models.Note.user_id == userId)\
        .order_by(models.Note.created_at.desc(), models.Note.note_id.desc()).all()
        
    return [{"noteId": note.note_id, "title": note.title, "content": note.content, "sourceType": note.source_type, "fileName": note.file_name, "createdAt": note.created_at, "subject": subj_name} for note, subj_name in results]

@router.post("/")
def create_note(note: schemas.NoteData, db: Session = Depends(get_db)):
    sub_id = get_subject_id(db, note.userId, note.subject)
    new_note = models.Note(
        user_id=note.userId, 
        subject_id=sub_id, 
        title=note.title, 
        content=note.content, 
        source_type=note.sourceType or "manual", 
        file_name=note.fileName
    )
    db.add(new_note)
    db.commit()
    return {"success": True}

@router.delete("/{note_id}")
def delete_note(note_id: int, userId: int, db: Session = Depends(get_db)):
    db.query(models.Note).filter(models.Note.note_id == note_id, models.Note.user_id == userId).delete()
    db.commit()
    return {"success": True}