package com.studentportal.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import com.studentportal.model.User;
import com.studentportal.util.DBConnection;
import com.studentportal.util.PasswordUtil;

/**
 * Data access for the users table (login accounts).
 */
public class UserDAO {

    /**
     * Checks a username/password pair.
     * Returns the User if the login is correct, otherwise null.
     */
    public User authenticate(String username, String password) throws SQLException {
        String sql = "SELECT user_id, username, password_hash, full_name, role FROM users WHERE username = ?";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setString(1, username);

            try (ResultSet rs = ps.executeQuery()) {
                if (!rs.next()) {
                    return null;    // no such username
                }
                // Compare the typed password with the stored PBKDF2 hash
                if (!PasswordUtil.verifyPassword(password, rs.getString("password_hash"))) {
                    return null;    // wrong password
                }
                User user = new User();
                user.setUserId(rs.getInt("user_id"));
                user.setUsername(rs.getString("username"));
                user.setFullName(rs.getString("full_name"));
                user.setRole(rs.getString("role"));
                return user;
            }
        }
    }
}
