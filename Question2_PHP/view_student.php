<?php
/* =========================================================
   view_student.php - READ: one student's full profile with
   profile image, uploaded documents and their metadata.
   ========================================================= */
require_once 'db.php';
require_once 'functions.php';

$id = get_id_param();
$student = $id ? find_student($conn, $id) : null;

if ($student === null) {
    set_flash('error', 'Student not found.');
    redirect('index.php');
}

$files = get_student_files($conn, $id);
$profileImage = $files['profile_image'] ?? null;

$pageTitle = $student['name'];
require 'includes/header.php';
?>

<div class="profile-layout">

    <!-- Left: photo + basic details -->
    <section class="card profile-card">
        <?php if ($profileImage): ?>
            <img src="<?= e($profileImage['file_path']) ?>" alt="Profile photo of <?= e($student['name']) ?>" class="profile-photo">
        <?php else: ?>
            <!-- Fallback when no profile image has been uploaded -->
            <div class="profile-photo profile-placeholder" aria-label="No profile image"><?= e(initials($student['name'])) ?></div>
            <p class="hint">No profile image uploaded.</p>
        <?php endif; ?>

        <h2 class="profile-name"><?= e($student['name']) ?></h2>
        <span class="badge"><?= e($student['course']) ?></span>

        <dl class="details">
            <dt>Student ID</dt>   <dd><?= (int) $student['student_id'] ?></dd>
            <dt>Roll Number</dt>  <dd><?= e($student['roll_number']) ?></dd>
            <dt>Email</dt>        <dd><a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a></dd>
            <dt>Registered</dt>   <dd><?= e(date('d M Y, h:i A', strtotime($student['created_at']))) ?></dd>
            <dt>Last updated</dt> <dd><?= e(date('d M Y, h:i A', strtotime($student['updated_at']))) ?></dd>
        </dl>

        <div class="form-actions">
            <a href="edit_student.php?id=<?= (int) $id ?>" class="btn btn-primary">Edit</a>
            <a href="delete_student.php?id=<?= (int) $id ?>" class="btn btn-danger">Delete</a>
            <a href="index.php" class="btn btn-secondary">Back</a>
        </div>
    </section>

    <!-- Right: uploaded files with metadata -->
    <section class="card">
        <h2 class="card-title">Uploaded Files</h2>
        <p class="card-subtitle">Metadata stored in the <code>student_files</code> table.</p>

        <?php if (empty($files)): ?>
            <p class="empty-state">No files have been uploaded for this student.
                <a href="edit_student.php?id=<?= (int) $id ?>">Upload files</a>.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File ID</th>
                            <th>Category</th>
                            <th>Original name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded on</th>
                            <th>Open</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($files as $category => $file): ?>
                        <tr>
                            <td><?= (int) $file['file_id'] ?></td>
                            <td><?= e(FILE_RULES[$category]['label']) ?></td>
                            <td class="break"><?= e($file['original_name']) ?></td>
                            <td><?= e($file['file_type']) ?></td>
                            <td><?= e(format_size((int) $file['file_size'])) ?></td>
                            <td><?= e(date('d M Y, h:i A', strtotime($file['uploaded_at']))) ?></td>
                            <td><a href="<?= e($file['file_path']) ?>" target="_blank" rel="noopener" class="btn btn-small btn-secondary">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Preview of image documents (PDFs are opened with the Open button) -->
        <?php foreach (['id_proof', 'certificate'] as $category): ?>
            <?php if (isset($files[$category]) && strpos($files[$category]['file_type'], 'image/') === 0): ?>
                <figure class="doc-preview">
                    <img src="<?= e($files[$category]['file_path']) ?>" alt="<?= e(FILE_RULES[$category]['label']) ?> preview">
                    <figcaption><?= e(FILE_RULES[$category]['label']) ?></figcaption>
                </figure>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Show which document types are still missing -->
        <?php foreach (['id_proof', 'certificate'] as $category): ?>
            <?php if (!isset($files[$category])): ?>
                <p class="missing-doc">&#9888; <?= e(FILE_RULES[$category]['label']) ?> not uploaded.</p>
            <?php endif; ?>
        <?php endforeach; ?>
    </section>
</div>

<?php require 'includes/footer.php'; ?>
