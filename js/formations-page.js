/**
 * formations-page.js — Studies Learning
 * Handles: grid entrance, mobile filter panel, "voir plus" categories,
 *          staggered card animations, URL-preserving filter navigation.
 */
(function () {
  "use strict";

  /* ── Util: run after DOM is ready ────────────────────── */
  function ready(fn) {
    if (document.readyState !== "loading") {
      fn();
    } else {
      document.addEventListener("DOMContentLoaded", fn);
    }
  }

  ready(function () {

    /* ── 1. Grid entrance animation ────────────────────── */
    var gridWrap = document.querySelector("[data-grid-animate]");
    if (gridWrap) {
      // Small delay so CSS transition is visible
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          gridWrap.classList.add("is-ready");
        });
      });
    }

    /* ── 2. Mobile filter panel toggle ─────────────────── */
    var filterPanel  = document.getElementById("formations-filters-panel");
    var filterToggle = document.querySelector("[data-open-filters]");

    if (filterPanel && filterToggle) {
      filterToggle.addEventListener("click", function () {
        var isOpen = filterPanel.classList.toggle("is-open");
        filterToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");

        // Update toggle label
        var labelEl = filterToggle.querySelector("span");
        if (labelEl) {
          labelEl.textContent = isOpen
            ? "Masquer les filtres"
            : "Filtrer les formations";
        }

        // Scroll into view when opening on small screens
        if (isOpen) {
          setTimeout(function () {
            filterPanel.scrollIntoView({ behavior: "smooth", block: "nearest" });
          }, 120);
        }
      });

      // Close panel when clicking outside on mobile
      document.addEventListener("click", function (e) {
        if (
          filterPanel.classList.contains("is-open") &&
          !filterPanel.contains(e.target) &&
          !filterToggle.contains(e.target)
        ) {
          filterPanel.classList.remove("is-open");
          filterToggle.setAttribute("aria-expanded", "false");
          var labelEl = filterToggle.querySelector("span");
          if (labelEl) labelEl.textContent = "Filtrer les formations";
        }
      });
    }

    /* ── 3. "Voir plus / Voir moins" categories ─────────── */
    var catContainer = document.querySelector(".formations-bubbles--categories");
    var seeMoreBtn   = document.querySelector("[data-toggle-categories]");

    if (catContainer && seeMoreBtn) {
      seeMoreBtn.addEventListener("click", function () {
        var expanded = catContainer.classList.toggle("is-expanded");
        seeMoreBtn.textContent = expanded ? "↑ Voir moins" : "Voir plus →";
        seeMoreBtn.setAttribute("aria-expanded", String(expanded));
      });
    }

    /* ── 4. Staggered card animations ──────────────────── */
    var cards = document.querySelectorAll(".formations-card");
    if (cards.length) {
      // CSS already handles the delay via nth-child, but we
      // force a reflow so the animation fires even on page load.
      cards.forEach(function (card) {
        card.style.willChange = "transform, opacity";
      });
    }

    /* ── 5. Active chip hover: add × icon title ─────────── */
    var chips = document.querySelectorAll(".formations-active-chip");
    chips.forEach(function (chip) {
      chip.setAttribute("title", "Retirer ce filtre");
    });

    /* ── 6. Keyboard navigation for filter bubbles ──────── */
    var bubbles = document.querySelectorAll(
      ".formations-bubble, .formations-author"
    );
    bubbles.forEach(function (bubble) {
      bubble.setAttribute("role", "listitem");
      // Already <a> tags — no extra keyboard handling needed,
      // but we add a visible focus ring via CSS class
      bubble.addEventListener("focus", function () {
        bubble.classList.add("is-focused");
      });
      bubble.addEventListener("blur", function () {
        bubble.classList.remove("is-focused");
      });
    });

    /* ── 7. Smooth anchor scroll when navigating pages ──── */
    var paginationLinks = document.querySelectorAll(
      ".formations-pagination a"
    );
    paginationLinks.forEach(function (link) {
      link.addEventListener("click", function () {
        // Add a brief fade-out before page reload
        if (gridWrap) {
          gridWrap.style.transition = "opacity 0.25s ease, transform 0.25s ease";
          gridWrap.style.opacity = "0";
          gridWrap.style.transform = "translateY(-8px)";
        }
      });
    });

    /* ── 8. Filter links: add micro-transition ──────────── */
    var filterLinks = document.querySelectorAll(
      ".formations-bubble, .formations-author, .formations-active-chip, .formations-reset-link"
    );
    filterLinks.forEach(function (link) {
      link.addEventListener("click", function () {
        if (gridWrap) {
          gridWrap.style.transition = "opacity 0.2s ease";
          gridWrap.style.opacity = "0.4";
        }
      });
    });

  });
})();
