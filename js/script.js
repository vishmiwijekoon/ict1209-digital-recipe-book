
document.addEventListener('DOMContentLoaded', () => {

  /* ---------- 1. Smooth scrolling for in-page nav links ---------- */
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
      const targetId = link.getAttribute('href');
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // collapse mobile nav after tap
        const navCollapse = document.querySelector('.navbar-collapse.show');
        if (navCollapse) {
          bootstrap.Collapse.getInstance(navCollapse)?.hide();
        }
      }
    });
  });

  /* ---------- 2. Navbar shadow on scroll (event handling) ---------- */
  const navbar = document.querySelector('.site-navbar');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 12) {
      navbar.style.boxShadow = '0 6px 18px -12px rgba(43,38,33,0.25)';
    } else {
      navbar.style.boxShadow = 'none';
    }
  });

  /* ---------- 3. Category filter -> dynamic content update ---------- */
  const categoryCards = document.querySelectorAll('.category-card');
  const filterNotice = document.getElementById('filter-notice');
  const recipeCards = document.querySelectorAll('.recipe-card');

  categoryCards.forEach(card => {
    card.addEventListener('click', (e) => {
      e.preventDefault();
      const chosen = card.dataset.category;

      categoryCards.forEach(c => c.classList.remove('active-category'));
      card.classList.add('active-category');

      let visibleCount = 0;
      recipeCards.forEach(recipe => {
        const match = recipe.dataset.category === chosen;
        recipe.closest('.col-12').style.display = match ? '' : 'none';
        if (match) visibleCount++;
      });

      if (filterNotice) {
        filterNotice.textContent = visibleCount > 0
          ? `Showing ${visibleCount} recipe${visibleCount > 1 ? 's' : ''} in “${chosen}”`
          : `No featured recipes tagged “${chosen}” yet — check back soon.`;
      }

      document.getElementById('featured')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  /* ---------- 4. Search form validation (real-time feedback) ---------- */
  const searchForm = document.getElementById('search-form');
  const searchInput = document.getElementById('search-input');
  const searchFeedback = document.getElementById('search-feedback');

  searchInput?.addEventListener('input', () => {
    if (searchInput.value.trim().length > 0) {
      searchFeedback.textContent = '';
    }
  });

  searchForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const value = searchInput.value.trim();
    if (value.length === 0) {
      searchFeedback.textContent = 'Type an ingredient or dish name to search.';
      searchFeedback.className = 'form-feedback error';
      searchInput.focus();
      return;
    }
    searchFeedback.textContent = `Searching for “${value}”…`;
    searchFeedback.className = 'form-feedback success';
  });

  /* ---------- 5. Newsletter form validation (email format check) ---------- */
  const subscribeForm = document.getElementById('subscribe-form');
  const subscribeInput = document.getElementById('subscribe-input');
  const subscribeFeedback = document.getElementById('subscribe-feedback');
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  subscribeInput?.addEventListener('input', () => {
    if (emailPattern.test(subscribeInput.value.trim())) {
      subscribeFeedback.textContent = '';
    }
  });

  subscribeForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = subscribeInput.value.trim();
    if (!emailPattern.test(email)) {
      subscribeFeedback.textContent = 'Please enter a valid email address.';
      subscribeFeedback.className = 'form-feedback error';
      subscribeInput.focus();
      return;
    }
    subscribeFeedback.textContent = 'Thanks for subscribing!';
    subscribeFeedback.className = 'form-feedback success';
    subscribeForm.reset();
  });

});