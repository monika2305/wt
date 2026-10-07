package com.studentportal.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Reusable JDBC connection helper.
 *
 * Every DAO class calls DBConnection.getConnection() instead of repeating
 * the URL, username and password. Change the three constants below if your
 * MySQL settings differ from a default XAMPP installation.
 *
 * Requires MySQL Connector/J (mysql-connector-j-8.x.jar) in WebContent/WEB-INF/lib.
 */
public final class DBConnection {

    // ---------- Database settings (the ONLY place they are written) ----------
    private static final String URL =
            "jdbc:mysql://localhost:3306/student_portal"
            + "?sslMode=DISABLED"              // local XAMPP MySQL has no SSL certificate
            + "&allowPublicKeyRetrieval=true"
            + "&characterEncoding=UTF-8";
    private static final String USER = "root";
    private static final String PASSWORD = "";

    private static final String DRIVER_CLASS = "com.mysql.cj.jdbc.Driver";

    static {
        // Load the driver class once. Inside Tomcat this explicit call is needed
        // because the driver jar lives in the web application's own WEB-INF/lib.
        try {
            Class.forName(DRIVER_CLASS);
        } catch (ClassNotFoundException e) {
            throw new IllegalStateException(
                    "MySQL JDBC driver not found. Copy mysql-connector-j-8.x.jar into WebContent/WEB-INF/lib.", e);
        }
    }

    private DBConnection() {
        // no objects needed
    }

    /**
     * Opens a new connection. Always use it inside try-with-resources so it
     * is closed automatically:
     *     try (Connection con = DBConnection.getConnection()) { ... }
     */
    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }
}
