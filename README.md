# Student Admission & Profile Management Portal

**Student details:**
- Name: Monika M
- Roll Number: 24CS122
- Course: B.Tech CSE

## About the Project

This project implements the three parts of Web Technology Assignment II as one student portal. Question 1 is a student registration form with JavaScript validation. Question 2 is a PHP admin panel for adding, viewing, editing and deleting student records with photo and document uploads. Question 3 is a Java Servlet/JSP portal where logged-in users view student profiles. Questions 2 and 3 share the same MySQL database and the same uploaded files.

## Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL / MariaDB
- Java Servlet
- JSP (with JSTL)
- JDBC
- Apache Tomcat

## Project Structure

```
WebTechnology_Assignment2/
├── Question1_Frontend/   Registration form (index.html, style.css, validation.js)
├── Question2_PHP/        PHP admin panel (CRUD pages, db.php, functions.php, uploads/)
├── Question3_Servlet/    Servlet/JSP portal (src/, WebContent/, build.bat, deploy.bat)
└── database/             student_portal.sql
```

## Question 1 – Student Registration Form

An HTML5 registration form with labelled fields for name, roll number, email, password, confirm password, course (dropdown) and a file upload, plus Submit and Reset buttons. A separate `style.css` gives it a clean, responsive layout that switches to a single column on mobile phones. `validation.js` checks every field: required fields, name and roll-number format, a valid email address, password strength (8+ characters with uppercase, lowercase, number and special character, shown as a live checklist), matching confirm password, a selected course, and the file type (JPG/PNG/PDF) and size (max 2 MB). Error messages appear next to each field.

## Question 2 – PHP Student Admin Panel

A PHP + MySQL admin panel that performs full CRUD on student records:
- **Add** a student with an optional profile image, ID proof and certificate.
- **View** all students in a searchable table, or one student's full profile.
- **Edit** student details. Existing files are kept unless a new file is chosen to replace them.
- **Delete** a student after a confirmation page. The related file records and files are removed too.

Uploaded files are checked for upload errors, size and real file type, and saved with a random unique name so files never overwrite each other. File details (student, file path, type, size and upload date) are stored in MySQL. All database queries use prepared statements, input is validated in both JavaScript and PHP, and output is escaped.

## Question 3 – Servlet Student Profile Portal

A Java web application that runs on Apache Tomcat:
- **LoginServlet** checks the username and hashed password against the `users` table and stores the user in an **HttpSession**.
- **AuthFilter** protects the portal pages: without a login, the user is redirected to the login page.
- **StudentServlet** uses **JDBC** (through `DBConnection` and `StudentDAO`) to load students and their file details from MySQL.
- **students.jsp** loops over all students and shows them as cards; **profile.jsp** shows one student's details and documents. Both show fallback content when a photo or document is missing.
- **FileServlet** (`FileServlet?id=…`) streams images and documents by their database ID, so no file path is ever exposed.
- **LogoutServlet** invalidates the session.

## Database

The schema is in `database/student_portal.sql` (database name: `student_portal`).

| Table | Purpose |
|-------|---------|
| `students` | Student details: ID, name, roll number, email, course |
| `student_files` | Uploaded file details: student ID (foreign key), category, file path, type, size, upload date |
| `users` | Login accounts for Question 3, with hashed passwords |

Deleting a student automatically deletes their file records (`ON DELETE CASCADE`). The SQL file also creates the demo login and three sample students: Monika M, Rahul Verma and Ananya Iyer.

## How to Run

**Requirements:** XAMPP (Apache, PHP, MySQL), JDK 11 or newer, and the Tomcat 8.5 that comes with XAMPP.

**Database setup:** start Apache and MySQL in the XAMPP Control Panel, open http://localhost/phpmyadmin, go to the **Import** tab, choose `database/student_portal.sql` and click **Import**. The database is created automatically.

> Importing the SQL file again resets all student records.

The project folder must be at `C:\xampp\htdocs\WebTechnology_Assignment2`.

### Question 1

No server is needed. You can open `index.html` directly, or with Apache running:

http://localhost/WebTechnology_Assignment2/Question1_Frontend/index.html

### Question 2

Apache and MySQL must be running in XAMPP. Database settings are in `Question2_PHP/db.php` (default XAMPP user `root` with no password).

http://localhost/WebTechnology_Assignment2/Question2_PHP/

### Question 3

MySQL and Tomcat must be running, and the Servlet project must be deployed.

1. Put these JAR files in `Question3_Servlet/WebContent/WEB-INF/lib/` (they are not stored on GitHub):
   - `mysql-connector-j-8.4.0.jar`, the MySQL JDBC driver ([download](https://repo1.maven.org/maven2/com/mysql/mysql-connector-j/8.4.0/mysql-connector-j-8.4.0.jar))
   - `taglibs-standard-impl-1.2.5.jar` and `taglibs-standard-spec-1.2.5.jar` (JSTL), copied from `C:\xampp\tomcat\webapps\examples\WEB-INF\lib\`
2. Check the settings if your setup is different: database login in `src/com/studentportal/util/DBConnection.java`, uploads folder in `WebContent/WEB-INF/web.xml` (`uploadDirectory`).
3. Run `Question3_Servlet\deploy.bat`. It compiles the Java code (`build.bat`) and copies the app to Tomcat's `webapps\StudentPortal` folder.
4. Start Tomcat from the XAMPP Control Panel and open:

http://localhost:8080/StudentPortal/

> The project uses `javax.servlet` (Tomcat 8.5/9). Tomcat 10 or newer will not work without changes.

## Demo Login

These are **local demo credentials** for Question 3 only:

- Username: `admin`
- Password: `Admin@123`

The password is stored in the database as a salted hash, not as plain text.

## Main Features

- Responsive student registration form
- JavaScript validation
- PHP/MySQL CRUD
- Secure file uploads
- File metadata storage
- Servlet/JDBC student retrieval
- Dynamic JSP profiles
- Session-based login protection

## Conclusion

Through this project I learned how to build a responsive front end with HTML5, CSS3 and JavaScript validation.
I implemented full CRUD operations and secure file uploads using PHP and a relational MySQL database.
I used Java Servlets, JSP and JDBC to display the same data dynamically and to stream files securely.
I also learned how login sessions protect pages, which shows how the different web technologies work together in one application.

## GitHub

Repository: https://github.com/monika2305/wt
