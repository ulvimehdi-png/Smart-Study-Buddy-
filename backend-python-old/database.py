from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

# Connect to MySQL. Format: mysql+pymysql://username:password@server/database_name
DB_URL = "mysql+pymysql://a25sewt301:EwSlrkVQ@mysql.studev.groept.be:3306/a25sewt301"

engine = create_engine(DB_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


if __name__ == "__main__":
    # Just a quick test to see if we can connect to the database
    try:
        with engine.connect() as connection:
            result = connection.execute("SELECT 1")
            print("Database connection successful:", result.fetchone())
    except Exception as e:
        print("Database connection failed:", e)