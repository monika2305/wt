<?php
/* =========================================================
   student_form.php - the form used by BOTH add_student.php
   and edit_student.php (so the HTML is written only once).

   Variables expected from the including page:
     $data          - field values to show (name, roll_number, email, course)
     $errors        - validation errors ['field' => 'message']
     $existingFiles - files already uploaded (empty array when adding)
     $submitLabel   - text of the submit button
   ========================================================= */
?>
<!--
    enctype="multipart/form-data" is REQUIRED for file uploads.
    novalidate: our own script.js shows messages instead of browser popups.
    data-validate tells script.js to validate this form.
-->
<form method="post" enctype="multipart/form-data" novalidate data-validate>
    <?= csrf_field() ?>

    <div class="form-row">
        <div class="form-group">
            <label for="name">Student Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" maxlength="50" value="<?= e($data['name']) ?>"
                   placeholder="e.g. Monika M"
                   class="<?= isset($errors['name']) ? 'invalid' : '' ?>">
            <span class="error-message" id="nameError"><?= e($errors['name'] ?? '') ?></span>
        </div>

        <div class="form-group">
            <label for="roll_number">Roll Number <span class="required">*</span></label>
            <input type="text" id="roll_number" name="roll_number" maxlength="15" value="<?= e($data['roll_number']) ?>"
                   placeholder="e.g. 24CS122" class="<?= isset($errors['roll_number']) ? 'invalid' : '' ?>">
            <span class="error-message" id="roll_numberError"><?= e($errors['roll_number'] ?? '') ?></span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" maxlength="100" value="<?= e($data['email']) ?>"
                   placeholder="e.g. monikam232007@gmail.com"
                   class="<?= isset($errors['email']) ? 'invalid' : '' ?>">
            <span class="error-message" id="emailError"><?= e($errors['email'] ?? '') ?></span>
        </div>

        <div class="form-group">
            <label for="course">Course <span class="required">*</span></label>
            <select id="course" name="course" class="<?= isset($errors['course']) ? 'invalid' : '' ?>">
                <option value="">-- Select course --</option>
                <?php foreach (COURSES as $course): ?>
                    <option value="<?= e($course) ?>" <?= $data['course'] === $course ? 'selected' : '' ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="error-message" id="courseError"><?= e($errors['course'] ?? '') ?></span>
        </div>
    </div>

    <h3 class="section-title">Uploads</h3>
    <p class="hint">All uploads are optional. Choosing a new file replaces the existing one of the same type;
        leaving a file input empty keeps the current file.</p>

    <!-- One file input per category, generated from FILE_RULES in functions.php -->
    <div class="upload-grid">
        <?php foreach (FILE_RULES as $category => $rules): ?>
            <?php
                $extensions = array_unique(array_values($rules['types']));
                $accept = '.' . implode(',.', $extensions);
            ?>
            <div class="form-group upload-box">
                <label for="<?= e($category) ?>"><?= e($rules['label']) ?></label>

                <?php if (isset($existingFiles[$category])): ?>
                    <p class="current-file">Current: <a href="<?= e($existingFiles[$category]['file_path']) ?>" target="_blank" rel="noopener">
                        <?= e($existingFiles[$category]['original_name']) ?></a></p>
                <?php endif; ?>

                <input type="file" id="<?= e($category) ?>" name="<?= e($category) ?>" accept="<?= e($accept) ?>"
                       data-max-size="<?= (int) $rules['max_size'] ?>" data-extensions="<?= e(implode(',', $extensions)) ?>"
                       class="<?= isset($errors[$category]) ? 'invalid' : '' ?>">
                <small class="hint"><?= e(strtoupper(implode(', ', $extensions))) ?> &middot; max <?= e(format_size($rules['max_size'])) ?></small>
                <span class="error-message" id="<?= e($category) ?>Error"><?= e($errors[$category] ?? '') ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>
