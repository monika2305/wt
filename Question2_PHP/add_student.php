<?php
/* =========================================================
   add_student.php - CREATE: add a new student with optional
   profile image, ID proof and certificate uploads.
   ========================================================= */
require_once 'db.php';
require_once 'functions.php';

$data = ['name' => '', 'roll_number' => '', 'email' => '', 'course' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If the whole request was bigger than post_max_size, PHP empties $_POST and $_FILES
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors['form'] = 'The uploaded files are too large. Please choose smaller files.';
    } elseif (!check_csrf()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    } else {
        // 1. Validate text fields and files BEFORE touching the database
        $data = read_student_post();
        $errors = validate_student($conn, $data);
        $validUploads = [];
        $errors += validate_all_uploads($validUploads);

        if (empty($errors)) {
            $savedFiles = []; // remember stored files so we can clean up on failure

            // 2. A transaction makes "insert student + insert files" all-or-nothing
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare('INSERT INTO students (name, roll_number, email, course) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $data['name'], $data['roll_number'], $data['email'], $data['course']);
                $stmt->execute();
                $studentId = $conn->insert_id;   // new AUTO_INCREMENT id
                $stmt->close();

                // 3. Save each uploaded file with a unique name + store its metadata
                foreach ($validUploads as $category => $upload) {
                    $storedName = store_upload($upload['file'], $upload['ext']);
                    $savedFiles[] = $storedName;
                    save_file_record($conn, $studentId, $category, $upload, $storedName);
                }

                $conn->commit();
                set_flash('success', 'Student "' . $data['name'] . '" was added successfully.');
                redirect('view_student.php?id=' . $studentId);

            } catch (Exception $ex) {
                // Undo database changes and delete any files already moved
                $conn->rollback();
                foreach ($savedFiles as $storedName) {
                    delete_stored_file($storedName);
                }
                $errors['form'] = 'Could not save the student: ' . $ex->getMessage();
            }
        }
    }
}

$pageTitle = 'Add Student';
$activePage = 'add';
require 'includes/header.php';
?>

<section class="card form-card-wide">
    <h2 class="card-title">Add New Student</h2>
    <p class="card-subtitle">Fields marked <span class="required">*</span> are mandatory.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <?= e($errors['form'] ?? 'Please correct the highlighted fields below.') ?>
        </div>
    <?php endif; ?>

    <?php
        $existingFiles = [];
        $submitLabel = 'Add Student';
        require 'includes/student_form.php';
    ?>
</section>

<?php require 'includes/footer.php'; ?>
