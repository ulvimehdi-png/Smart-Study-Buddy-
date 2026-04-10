from sqlalchemy.orm import Session
import models

def get_subject_id(db: Session, user_id: int, subject_name: str):
    if not subject_name or not subject_name.strip(): 
        return None
    clean_name = subject_name.strip()
    
    # Check if subject exists
    subject = db.query(models.Subject).filter(
        models.Subject.user_id == user_id, 
        models.Subject.subject_name == clean_name
    ).first()
    
    if subject: 
        return subject.subject_id
        
    # If not, create it
    new_sub = models.Subject(user_id=user_id, subject_name=clean_name)
    db.add(new_sub)
    db.commit()
    db.refresh(new_sub)
    return new_sub.subject_id