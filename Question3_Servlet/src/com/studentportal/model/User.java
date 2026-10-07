package com.studentportal.model;

import java.io.Serializable;

/**
 * A logged-in portal user (row of the users table).
 * Stored in the HttpSession after a successful login.
 * The password hash is deliberately NOT kept in this object.
 * Serializable because Tomcat may save sessions to disk on restart.
 */
public class User implements Serializable {

    private static final long serialVersionUID = 1L;

    private int userId;
    private String username;
    private String fullName;
    private String role;

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }

    public String getFullName() { return fullName; }
    public void setFullName(String fullName) { this.fullName = fullName; }

    public String getRole() { return role; }
    public void setRole(String role) { this.role = role; }
}
