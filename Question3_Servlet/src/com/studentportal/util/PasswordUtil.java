package com.studentportal.util;

import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.security.spec.InvalidKeySpecException;
import java.security.MessageDigest;
import java.util.Base64;

import javax.crypto.SecretKeyFactory;
import javax.crypto.spec.PBEKeySpec;

/**
 * Password hashing helper.
 *
 * Passwords are never stored as plain text. Instead we store a
 * PBKDF2-HMAC-SHA256 hash, which is built into Java (no extra library).
 *
 * Stored format:  pbkdf2$<iterations>$<base64 salt>$<base64 hash>
 *
 * - The SALT is random for every password, so two users with the same
 *   password get different hashes.
 * - The many ITERATIONS make brute-force guessing slow.
 *
 * To create a hash for a new user, run (after build.bat):
 *   java -cp WebContent\WEB-INF\classes com.studentportal.util.PasswordUtil MyNewPassword
 * and put the printed value into the users.password_hash column.
 */
public final class PasswordUtil {

    private static final String ALGORITHM = "PBKDF2WithHmacSHA256";
    private static final int ITERATIONS = 65536;
    private static final int SALT_BYTES = 16;
    private static final int HASH_BITS = 256;

    private PasswordUtil() {
        // utility class - no objects needed
    }

    /** Creates a new salted hash for the given password. */
    public static String hashPassword(String password) {
        byte[] salt = new byte[SALT_BYTES];
        new SecureRandom().nextBytes(salt);
        byte[] hash = pbkdf2(password.toCharArray(), salt, ITERATIONS);

        Base64.Encoder encoder = Base64.getEncoder();
        return "pbkdf2$" + ITERATIONS + "$" + encoder.encodeToString(salt) + "$" + encoder.encodeToString(hash);
    }

    /** Checks a typed password against a stored hash. */
    public static boolean verifyPassword(String password, String storedHash) {
        if (password == null || storedHash == null) {
            return false;
        }
        String[] parts = storedHash.split("\\$");
        if (parts.length != 4 || !"pbkdf2".equals(parts[0])) {
            return false; // unknown format
        }
        try {
            int iterations = Integer.parseInt(parts[1]);
            byte[] salt = Base64.getDecoder().decode(parts[2]);
            byte[] expectedHash = Base64.getDecoder().decode(parts[3]);

            // Hash the typed password with the SAME salt and iterations
            byte[] actualHash = pbkdf2(password.toCharArray(), salt, iterations);

            // MessageDigest.isEqual compares in constant time (safer than Arrays.equals)
            return MessageDigest.isEqual(expectedHash, actualHash);
        } catch (IllegalArgumentException e) {
            return false; // bad number or bad Base64 in the stored value
        }
    }

    private static byte[] pbkdf2(char[] password, byte[] salt, int iterations) {
        try {
            PBEKeySpec spec = new PBEKeySpec(password, salt, iterations, HASH_BITS);
            SecretKeyFactory factory = SecretKeyFactory.getInstance(ALGORITHM);
            return factory.generateSecret(spec).getEncoded();
        } catch (NoSuchAlgorithmException | InvalidKeySpecException e) {
            throw new IllegalStateException("Password hashing failed", e);
        }
    }

    /** Command-line helper: prints a hash for the password given as argument. */
    public static void main(String[] args) {
        if (args.length != 1) {
            System.out.println("Usage: java com.studentportal.util.PasswordUtil <password>");
            return;
        }
        System.out.println(hashPassword(args[0]));
    }
}
