(() => {
  const menuToggle = document.getElementById("menu-toggle");
  const navigation = document.getElementById("primary-nav");
  const themeToggle = document.getElementById("theme-toggle");
  const savedTheme = localStorage.getItem("studenthub-theme");

  if (savedTheme === "dark") {
    document.body.classList.add("dark-theme");
  }

  function updateThemeButton() {
    if (!themeToggle) return;
    const isDark = document.body.classList.contains("dark-theme");
    themeToggle.setAttribute("aria-pressed", String(isDark));
    themeToggle.textContent = isDark ? "Switch to light theme" : "Switch to dark theme";
  }

  updateThemeButton();

  if (menuToggle && navigation) {
    menuToggle.addEventListener("click", () => {
      const isOpen = menuToggle.getAttribute("aria-expanded") === "true";
      menuToggle.setAttribute("aria-expanded", String(!isOpen));
      navigation.classList.toggle("is-open", !isOpen);
    });

    navigation.addEventListener("click", (event) => {
      if (event.target.closest("a") && window.matchMedia("(max-width: 760px)").matches) {
        menuToggle.setAttribute("aria-expanded", "false");
        navigation.classList.remove("is-open");
      }
    });
  }

  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      const isDark = document.body.classList.toggle("dark-theme");
      localStorage.setItem("studenthub-theme", isDark ? "dark" : "light");
      updateThemeButton();
    });
  }
})();
