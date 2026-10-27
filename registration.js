(() => {
  const form = document.getElementById("registration-form");
  if (!form) return;

  const fields = {
    name: document.getElementById("student-name"),
    email: document.getElementById("student-email"),
    mobile: document.getElementById("student-mobile"),
    password: document.getElementById("student-password"),
    confirmPassword: document.getElementById("confirm-password"),
    course: document.getElementById("student-course"),
    year: document.getElementById("student-year"),
    terms: document.getElementById("terms")
  };
  const messages = {
    name: document.getElementById("name-error"),
    email: document.getElementById("email-error"),
    mobile: document.getElementById("mobile-error"),
    password: document.getElementById("password-error"),
    confirmPassword: document.getElementById("confirm-password-error"),
    course: document.getElementById("course-error"),
    year: document.getElementById("year-error"),
    gender: document.getElementById("gender-error"),
    terms: document.getElementById("terms-error")
  };
  const status = document.getElementById("form-status");
  const strengthMeter = document.getElementById("password-strength");
  const strengthText = document.getElementById("password-strength-text");

  function showError(name, message, control = fields[name]) {
    messages[name].textContent = message;
    if (control) control.setAttribute("aria-invalid", message ? "true" : "false");
  }

  function updatePasswordStrength() {
    const password = fields.password.value;
    const checks = [
      password.length >= 8,
      /[a-z]/.test(password),
      /[A-Z]/.test(password),
      /\d/.test(password) && /[^A-Za-z0-9]/.test(password)
    ];
    const score = password ? checks.filter(Boolean).length : 0;
    strengthMeter.value = score;
    const labels = ["not entered", "weak", "weak", "good", "strong"];
    strengthText.textContent = `Password strength: ${labels[score]}`;
  }

  fields.password.addEventListener("input", updatePasswordStrength);

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    status.textContent = "";
    Object.keys(messages).forEach((name) => showError(name, "", name === "gender" ? form.elements.gender[0] : fields[name]));

    const fullName = fields.name.value.trim();
    if (!/^[\p{L}][\p{L}\s'.-]{1,59}$/u.test(fullName)) {
      showError("name", "Enter a name using 2 to 60 letters.");
    }

    const email = fields.email.value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showError("email", "Enter a valid email address.");
    }

    const mobile = fields.mobile.value.trim().replace(/[\s()-]/g, "");
    if (!/^\+?\d{10,15}$/.test(mobile)) {
      showError("mobile", "Enter 10 to 15 digits, with an optional leading +.");
    }

    const password = fields.password.value;
    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)) {
      showError("password", "Use at least 8 characters with uppercase, lowercase, number, and symbol.");
    }

    if (fields.confirmPassword.value !== password || !fields.confirmPassword.value) {
      showError("confirmPassword", "The passwords must match.");
    }

    if (!fields.course.value) showError("course", "Select a course.");
    if (!fields.year.value) showError("year", "Select a year.");

    const selectedGender = form.querySelector('input[name="gender"]:checked');
    if (!selectedGender) showError("gender", "Select a gender.", form.elements.gender[0]);

    if (!fields.terms.checked) showError("terms", "Accept the terms and conditions.");

    const hasErrors = Object.values(messages).some((message) => message.textContent !== "");
    if (hasErrors) {
      status.textContent = "Please correct the fields marked above.";
      const firstInvalid = form.querySelector('[aria-invalid="true"]');
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    status.textContent = "Registration details are valid.";
  });

  updatePasswordStrength();
})();
