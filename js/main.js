// Studies Learning Premium JS
document.addEventListener("DOMContentLoaded", () => {
  // 1. Scroll Restoration
  if ("scrollRestoration" in history) {
    history.scrollRestoration = "manual";
  }
  window.scrollTo(0, 0);

  // 2. Navbar Scroll Effect
  const navbar = document.querySelector(".navbar-premium");

  if (navbar) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 30) {
        navbar.style.padding = "0.7rem 3rem";
        navbar.style.background = "rgba(255, 255, 255, 0.15)";
        navbar.style.backdropFilter = "blur(20px) saturate(180%)";
        navbar.style.boxShadow = "0 10px 40px rgba(0, 0, 0, 0.1)";
      } else {
        navbar.style.padding = "1.2rem 4rem";
        navbar.style.background = "rgba(255, 255, 255, 0.12)";
        navbar.style.backdropFilter = "blur(30px) saturate(200%)";
        navbar.style.boxShadow = "0 10px 40px rgba(0, 0, 0, 0.05)";
      }
    });
  }

  // 3. revealObserve - Consolidated for all elements
  const revealCallback = (entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("revealed");
        observer.unobserve(entry.target); // Once revealed, no need to watch again
      }
    });
  };

  const revealObserver = new IntersectionObserver(revealCallback, {
    threshold: 0.15,
  });

  document
    .querySelectorAll(
      ".courses-section, .modern-banner-container, .hero-premium",
    )
    .forEach((el) => {
      revealObserver.observe(el);
    });

  // 4. Modern Banner Interactive Elements
  const modernBanner = document.querySelector(".modern-banner-container");

  if (modernBanner) {
    const pathCards = document.querySelectorAll(".path-card");
    pathCards.forEach((card) => {
      card.addEventListener("mouseenter", () => {
        card.style.transform = "translateX(8px) scale(1.02)";
      });
      card.addEventListener("mouseleave", () => {
        card.style.transform = "translateX(0) scale(1)";
      });
    });
  }

  // 6. Soap Bubbles Physics Loop
  const spheres = document.querySelectorAll(".floating-sphere");
  const bannerContainer = document.querySelector(".banner-inner");

  if (bannerContainer && spheres.length > 0) {
    const bannerBox = bannerContainer.getBoundingClientRect();

    const bubbles = Array.from(spheres).map((sphere) => ({
      el: sphere,
      x: Math.random() * (bannerBox.width - 100) + 50,
      y: Math.random() * (bannerBox.height - 100) + 50,
      vx: (Math.random() - 0.5) * 1.3, // Naturally slow and smooth
      vy: (Math.random() - 0.5) * 1.3,
      size: 75,
      hue: Math.random() * 360,
    }));

    function runPhysics() {
      const box = bannerContainer.getBoundingClientRect();
      if (!box || box.width === 0) return;

      bubbles.forEach((b) => {
        b.x += b.vx;
        b.y += b.vy;
        b.hue += 0.2;

        if (b.x <= 0 || b.x >= box.width - b.size) {
          b.vx *= -1;
          b.x = Math.max(0, Math.min(b.x, box.width - b.size));
        }
        if (b.y <= 0 || b.y >= box.height - b.size) {
          b.vy *= -1;
          b.y = Math.max(0, Math.min(b.y, box.height - b.size));
        }

        b.el.style.transform = `translate3d(${b.x}px, ${b.y}px, 0)`;
        b.el.style.filter = `hue-rotate(${b.hue}deg)`;
      });

      requestAnimationFrame(runPhysics);
    }

    runPhysics();
  }

  // 6. Path Cards Slider - Vertical sliding (auto only)
  const pathSlider = document.getElementById("pathCardsSlider");

  if (pathSlider) {
    const pathCards = pathSlider.querySelectorAll(".path-card");
    let currentPathIndex = 0;
    const visiblePathCards = 3;
    const totalPathCards = pathCards.length;

    function showPathCards() {
      pathCards.forEach((card, index) => {
        if (
          index >= currentPathIndex &&
          index < currentPathIndex + visiblePathCards
        ) {
          card.style.display = "flex";
          card.style.animation = "none";
          card.offsetHeight;
          card.style.animation = "";
        } else {
          card.style.display = "none";
        }
      });
    }

    showPathCards();

    let pathSlideInterval = setInterval(() => {
      if (currentPathIndex + visiblePathCards < totalPathCards) {
        currentPathIndex++;
      } else {
        currentPathIndex = 0;
      }
      showPathCards();
    }, 4000);

    const pathContainer = pathSlider.closest(".path-cards-container");
    if (pathContainer) {
      pathContainer.addEventListener("mouseenter", () => {
        clearInterval(pathSlideInterval);
      });
      pathContainer.addEventListener("mouseleave", () => {
        pathSlideInterval = setInterval(() => {
          if (currentPathIndex + visiblePathCards < totalPathCards) {
            currentPathIndex++;
          } else {
            currentPathIndex = 0;
          }
          showPathCards();
        }, 4000);
      });
    }
  }
});
