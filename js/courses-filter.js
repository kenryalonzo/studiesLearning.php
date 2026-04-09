jQuery(document).ready(function ($) {
  const $container = $("#courses-ajax-container");
  const $loader = $(".courses-loader");
  const $resetBtn = $("#reset-filters");
  const $allBtn = $('.filter-btn[data-filter="all"]');

  // Function to perform the filtering
  function filterCourses() {
    const category = $("#filter-category").val();
    const level = $("#filter-level").val();
    const price = $("#filter-price").val();

    // Show/hide reset button
    if (category || level || price) {
      $resetBtn.show();
      $allBtn.removeClass("active");
    } else {
      $resetBtn.hide();
      $allBtn.addClass("active");
    }

    // Show loader
    $loader.fadeIn(200);

    $.ajax({
      url: studiesAjax.ajax_url,
      type: "POST",
      data: {
        action: "filter_courses",
        nonce: studiesAjax.nonce,
        category: category,
        level: level,
        price: price,
      },
      success: function (response) {
        // Update content
        $container.html(response);

        // Re-initialize or update Swiper
        const swiperInstance = document.querySelector(
          ".eduma-courses-swiper",
        ).swiper;
        if (swiperInstance) {
          swiperInstance.destroy(true, true);
        }

        // Re-init with the same parameters as in courses-slider.js
        new Swiper(".eduma-courses-swiper", {
          slidesPerView: 1,
          spaceBetween: 20,
          loop: $container.find(".swiper-slide").length > 1,
          speed: 600,
          grabCursor: true,
          watchSlidesProgress: true,
          mousewheel: {
            forceToAxis: true,
          },
          autoplay: {
            delay: 4000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
          },
          navigation: {
            nextEl: ".swiper-button-next-custom",
            prevEl: ".swiper-button-prev-custom",
          },
          breakpoints: {
            640: { slidesPerView: 2, spaceBetween: 25 },
            1024: { slidesPerView: 3, spaceBetween: 30 },
            1400: { slidesPerView: 4, spaceBetween: 35 },
          },
        });

        $loader.fadeOut(200);
      },
      error: function () {
        $container.html(
          '<div class="no-courses-found">Une erreur est survenue lors du filtrage.</div>',
        );
        $loader.fadeOut(200);
      },
    });
  }

  // Event listeners
  $(".filter-select").on("change", function () {
    filterCourses();
  });

  $allBtn.on("click", function (e) {
    e.preventDefault();
    $(".filter-select").val("");
    filterCourses();
  });

  $resetBtn.on("click", function (e) {
    e.preventDefault();
    $(".filter-select").val("");
    filterCourses();
  });
});
