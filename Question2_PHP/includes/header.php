<?php
/* =========================================================
   header.php - top part of every page (HTML head, header, nav,
   flash message). Set $pageTitle and $activePage before including.
   ========================================================= */
$pageTitle = $pageTitle ?? 'Student Admin Panel';
$activePage = $activePage ?? '';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Student Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <div class="brand">
            <span class="brand-logo" aria-hidden="true">SP</span>
            <div>
                <h1 class="brand-title">Student Admin Panel</h1>
                <p class="brand-subtitle">Admission &amp; Profile Management</p>
            </div>
        </div>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="index.php" class="<?= $activePage === 'list' ? 'active' : '' ?>">All Students</a>
            <a href="add_student.php" class="<?= $activePage === 'add' ? 'active' : '' ?>">Add Student</a>
        </nav>
    </div>
</header>

<main class="container">
<?php if ($flash): ?>
    <!-- Flash message from the previous action (add/edit/delete) -->
    <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endif; ?>
