(() => {
  const password = document.getElementById("secure-password");
  const confirmation = document.getElementById("secure-confirm-password");
  const message = document.getElementById("secure-confirm-error");
  if (!password || !confirmation || !message) return;

  function checkPasswords() {
    const matches = password.value === confirmation.value;
    confirmation.setCustomValidity(matches ? "" : "The passwords must match.");
    confirmation.setAttribute("aria-invalid", String(!matches));
    message.textContent = confirmation.value && !matches ? "The passwords must match." : "";
  }

  password.addEventListener("input", checkPasswords);
  confirmation.addEventListener("input", checkPasswords);
})();
