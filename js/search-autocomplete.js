/**
 * search-autocomplete.js – Studies Learning
 * Drives the 2-column search banner live results panel.
 */
jQuery(document).ready(function ($) {
  "use strict";

  const $input = $("#formation-search-input");
  const $panel = $("#search-results-panel");
  const $loader = $(".search-loader");
  const $colRight = $(".search-col-right");
  let debounceTimer;

  /* ── Helpers ───────────────────────────────────────────── */
  function showEmpty() {
    $panel
      .removeClass("has-results")
      .html(
        '<div class="search-empty-state">' +
          '<div class="search-empty-icon"><i class="ph ph-books" aria-hidden="true"></i></div>' +
          '<p class="search-empty-title">Commencez à taper…</p>' +
          '<p class="search-empty-sub">Vos résultats apparaîtront ici en temps réel.</p>' +
          "</div>",
      );
  }

  function showNoResults() {
    $panel
      .removeClass("has-results")
      .html(
        '<div class="search-no-results">Aucune formation trouvée pour cette recherche.</div>',
      );
  }

  function renderResults(items) {
    let html =
      '<div class="search-panel-header">' +
      '<span class="search-panel-count">' +
      items.length +
      " résultat" +
      (items.length > 1 ? "s" : "") +
      "</span>" +
      "</div>" +
      '<div class="search-cards-list">';

    items.forEach(function (item) {
      const img = item.image
        ? '<img src="' +
          item.image +
          '" alt="' +
          item.title +
          '" class="mini-course-img" />'
        : '<div class="mini-course-img-placeholder"><i class="ph ph-graduation-cap"></i></div>';

      html +=
        '<a href="' +
        item.url +
        '" class="mini-course-card" data-id="' +
        item.id +
        '">' +
        '<div class="mini-course-img-wrapper">' +
        img +
        "</div>" +
        '<div class="mini-course-info">' +
        '<h4 class="mini-course-title">' +
        item.title +
        "</h4>" +
        '<div class="mini-course-meta">' +
        '<span class="m-cat">' +
        item.category +
        "</span>" +
        '<span class="m-price ' +
        (item.is_free ? "is-free" : "") +
        '">' +
        item.price +
        "</span>" +
        "</div>" +
        "</div>" +
        "</a>";
    });

    html += "</div>";
    $panel.addClass("has-results").html(html);

    // On mobile, make the right column visible once results arrive
    $colRight.addClass("active");
  }

  /* ── Search ────────────────────────────────────────────── */
  function performSearch(term) {
    if (term.length < 2) {
      $loader.hide();
      showEmpty();
      $colRight.removeClass("active");
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
          renderResults(response.data);
        } else {
          showNoResults();
          $colRight.addClass("active");
        }
      },
      error: function () {
        $loader.hide();
      },
    });
  }

  /* ── Event listeners ───────────────────────────────────── */
  $input.on("input", function () {
    clearTimeout(debounceTimer);
    const term = $(this).val().trim();
    debounceTimer = setTimeout(function () {
      performSearch(term);
    }, 280);
  });

  // Hint tag clicks → populate input and trigger search
  $(document).on("click", ".search-hint-tag", function () {
    const tag = $(this).text().trim();
    $input.val(tag).trigger("input").focus();
  });

  // Keyboard: Escape clears
  $input.on("keydown", function (e) {
    if (e.key === "Escape") {
      $(this).val("");
      showEmpty();
      $colRight.removeClass("active");
    }
  });
});
