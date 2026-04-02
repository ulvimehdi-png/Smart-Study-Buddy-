-- ============================================
-- StudyBuddy baseline database v1.0
-- MariaDB / MySQL DDL
-- ============================================

CREATE DATABASE IF NOT EXISTS studybuddy
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE studybuddy;

SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS v_subject_overview;
DROP VIEW IF EXISTS v_subject_study_totals;
DROP VIEW IF EXISTS v_subject_task_progress;

DROP TABLE IF EXISTS study_sessions;
DROP TABLE IF EXISTS task_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS note_attachments;
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS subject_resources;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS user_settings;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 1. Users
-- ============================================

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    hashed_password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. User settings
-- ============================================

CREATE TABLE user_settings (
    user_id INT NOT NULL,
    timezone VARCHAR(64) NOT NULL DEFAULT 'UTC',
    week_start_day ENUM('monday', 'sunday') NOT NULL DEFAULT 'monday',
    default_session_type ENUM('manual', 'pomodoro', 'review', 'reading', 'practice') NOT NULL DEFAULT 'manual',
    default_session_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 50,
    default_break_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),
    CONSTRAINT fk_user_settings_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Subjects
-- ============================================

CREATE TABLE subjects (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    code VARCHAR(32) NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    color_hex CHAR(7) NULL,
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    archived_at DATETIME NULL,
    target_total_minutes INT UNSIGNED NULL,
    target_weekly_minutes INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_subjects_user_code (user_id, code),
    KEY idx_subjects_user_id (user_id),
    KEY idx_subjects_is_archived (is_archived),

    CONSTRAINT fk_subjects_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Tasks
-- ============================================

CREATE TABLE tasks (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    subject_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('todo', 'in_progress', 'done', 'cancelled') NOT NULL DEFAULT 'todo',
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    estimated_minutes INT UNSIGNED NULL,
    due_date DATE NULL,
    scheduled_for DATE NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_tasks_user_id (user_id),
    KEY idx_tasks_subject_id (subject_id),
    KEY idx_tasks_status (status),
    KEY idx_tasks_priority (priority),
    KEY idx_tasks_due_date (due_date),
    KEY idx_tasks_scheduled_for (scheduled_for),

    CONSTRAINT fk_tasks_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_tasks_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Tags
-- ============================================

CREATE TABLE tags (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    color_hex CHAR(7) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_tags_user_name (user_id, name),
    KEY idx_tags_user_id (user_id),

    CONSTRAINT fk_tags_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. Task <-> Tag mapping
-- ============================================

CREATE TABLE task_tags (
    task_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (task_id, tag_id),
    KEY idx_task_tags_tag_id (tag_id),

    CONSTRAINT fk_task_tags_task
        FOREIGN KEY (task_id) REFERENCES tasks(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_task_tags_tag
        FOREIGN KEY (tag_id) REFERENCES tags(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. Notes
-- ============================================

CREATE TABLE notes (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    subject_id INT NULL,
    task_id INT NULL,
    title VARCHAR(150) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    source_type ENUM('manual', 'upload', 'import', 'link') NOT NULL DEFAULT 'manual',
    external_url VARCHAR(1024) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_notes_user_id (user_id),
    KEY idx_notes_subject_id (subject_id),
    KEY idx_notes_task_id (task_id),
    KEY idx_notes_source_type (source_type),

    CONSTRAINT fk_notes_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notes_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_notes_task
        FOREIGN KEY (task_id) REFERENCES tasks(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. Note attachments
-- ============================================

CREATE TABLE note_attachments (
    id INT NOT NULL AUTO_INCREMENT,
    note_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    storage_path VARCHAR(512) NOT NULL,
    mime_type VARCHAR(100) NULL,
    size_bytes BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_note_attachments_note_id (note_id),

    CONSTRAINT fk_note_attachments_note
        FOREIGN KEY (note_id) REFERENCES notes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. Subject resources
-- ============================================

CREATE TABLE subject_resources (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    resource_type ENUM('link', 'file', 'book', 'video', 'article', 'other') NOT NULL DEFAULT 'link',
    url VARCHAR(1024) NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_subject_resources_user_id (user_id),
    KEY idx_subject_resources_subject_id (subject_id),
    KEY idx_subject_resources_type (resource_type),

    CONSTRAINT fk_subject_resources_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_subject_resources_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. Study sessions
-- ============================================

CREATE TABLE study_sessions (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    task_id INT NULL,
    note_id INT NULL,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    session_type ENUM('manual', 'pomodoro', 'review', 'reading', 'practice') NOT NULL DEFAULT 'manual',
    started_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    planned_minutes SMALLINT UNSIGNED NULL,
    actual_focus_minutes SMALLINT UNSIGNED NULL,
    break_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    distraction_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    session_note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_study_sessions_user_id (user_id),
    KEY idx_study_sessions_subject_id (subject_id),
    KEY idx_study_sessions_task_id (task_id),
    KEY idx_study_sessions_note_id (note_id),
    KEY idx_study_sessions_status (status),
    KEY idx_study_sessions_session_type (session_type),
    KEY idx_study_sessions_started_at (started_at),

    CONSTRAINT fk_study_sessions_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_study_sessions_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_study_sessions_task
        FOREIGN KEY (task_id) REFERENCES tasks(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_study_sessions_note
        FOREIGN KEY (note_id) REFERENCES notes(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Derived views
-- Do NOT store these as physical tables
-- ============================================

CREATE VIEW v_subject_task_progress AS
SELECT
    s.id AS subject_id,
    s.user_id,
    s.name AS subject_name,
    COUNT(t.id) AS total_tasks,
    SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) AS completed_tasks,
    SUM(CASE WHEN t.status IN ('todo', 'in_progress') THEN 1 ELSE 0 END) AS open_tasks
FROM subjects s
LEFT JOIN tasks t
    ON t.subject_id = s.id
GROUP BY
    s.id,
    s.user_id,
    s.name;

CREATE VIEW v_subject_study_totals AS
SELECT
    s.id AS subject_id,
    s.user_id,
    s.name AS subject_name,
    COUNT(ss.id) AS total_sessions,