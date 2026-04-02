from datetime import date, datetime, timedelta
from decimal import Decimal

import pymysql
from flask import Flask, jsonify, request

from db_connection import get_connection

app = Flask(__name__)


@app.after_request
def add_cors_headers(response):
    response.headers["Access-Control-Allow-Origin"] = "*"
    response.headers["Access-Control-Allow-Methods"] = "GET,POST,PUT,DELETE,OPTIONS"
    response.headers["Access-Control-Allow-Headers"] = "Content-Type"
    response.headers["Content-Type"] = "application/json"
    return response


@app.route("/<path:path>", methods=["OPTIONS"])
@app.route("/", methods=["OPTIONS"])
def options_handler(path=""):
    return jsonify({"ok": True}), 200


@app.get("/api/health")
def health():
    return jsonify({"ok": True}), 200


@app.get("/api/db-test")
def db_test():
    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute("SELECT DATABASE() AS db_name")
            row = cursor.fetchone()

        return jsonify({
            "ok": True,
            "connectedDatabase": row["db_name"] if row else None
        }), 200
    except Exception as exc:
        return jsonify({
            "ok": False,
            "message": "Database connection failed",
            "error": str(exc)
        }), 500
    finally:
        if connection:
            connection.close()


@app.get("/api/dashboard")
def dashboard():
    user_id = int(request.args.get("userId"))
    return jsonify(get_dashboard_data(user_id)), 200


@app.get("/api/notes")
def notes_get():
    user_id = int(request.args.get("userId"))
    return jsonify(get_notes_by_user(user_id)), 200


@app.post("/api/notes")
def notes_post():
    body = request.get_json(force=True)

    user_id = int(body["userId"])
    subject = get_optional_string(body, "subject")
    title = body["title"]
    content = body["content"]
    source_type = get_optional_string(body, "sourceType")
    file_name = get_optional_string(body, "fileName")

    ok = save_note(user_id, subject, title, content, source_type, file_name)

    if not ok:
        return jsonify({"success": False, "message": "Could not save note"}), 400

    return jsonify({"success": True}), 200


@app.delete("/api/notes/<int:note_id>")
def notes_delete(note_id: int):
    user_id = int(request.args.get("userId"))
    ok = delete_note(note_id, user_id)

    if not ok:
        return jsonify({"success": False, "message": "Could not delete note"}), 400

    return jsonify({"success": True}), 200


@app.post("/api/study-sessions")
def study_sessions_post():
    body = request.get_json(force=True)

    user_id = int(body["userId"])
    subject = body["subject"]
    minutes_studied = int(body["minutesStudied"])
    session_note = get_optional_string(body, "sessionNote")

    ok = save_study_session(user_id, subject, minutes_studied, session_note)

    if not ok:
        return jsonify({"success": False, "message": "Could not save study session"}), 400

    return jsonify({"success": True}), 200


@app.get("/api/tasks")
def tasks_get():
    user_id = int(request.args.get("userId"))
    return jsonify(get_tasks_by_user(user_id)), 200


@app.post("/api/tasks")
def tasks_post():
    body = request.get_json(force=True)

    user_id = int(body["userId"])
    subject = get_optional_string(body, "subject")
    title = body["title"]
    description = get_optional_string(body, "description")
    due_date = get_optional_string(body, "dueDate")

    ok = save_task(user_id, subject, title, description, due_date)

    if not ok:
        return jsonify({"success": False, "message": "Could not save task"}), 400

    return jsonify({"success": True}), 200


@app.put("/api/tasks/<int:task_id>/toggle")
def tasks_toggle(task_id: int):
    user_id = int(request.args.get("userId"))
    ok = toggle_task(task_id, user_id)

    if not ok:
        return jsonify({"success": False, "message": "Could not toggle task"}), 400

    return jsonify({"success": True}), 200


@app.delete("/api/tasks/<int:task_id>")
def tasks_delete(task_id: int):
    user_id = int(request.args.get("userId"))
    ok = delete_task(task_id, user_id)

    if not ok:
        return jsonify({"success": False, "message": "Could not delete task"}), 400

    return jsonify({"success": True}), 200


def get_optional_string(body, key):
    value = body.get(key)
    if value is None:
        return None

    value = str(value).strip()
    return value if value else None


def normalize_value(value):
    if isinstance(value, Decimal):
        return float(value)
    if isinstance(value, (datetime, date)):
        return value.isoformat()
    return value


def get_or_create_subject_id(connection, user_id, subject_name):
    if subject_name is None or subject_name.isblank() if hasattr(subject_name, "isblank") else False:
        return -1

    if subject_name is None or not str(subject_name).strip():
        return -1

    clean_name = str(subject_name).strip()

    select_sql = "SELECT subject_id FROM subjects WHERE user_id = %s AND subject_name = %s"
    insert_sql = "INSERT INTO subjects (user_id, subject_name) VALUES (%s, %s)"

    with connection.cursor() as cursor:
        cursor.execute(select_sql, (user_id, clean_name))
        row = cursor.fetchone()
        if row:
            return int(row["subject_id"])

        try:
            cursor.execute(insert_sql, (user_id, clean_name))
            return int(cursor.lastrowid)
        except pymysql.IntegrityError:
            cursor.execute(select_sql, (user_id, clean_name))
            row = cursor.fetchone()
            if row:
                return int(row["subject_id"])

    return -1


def save_note(user_id, subject, title, content, source_type, file_name):
    sql = """
          INSERT INTO notes (user_id, subject_id, title, content, source_type, file_name)
          VALUES (%s, %s, %s, %s, %s, %s) \
          """

    connection = None
    try:
        connection = get_connection()
        subject_id = get_or_create_subject_id(connection, user_id, subject)

        with connection.cursor() as cursor:
            cursor.execute(
                sql,
                (
                    user_id,
                    None if subject_id == -1 else subject_id,
                    title,
                    content,
                    "manual" if source_type is None or not str(source_type).strip() else source_type,
                    file_name,
                ),
            )

        connection.commit()
        return True
    except Exception as exc:
        print(f"save_note error: {exc}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()


def delete_note(note_id, user_id):
    sql = "DELETE FROM notes WHERE note_id = %s AND user_id = %s"

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            affected = cursor.execute(sql, (note_id, user_id))

        connection.commit()
        return affected > 0
    except Exception as exc:
        print(f"delete_note error: {exc}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()


def save_study_session(user_id, subject, minutes_studied, session_note):
    sql = """
          INSERT INTO sessions
          (subject_id, note_id, session_type, priority, session_date, duration, study_date, minutes_spent, session_note)
          VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s) \
          """

    connection = None
    try:
        connection = get_connection()
        subject_id = get_or_create_subject_id(connection, user_id, subject)

        if subject_id == -1:
            return False

        today = date.today()

        with connection.cursor() as cursor:
            cursor.execute(
                sql,
                (
                    subject_id,
                    None,
                    "exercise",
                    1,
                    today,
                    minutes_studied,
                    today,
                    minutes_studied,
                    session_note,
                ),
            )

        connection.commit()
        return True
    except Exception as exc:
        print(f"save_study_session error: {exc}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()


def save_task(user_id, subject, title, description, due_date):
    sql = """
          INSERT INTO tasks (user_id, subject_id, title, description, is_completed, due_date)
          VALUES (%s, %s, %s, %s, FALSE, %s) \
          """

    connection = None
    try:
        connection = get_connection()
        subject_id = get_or_create_subject_id(connection, user_id, subject)

        with connection.cursor() as cursor:
            cursor.execute(
                sql,
                (
                    user_id,
                    None if subject_id == -1 else subject_id,
                    title,
                    description,
                    None if due_date is None or not str(due_date).strip() else due_date,
                ),
            )

        connection.commit()
        return True
    except Exception as exc:
        print(f"save_task error: {exc}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()


def toggle_task(task_id, user_id):
    sql = """
          UPDATE tasks
          SET is_completed = NOT is_completed,
              completed_at = CASE
                                 WHEN is_completed = 0 THEN CURRENT_TIMESTAMP
                                 ELSE NULL
                  END
          WHERE task_id = %s AND user_id = %s \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            affected = cursor.execute(sql, (task_id, user_id))

        connection.commit()
        return affected > 0
    except Exception as exc:
        print(f"toggle_task error: {exc}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()


def delete_task(task_id, user_id):
    sql = "DELETE FROM tasks WHERE task_id = %s AND user_id = %s"

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            affected = cursor.execute(sql, (task_id, user_id))

        connection.commit()
        return affected > 0
    except Exception as exc:
        print(f"delete_task error: {exc}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()


def get_notes_by_user(user_id):
    sql = """
          SELECT n.note_id, n.title, n.content, n.source_type, n.file_name, n.created_at, s.subject_name
          FROM notes n
                   LEFT JOIN subjects s ON n.subject_id = s.subject_id
          WHERE n.user_id = %s
          ORDER BY n.created_at DESC, n.note_id DESC \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            rows = cursor.fetchall()

        return [
            {
                "noteId": row["note_id"],
                "title": row["title"],
                "content": row["content"],
                "sourceType": row["source_type"],
                "fileName": row["file_name"],
                "createdAt": normalize_value(row["created_at"]),
                "subject": row["subject_name"],
            }
            for row in rows
        ]
    except Exception as exc:
        print(f"get_notes_by_user error: {exc}")
        return []
    finally:
        if connection:
            connection.close()


def get_recent_notes(user_id):
    sql = """
          SELECT n.note_id, n.title, n.content, n.created_at, s.subject_name
          FROM notes n
                   LEFT JOIN subjects s ON n.subject_id = s.subject_id
          WHERE n.user_id = %s
          ORDER BY n.created_at DESC, n.note_id DESC
              LIMIT 5 \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            rows = cursor.fetchall()

        return [
            {
                "noteId": row["note_id"],
                "title": row["title"],
                "content": row["content"],
                "createdAt": normalize_value(row["created_at"]),
                "subject": row["subject_name"],
            }
            for row in rows
        ]
    except Exception as exc:
        print(f"get_recent_notes error: {exc}")
        return []
    finally:
        if connection:
            connection.close()


def get_tasks_by_user(user_id):
    sql = """
          SELECT t.task_id, t.title, t.description, t.is_completed, t.due_date, t.created_at, t.completed_at, s.subject_name
          FROM tasks t
                   LEFT JOIN subjects s ON t.subject_id = s.subject_id
          WHERE t.user_id = %s
          ORDER BY t.is_completed ASC, t.created_at DESC, t.task_id DESC \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            rows = cursor.fetchall()

        return [
            {
                "taskId": row["task_id"],
                "title": row["title"],
                "description": row["description"],
                "isCompleted": bool(row["is_completed"]),
                "dueDate": normalize_value(row["due_date"]),
                "createdAt": normalize_value(row["created_at"]),
                "completedAt": normalize_value(row["completed_at"]),
                "subject": row["subject_name"],
            }
            for row in rows
        ]
    except Exception as exc:
        print(f"get_tasks_by_user error: {exc}")
        return []
    finally:
        if connection:
            connection.close()


def get_progress_by_user(user_id):
    sql = """
          SELECT
              s.subject_name,
              COUNT(t.task_id) AS total_tasks,
              COALESCE(SUM(CASE WHEN t.is_completed = TRUE THEN 1 ELSE 0 END), 0) AS completed_tasks,
              CASE
                  WHEN COUNT(t.task_id) = 0 THEN 0
                  ELSE ROUND(
                          COALESCE(SUM(CASE WHEN t.is_completed = TRUE THEN 1 ELSE 0 END), 0) * 100.0 / COUNT(t.task_id),
                          1
                       )
                  END AS progress_percent
          FROM subjects s
                   LEFT JOIN tasks t ON s.subject_id = t.subject_id
          WHERE s.user_id = %s
          GROUP BY s.subject_id, s.subject_name
          ORDER BY s.subject_name \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            rows = cursor.fetchall()

        return [
            {
                "subject": row["subject_name"],
                "completedTasks": int(row["completed_tasks"]),
                "totalTasks": int(row["total_tasks"]),
                "progressPercent": float(row["progress_percent"]),
            }
            for row in rows
        ]
    except Exception as exc:
        print(f"get_progress_by_user error: {exc}")
        return []
    finally:
        if connection:
            connection.close()


def get_notes_count(user_id):
    sql = "SELECT COUNT(*) AS cnt FROM notes WHERE user_id = %s"

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            row = cursor.fetchone()

        return int(row["cnt"]) if row else 0
    except Exception as exc:
        print(f"get_notes_count error: {exc}")
        return 0
    finally:
        if connection:
            connection.close()


def get_total_hours(user_id):
    sql = """
          SELECT COALESCE(SUM(se.minutes_spent), 0) / 60.0 AS hours
          FROM sessions se
                   JOIN subjects s ON se.subject_id = s.subject_id
          WHERE s.user_id = %s \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            row = cursor.fetchone()

        return float(row["hours"]) if row and row["hours"] is not None else 0.0
    except Exception as exc:
        print(f"get_total_hours error: {exc}")
        return 0.0
    finally:
        if connection:
            connection.close()


def get_today_hours(user_id):
    sql = """
          SELECT COALESCE(SUM(se.minutes_spent), 0) / 60.0 AS hours
          FROM sessions se
                   JOIN subjects s ON se.subject_id = s.subject_id
          WHERE s.user_id = %s AND se.study_date = CURDATE() \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            row = cursor.fetchone()

        return float(row["hours"]) if row and row["hours"] is not None else 0.0
    except Exception as exc:
        print(f"get_today_hours error: {exc}")
        return 0.0
    finally:
        if connection:
            connection.close()


def get_study_streak_days(user_id):
    sql = """
          SELECT DISTINCT se.study_date
          FROM sessions se
                   JOIN subjects s ON se.subject_id = s.subject_id
          WHERE s.user_id = %s
          ORDER BY se.study_date DESC \
          """

    connection = None
    try:
        connection = get_connection()
        with connection.cursor() as cursor:
            cursor.execute(sql, (user_id,))
            rows = cursor.fetchall()

        dates = [row["study_date"] for row in rows if row.get("study_date") is not None]
        if not dates:
            return 0

        streak = 0
        expected = date.today()

        for current in dates:
            if current == expected:
                streak += 1
                expected = expected - timedelta(days=1)
            elif streak == 0 and current == expected - timedelta(days=1):
                streak = 1
                expected = current - timedelta(days=1)
            else:
                break

        return streak
    except Exception as exc:
        print(f"get_study_streak_days error: {exc}")
        return 0
    finally:
        if connection:
            connection.close()


def get_dashboard_data(user_id):
    return {
        "totalNotes": get_notes_count(user_id),
        "totalHours": get_total_hours(user_id),
        "todayHours": get_today_hours(user_id),
        "studyStreak": get_study_streak_days(user_id),
        "progress": get_progress_by_user(user_id),
        "recentNotes": get_recent_notes(user_id),
    }


if __name__ == "__main__":
    print("Backend running on http://127.0.0.1:5000")
    app.run(host="127.0.0.1", port=5000, debug=False, use_reloader=False)