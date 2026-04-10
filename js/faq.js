document.addEventListener("DOMContentLoaded", function () {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const button = item.querySelector(".faq-question");

    button.addEventListener("click", () => {
      const isActive = item.classList.contains("active");

      if (isActive) {
        // Close it
        item.classList.remove("active");
        button.setAttribute("aria-expanded", "false");
      } else {
        // Open it (allowing multiple to be open as per spec)
        item.classList.add("active");
        button.setAttribute("aria-expanded", "true");
      }
    });
  });
});
