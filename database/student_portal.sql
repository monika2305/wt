-- =========================================================
-- Student Admission & Profile Management Portal
-- Database schema: student_portal
--
-- Used by:
--   Question 2 (PHP + MySQL CRUD) -> students, student_files
--   Question 3 (Servlet + JDBC)   -> students, student_files, users
--
-- Import in phpMyAdmin (Import tab) or from the command line:
--   C:\xampp\mysql\bin\mysql.exe -u root < student_portal.sql
--
-- WARNING: Re-importing this file DROPS the tables and deletes all
-- existing records. Uploaded files in Question2_PHP/uploads are NOT
-- deleted automatically.
-- =========================================================

CREATE DATABASE IF NOT EXISTS student_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE student_portal;

-- Drop child table first because of the foreign key
DROP TABLE IF EXISTS student_files;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------
-- Table 1: students  (one row per student)
-- ---------------------------------------------------------
CREATE TABLE students (
    student_id   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name         VARCHAR(50)   NOT NULL,
    roll_number  VARCHAR(15)   NOT NULL,
    email        VARCHAR(100)  NOT NULL,
    course       VARCHAR(50)   NOT NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (student_id),
    UNIQUE KEY uq_students_roll (roll_number),   -- no two students with the same roll number
    UNIQUE KEY uq_students_email (email),        -- no two students with the same email
    CONSTRAINT chk_students_email CHECK (email LIKE '%_@_%._%')
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table 2: student_files  (uploaded images/documents + metadata)
-- One student can have many files (one-to-many relationship).
-- ---------------------------------------------------------
CREATE TABLE student_files (
    file_id        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    student_id     INT UNSIGNED  NOT NULL,                     -- which student owns the file
    file_category  ENUM('profile_image', 'id_proof', 'certificate') NOT NULL,
    original_name  VARCHAR(255)  NOT NULL,                     -- name on the user's computer (display only)
    stored_name    VARCHAR(100)  NOT NULL,                     -- unique random name on the server
    file_path      VARCHAR(255)  NOT NULL,                     -- relative path, e.g. uploads/abc123.jpg
    file_type      VARCHAR(100)  NOT NULL,                     -- MIME type, e.g. image/jpeg
    file_size      INT UNSIGNED  NOT NULL,                     -- size in bytes
    uploaded_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (file_id),
    UNIQUE KEY uq_files_stored_name (stored_name),
    -- each student has at most ONE file per category (new upload replaces old one)
    UNIQUE KEY uq_files_student_category (student_id, file_category),
    -- deleting a student automatically deletes his/her file records
    CONSTRAINT fk_files_student FOREIGN KEY (student_id)
        REFERENCES students (student_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_files_size CHECK (file_size > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table 3: users  (login accounts for the Question 3 Servlet portal)
-- Passwords are NEVER stored as plain text. password_hash holds a
-- PBKDF2-HMAC-SHA256 hash in the format:
--     pbkdf2$<iterations>$<base64 salt>$<base64 hash>
-- Generate a new hash with the PasswordUtil class (see README.md).
-- ---------------------------------------------------------
CREATE TABLE users (
    user_id        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    username       VARCHAR(50)   NOT NULL,
    password_hash  VARCHAR(255)  NOT NULL,
    full_name      VARCHAR(100)  NOT NULL,
    role           ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Demo login account for Question 3
--   Username: admin
--   Password: Admin@123
-- (Change or delete this account for anything other than a demo.)
-- ---------------------------------------------------------
INSERT INTO users (username, password_hash, full_name, role) VALUES
('admin', 'pbkdf2$65536$QpRlN5n3kBKMQltCnYbc6w==$iR2RU5I64GQvlOJugki9q4wekxevoJJgmiSmTgramIo=', 'Portal Administrator', 'admin');

-- ---------------------------------------------------------
-- Demonstration students (inserted without files).
--   * Monika M  - the project author's own demonstration record.
--     Upload her profile image / documents from the PHP admin panel
--     (Edit Student) after importing.
--   * Ananya Iyer, Rahul Verma - extra sample students so the list and
--     the JSP loop show multiple records and the "no image" fallback.
-- Monika is inserted last so she appears first (lists are newest first).
-- ---------------------------------------------------------
INSERT INTO students (name, roll_number, email, course) VALUES
('Ananya Iyer',   '23CS001', 'ananya.iyer@example.com',   'B.Tech CSE'),
('Rahul Verma',   '23CA014', 'rahul.verma@example.com',   'BCA'),
('Monika M',      '24CS122', 'monikam232007@gmail.com',   'B.Tech CSE');
