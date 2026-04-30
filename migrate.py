import pymysql
from db_connection import get_connection

def run_migration():
    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            # Create streaks table
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS streaks (
                    user_id INT PRIMARY KEY,
                    current_streak INT DEFAULT 0,
                    longest_streak INT DEFAULT 0,
                    last_study_date DATE
                )
            """)

            # Check if priority column exists in tasks
            cursor.execute("SHOW COLUMNS FROM tasks LIKE 'priority'")
            if not cursor.fetchone():
                cursor.execute("""
                    ALTER TABLE tasks
                    ADD COLUMN priority ENUM('low', 'medium', 'high') DEFAULT 'medium'
                """)

        connection.commit()
        print("Migrations completed successfully.")
    except Exception as exc:
        print(f"Migration error: {exc}")
        if connection:
            connection.rollback()
    finally:
        if connection:
            connection.close()

if __name__ == "__main__":
    run_migration()