/**
 * formations-page.js — Studies Learning
 * Handles: AJAX filtering/pagination, grid entrance, mobile filter panel,
 *          staggered card animations, URL-preserving navigation.
 */
(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") {
      fn();
    } else {
      document.addEventListener("DOMContentLoaded", fn);
    }
  }

  ready(function () {
    var gridWrap = document.querySelector("[data-grid-animate]");
    var gridEl = document.getElementById("formations-ajax-grid");
    var paginationEl = document.getElementById("formations-ajax-pagination");
    var countEl = document.getElementById("formations-ajax-count");
    var filterPanel = document.getElementById("formations-filters-panel");

    // Helper: Initialize Grid Entrance
    function initGridEntrance() {
      if (gridWrap) {
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            gridWrap.classList.add("is-ready");
          });
        });
      }
      
      var cards = document.querySelectorAll(".formations-card");
      if (cards.length) {
        cards.forEach(function (card) {
          card.style.willChange = "transform, opacity";
        });
      }
    }
    
    initGridEntrance();

    /* ── Mobile filter panel toggle ─────────────────── */
    var filterToggle = document.querySelector("[data-open-filters]");
    if (filterPanel && filterToggle) {
      filterToggle.addEventListener("click", function () {
        var isOpen = filterPanel.classList.toggle("is-open");
        filterToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        var labelEl = filterToggle.querySelector("span");
        if (labelEl) {
          labelEl.textContent = isOpen ? "Masquer les filtres" : "Filtrer les formations";
        }
        if (isOpen) {
          setTimeout(function () {
            filterPanel.scrollIntoView({ behavior: "smooth", block: "nearest" });
          }, 120);
        }
      });

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

    /* ── "Voir plus" categories ─────────── */
    function initVoirPlus() {
      var catContainer = document.querySelector(".formations-bubbles--categories");
      var seeMoreBtn = document.querySelector("[data-toggle-categories]");
      if (catContainer && seeMoreBtn) {
        // Cleanup old listeners to avoid duplicates
        var newBtn = seeMoreBtn.cloneNode(true);
        seeMoreBtn.parentNode.replaceChild(newBtn, seeMoreBtn);
        newBtn.addEventListener("click", function () {
          var expanded = catContainer.classList.toggle("is-expanded");
          newBtn.innerHTML = expanded ? "↑ Voir moins" : "Voir plus &rarr;";
          newBtn.setAttribute("aria-expanded", String(expanded));
        });
      }
    }
    initVoirPlus();

    /* ── AJAX Filtering & Pagination ──────────────────── */

    function getParamsFromUrl(url) {
      var urlParams = new URL(url).searchParams;
      return {
        cat: urlParams.get('cat') || '',
        author: urlParams.get('author') || '',
        level: urlParams.get('level') || '',
        price: urlParams.get('price') || '',
        paged: urlParams.get('paged') || ''
      };
    }

    function updateActiveStates(params) {
      // We will actually rely on the server to re-render the filter panel with correct active states
      // if possible, but since we decided to only re-render the grid, we must manually update 
      // the 'is-active' classes on the filter links.
      
      var filterLinks = document.querySelectorAll(".formations-bubble, .formations-author");
      filterLinks.forEach(function(link) {
        link.classList.remove('is-active');
        
        var href = link.getAttribute('href');
        if (!href) return;
        var linkParams = getParamsFromUrl(href);
        
        // This is complex because clicking an inactive link makes it active, and clicking an active one resets it.
        // The simplest way: fetch the entire filter panel HTML via AJAX too, or just reload the whole page via JS fetch
        // Let's actually fetch the full page and extract just what we need to ensure perfectly accurate UI sync.
      });
    }

    function loadFormations(url) {
      if (!window.studiesAjax || !gridWrap) return;

      gridWrap.classList.add("is-loading");

      var params = getParamsFromUrl(url);

      var formData = new FormData();
      formData.append('action', 'studies_load_formations');
      formData.append('nonce', window.studiesAjax.nonce);
      if (params.cat) formData.append('cat', params.cat);
      if (params.author) formData.append('author', params.author);
      if (params.level) formData.append('level', params.level);
      if (params.price) formData.append('price', params.price);
      if (params.paged) formData.append('paged', params.paged);

      fetch(window.studiesAjax.ajax_url, {
        method: 'POST',
        body: formData
      })
      .then(function(res) { return res.json(); })
      .then(function(res) {
        gridWrap.classList.remove("is-loading");
        if (res.success) {
          history.pushState(params, '', url);
          
          if (gridEl) {
             gridEl.innerHTML = res.data.grid_html;
          } else {
             // In case gridEl was the empty state and we need to wrap it again.
             // We'll replace the inner HTML of the wrap.
             gridWrap.innerHTML = '<div class="formations-grid" id="formations-ajax-grid">' + res.data.grid_html + '</div>';
             gridEl = document.getElementById("formations-ajax-grid");
          }

          if (paginationEl) {
             paginationEl.innerHTML = res.data.pagination_html;
          }
          if (countEl) {
             countEl.innerHTML = res.data.count_html;
          }

          // Fetch the page itself to silently replace the filter panel
          // This guarantees chips, counts, active states, etc. are 100% correct.
          fetch(url)
            .then(function(htmlRes) { return htmlRes.text(); })
            .then(function(html) {
               var parser = new DOMParser();
               var doc = parser.parseFromString(html, 'text/html');
               var newFilters = doc.getElementById('formations-filters-panel');
               if (newFilters && filterPanel) {
                 filterPanel.innerHTML = newFilters.innerHTML;
                 initVoirPlus();
               }
            });

          // Retrigger animations
          gridWrap.classList.remove("is-ready");
          initGridEntrance();
          
          // Scroll back to top of grid
          var yOffset = -120; 
          var y = gridWrap.getBoundingClientRect().top + window.scrollY + yOffset;
          window.scrollTo({top: y, behavior: 'smooth'});

        }
      })
      .catch(function(err) {
        console.error("Erreur chargement formations: ", err);
        gridWrap.classList.remove("is-loading");
      });
    }

    // Intercept clicks
    document.addEventListener("click", function(e) {
      // Find closest link inside filters or pagination
      var targetLink = e.target.closest("#formations-filters-panel a, #formations-ajax-pagination a, [data-reset-filters]");
      
      if (targetLink) {
        e.preventDefault();
        
        var url;
        if (targetLink.hasAttribute('data-reset-filters')) {
          url = window.location.pathname; // Clear all
        } else {
          url = targetLink.href;
        }
        
        loadFormations(url);
      }
    });

    // Handle back button
    window.addEventListener("popstate", function() {
      loadFormations(window.location.href);
    });

  });
})();
