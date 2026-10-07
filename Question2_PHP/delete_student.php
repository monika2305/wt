<?php
/* =========================================================
   delete_student.php - DELETE a student.

   GET  -> shows a confirmation page (nothing is deleted yet)
   POST -> deletes the student row. Because of ON DELETE CASCADE,
           MySQL also deletes the rows in student_files. Then the
           actual files are removed from the uploads folder.
   ========================================================= */
require_once 'db.php';
require_once 'functions.php';

$id = get_id_param();
$student = $id ? find_student($conn, $id) : null;

if ($student === null) {
    set_flash('error', 'Student not found.');
    redirect('index.php');
}

// Read the file list BEFORE deleting: after the DELETE the cascade removes
// these rows, and we still need the stored names to remove the files from disk.
$files = get_student_files($conn, $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only delete when the form came from our own confirmation page
    if (!check_csrf()) {
        set_flash('error', 'Your session expired. Please try deleting again.');
        redirect('delete_student.php?id=' . $id);
    }

    try {
        // Prepared statement: the id is sent separately from the SQL text
        $stmt = $conn->prepare('DELETE FROM students WHERE student_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        // Database rows are gone -> remove the physical files as well
        foreach ($files as $file) {
            delete_stored_file($file['stored_name']);
        }

        set_flash('success', 'Student "' . $student['name'] . '" and ' . count($files) . ' uploaded file(s) were deleted.');
    } catch (mysqli_sql_exception $ex) {
        set_flash('error', 'Could not delete the student: ' . $ex->getMessage());
    }
    redirect('index.php');
}

$pageTitle = 'Delete Student';
require 'includes/header.php';
?>

<section class="card confirm-card">
    <h2 class="card-title danger-text">Delete Student?</h2>
    <p>You are about to permanently delete this student record:</p>

    <dl class="details">
        <dt>Name</dt>        <dd><?= e($student['name']) ?></dd>
        <dt>Roll Number</dt> <dd><?= e($student['roll_number']) ?></dd>
        <dt>Course</dt>      <dd><?= e($student['course']) ?></dd>
        <dt>Files</dt>       <dd><?= count($files) ?> uploaded file(s) will also be deleted</dd>
    </dl>

    <div class="alert alert-error">This action cannot be undone.</div>

    <!-- data-confirm makes script.js show an extra JavaScript confirm() box -->
    <form method="post" data-confirm="Are you sure you want to delete <?= e($student['name']) ?>?">
        <?= csrf_field() ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-danger">Yes, delete permanently</button>
            <a href="view_student.php?id=<?= (int) $id ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?php require 'includes/footer.php'; ?>
