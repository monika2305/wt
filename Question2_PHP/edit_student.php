<?php
/* =========================================================
   edit_student.php - UPDATE: edit student details and
   optionally replace uploaded files.

   Safe replacement order:
     1. validate the new file
     2. save it under a NEW unique name
     3. update the database (transaction)
     4. only after a successful commit, delete the OLD file
   If anything fails, the old file stays untouched.
   ========================================================= */
require_once 'db.php';
require_once 'functions.php';

$id = get_id_param();
$student = $id ? find_student($conn, $id) : null;

if ($student === null) {
    set_flash('error', 'Student not found.');
    redirect('index.php');
}

$existingFiles = get_student_files($conn, $id);
$data = $student;   // pre-fill the form with current values
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors['form'] = 'The uploaded files are too large. Please choose smaller files.';
    } elseif (!check_csrf()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    } else {
        $data = read_student_post();
        // Pass $id so the student's own roll number/email are not reported as duplicates
        $errors = validate_student($conn, $data, $id);
        $validUploads = [];
        $errors += validate_all_uploads($validUploads);

        if (empty($errors)) {
            $newFiles = [];        // files saved during this request
            $filesToDelete = [];   // old files to remove after commit

            $conn->begin_transaction();
            try {
                // Update the normal student fields. File rows are NOT touched here,
                // so editing only the text never removes existing uploads.
                $stmt = $conn->prepare('UPDATE students SET name = ?, roll_number = ?, email = ?, course = ? WHERE student_id = ?');
                $stmt->bind_param('ssssi', $data['name'], $data['roll_number'], $data['email'], $data['course'], $id);
                $stmt->execute();
                $stmt->close();

                // Only categories where a NEW file was chosen are replaced
                foreach ($validUploads as $category => $upload) {
                    $storedName = store_upload($upload['file'], $upload['ext']);
                    $newFiles[] = $storedName;
                    save_file_record($conn, $id, $category, $upload, $storedName);

                    if (isset($existingFiles[$category])) {
                        $filesToDelete[] = $existingFiles[$category]['stored_name'];
                    }
                }

                $conn->commit();

                // The database now points to the new files -> old ones can go
                foreach ($filesToDelete as $oldName) {
                    delete_stored_file($oldName);
                }

                set_flash('success', 'Student "' . $data['name'] . '" was updated successfully.');
                redirect('view_student.php?id=' . $id);

            } catch (Exception $ex) {
                $conn->rollback();
                foreach ($newFiles as $storedName) {
                    delete_stored_file($storedName);   // old files are still intact
                }
                $errors['form'] = 'Could not update the student: ' . $ex->getMessage();
            }
        }
    }
}

$pageTitle = 'Edit Student';
require 'includes/header.php';
?>

<section class="card form-card-wide">
    <h2 class="card-title">Edit Student #<?= (int) $id ?></h2>
    <p class="card-subtitle">Update the details below. Existing files are kept unless you choose a replacement.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <?= e($errors['form'] ?? 'Please correct the highlighted fields below.') ?>
        </div>
    <?php endif; ?>

    <?php
        $submitLabel = 'Save Changes';
        require 'includes/student_form.php';
    ?>
</section>

<?php require 'includes/footer.php'; ?>
