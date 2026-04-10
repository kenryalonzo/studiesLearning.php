jQuery(document).ready(function ($) {
  const $input = $("#formation-search-input");
  const $results = $("#search-results-dropdown");
  const $loader = $(".search-loader");
  let debounceTimer;

  // Search Logic
  function performSearch(term) {
    if (term.length < 2) {
      $results.hide().empty();
      $loader.hide();
      return;
    }

    $loader.show();

    $.ajax({
      url: studiesSearchAjax.ajax_url,
      type: "POST",
      data: {
        action: "search_formations",
        nonce: studiesSearchAjax.nonce,
        term: term,
      },
      success: function (response) {
        $loader.hide();
        if (response.success && response.data.length > 0) {
          let html = '<div class="search-cards-grid">';
          response.data.forEach((item) => {
            html += `
              <a href="${item.url}" class="mini-course-card" data-id="${item.id}">
                <div class="mini-course-img-wrapper">
                  ${item.image ? `<img src="${item.image}" alt="${item.title}" class="mini-course-img" />` : `<div class="mini-course-img-placeholder"><i class="ph ph-graduation-cap"></i></div>`}
                </div>
                <div class="mini-course-info">
                  <h4 class="mini-course-title">${item.title}</h4>
                  <div class="mini-course-meta">
                    <span class="m-cat">${item.category}</span>
                    <span class="m-price ${item.is_free ? "is-free" : ""}">${item.price}</span>
                  </div>
                </div>
              </a>`;
          });
          html += "</div>";
          $results.html(html).fadeIn(200);
        } else {
          $results
            .html(
              '<div class="search-no-results">Aucune formation trouvée.</div>',
            )
            .fadeIn(200);
        }
      },
      error: function () {
        $loader.hide();
      },
    });
  }

  // Input Event Listener
  $input.on("input", function () {
    clearTimeout(debounceTimer);
    const term = $(this).val().trim();

    debounceTimer = setTimeout(() => {
      performSearch(term);
    }, 300);
  });

  // Keyboard Navigation
  $input.on("keydown", function (e) {
    const $items = $results.find(".search-suggestion-item");
    let index = $items.filter(".is-focused").index();

    if (e.key === "ArrowDown") {
      e.preventDefault();
      index++;
      if (index >= $items.length) index = 0;
      $items.removeClass("is-focused").eq(index).addClass("is-focused");
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      index--;
      if (index < 0) index = $items.length - 1;
      $items.removeClass("is-focused").eq(index).addClass("is-focused");
    } else if (e.key === "Enter") {
      const $focused = $items.filter(".is-focused");
      if ($focused.length) {
        e.preventDefault();
        window.location.href = $focused.attr("href");
      }
    } else if (e.key === "Escape") {
      $results.fadeOut(200);
    }
  });

  // Close on click outside
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".search-input-wrapper").length) {
      $results.fadeOut(200);
    }
  });

  // Re-open on focus if has content
  $input.on("focus", function () {
    if ($results.children().length > 0) {
      $results.fadeIn(200);
    }
  });
});
