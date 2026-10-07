package com.studentportal.servlet;

import java.io.IOException;
import java.sql.SQLException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

import com.studentportal.dao.UserDAO;
import com.studentportal.model.User;

/**
 * /login
 *   GET  -> show login.jsp (or go straight to the portal if already logged in)
 *   POST -> check username/password, create the HttpSession on success
 */
public class LoginServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;
    private final UserDAO userDAO = new UserDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        HttpSession session = request.getSession(false);   // false = do not create a new one
        if (session != null && session.getAttribute("user") != null) {
            response.sendRedirect(request.getContextPath() + "/students");
            return;
        }
        request.getRequestDispatcher("/login.jsp").forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        String username = request.getParameter("username");
        String password = request.getParameter("password");

        // ---------- Server-side validation ----------
        if (username == null || username.trim().isEmpty() || password == null || password.isEmpty()) {
            showError(request, response, "Please enter both username and password.", username);
            return;
        }
        username = username.trim();

        try {
            User user = userDAO.authenticate(username, password);
            if (user == null) {
                // Same message for wrong username and wrong password (does not reveal which one)
                showError(request, response, "Invalid username or password.", username);
                return;
            }

            // Prevent "session fixation": throw away any old session, then create a new one
            HttpSession oldSession = request.getSession(false);
            if (oldSession != null) {
                oldSession.invalidate();
            }
            HttpSession session = request.getSession(true);
            session.setAttribute("user", user);

            // Redirect (not forward) so refreshing the page does not re-submit the password
            response.sendRedirect(request.getContextPath() + "/students");

        } catch (SQLException e) {
            log("Login database error", e);
            showError(request, response, "Database error: " + e.getMessage(), username);
        }
    }

    private void showError(HttpServletRequest request, HttpServletResponse response,
                           String message, String username) throws ServletException, IOException {
        request.setAttribute("error", message);
        request.setAttribute("username", username);   // keep the typed username in the form
        request.getRequestDispatcher("/login.jsp").forward(request, response);
    }
}
