package com.studentportal.servlet;

import java.io.IOException;
import java.sql.SQLException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import com.studentportal.dao.StudentDAO;
import com.studentportal.model.Student;

/**
 * /students        -> all students   -> students.jsp
 * /students?id=5   -> one student    -> profile.jsp
 * Protected by AuthFilter (only logged-in users get here).
 */
public class StudentServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;
    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        String idParam = request.getParameter("id");

        try {
            if (idParam == null) {
                // ---------- List of all students ----------
                request.setAttribute("students", studentDAO.findAll());
                request.getRequestDispatcher("/students.jsp").forward(request, response);
                return;
            }

            // ---------- Single student profile ----------
            int id;
            try {
                id = Integer.parseInt(idParam);
            } catch (NumberFormatException e) {
                response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid student id");
                return;
            }

            Student student = studentDAO.findById(id);
            if (student == null) {
                response.sendError(HttpServletResponse.SC_NOT_FOUND, "Student not found");
                return;
            }
            request.setAttribute("student", student);
            request.getRequestDispatcher("/profile.jsp").forward(request, response);

        } catch (SQLException e) {
            log("Database error while loading students", e);
            throw new ServletException("Could not load students from the database: " + e.getMessage(), e);
        }
    }
}
