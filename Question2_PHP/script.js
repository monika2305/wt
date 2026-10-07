/* =========================================================
   script.js - Question 2 client-side checks.
   The SAME rules are checked again in PHP (functions.php),
   because client-side validation can be bypassed.
   ========================================================= */

const RULES = {
    name: [/^[A-Za-z][A-Za-z .]{2,49}$/, "Name must be 3-50 characters and contain only letters, spaces and dots."],
    roll_number: [/^(?=.*\d)[A-Za-z0-9]{5,15}$/, "Roll number must be 5-15 letters/digits and contain at least one digit."],
    email: [/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/, "Please enter a valid email address."]
};

function setError(input, message) {
    const span = document.getElementById(input.id + "Error");
    if (span) span.textContent = message;
    input.classList.toggle("invalid", message !== "");
}

/** Returns "" if the input is valid, otherwise an error message. */
function checkInput(input) {
    const value = input.value.trim();

    // File inputs: optional, but if a file is chosen check extension and size
    if (input.type === "file") {
        if (input.files.length === 0) return "";
        const file = input.files[0];
        const allowed = input.dataset.extensions.split(",");
        const ext = file.name.split(".").pop().toLowerCase();
        const extOk = allowed.includes(ext) || (ext === "jpeg" && allowed.includes("jpg"));
        if (!extOk) return "Allowed file types: " + allowed.join(", ").toUpperCase() + ".";
        if (file.size > Number(input.dataset.maxSize)) {
            return "File is too large. Maximum size is " + (input.dataset.maxSize / 1048576) + " MB.";
        }
        return "";
    }

    if (input.id === "course") return value === "" ? "Please select a course." : "";

    if (value === "") return "This field is required.";
    const rule = RULES[input.id];
    if (rule && !rule[0].test(value)) return rule[1];
    return "";
}

// Attach validation to every form marked with data-validate
document.querySelectorAll("form[data-validate]").forEach(function (form) {
    const inputs = form.querySelectorAll("input[type=text], input[type=email], select, input[type=file]");

    inputs.forEach(function (input) {
        const eventName = (input.tagName === "SELECT" || input.type === "file") ? "change" : "blur";
        input.addEventListener(eventName, function () {
            setError(input, checkInput(input));
        });
    });

    form.addEventListener("submit", function (event) {
        let firstInvalid = null;
        inputs.forEach(function (input) {
            const message = checkInput(input);
            setError(input, message);
            if (message && !firstInvalid) firstInvalid = input;
        });
        if (firstInvalid) {
            event.preventDefault();   // do not send the form to the server
            firstInvalid.focus();
        }
    });
});

// Extra confirmation box for dangerous actions (delete)
document.querySelectorAll("form[data-confirm]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
        if (!confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});
