document.addEventListener("DOMContentLoaded", function () {
  // Initialize Swiper.js for courses
  const coursesSwiper = new Swiper(".courses-swiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    breakpoints: {
      640: {
        slidesPerView: 2,
        spaceBetween: 20,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
    },
  });

  // Handle flip effect on click
  const courseCards = document.querySelectorAll(".course-card-inner");
  courseCards.forEach((card) => {
    card.addEventListener("click", function (e) {
      // Prevent flip if clicking the "Voir le cours" button on the back
      if (e.target.classList.contains("btn-primary-soft")) {
        return;
      }
      this.classList.toggle("is-flipped");
    });
  });
});
