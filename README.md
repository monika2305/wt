# Student Admission & Profile Management Portal

**Web Technology – Assignment II**

| | |
|---|---|
| **Student** | Monika M |
| **Roll Number** | 24CS122 |
| **Course** | B.Tech CSE |
| **Email** | monikam232007@gmail.com |

A three-part college web application that covers front-end validation, a PHP/MySQL admin panel with file uploads, and a Java Servlet/JSP portal with login sessions. All three parts share one MySQL database and one visual design.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technologies](#2-technologies)
3. [Project Structure](#3-project-structure)
4. [Software Requirements (tested versions)](#4-software-requirements-tested-versions)
5. [Database Setup](#5-database-setup)
6. [Running Question 1](#6-running-question-1--html-css-javascript)
7. [Running Question 2](#7-running-question-2--php--mysql)
8. [Running Question 3](#8-running-question-3--java-servlet--jsp--jdbc)
9. [How Question 1 Works](#9-how-question-1-works)
10. [How Question 2 Works](#10-how-question-2-works)
11. [How Question 3 Works](#11-how-question-3-works)
12. [Security Measures](#12-security-measures)
13. [Troubleshooting](#13-troubleshooting)
14. [Uploading to GitHub](#14-uploading-to-github)
15. [Assignment Documentation](#15-assignment-documentation)
16. [Required Screenshots](#16-required-screenshots)
17. [Final Requirement Checklist](#17-final-requirement-checklist)

---

## 1. Project Overview

The **Student Admission & Profile Management Portal** handles the main steps of college admission:

| Part | Who uses it | What it does |
|------|-------------|--------------|
| **Question 1** – *Student Admission Portal* (registration form) | New students | Registration form with live JavaScript validation (name, roll number, email, password strength, course, file). |
| **Question 2** – *Student Admin Panel* (PHP) | College admin | Add, view, edit and delete student records (CRUD). Upload a profile image, ID proof and certificate; file details are stored in MySQL. |
| **Question 3** – *Student Profile Portal* (Servlet/JSP) | Logged-in staff | Login with sessions, then browse student profiles. Photos and documents are streamed securely by a Servlet. |

Questions 2 and 3 use the **same `student_portal` database** and the **same uploads folder**. A student added in the PHP admin panel appears in the Servlet portal straight away.

---

## 2. Technologies

| Technology | Used for |
|------------|----------|
| **HTML5** | Page structure, semantic tags, form input types (`email`, `password`, `file`, `select`) |
| **CSS3** | Custom design (no Bootstrap): CSS variables, Flexbox, Grid, media queries for responsive layout |
| **JavaScript** | Client-side validation, live password checklist, delete confirmation |
| **PHP 8.2** | Server-side CRUD logic, validation, file uploads, sessions, CSRF protection |
| **MySQL / MariaDB 10.4** | `students`, `student_files` and `users` tables with primary/foreign keys |
| **Java** | Server-side language for Question 3 (model classes, DAO, password hashing) |
| **Java Servlet 3.1** | Login, logout, student retrieval and file-streaming controllers, authentication filter |
| **JSP 2.3 + JSTL 1.2** | Dynamic pages with `<c:forEach>` loops and `<c:choose>/<c:if>` conditions |
| **JDBC (MySQL Connector/J 8.4.0)** | Connecting Java to MySQL with `PreparedStatement` |
| **Apache Tomcat 8.5** | Servlet/JSP container that runs Question 3 (port 8080) |
| **XAMPP** | Apache web server + PHP + MariaDB + phpMyAdmin for Questions 1–2 and the database |

---

## 3. Project Structure

```
WebTechnology_Assignment2/
│
├── Question1_Frontend/
│   ├── index.html            Registration form
│   ├── style.css             Custom responsive styling
│   └── validation.js         Client-side validation
│
├── Question2_PHP/
│   ├── index.php             READ   – list/search all students
│   ├── add_student.php       CREATE – add student + uploads
│   ├── view_student.php      READ   – one profile, image, documents, file metadata
│   ├── edit_student.php      UPDATE – edit details / replace files safely
│   ├── delete_student.php    DELETE – confirmation page, then delete record + files
│   ├── db.php                Database connection (credentials in ONE place)
│   ├── functions.php         Escaping, validation, upload handling, CSRF, flash messages
│   ├── script.js             Client-side validation + delete confirm()
│   ├── style.css
│   ├── includes/             header.php, footer.php, student_form.php (blocked from direct access)
│   └── uploads/              Uploaded files (.htaccess stops scripts running here)
│
├── Question3_Servlet/
│   ├── src/com/studentportal/
│   │   ├── util/      DBConnection.java (JDBC), PasswordUtil.java (PBKDF2 hashing)
│   │   ├── model/     Student.java, StudentFile.java, User.java
│   │   ├── dao/       StudentDAO.java, UserDAO.java (all SQL)
│   │   ├── servlet/   LoginServlet, LogoutServlet, StudentServlet, FileServlet
│   │   └── filter/    AuthFilter.java (session check for protected pages)
│   ├── WebContent/
│   │   ├── index.jsp, login.jsp, students.jsp, profile.jsp, error.jsp
│   │   ├── css/style.css
│   │   └── WEB-INF/
│   │       ├── web.xml          Servlet/filter mappings, upload folder, session config
│   │       ├── jspf/            Shared header/footer fragments
│   │       └── lib/             JDBC driver + JSTL jars (see lib/README.txt)
│   ├── build.bat              Compiles Java into WEB-INF/classes
│   ├── deploy.bat             Builds and copies the app to Tomcat
│   └── .htaccess              Stops Apache from serving the Java source
│
├── database/
│   └── student_portal.sql     Full schema + demo login + 3 demo students
│
├── .gitignore
└── README.md
```

---

## 4. Software Requirements (tested versions)

This project was developed and tested on Windows 11 with:

| Software | Version tested | Notes |
|----------|---------------|-------|
| XAMPP | 8.2.12 | Includes Apache 2.4.58, PHP 8.2.12, MariaDB 10.4.32, phpMyAdmin |
| Apache Tomcat | **8.5.96** (bundled with XAMPP at `C:\xampp\tomcat`) | **Servlet 3.1 / JSP 2.3 → `javax.servlet.*` packages** |
| Java JDK | 24.0.2 | Any **JDK 11 or newer** can build it (`build.bat` uses `javac --release 8`, so the classes also run on Java 8+) |
| MySQL Connector/J | **8.4.0** (`mysql-connector-j-8.4.0.jar`) | The JDBC driver |
| JSTL | 1.2.5 (`taglibs-standard-impl` + `taglibs-standard-spec`) | Copied from Tomcat's own `examples` app |

> **Important – javax vs jakarta:** This project uses `javax.servlet` throughout and targets **Tomcat 8.5 (or Tomcat 9)**.
> **Tomcat 10 and 11 will not work without changes**, because they use the renamed `jakarta.servlet` packages. Do not mix the two.

PHP extensions needed (all enabled by default in XAMPP): `mysqli`, `fileinfo`, `mbstring`, `session`.

---

## 5. Database Setup

1. **Start Apache and MySQL in XAMPP**
   Open the **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`) and click **Start** next to **Apache** and **MySQL**. Both should turn green.

2. **Open phpMyAdmin**
   In a browser go to **http://localhost/phpmyadmin**

3. **Create / import the `student_portal` database**
   You do *not* need to create the database by hand, because the SQL file contains `CREATE DATABASE IF NOT EXISTS student_portal`.
   *(Optional: click **New**, type `student_portal`, choose collation `utf8mb4_unicode_ci`, click **Create**.)*

4. **Import `student_portal.sql`**
   - Click the **Import** tab at the top of phpMyAdmin.
   - Click **Choose File** and select `WebTechnology_Assignment2\database\student_portal.sql`.
   - Leave the other options as they are and click **Import** (or **Go**) at the bottom.
   - You should see *"Import has been successfully finished"*. The left panel now shows `student_portal` with tables **students**, **student_files** and **users**.

   *Command-line alternative:*
   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\WebTechnology_Assignment2\database\student_portal.sql
   ```

> ⚠️ Re-importing the SQL file **drops and recreates** the tables, so all student records are deleted. Files in `Question2_PHP/uploads/` are not deleted automatically; delete them by hand if you want a completely clean start (keep the `.htaccess` file).

### Database design

```
students (1) ─────────────< (many) student_files          users
-----------                        -------------          -----
student_id  PK, AUTO_INCREMENT     file_id      PK        user_id        PK
name        NOT NULL               student_id   FK → students ON DELETE CASCADE
roll_number UNIQUE                 file_category ENUM(profile_image,id_proof,certificate)
email       UNIQUE, CHECK          original_name          username       UNIQUE
course      NOT NULL               stored_name  UNIQUE    password_hash  (PBKDF2)
created_at, updated_at             file_path              full_name, role
                                   file_type (MIME)
                                   file_size  CHECK > 0
                                   uploaded_at
                                   UNIQUE (student_id, file_category)
```

* The **foreign key with `ON DELETE CASCADE`** means deleting a student automatically deletes their file records.
* **`UNIQUE (student_id, file_category)`** means each student has at most one profile image, one ID proof and one certificate. Uploading a new one replaces the old one.

### Demonstration student records

A fresh import creates these students (newest first):

| Name | Roll No. | Email | Course |
|------|----------|-------|--------|
| **Monika M** | 24CS122 | monikam232007@gmail.com | B.Tech CSE |
| Rahul Verma | 23CA014 | rahul.verma@example.com | BCA |
| Ananya Iyer | 23CS001 | ananya.iyer@example.com | B.Tech CSE |

Monika M is the author's own demonstration record. Upload her profile image, ID proof and certificate through **Edit Student** in Question 2. The two other students are kept without files on purpose, so the list shows several records and the "no image / no documents" fallbacks.

### Demo login account (for Question 3)

| Username | Password |
|----------|----------|
| `admin`  | `Admin@123` |

The password is **not** stored as plain text. The `users.password_hash` column holds a salted **PBKDF2-HMAC-SHA256** hash (`pbkdf2$65536$<salt>$<hash>`).

**To create another account:**
1. Build the Servlet project once (`Question3_Servlet\build.bat`).
2. Generate a hash:
   ```bash
   java -cp C:\xampp\htdocs\WebTechnology_Assignment2\Question3_Servlet\WebContent\WEB-INF\classes com.studentportal.util.PasswordUtil MyNewPass@1
   ```
3. Copy the printed value and run this in phpMyAdmin → **SQL** tab:
   ```sql
   INSERT INTO users (username, password_hash, full_name, role)
   VALUES ('staff1', 'PASTE_HASH_HERE', 'Staff Member', 'staff');
   ```

---

## 6. Running Question 1 – HTML, CSS, JavaScript

Question 1 is purely front-end and needs no server.

* **Option A:** double-click `Question1_Frontend\index.html`. It opens in your browser.
* **Option B (with Apache running):** open **http://localhost/WebTechnology_Assignment2/Question1_Frontend/index.html**

**To test it:**
1. Click **Register** with everything empty. Every field shows a red message and the cursor jumps to the first field.
2. Type a weak password and watch the checklist under the password fields turn green rule by rule.
3. Type a different value in *Confirm Password*. You should see *"Passwords do not match."*
4. Choose a `.exe`/`.txt` file or a file larger than 2 MB. The file type or size error appears.
5. Fill everything correctly (e.g. `Monika M`, `24CS122`, `monikam232007@gmail.com`, a password such as `Secure@123`, B.Tech CSE, a small JPG). A green success message appears and the form resets.
6. Click **Reset**. The form and all messages are cleared.
7. Shrink the browser window (or press F12 → device toolbar). The layout becomes one column.

---

## 7. Running Question 2 – PHP + MySQL

1. The project folder must be inside XAMPP's web root:
   ```
   C:\xampp\htdocs\WebTechnology_Assignment2\Question2_PHP
   ```
   (Apache only runs PHP files that are under `C:\xampp\htdocs`.)
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Import the database (Section 5).
4. Open: **http://localhost/WebTechnology_Assignment2/Question2_PHP/**

**Database credentials** are written in **one place only**, `Question2_PHP/db.php`:
```php
$dbConfig = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'student_portal'];
```
If your MySQL root user has a password, either change it there or create `Question2_PHP/db.local.php` containing `<?php return ['pass' => 'yourPassword'];`. That file is in `.gitignore`, so your password is never uploaded.

**To test it:**
1. On the list, click **Edit** for **Monika M (24CS122)** → choose a profile photo (JPG/PNG ≤ 2 MB) and, if you like, a new ID proof / certificate (PDF/JPG/PNG ≤ 5 MB) → **Save Changes**. You land on the View page with the photo, document previews and the file metadata table.
2. Edit Monika again and change nothing but the text (e.g. retype the course). The files remain.
3. Use **Search** (e.g. `24CS122` or `Monika`).
4. To test **Add** and **Delete** without touching your own record, add a temporary student (e.g. `Temporary Student`, roll `TEMP0001`, `temp@example.com`), then delete *that* student (confirmation page + popup).
5. Try adding a student with roll number `24CS122` or email `monikam232007@gmail.com`. Both are rejected as duplicates.

---

## 8. Running Question 3 – Java Servlet + JSP + JDBC

### 8.1 Requirements
* **Java:** JDK 11 or newer (tested: JDK 24.0.2). `javac` must be on your PATH: run `javac -version` in a terminal to check.
* **Tomcat:** **8.5.x** (tested: 8.5.96 from XAMPP at `C:\xampp\tomcat`). Tomcat 9 also works; **Tomcat 10+ does not** (jakarta packages).
* **MySQL** running with the `student_portal` database imported (Section 5).

### 8.2 Dependencies: where the JAR files go
All three JARs go into **`Question3_Servlet\WebContent\WEB-INF\lib\`** (Tomcat loads every JAR in that folder automatically):

| JAR | Where to get it |
|-----|-----------------|
| `mysql-connector-j-8.4.0.jar` | Maven Central: https://repo1.maven.org/maven2/com/mysql/mysql-connector-j/8.4.0/mysql-connector-j-8.4.0.jar |
| `taglibs-standard-impl-1.2.5.jar` | Copy from `C:\xampp\tomcat\webapps\examples\WEB-INF\lib\` |
| `taglibs-standard-spec-1.2.5.jar` | Copy from `C:\xampp\tomcat\webapps\examples\WEB-INF\lib\` |

*(They are already present on the development computer. They are excluded from GitHub by `.gitignore`, so add them after cloning.)*
`servlet-api.jar` must **not** be copied there. `build.bat` uses Tomcat's own copy only for compiling.

### 8.3 Configuration
* **Database:** `src/com/studentportal/util/DBConnection.java` holds the URL, username and password constants (default: `localhost:3306`, `root`, empty password).
* **Uploads folder:** `WebContent/WEB-INF/web.xml` → `<context-param>` **`uploadDirectory`** = `C:/xampp/htdocs/WebTechnology_Assignment2/Question2_PHP/uploads`. Change it if your project lives elsewhere.

### 8.4 Build and deploy
Open a terminal in `Question3_Servlet` (or double-click the files):

```bash
cd C:\xampp\htdocs\WebTechnology_Assignment2\Question3_Servlet
```
```bash
deploy.bat
```

`deploy.bat` does the following:
1. Runs **`build.bat`**, which compiles all `.java` files with `javac --release 8 -cp C:\xampp\tomcat\lib\servlet-api.jar` into `WebContent\WEB-INF\classes`.
2. Copies `WebContent` to **`C:\xampp\tomcat\webapps\StudentPortal`** (the app's URL name is the folder name).
3. Updates the timestamp of the deployed `web.xml`, so an already-running Tomcat reloads the app.

If Tomcat is installed somewhere else, set `CATALINA_HOME` first, e.g. `set CATALINA_HOME=D:\tomcat` (or edit the top of the `.bat` files).

### 8.5 Start Tomcat
* **XAMPP Control Panel:** click **Start** next to **Tomcat**. XAMPP's `catalina_start.bat` finds the JDK through the Windows registry (verified: it detected `C:\Program Files\Java\jdk-24`).
* **Manual alternative:**
  ```bash
  set "JAVA_HOME=C:\Program Files\Java\jdk-24"
  ```
  ```bash
  C:\xampp\tomcat\bin\startup.bat
  ```

### 8.6 Open the portal
**http://localhost:8080/StudentPortal/** → login page → sign in with **admin / Admin@123**.

### 8.7 Using an IDE instead (optional)
In **Eclipse IDE for Enterprise Java**: *File → New → Dynamic Web Project* (Target runtime: Apache Tomcat v8.5, Dynamic web module 3.1), set *Content directory* to `WebContent` and *Source folder* to `src`, copy the files in, then *Run As → Run on Server*.

---

## 9. How Question 1 Works

* **`index.html`** has a semantic HTML5 page (`header`, `nav`, `main`, `section`, `aside`, `footer`). Every input has a `<label for>`, and suitable types are used (`email`, `password`, `file` with `accept`, `select`). `novalidate` turns off the browser's popups so the custom messages are shown instead.
* **`style.css`** uses CSS variables for the colour theme, a Grid layout (form + guidelines panel), a styled dropdown arrow, a dashed file "drop-zone", focus rings, red/green validation states, and **media queries at 850px and 560px** that switch to a single column and full-width buttons for phones.
* **`validation.js`** has one validator function per field (each returns an error message or `""`):

  | Field | Rule |
  |-------|------|
  | Name | required, 3–50 chars, letters/spaces/dots only |
  | Roll number | required, 5–15 letters/digits with at least one digit |
  | Email | required, `name@domain.tld` pattern |
  | Password | ≥ 8 chars, uppercase, lowercase, number, special character |
  | Confirm password | must match password |
  | Course | an option must be selected |
  | File | required, JPG/PNG/PDF only, not empty, ≤ 2 MB |

  Fields are checked on **blur/change** and again on **submit**. On submit the first wrong field receives focus. The success message is written with `textContent`, not `innerHTML`, so typed text can never be treated as HTML.

---

## 10. How Question 2 Works

### CRUD
| Operation | File | SQL (always a prepared statement) |
|-----------|------|-----------------------------------|
| Create | `add_student.php` | `INSERT INTO students …` then `INSERT INTO student_files …` inside **one transaction** |
| Read (list) | `index.php` | `SELECT … LEFT JOIN student_files …` with optional `LIKE ?` search |
| Read (one) | `view_student.php` | `SELECT * FROM students WHERE student_id = ?` + files |
| Update | `edit_student.php` | `UPDATE students SET … WHERE student_id = ?` + `INSERT … ON DUPLICATE KEY UPDATE` for replaced files |
| Delete | `delete_student.php` | `DELETE FROM students WHERE student_id = ?` (file rows removed by `ON DELETE CASCADE`) |

### File upload handling (`functions.php`)
1. **Upload errors:** each PHP `UPLOAD_ERR_*` code gets a readable message. A request larger than `post_max_size` is detected separately.
2. **`is_uploaded_file()`** confirms the file really came from the form.
3. **Size limit:** 2 MB for the profile image, 5 MB for documents.
4. **Real type check:** `finfo` reads the file's *content* (magic bytes); the file name and the browser-supplied type are ignored. Images must also pass `getimagesize()`. Allowed: JPG/PNG for photos, PDF/JPG/PNG for documents.
5. **Unique filename:** `bin2hex(random_bytes(16)) . '.' . ext` (e.g. `9f86d081…e3.png`). The original name is stored in the database for display only, so files can never overwrite each other and names like `../../evil.php` are harmless.
6. **Metadata stored in MySQL:** student relationship (`student_id`), category, original name, stored name, `file_path`, MIME `file_type`, `file_size`, `uploaded_at`.

### Safe update and delete
* **Editing only text** never touches the `student_files` rows, so existing files are kept.
* **Replacing a file:** the new file is saved under a new name → the database is updated → *only after the commit succeeds* is the old file deleted. If anything fails, the transaction is rolled back and the new file is removed, so the old file is never lost.
* **Deleting:** a GET request shows a confirmation page (plus a JavaScript `confirm()` popup). The actual delete is a POST with a CSRF token. After the row is deleted, the physical files are removed from `uploads/`.

---

## 11. How Question 3 Works

### Request flow
```
Browser ──► AuthFilter ──(no session)──► redirect /login?required=1
                │
           (logged in)
                ▼
         StudentServlet ──► StudentDAO ──JDBC──► MySQL
                │                 (PreparedStatement, LEFT JOIN)
                ▼
      request.setAttribute("students", list)
                ▼
         students.jsp  ──  <img src="FileServlet?id=2">  ──► FileServlet ──► uploads folder
```

### Components
| Class / page | Role |
|--------------|------|
| `DBConnection` | The **only** place holding the JDBC URL/user/password. Loads `com.mysql.cj.jdbc.Driver` once and returns connections used with try-with-resources. |
| `StudentDAO` | `findAll()`, `findById()`, `findFileById()`. One `LEFT JOIN` query; a `LinkedHashMap` groups rows into `Student` objects that each hold a list of `StudentFile`s. |
| `UserDAO` + `PasswordUtil` | Loads the user by username (prepared statement) and verifies the password against the PBKDF2 hash with a constant-time comparison. |
| `LoginServlet` (`/login`) | GET shows the form; POST validates input, authenticates, **invalidates any old session and creates a new one** (prevents session fixation), stores `user` in the `HttpSession`, then redirects to `/students`. |
| `LogoutServlet` (`/logout`) | `session.invalidate()` → redirect to `/login?logout=1`. |
| `StudentServlet` (`/students`) | No `id` → all students → `students.jsp`; `?id=N` → `profile.jsp`; bad id → 400, unknown id → 404. |
| `FileServlet` (`/FileServlet?id=N`) | Looks the file up **by numeric ID in the database** and builds the path from the stored random name. It checks the canonical path stays inside the uploads folder, only serves JPEG/PNG/PDF, sets `Content-Type`, `Content-Length`, `Content-Disposition: inline` and `X-Content-Type-Options: nosniff`, then streams the bytes. The browser never sends a filesystem path. |
| `AuthFilter` | Mapped in `web.xml` to `/students`, `/FileServlet`, `/students.jsp`, `/profile.jsp`. With no `user` in the session it redirects to login; otherwise it adds no-cache headers (so the Back button cannot show data after logout) and continues. |
| `students.jsp` | `<c:forEach>` over all students (plus a **nested** `<c:forEach>` over each student's documents). `<c:choose>` shows the photo via `FileServlet`, or **initials as a fallback**. `<c:if>`-style fallbacks show *"No documents uploaded"*. All text goes through `<c:out>` (HTML-escaped). |
| `profile.jsp` | Full profile, a file metadata table, image previews for image documents, a PDF card with an *Open document* link, and *"Not uploaded"* warnings for missing documents. |
| `index.jsp` | Welcome page: logged in → `/students`, otherwise → `/login`. |
| `error.jsp` | Friendly 400/404/500 page (configured with `<error-page>` in `web.xml`). |
| `web.xml` | Servlet and filter mappings, the `uploadDirectory` context parameter, a 30-minute session timeout, HTTP-only session cookie, and error pages. |

### Session behaviour
| Situation | Result |
|-----------|--------|
| Unauthenticated user opens `/StudentPortal/` | → Login page |
| Successful login | → Student portal (`/students`) |
| Protected page/file without a session | → Redirect to `/login?required=1` ("Please log in to access that page.") |
| Logout | Session invalidated → `/login?logout=1` ("You have been logged out successfully.") |
| Old session cookie used after logout | → Redirect to login |

---

## 12. Security Measures

* **SQL injection:** every query in PHP (`mysqli` prepared statements) and Java (`PreparedStatement`) uses placeholders.
* **XSS:** PHP output goes through `htmlspecialchars` (`e()` helper); JSP output goes through `<c:out>`.
* **CSRF:** all PHP POST forms (add/edit/delete) include a session token checked with `hash_equals`.
* **Uploads:** content-based MIME check, size limits, random filenames, and `uploads/.htaccess` blocks script execution and directory listing.
* **Passwords:** salted PBKDF2 (65,536 iterations), never plain text. The same error message is shown for a wrong username and a wrong password.
* **Sessions:** new session ID on login, invalidated on logout, HTTP-only cookie, 30-minute timeout, no-cache headers on protected pages.
* **Error messages:** PHP errors are logged, not displayed (no file paths or stack traces in the browser); Tomcat errors go to a friendly `error.jsp` (catch-all `<error-page>`).
* **Source protection:** `Question3_Servlet/.htaccess` and `Question2_PHP/includes/.htaccess` stop Apache serving Java source, `web.xml` or PHP fragments.

---

## 13. Troubleshooting

| Problem | Fix |
|---------|-----|
| *"Database connection failed"* (PHP) | Start MySQL in XAMPP; import `student_portal.sql`; check `db.php`. |
| Tomcat page shows *"MySQL JDBC driver not found"* / 500 error | `mysql-connector-j-8.4.0.jar` is missing from `WEB-INF\lib`. Add it and run `deploy.bat` again. |
| JSP error *"cannot find the tag library descriptor for http://java.sun.com/jsp/jstl/core"* | The two `taglibs-standard-*.jar` files are missing from `WEB-INF\lib`. |
| `javac` is not recognized | Install a JDK and add `C:\Program Files\Java\jdk-XX\bin` to PATH. |
| Tomcat won't start in XAMPP | Check port 8080 isn't used by another program; or start it manually with `JAVA_HOME` set (Section 8.5). |
| Images show initials in the Servlet portal even though they were uploaded | Check `uploadDirectory` in `web.xml` points to `Question2_PHP/uploads`. |
| `ClassNotFoundException: javax.servlet…` | You are using Tomcat 10+. Use Tomcat 8.5/9. |
| Upload fails with *"larger than the server allows"* | The file exceeds PHP's `upload_max_filesize` (40 MB in XAMPP) or the app's own 2 MB / 5 MB limits. |

---

## 14. Uploading to GitHub

The repository is already prepared: `.gitignore` excludes uploaded student files, compiled `.class` files, third-party JARs, IDE folders, logs and `db.local.php`.

1. Create a new **empty** repository on https://github.com (no README, so there are no conflicts), e.g. `WebTechnology_Assignment2`.
2. Open a terminal in the project folder:
   ```bash
   cd C:\xampp\htdocs\WebTechnology_Assignment2
   ```
3. Initialise Git and make the first commit:
   ```bash
   git init
   ```
   ```bash
   git add .
   ```
   ```bash
   git status
   ```
   Check that no `uploads/*.png`, `.class` or `.jar` files are listed, then:
   ```bash
   git commit -m "Web Technology Assignment II - Student Admission & Profile Management Portal"
   ```
4. Connect to GitHub and push (replace `YOUR-USERNAME`):
   ```bash
   git branch -M main
   ```
   ```bash
   git remote add origin https://github.com/YOUR-USERNAME/WebTechnology_Assignment2.git
   ```
   ```bash
   git push -u origin main
   ```
5. After cloning on another computer, add the JARs listed in `Question3_Servlet/WebContent/WEB-INF/lib/README.txt`.

---

## 15. Assignment Documentation

### Question 1 – HTML, CSS and JavaScript

**Aim:** To design a well-structured and styled student registration form and validate user input on the client side using JavaScript.

**Technologies used:** HTML5, CSS3 (custom, no framework), JavaScript (ES6).

**Main files:** `Question1_Frontend/index.html`, `style.css`, `validation.js`.

**Working principle:** The HTML5 form has labelled fields for name, roll number, email, password, confirm password, course (dropdown) and a file upload, with Submit and Reset buttons. The browser's default validation is disabled with `novalidate`. `validation.js` attaches event listeners that run a validator for each field when the user leaves it, and run all validators again when the form is submitted. Errors appear in a `<span>` beneath each field, and the field's border turns red (or green when valid). The form is accepted only when every validator passes.

**Validation:** Required fields; name pattern (letters/spaces/dots, 3–50 characters); roll-number pattern (5–15 alphanumeric characters with at least one digit); email format; password strength (minimum 8 characters, uppercase, lowercase, digit, special character) with a live checklist; confirm-password match; course selected; file required, JPG/PNG/PDF only, maximum 2 MB.

**Expected output:** A responsive registration page. Invalid input shows clear messages next to the relevant fields; valid input shows a green success message summarising the registration and resets the form. On mobile screens the layout becomes a single column.

### Question 2 – PHP + MySQL CRUD and File Upload

**Aim:** To build a college admin panel that performs Create, Read, Update and Delete operations on student records and manages their uploaded profile images and documents, storing file metadata in MySQL.

**Technologies used:** PHP 8 (mysqli, sessions, fileinfo), MySQL/MariaDB, HTML5, CSS3, JavaScript.

**Main files:** `index.php`, `add_student.php`, `view_student.php`, `edit_student.php`, `delete_student.php`, `db.php`, `functions.php`, `includes/*`, `uploads/`, `database/student_portal.sql`.

**Working principle:** `db.php` opens a single mysqli connection using credentials defined in one place. Forms submit with `multipart/form-data`. The server validates the text fields and every uploaded file before any database change. Inside a transaction the student row is inserted or updated, each file is moved into `uploads/` under a random unique name, and its metadata (student ID, category, original name, stored name, path, MIME type, size, upload date) is written to `student_files`. The list page joins both tables to show thumbnails and document counts. The view page shows the profile image, document previews and a metadata table. Deleting removes the student (the foreign key cascades to the file rows) and then deletes the physical files.

**Validation:** Client side: `script.js` checks the patterns, required fields, file extension and size. Server side: the same patterns; `filter_var` for email; course whitelist; duplicate roll number/email checks; PHP upload error codes; `is_uploaded_file`; size limits; real MIME type via `finfo`; `getimagesize` for images; CSRF token on every POST; all output escaped.

**Expected output:** A table of students with photos; an add/edit form with three upload boxes and inline error messages; a profile page showing the image, document previews and file metadata; a confirmation page before deletion; green/red flash messages after each action.

### Question 3 – Java Servlet, JSP and JDBC

**Aim:** To create a login-protected student portal where authenticated users can view student profiles and their associated images and documents, retrieved from MySQL with JDBC and streamed through a Servlet.

**Technologies used:** Java (JDK 11+), Servlet 3.1, JSP 2.3, JSTL 1.2, JDBC (MySQL Connector/J 8.4.0), Apache Tomcat 8.5, MySQL.

**Main files:** `DBConnection.java`, `StudentDAO.java`, `UserDAO.java`, `PasswordUtil.java`, `LoginServlet.java`, `LogoutServlet.java`, `StudentServlet.java`, `FileServlet.java`, `AuthFilter.java`, `login.jsp`, `students.jsp`, `profile.jsp`, `web.xml`.

**Working principle:** `LoginServlet` checks the username and password against the `users` table (PBKDF2 hash) and stores the user in an `HttpSession`. `AuthFilter` intercepts every protected URL and redirects to the login page if no session user exists. `StudentServlet` uses `StudentDAO` (JDBC prepared statements through the reusable `DBConnection` class) to load students together with their file records, puts them in the request, and forwards to a JSP. The JSPs use `<c:forEach>` to loop over students and documents and `<c:choose>/<c:if>` to show either the real image or fallback content. Images and documents are requested as `FileServlet?id=N`; the Servlet looks the file up by ID, verifies the path and type, and streams the bytes. `LogoutServlet` invalidates the session.

**Validation:** Login fields checked in JavaScript and again in the Servlet; numeric ID parsing with 400/404 responses; only whitelisted MIME types served; canonical-path check prevents directory traversal; all JSP output escaped with `<c:out>`; session ID regenerated at login.

**Expected output:** Opening the portal shows the login page. Wrong credentials show an error. Successful login shows a grid of student cards with photos (or initials) and document links. A profile page shows full details, a metadata table, an image preview and a PDF link. Accessing any protected page without logging in, or after logging out, returns to the login page.

### Conclusion

The Student Admission & Profile Management Portal shows responsive front-end development with HTML5 and CSS3, and client-side validation with JavaScript.
It implements complete PHP/MySQL CRUD operations with secure file uploads, whose type, size and metadata are validated and stored in a relational database.
Using Java Servlets, JSP and JDBC, the same data is presented as dynamic content, with images and documents streamed by a Servlet.
Session-based access control with hashed passwords protects the portal, so validation and security are applied at every layer of the application.

---

## 16. Required Screenshots

Capture these from your own running project (browser zoom 100%, full window). Suggested file names are given in brackets.

> **Before taking screenshots:** in Question 2, **Edit Monika M (24CS122)** and upload your own profile photo, plus your own ID proof/certificate (or another appropriate document) to replace the placeholder test documents. Then her photo appears in Question 2's list and profile, and in Question 3 (streamed by `FileServlet`).
> For the **Add** and **Delete** screenshots use a *temporary* student, never your own record.

**Question 1**
1. **Registration form** – empty form at `Question1_Frontend/index.html` *(q1_01_form.png)*
2. **Validation errors** – after clicking Register with empty/invalid fields; all red messages visible *(q1_02_errors.png)*
3. **Password strength checklist** – partially typed password with some rules green, some red *(q1_03_password.png)*
4. **Successful registration** – filled with *Monika M / 24CS122 / monikam232007@gmail.com / B.Tech CSE*; green success message *(q1_04_success.png)*
5. **Mobile view** – F12 → device toolbar (e.g. iPhone) showing the single-column layout *(q1_05_mobile.png)*

**Question 2**
6. **Student list** – `Question2_PHP/index.php` showing Monika M (with photo), Rahul Verma and Ananya Iyer *(q2_01_list.png)*
7. **Add student form** – a temporary student filled in, with files chosen *(q2_02_add.png)*
8. **Server-side validation** – e.g. adding roll number `24CS122` again (duplicate) or a wrong file type *(q2_03_validation.png)*
9. **View Student page (Monika M)** – profile image, document previews and the file metadata table (path, type, size, upload date) *(q2_04_view.png)*
10. **Edit student (Monika M)** – form showing "Current: …" for existing files *(q2_05_edit.png)*
11. **Delete confirmation** – for the temporary student: confirmation page (and/or the JavaScript popup), then the success message *(q2_06_delete.png)*
12. **Uploads folder** – Windows Explorer showing the random unique filenames in `Question2_PHP\uploads` *(q2_07_uploads_folder.png)*

**Question 3**
13. **Servlet login page** – `http://localhost:8080/StudentPortal/` *(q3_01_login.png)*
14. **Failed login** – "Invalid username or password." *(q3_02_login_error.png)*
15. **Student portal (cards)** – after login: Monika M with photo and document links, Rahul/Ananya with the initials fallback *(q3_03_students.png)*
16. **Student profile page (Monika M)** – click *View Profile*: photo, metadata table, document previews *(q3_04_profile.png)*
17. **Streamed file** – an image or PDF opened in a new tab, with the address bar showing `FileServlet?id=…` *(q3_05_fileservlet.png)*
18. **Session-protected page behaviour** – log out (or use a private window), paste `http://localhost:8080/StudentPortal/students` and capture the login page with "Please log in to access that page." *(q3_06_protected.png)*
19. **Logout** – "You have been logged out successfully." *(q3_07_logout.png)*

**Database / setup**
20. **phpMyAdmin tables** – `student_portal` database showing the `students`, `student_files`, `users` tables *(db_01_tables.png)*
21. **`student_files` data** – Browse tab showing metadata rows (path, type, size, upload date) *(db_02_files.png)*
22. **`users` table** – showing the hashed password (not plain text) *(db_03_users.png)*
23. *(Optional)* **XAMPP Control Panel** with Apache, MySQL and Tomcat running *(setup_01_xampp.png)*

> Use real-looking photos/documents of your own when adding students for the screenshots, not someone else's personal ID documents.

---

## 17. Final Requirement Checklist

**Question 1**
- [x] HTML registration form (name, roll no., email, password, confirm password, course dropdown, file upload, Submit/Reset)
- [x] CSS styling in a separate `style.css` (custom, no Bootstrap)
- [x] Responsive design (media queries at 850px / 560px)
- [x] Required-field validation
- [x] Email validation
- [x] Password validation (strength + confirm match)
- [x] File upload field (type + size validated)

**Question 2**
- [x] Add student
- [x] View students (list + single profile)
- [x] Edit student
- [x] Delete student (with confirmation)
- [x] Profile image upload
- [x] Document upload (ID proof + certificate)
- [x] Metadata stored in MySQL (`student_files`)
- [x] Uploaded files displayed
- [x] File size/type validation
- [x] Filename collision prevention (random unique names)

**Question 3**
- [x] JDBC connection (`DBConnection`)
- [x] Student retrieval Servlet (`StudentServlet` + `StudentDAO`)
- [x] File streaming Servlet (`FileServlet?id=`)
- [x] JSP dynamic content
- [x] Multiple-student looping (`<c:forEach>`)
- [x] Conditional rendering (`<c:choose>`, `<c:if>`, fallbacks)
- [x] Login
- [x] HttpSession
- [x] Protected pages (`AuthFilter`)
- [x] Logout

**Submission**
- [x] SQL schema (`database/student_portal.sql`)
- [x] Inline comments
- [x] README / explanation
- [x] Screenshot checklist
- [x] 3–5 line conclusion
- [x] GitHub-ready structure (`.gitignore`)
