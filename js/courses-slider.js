document.addEventListener("DOMContentLoaded", function () {
  // Initialize Swiper.js for Eduma-style courses with premium fluidity
  const edumaCoursesSwiper = new Swiper(".eduma-courses-swiper", {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: true,
    speed: 600, // Smoother transition speed
    grabCursor: true, // Show grab cursor for better UX
    watchSlidesProgress: true,
    mousewheel: {
      forceToAxis: true,
    },
    autoplay: {
      delay: 4000,
      disableOnInteraction: false, // Resume after interaction
      pauseOnMouseEnter: true,
    },
    navigation: {
      nextEl: ".swiper-button-next-custom",
      prevEl: ".swiper-button-prev-custom",
    },
    // Premium feel: slide transition effect
    touchEventsTarget: "container",
    resistanceRatio: 0.85,
    freeMode: {
      enabled: false, // Keeping it snapped for a clean grid look, but can be true if user prefers
    },
    breakpoints: {
      640: {
        slidesPerView: 2,
        spaceBetween: 25,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
      1400: {
        slidesPerView: 4,
        spaceBetween: 35,
      },
    },
  });
});
