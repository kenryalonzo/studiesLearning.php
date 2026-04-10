/**
 * footer.js – Studies Learning
 * Micro-interactions for the newsletter form
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector(".footer-newsletter-form");
    var input = document.querySelector(".footer-newsletter-input");
    var success = document.querySelector(".footer-newsletter-success");

    if (!form || !input) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var email = input.value.trim();

      // Basic validation
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        form.classList.remove("shake");
        // Force reflow to restart animation
        void form.offsetWidth;
        form.classList.add("shake");
        input.focus();
        return;
      }

      // Success state
      form.style.opacity = "0.5";
      form.style.pointerEvents = "none";

      if (success) {
        success.classList.add("visible");
      }

      input.value = "";

      // Reset after 4 s
      setTimeout(function () {
        form.style.opacity = "";
        form.style.pointerEvents = "";
        if (success) success.classList.remove("visible");
      }, 4000);
    });
  });
})();
