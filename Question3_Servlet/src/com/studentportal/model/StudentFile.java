package com.studentportal.model;

import java.sql.Timestamp;

/**
 * One uploaded file (row of the student_files table).
 * The files themselves are uploaded through the Question 2 PHP panel.
 */
public class StudentFile {

    private int fileId;
    private int studentId;
    private String category;      // profile_image, id_proof or certificate
    private String originalName;  // name on the user's computer (display only)
    private String storedName;    // random unique name inside the uploads folder
    private String fileType;      // MIME type, e.g. image/png
    private long fileSize;        // bytes
    private Timestamp uploadedAt;

    // ---------- Helper methods used by the JSP pages ----------

    /** true for image/jpeg, image/png ... (used to decide between <img> and a link) */
    public boolean isImage() {
        return fileType != null && fileType.startsWith("image/");
    }

    /** Human-readable category name. */
    public String getCategoryLabel() {
        if ("profile_image".equals(category)) {
            return "Profile Image";
        } else if ("id_proof".equals(category)) {
            return "ID Proof";
        } else if ("certificate".equals(category)) {
            return "Certificate";
        }
        return category;
    }

    /** 1536 -> "1.5 KB" */
    public String getFormattedSize() {
        if (fileSize >= 1024 * 1024) {
            return String.format("%.2f MB", fileSize / (1024.0 * 1024.0));
        } else if (fileSize >= 1024) {
            return String.format("%.1f KB", fileSize / 1024.0);
        }
        return fileSize + " bytes";
    }

    // ---------- Getters and setters ----------

    public int getFileId() { return fileId; }
    public void setFileId(int fileId) { this.fileId = fileId; }

    public int getStudentId() { return studentId; }
    public void setStudentId(int studentId) { this.studentId = studentId; }

    public String getCategory() { return category; }
    public void setCategory(String category) { this.category = category; }

    public String getOriginalName() { return originalName; }
    public void setOriginalName(String originalName) { this.originalName = originalName; }

    public String getStoredName() { return storedName; }
    public void setStoredName(String storedName) { this.storedName = storedName; }

    public String getFileType() { return fileType; }
    public void setFileType(String fileType) { this.fileType = fileType; }

    public long getFileSize() { return fileSize; }
    public void setFileSize(long fileSize) { this.fileSize = fileSize; }

    public Timestamp getUploadedAt() { return uploadedAt; }
    public void setUploadedAt(Timestamp uploadedAt) { this.uploadedAt = uploadedAt; }
}
