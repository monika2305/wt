/* =========================================================
   Student Admission Portal - Question 1
   validation.js : client-side validation of the registration form.

   How it works:
   1. Every field has its own validate function that returns an
      error message ("" means the value is valid).
   2. Errors are shown in the <span class="error-message"> under
      each field, and the field gets a red/green border.
   3. Fields are checked when the user leaves them (blur) and
      all fields are checked again when the form is submitted.
   4. The form is only "submitted" if every field is valid.
   ========================================================= */

// ---------- Regular expressions used for validation ----------
// Name: starts with a letter, then letters, spaces or dots, 3-50 characters in total
const NAME_PATTERN = /^[A-Za-z][A-Za-z .]{2,49}$/;
// Roll number: 5-15 letters/digits and at least one digit (e.g. 24CS122)
const ROLL_PATTERN = /^(?=.*\d)[A-Za-z0-9]{5,15}$/;
// Email: something@something.domain (simple, readable check)
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/;

// File upload rules
const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "pdf"];
const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB in bytes

// ---------- Get references to the form and its fields ----------
const form = document.getElementById("registrationForm");
const successBox = document.getElementById("successBox");

const fields = {
    studentName: document.getElementById("studentName"),
    rollNumber: document.getElementById("rollNumber"),
    email: document.getElementById("email"),
    password: document.getElementById("password"),
    confirmPassword: document.getElementById("confirmPassword"),
    course: document.getElementById("course"),
    document: document.getElementById("document")
};

// ---------- Password rule checks (also used for the live checklist) ----------
function getPasswordRules(password) {
    return {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)   // anything that is not a letter or digit
    };
}

// ---------- One validation function per field ----------
// Each function returns "" when valid, otherwise an error message.
const validators = {
    studentName: function (value) {
        value = value.trim();
        if (value === "") return "Student name is required.";
        if (value.length < 3) return "Name must be at least 3 characters long.";
        if (!NAME_PATTERN.test(value)) return "Name can contain only letters, spaces and dots.";
        return "";
    },

    rollNumber: function (value) {
        value = value.trim();
        if (value === "") return "Roll number is required.";
        if (!ROLL_PATTERN.test(value)) {
            return "Roll number must be 5-15 letters/digits and contain at least one digit.";
        }
        return "";
    },

    email: function (value) {
        value = value.trim();
        if (value === "") return "Email address is required.";
        if (!EMAIL_PATTERN.test(value)) return "Please enter a valid email address (e.g. name@example.com).";
        return "";
    },

    password: function (value) {
        if (value === "") return "Password is required.";
        const rules = getPasswordRules(value);
        if (!rules.length) return "Password must be at least 8 characters long.";
        if (!rules.upper) return "Password must contain at least one uppercase letter.";
        if (!rules.lower) return "Password must contain at least one lowercase letter.";
        if (!rules.number) return "Password must contain at least one number.";
        if (!rules.special) return "Password must contain at least one special character.";
        return "";
    },

    confirmPassword: function (value) {
        if (value === "") return "Please confirm your password.";
        if (value !== fields.password.value) return "Passwords do not match.";
        return "";
    },

    course: function (value) {
        if (value === "") return "Please select a course.";
        return "";
    },

    // For the file input we check the selected File object, not the text value
    document: function () {
        const fileList = fields.document.files;
        if (fileList.length === 0) return "Please choose a file to upload.";

        const file = fileList[0];
        const extension = file.name.split(".").pop().toLowerCase();

        if (!ALLOWED_EXTENSIONS.includes(extension)) {
            return "Invalid file type. Only JPG, PNG and PDF files are allowed.";
        }
        if (file.size === 0) return "The selected file is empty.";
        if (file.size > MAX_FILE_SIZE) {
            // Round UP so a file just over the limit never shows as "2.00 MB"
            const sizeMb = (Math.ceil(file.size / (1024 * 1024) * 100) / 100).toFixed(2);
            return "File is too large (" + sizeMb + " MB). Maximum allowed size is 2 MB.";
        }
        return "";
    }
};

// ---------- Show / clear an error for one field ----------
function showResult(fieldName, message) {
    const input = fields[fieldName];
    const errorSpan = document.getElementById(fieldName + "Error");

    errorSpan.textContent = message;

    if (message) {
        input.classList.add("invalid");
        input.classList.remove("valid");
        input.setAttribute("aria-invalid", "true");
    } else {
        input.classList.remove("invalid");
        input.classList.add("valid");
        input.removeAttribute("aria-invalid");
    }
}

// Validate a single field and display the result. Returns true if valid.
function validateField(fieldName) {
    const message = validators[fieldName](fields[fieldName].value);
    showResult(fieldName, message);
    return message === "";
}

// ---------- Live password checklist ----------
function updatePasswordChecklist() {
    const rules = getPasswordRules(fields.password.value);
    document.querySelectorAll("#passwordRules li").forEach(function (item) {
        // data-rule="length" -> rules.length, etc.
        item.classList.toggle("met", rules[item.dataset.rule]);
    });
}

// ---------- Event listeners ----------

// Validate each field when the user leaves it (blur) or changes it
Object.keys(fields).forEach(function (fieldName) {
    const input = fields[fieldName];
    const eventName = (input.tagName === "SELECT" || input.type === "file") ? "change" : "blur";

    input.addEventListener(eventName, function () {
        validateField(fieldName);
    });
});

// Update the password checklist on every key press
fields.password.addEventListener("input", function () {
    updatePasswordChecklist();
    // If confirm password was already typed, re-check that it still matches
    if (fields.confirmPassword.value !== "") {
        validateField("confirmPassword");
    }
});

// Form submission
form.addEventListener("submit", function (event) {
    // Stop the browser from submitting until we have validated everything.
    // (This is a front-end only demo, so there is no server to submit to.)
    event.preventDefault();
    successBox.hidden = true;

    let allValid = true;
    let firstInvalidField = null;

    Object.keys(fields).forEach(function (fieldName) {
        const valid = validateField(fieldName);
        if (!valid) {
            allValid = false;
            if (firstInvalidField === null) firstInvalidField = fields[fieldName];
        }
    });

    if (!allValid) {
        // Move the cursor to the first wrong field so the user can fix it
        firstInvalidField.focus();
        return;
    }

    // All fields are valid -> show a success message with a summary
    const name = fields.studentName.value.trim();
    const roll = fields.rollNumber.value.trim().toUpperCase();
    const course = fields.course.value;
    const fileName = fields.document.files[0].name;

    // textContent is used (not innerHTML) so user input can never inject HTML
    successBox.textContent = "Registration successful! Welcome, " + name +
        " (Roll No: " + roll + "), Course: " + course +
        ". Uploaded file: " + fileName + ".";
    successBox.hidden = false;

    form.reset();
    clearAllMessages();
    window.scrollTo({ top: 0 });
});

// Reset button: also remove all error messages and colours
form.addEventListener("reset", function () {
    // setTimeout lets the browser finish clearing the fields first
    setTimeout(function () {
        clearAllMessages();
    }, 0);
});

function clearAllMessages() {
    Object.keys(fields).forEach(function (fieldName) {
        fields[fieldName].classList.remove("invalid", "valid");
        fields[fieldName].removeAttribute("aria-invalid");
        document.getElementById(fieldName + "Error").textContent = "";
    });
    updatePasswordChecklist();
}
