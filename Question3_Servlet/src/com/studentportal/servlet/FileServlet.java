package com.studentportal.servlet;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.nio.file.Files;
import java.sql.SQLException;
import java.util.Arrays;
import java.util.List;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.studentportal.dao.StudentDAO;
import com.studentportal.model.StudentFile;

/**
 * /FileServlet?id=FILE_ID - streams an uploaded image or document.
 *
 * Security:
 *  - The browser only sends a numeric FILE ID, never a path.
 *  - The stored filename comes from the database, and the final path is
 *    checked to be inside the uploads folder (blocks "../" tricks).
 *  - Only whitelisted MIME types are served.
 *  - Protected by AuthFilter, so only logged-in users can download files.
 */
public class FileServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;
    private static final List<String> ALLOWED_TYPES = Arrays.asList("image/jpeg", "image/png", "application/pdf");

    private final StudentDAO studentDAO = new StudentDAO();
    private File uploadDir;

    @Override
    public void init() throws ServletException {
        // The folder is configured in web.xml (<context-param> uploadDirectory)
        String path = getServletContext().getInitParameter("uploadDirectory");
        if (path == null || path.trim().isEmpty()) {
            throw new ServletException("uploadDirectory is not configured in web.xml");
        }
        uploadDir = new File(path.trim());
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        // 1. Read and validate the id
        int fileId;
        try {
            fileId = Integer.parseInt(request.getParameter("id"));
        } catch (NumberFormatException e) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid file id");
            return;
        }

        // 2. Look up the file metadata in MySQL
        StudentFile meta;
        try {
            meta = studentDAO.findFileById(fileId);
        } catch (SQLException e) {
            throw new ServletException("Database error: " + e.getMessage(), e);
        }
        if (meta == null || !ALLOWED_TYPES.contains(meta.getFileType())) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found");
            return;
        }

        // 3. Build the path and make sure it stays inside the uploads folder
        File file = new File(uploadDir, meta.getStoredName());
        String basePath = uploadDir.getCanonicalPath() + File.separator;
        if (!file.getCanonicalPath().startsWith(basePath) || !file.isFile()) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found on server");
            return;
        }

        // 4. Response headers
        response.setContentType(meta.getFileType());
        response.setContentLengthLong(file.length());
        response.setHeader("X-Content-Type-Options", "nosniff");
        // "inline" = show in the browser (image/PDF viewer); quotes removed from the name for safety
        String safeName = meta.getOriginalName().replaceAll("[\"\\\\\\r\\n]", "_");
        response.setHeader("Content-Disposition", "inline; filename=\"" + safeName + "\"");

        // 5. Copy the file bytes to the response
        try (InputStream in = Files.newInputStream(file.toPath());
             OutputStream out = response.getOutputStream()) {
            byte[] buffer = new byte[8192];
            int bytesRead;
            while ((bytesRead = in.read(buffer)) != -1) {
                out.write(buffer, 0, bytesRead);
            }
        }
    }
}
