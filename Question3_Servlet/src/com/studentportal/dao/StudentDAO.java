package com.studentportal.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import com.studentportal.model.Student;
import com.studentportal.model.StudentFile;
import com.studentportal.util.DBConnection;

/**
 * DAO = Data Access Object. All SQL for students and their files is here,
 * so the Servlets do not contain any SQL. Every query uses a
 * PreparedStatement (protects against SQL injection).
 */
public class StudentDAO {

    // LEFT JOIN returns every student, even those with no files
    // (the file columns are then NULL).
    private static final String SELECT_STUDENTS_WITH_FILES =
            "SELECT s.student_id, s.name, s.roll_number, s.email, s.course, s.created_at, "
            + "       f.file_id, f.file_category, f.original_name, f.stored_name, "
            + "       f.file_type, f.file_size, f.uploaded_at "
            + "FROM students s "
            + "LEFT JOIN student_files f ON f.student_id = s.student_id ";

    /** Returns all students (newest first), each with its list of files. */
    public List<Student> findAll() throws SQLException {
        String sql = SELECT_STUDENTS_WITH_FILES + "ORDER BY s.student_id DESC, f.file_category";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            return mapStudents(rs);
        }
    }

    /** Returns one student with files, or null if the id does not exist. */
    public Student findById(int studentId) throws SQLException {
        String sql = SELECT_STUDENTS_WITH_FILES + "WHERE s.student_id = ? ORDER BY f.file_category";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, studentId);
            try (ResultSet rs = ps.executeQuery()) {
                List<Student> list = mapStudents(rs);
                return list.isEmpty() ? null : list.get(0);
            }
        }
    }

    /** Returns the metadata of one file (used by FileServlet), or null. */
    public StudentFile findFileById(int fileId) throws SQLException {
        String sql = "SELECT file_id, student_id, file_category, original_name, stored_name, "
                   + "file_type, file_size, uploaded_at FROM student_files WHERE file_id = ?";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, fileId);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    StudentFile file = mapFile(rs);
                    file.setStudentId(rs.getInt("student_id"));
                    return file;
                }
                return null;
            }
        }
    }

    /**
     * The JOIN gives one row PER FILE, so a student with 3 files appears in
     * 3 rows. A LinkedHashMap (keeps insertion order) groups the rows back
     * into one Student object per student_id.
     */
    private List<Student> mapStudents(ResultSet rs) throws SQLException {
        Map<Integer, Student> students = new LinkedHashMap<>();

        while (rs.next()) {
            int id = rs.getInt("student_id");
            Student student = students.get(id);

            if (student == null) {               // first row for this student
                student = new Student();
                student.setStudentId(id);
                student.setName(rs.getString("name"));
                student.setRollNumber(rs.getString("roll_number"));
                student.setEmail(rs.getString("email"));
                student.setCourse(rs.getString("course"));
                student.setCreatedAt(rs.getTimestamp("created_at"));
                students.put(id, student);
            }

            rs.getInt("file_id");
            if (!rs.wasNull()) {                 // NULL file_id = student has no files
                StudentFile file = mapFile(rs);
                file.setStudentId(id);
                student.addFile(file);
            }
        }
        return new ArrayList<>(students.values());
    }

    private StudentFile mapFile(ResultSet rs) throws SQLException {
        StudentFile file = new StudentFile();
        file.setFileId(rs.getInt("file_id"));
        file.setCategory(rs.getString("file_category"));
        file.setOriginalName(rs.getString("original_name"));
        file.setStoredName(rs.getString("stored_name"));
        file.setFileType(rs.getString("file_type"));
        file.setFileSize(rs.getLong("file_size"));
        file.setUploadedAt(rs.getTimestamp("uploaded_at"));
        return file;
    }
}
