package com.studentportal.model;

import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;

/**
 * A student (row of the students table) together with his/her uploaded files.
 * This is a plain JavaBean: private fields + getters/setters, so JSP
 * Expression Language can read it, e.g. ${student.name}.
 */
public class Student {

    private int studentId;
    private String name;
    private String rollNumber;
    private String email;
    private String course;
    private Timestamp createdAt;
    private final List<StudentFile> files = new ArrayList<>();

    // ---------- Helper methods used by the JSP pages ----------

    /** Returns the profile image, or null if none was uploaded. */
    public StudentFile getProfileImage() {
        return getFileByCategory("profile_image");
    }

    /** Returns the ID proof document, or null if none was uploaded. */
    public StudentFile getIdProof() {
        return getFileByCategory("id_proof");
    }

    /** Returns the certificate document, or null if none was uploaded. */
    public StudentFile getCertificate() {
        return getFileByCategory("certificate");
    }

    private StudentFile getFileByCategory(String category) {
        for (StudentFile file : files) {
            if (category.equals(file.getCategory())) {
                return file;
            }
        }
        return null;
    }

    /** Returns all files except the profile image (ID proof, certificate). */
    public List<StudentFile> getDocuments() {
        List<StudentFile> documents = new ArrayList<>();
        for (StudentFile file : files) {
            if (!"profile_image".equals(file.getCategory())) {
                documents.add(file);
            }
        }
        return documents;
    }

    /** "Monika M" -> "MM" (shown when there is no profile image). */
    public String getInitials() {
        StringBuilder letters = new StringBuilder();
        if (name != null) {
            for (String part : name.trim().split("\\s+")) {
                if (!part.isEmpty() && letters.length() < 2) {
                    letters.append(Character.toUpperCase(part.charAt(0)));
                }
            }
        }
        return letters.toString();
    }

    public void addFile(StudentFile file) {
        files.add(file);
    }

    // ---------- Getters and setters ----------

    public int getStudentId() { return studentId; }
    public void setStudentId(int studentId) { this.studentId = studentId; }

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getRollNumber() { return rollNumber; }
    public void setRollNumber(String rollNumber) { this.rollNumber = rollNumber; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getCourse() { return course; }
    public void setCourse(String course) { this.course = course; }

    public Timestamp getCreatedAt() { return createdAt; }
    public void setCreatedAt(Timestamp createdAt) { this.createdAt = createdAt; }

    public List<StudentFile> getFiles() { return files; }
}
