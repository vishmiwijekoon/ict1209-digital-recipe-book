// ============================================================
// Diary of Taste — Combined JavaScript File
// Features:
// 1. Homepage interactions
// 2. Recipes page interactions
// 3. Contact page interactions
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  // ==========================================================
  // HOMEPAGE INTERACTIONS
  // ==========================================================

  // Smooth scrolling
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
      const targetId = link.getAttribute('href');
      const target = document.querySelector(targetId);

      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });

        const navCollapse = document.querySelector('.navbar-collapse.show');
        if (navCollapse && typeof bootstrap !== 'undefined') {
          bootstrap.Collapse.getInstance(navCollapse)?.hide();
        }
      }
    });
  });

  // Navbar shadow on scroll
  const navbar = document.querySelector('.site-navbar');

  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.style.boxShadow =
        window.scrollY > 12
          ? '0 6px 18px -12px rgba(43,38,33,0.25)'
          : 'none';
    });
  }

  // Category filter
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
        const col = recipe.closest('.col-12');
        if (col && col.dataset.category) {
            const match = col.dataset.category === chosen || chosen === 'all' || !chosen;
            col.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        }
      });

      if (filterNotice) {
        filterNotice.textContent =
          visibleCount > 0
            ? `Showing ${visibleCount} recipe${visibleCount > 1 ? 's' : ''} in “${chosen}”`
            : `No featured recipes tagged “${chosen}” yet — check back soon.`;
      }

      document
        .getElementById('featured')
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  // Search form validation
  const searchForm = document.getElementById('search-form');
  const searchInput = document.getElementById('search-input');
  const searchFeedback = document.getElementById('search-feedback');

  searchInput?.addEventListener('input', () => {
    if (searchInput.value.trim().length > 0) {
      if(searchFeedback) searchFeedback.textContent = '';
    }
  });

  searchForm?.addEventListener('submit', (e) => {
    e.preventDefault();

    const value = searchInput.value.trim();

    if (value.length === 0) {
      if(searchFeedback) {
        searchFeedback.textContent = 'Type an ingredient or dish name to search.';
        searchFeedback.className = 'form-feedback error';
      }
      searchInput.focus();
      return;
    }

    if(searchFeedback) {
      searchFeedback.textContent = `Searching for “${value}”…`;
      searchFeedback.className = 'form-feedback success';
    }
  });

  // Newsletter validation
  const subscribeForm = document.getElementById('subscribe-form');
  const subscribeInput = document.getElementById('subscribe-input');
  const subscribeFeedback = document.getElementById('subscribe-feedback');

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  subscribeInput?.addEventListener('input', () => {
    if (emailPattern.test(subscribeInput.value.trim())) {
      if(subscribeFeedback) subscribeFeedback.textContent = '';
    }
  });

  subscribeForm?.addEventListener('submit', (e) => {
    e.preventDefault();

    const email = subscribeInput.value.trim();

    if (!emailPattern.test(email)) {
      if(subscribeFeedback) {
          subscribeFeedback.textContent = 'Please enter a valid email address.';
          subscribeFeedback.className = 'form-feedback error';
      }
      subscribeInput.focus();
      return;
    }

    if(subscribeFeedback) {
        subscribeFeedback.textContent = 'Thanks for subscribing!';
        subscribeFeedback.className = 'form-feedback success';
    }
    subscribeForm.reset();
  });

  // ==========================================================
  // RECIPES PAGE INTERACTIONS
  // ==========================================================

  const chips = document.querySelectorAll('.chip');
  const recipeSearchInput = document.getElementById('recipe-search-input');
  const recipeSearchForm = document.getElementById('recipe-search-form');
  const recipeItems = document.querySelectorAll('.recipe-item');
  const noResults = document.getElementById('no-results');
  const loadMoreBtn = document.getElementById('load-more-btn');

  let activeFilter = 'all';

  function applyFilters() {
    if (!recipeItems.length) return;

    const query = recipeSearchInput?.value.trim().toLowerCase() || '';
    let visibleCount = 0;

    recipeItems.forEach(item => {
      const isExtra =
        item.dataset.extra === 'true' &&
        loadMoreBtn?.dataset.expanded !== 'true';

      const matchesCategory =
        activeFilter === 'all' || item.dataset.category === activeFilter;

      const matchesSearch =
        item.dataset.name.toLowerCase().includes(query);

      const shouldShow =
        matchesCategory && matchesSearch && !isExtra;

      item.classList.toggle('is-hidden', !shouldShow);

      if (shouldShow) visibleCount++;
    });

    noResults?.classList.toggle('show', visibleCount === 0);

    const hasExtras = document.querySelector('[data-extra="true"]');

    if (loadMoreBtn?.parentElement) {
      loadMoreBtn.parentElement.style.display =
        hasExtras &&
        loadMoreBtn.dataset.expanded !== 'true' &&
        query === '' &&
        activeFilter === 'all'
          ? 'block'
          : hasExtras && loadMoreBtn.dataset.expanded === 'true'
          ? 'none'
          : loadMoreBtn.parentElement.style.display;
    }
  }

  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activeFilter = chip.dataset.filter;
      applyFilters();
    });
  });

  recipeSearchInput?.addEventListener('input', applyFilters);

  recipeSearchForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    applyFilters();
  });

  loadMoreBtn?.addEventListener('click', () => {
    const hiddenExtras = document.querySelectorAll(
      '.recipe-item[data-extra="true"].is-hidden'
    );

    const icon = loadMoreBtn.querySelector('.bi-arrow-clockwise');
    icon?.classList.add('spin');

    setTimeout(() => {
      hiddenExtras.forEach(item => {
        item.classList.remove('is-hidden');
        item.classList.add('fade-in');
      });

      icon?.classList.remove('spin');

      loadMoreBtn.dataset.expanded = 'true';
      loadMoreBtn.disabled = true;
      loadMoreBtn.innerHTML =
        '<i class="bi bi-check2"></i> All recipes loaded';
    }, 450);
  });

  // ==========================================================
  // CONTACT PAGE INTERACTIONS
  // ==========================================================

  const contactForm = document.getElementById('contact-form');

  if (contactForm) {

    const nameInput = document.getElementById('contact-name');
    const emailInput = document.getElementById('contact-email');
    const messageInput = document.getElementById('contact-message');

    const nameFeedback = document.getElementById('name-feedback');
    const emailFeedback = document.getElementById('email-feedback');
    const messageFeedback = document.getElementById('message-feedback');

    const formStatus = document.getElementById('form-status');
    const submitBtn = document.getElementById('contact-submit');

    function validateName(showError) {
      const ok = nameInput.value.trim().length > 0;

      nameInput.classList.toggle('is-invalid', showError && !ok);

      if(nameFeedback) {
          nameFeedback.textContent =
            showError && !ok ? 'Please enter your name.' : '';
          nameFeedback.className = 'form-feedback error';
      }

      return ok;
    }

    function validateEmail(showError) {
      const ok = emailPattern.test(emailInput.value.trim());

      emailInput.classList.toggle('is-invalid', showError && !ok);

      if(emailFeedback) {
          emailFeedback.textContent =
            showError && !ok
              ? 'Please enter a valid email address.'
              : '';
          emailFeedback.className = 'form-feedback error';
      }

      return ok;
    }

    function validateMessage(showError) {
      const ok = messageInput.value.trim().length > 0;

      messageInput.classList.toggle('is-invalid', showError && !ok);

      if(messageFeedback) {
          messageFeedback.textContent =
            showError && !ok ? 'Please enter a message.' : '';
          messageFeedback.className = 'form-feedback error';
      }

      return ok;
    }

    nameInput.addEventListener('input', () => validateName(false));
    emailInput.addEventListener('input', () => validateEmail(false));
    messageInput.addEventListener('input', () => validateMessage(false));

    nameInput.addEventListener('blur', () => validateName(true));
    emailInput.addEventListener('blur', () => validateEmail(true));
    messageInput.addEventListener('blur', () => validateMessage(true));

    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const nameOk = validateName(true);
      const emailOk = validateEmail(true);
      const messageOk = validateMessage(true);

      if (!nameOk || !emailOk || !messageOk) {
        if(formStatus) {
            formStatus.textContent =
              'Please fix the highlighted fields.';
            formStatus.className = 'form-feedback error';
        }
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending…';

      setTimeout(() => {
        if(formStatus) {
            formStatus.textContent =
              'Thanks! Your message has been sent.';
            formStatus.className = 'form-feedback success';
        }

        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message';

        contactForm.reset();
      }, 500);
    });
  }

});
