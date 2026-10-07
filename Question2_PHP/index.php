<?php
/* =========================================================
   index.php - READ: list all students (with optional search)
   ========================================================= */
require_once 'db.php';
require_once 'functions.php';

$search = trim($_GET['q'] ?? '');

// One query fetches every student, their profile image (if any) and how
// many documents they have. LEFT JOIN keeps students without files.
$sql = "SELECT s.student_id, s.name, s.roll_number, s.email, s.course,
               img.file_path AS image_path,
               (SELECT COUNT(*) FROM student_files d
                 WHERE d.student_id = s.student_id
                   AND d.file_category <> 'profile_image') AS document_count
        FROM students s
        LEFT JOIN student_files img
               ON img.student_id = s.student_id AND img.file_category = 'profile_image'";

if ($search !== '') {
    // Prepared statement with LIKE: the % wildcards are added to the VALUE,
    // never concatenated into the SQL text.
    $sql .= ' WHERE s.name LIKE ? OR s.roll_number LIKE ? OR s.course LIKE ?';
    $sql .= ' ORDER BY s.student_id DESC';
    $stmt = $conn->prepare($sql);
    $like = '%' . $search . '%';
    $stmt->bind_param('sss', $like, $like, $like);
} else {
    $sql .= ' ORDER BY s.student_id DESC';
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'All Students';
$activePage = 'list';
require 'includes/header.php';
?>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Student Records</h2>
            <p class="card-subtitle"><?= count($students) ?> student(s) found</p>
        </div>
        <a href="add_student.php" class="btn btn-primary">+ Add Student</a>
    </div>

    <!-- Search form (GET so the search appears in the URL) -->
    <form method="get" action="index.php" class="search-form" role="search">
        <label for="q" class="visually-hidden">Search students</label>
        <input type="text" id="q" name="q" value="<?= e($search) ?>" placeholder="Search by name, roll number or course">
        <button type="submit" class="btn btn-secondary">Search</button>
        <?php if ($search !== ''): ?>
            <a href="index.php" class="btn btn-link">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (count($students) === 0): ?>
        <p class="empty-state">No students found. <a href="add_student.php">Add the first student</a>.</p>
    <?php else: ?>
        <!-- table-wrap lets the table scroll sideways on small screens -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>ID</th>
                        <th>Roll No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Documents</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td>
                            <?php if ($s['image_path']): ?>
                                <img src="<?= e($s['image_path']) ?>" alt="Photo of <?= e($s['name']) ?>" class="avatar">
                            <?php else: ?>
                                <span class="avatar avatar-placeholder" aria-label="No photo"><?= e(initials($s['name'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $s['student_id'] ?></td>
                        <td><?= e($s['roll_number']) ?></td>
                        <td><?= e($s['name']) ?></td>
                        <td><?= e($s['email']) ?></td>
                        <td><span class="badge"><?= e($s['course']) ?></span></td>
                        <td><?= (int) $s['document_count'] ?></td>
                        <td class="actions">
                            <a href="view_student.php?id=<?= (int) $s['student_id'] ?>" class="btn btn-small btn-secondary">View</a>
                            <a href="edit_student.php?id=<?= (int) $s['student_id'] ?>" class="btn btn-small btn-primary">Edit</a>
                            <a href="delete_student.php?id=<?= (int) $s['student_id'] ?>" class="btn btn-small btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>
