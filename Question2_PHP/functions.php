<?php
/* =========================================================
   functions.php - helper functions shared by all PHP pages:
   output escaping, validation, file uploads, flash messages
   and CSRF protection.
   ========================================================= */

// Sessions are used for flash messages and the CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Constants ----------

// Folder where uploaded files are saved (relative to this file)
define('UPLOAD_DIR', __DIR__ . '/uploads/');
// Same folder as a web path, stored in the database (file_path column)
define('UPLOAD_WEB_PATH', 'uploads/');

// Courses allowed in the dropdown (also used to validate on the server)
const COURSES = ['B.Sc Computer Science', 'BCA', 'B.Tech CSE', 'B.Tech IT', 'B.Com', 'BBA', 'MCA'];

// Upload rules for each file category:
//   label      - text shown on the page
//   types      - allowed MIME type => file extension used when saving
//   max_size   - maximum size in bytes
const FILE_RULES = [
    'profile_image' => [
        'label'    => 'Profile Image',
        'types'    => ['image/jpeg' => 'jpg', 'image/png' => 'png'],
        'max_size' => 2 * 1024 * 1024,   // 2 MB
    ],
    'id_proof' => [
        'label'    => 'ID Proof',
        'types'    => ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'],
        'max_size' => 5 * 1024 * 1024,   // 5 MB
    ],
    'certificate' => [
        'label'    => 'Certificate',
        'types'    => ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'],
        'max_size' => 5 * 1024 * 1024,   // 5 MB
    ],
];

// ---------- Output escaping ----------

/** Escape text before printing it into HTML (prevents XSS attacks). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ---------- Flash messages (shown once on the next page) ----------

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ---------- CSRF protection ----------
// A random token is stored in the session and added to every form as a
// hidden field. A POST request is accepted only if the token matches,
// so another website cannot secretly submit our forms.

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function check_csrf(): bool
{
    return isset($_POST['csrf_token']) && hash_equals(csrf_token(), $_POST['csrf_token']);
}

// ---------- Student data validation (server side) ----------

/**
 * Validates the student form fields.
 * Returns an array of errors: ['field' => 'message']. Empty array = valid.
 * $excludeId is used when editing, so the student's own roll number/email
 * do not count as duplicates.
 */
function validate_student(mysqli $conn, array $data, ?int $excludeId = null): array
{
    $errors = [];

    // Name: letters, spaces and dots, 3-50 characters
    if ($data['name'] === '') {
        $errors['name'] = 'Name is required.';
    } elseif (!preg_match('/^[A-Za-z][A-Za-z .]{2,49}$/', $data['name'])) {
        $errors['name'] = 'Name must be 3-50 characters and contain only letters, spaces and dots.';
    }

    // Roll number: 5-15 letters/digits with at least one digit
    if ($data['roll_number'] === '') {
        $errors['roll_number'] = 'Roll number is required.';
    } elseif (!preg_match('/^(?=.*\d)[A-Z0-9]{5,15}$/', $data['roll_number'])) {
        $errors['roll_number'] = 'Roll number must be 5-15 letters/digits and contain at least one digit.';
    }

    // Email: PHP's built-in email filter
    if ($data['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (strlen($data['email']) > 100 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Course must be one of the allowed values (never trust the dropdown blindly)
    if (!in_array($data['course'], COURSES, true)) {
        $errors['course'] = 'Please select a valid course.';
    }

    // Duplicate checks with prepared statements
    if (!isset($errors['roll_number']) && value_exists($conn, 'roll_number', $data['roll_number'], $excludeId)) {
        $errors['roll_number'] = 'This roll number is already registered.';
    }
    if (!isset($errors['email']) && value_exists($conn, 'email', $data['email'], $excludeId)) {
        $errors['email'] = 'This email is already registered.';
    }

    return $errors;
}

/** Returns true if another student already uses this roll number/email. */
function value_exists(mysqli $conn, string $column, string $value, ?int $excludeId): bool
{
    // $column is never user input - only 'roll_number' or 'email' from our own code
    $sql = "SELECT COUNT(*) FROM students WHERE $column = ? AND student_id <> ?";
    $stmt = $conn->prepare($sql);
    $id = $excludeId ?? 0;
    $stmt->bind_param('si', $value, $id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $count > 0;
}

/** Reads and cleans the student fields from $_POST. */
function read_student_post(): array
{
    return [
        'name'        => trim($_POST['name'] ?? ''),
        'roll_number' => strtoupper(trim($_POST['roll_number'] ?? '')),
        'email'       => strtolower(trim($_POST['email'] ?? '')),
        'course'      => trim($_POST['course'] ?? ''),
    ];
}

// ---------- File upload handling ----------

/**
 * Checks one uploaded file from $_FILES.
 * Returns:
 *   null                         - no file was chosen (that is allowed)
 *   ['error' => 'message']       - the file is not acceptable
 *   ['mime' => ..., 'ext' => ...] - the file is valid
 */
function validate_upload(array $file, string $category): ?array
{
    $rules = FILE_RULES[$category];

    // 1. PHP upload error codes
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // optional field left empty
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['error' => $rules['label'] . ' is larger than the server allows.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => $rules['label'] . ' upload failed (error code ' . (int) $file['error'] . '). Please try again.'];
    }

    // 2. Make sure the file really came from an HTTP upload
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'Invalid upload for ' . $rules['label'] . '.'];
    }

    // 3. Size check
    if ($file['size'] <= 0) {
        return ['error' => $rules['label'] . ' is empty.'];
    }
    if ($file['size'] > $rules['max_size']) {
        return ['error' => $rules['label'] . ' must be at most ' . format_size($rules['max_size']) . '.'];
    }

    // 4. Type check using the file CONTENT (finfo), not the name or the
    //    browser-supplied type, which can both be faked.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!array_key_exists($mime, $rules['types'])) {
        $allowed = strtoupper(implode(', ', array_unique(array_values($rules['types']))));
        return ['error' => $rules['label'] . ' must be one of: ' . $allowed . '.'];
    }

    // 5. Images must be real, readable images
    if (strpos($mime, 'image/') === 0 && getimagesize($file['tmp_name']) === false) {
        return ['error' => $rules['label'] . ' is not a valid image.'];
    }

    return ['mime' => $mime, 'ext' => $rules['types'][$mime]];
}

/**
 * Moves a validated upload into the uploads folder with a UNIQUE random
 * name (the original filename is never used on disk, so files cannot
 * overwrite each other and dangerous names like "../x.php" are harmless).
 * Returns the new stored filename.
 */
function store_upload(array $file, string $extension): string
{
    do {
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;  // e.g. 9f86d081...e3.jpg
    } while (file_exists(UPLOAD_DIR . $storedName));                  // practically never repeats

    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $storedName)) {
        throw new RuntimeException('Could not save the uploaded file. Check that the uploads folder is writable.');
    }
    return $storedName;
}

/** Deletes a stored file from the uploads folder (if it exists). */
function delete_stored_file(string $storedName): void
{
    // basename() guarantees we only ever delete inside the uploads folder
    $path = UPLOAD_DIR . basename($storedName);
    if (is_file($path)) {
        unlink($path);
    }
}

/**
 * Validates all file inputs of the add/edit form.
 * Fills $valid with the files to save and returns error messages.
 */
function validate_all_uploads(array &$valid): array
{
    $errors = [];
    $valid = [];
    foreach (array_keys(FILE_RULES) as $category) {
        if (!isset($_FILES[$category])) {
            continue;
        }
        $result = validate_upload($_FILES[$category], $category);
        if ($result === null) {
            continue;                       // nothing chosen for this category
        }
        if (isset($result['error'])) {
            $errors[$category] = $result['error'];
        } else {
            $valid[$category] = $result + ['file' => $_FILES[$category]];
        }
    }
    return $errors;
}

/** Inserts or replaces the metadata row for one file category. */
function save_file_record(mysqli $conn, int $studentId, string $category, array $upload, string $storedName): void
{
    $originalName = mb_substr(basename($upload['file']['name']), 0, 255);  // display only
    $filePath = UPLOAD_WEB_PATH . $storedName;
    $size = (int) $upload['file']['size'];

    // The UNIQUE key (student_id, file_category) means a second upload of the
    // same category UPDATES the existing row instead of adding a new one.
    $sql = 'INSERT INTO student_files
                (student_id, file_category, original_name, stored_name, file_path, file_type, file_size)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                original_name = VALUES(original_name), stored_name = VALUES(stored_name),
                file_path = VALUES(file_path), file_type = VALUES(file_type),
                file_size = VALUES(file_size), uploaded_at = CURRENT_TIMESTAMP';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssssi', $studentId, $category, $originalName, $storedName, $filePath, $upload['mime'], $size);
    $stmt->execute();
    $stmt->close();
}

// ---------- Reading data ----------

/** Fetches one student row, or null if the id does not exist. */
function find_student(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $student ?: null;
}

/** Returns a student's files as ['profile_image' => row, 'id_proof' => row, ...]. */
function get_student_files(mysqli $conn, int $studentId): array
{
    $stmt = $conn->prepare('SELECT * FROM student_files WHERE student_id = ? ORDER BY file_category');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $files = [];
    while ($row = $result->fetch_assoc()) {
        $files[$row['file_category']] = $row;
    }
    $stmt->close();
    return $files;
}

/** Reads the ?id= value from the URL as a positive integer (0 if invalid). */
function get_id_param(): int
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id ?: 0;
}

// ---------- Formatting ----------

/** 1536 -> "1.5 KB" */
function format_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' bytes';
}

/** "Monika M" -> "MM" (used when a student has no profile image) */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $letters .= strtoupper(substr($part, 0, 1));
    }
    return $letters;
}

/** Redirects to another page and stops the script. */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
