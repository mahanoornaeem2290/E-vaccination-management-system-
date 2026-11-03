document.addEventListener('DOMContentLoaded', () => {
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];

  $$('[data-demo-account]').forEach(card => {
    card.addEventListener('click', () => {
      const email = card.dataset.email || '';
      const password = card.dataset.password || '';
      const emailField = $('#email');
      const passwordField = $('#password');
      if (emailField) {
        emailField.value = email;
        emailField.classList.add('filled-demo');
      }
      if (passwordField) {
        passwordField.value = password;
        passwordField.classList.add('filled-demo');
      }
      if (emailField) emailField.focus();
      card.animate([
        { transform: 'translateY(0) scale(1)' },
        { transform: 'translateY(-2px) scale(1.01)' },
        { transform: 'translateY(0) scale(1)' }
      ], { duration: 280, easing: 'ease-out' });
    });
  });

  $$('[data-toggle-target]').forEach(button => {
    button.addEventListener('click', () => {
      const target = document.getElementById(button.dataset.toggleTarget);
      if (!target) return;
      const show = target.hidden;
      target.hidden = !show;
      button.setAttribute('aria-expanded', String(show));
      button.textContent = show ? 'Hide sample checklist' : 'Show sample checklist';
    });
  });

  const vaccineRows = $$('[data-vaccine-toggle]');
  if (vaccineRows.length) {
    const updateVaccineCount = () => {
      const given = vaccineRows.filter(row => row.dataset.state === 'given').length;
      const count = $('[data-vaccine-count]');
      const progress = $('[data-vaccine-progress]');
      if (count) count.textContent = `${given} of ${vaccineRows.length} given`;
      if (progress) progress.style.width = `${(given / vaccineRows.length) * 100}%`;
    };

    vaccineRows.forEach(row => row.addEventListener('click', () => {
      const given = row.dataset.state !== 'given';
      const label = row.children[1].textContent.trim();
      const due = row.dataset.pendingLabel;
      const status = row.querySelector('[data-vaccine-status]');
      const tick = row.querySelector('.tick');

      row.dataset.state = given ? 'given' : 'pending';
      row.classList.toggle('done', given);
      row.classList.toggle('next', !given && due.startsWith('Due'));
      row.setAttribute('aria-pressed', String(given));
      row.setAttribute('aria-label', given
        ? `${label}, given. Select to mark not given`
        : `${label}, ${due.toLowerCase()}. Select to mark given`);
      if (status) status.textContent = given ? `Given · ${due}` : (due.startsWith('Due') ? due : `Not given · ${due}`);
      if (tick) tick.innerHTML = given
        ? '<i class="bi bi-check" aria-hidden="true"></i>'
        : (due.startsWith('Due') ? '<i class="bi bi-bell" aria-hidden="true"></i>' : '');
      updateVaccineCount();
    }));

    updateVaccineCount();
  }

  const revealItems = $$('[data-reveal]');
  if (revealItems.length && 'IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.documentElement.classList.add('reveal-enabled');
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -32px 0px' });
    revealItems.forEach(item => revealObserver.observe(item));
  }

  // Day / night toggle (the choice is remembered; the <head> script applies it before first paint)
  $('#themeToggle')?.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    try { localStorage.setItem('vaxTheme', next); } catch (e) {}
    document.dispatchEvent(new CustomEvent('themechange', { detail: next }));
  });

  $('#burger')?.addEventListener('click', () => $('#side').classList.toggle('open'));
  $$('[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));
  $$('[data-filter]').forEach(i => i.addEventListener('input', () => {
    const t = i.value.toLowerCase();
    $$(i.dataset.filter + ' tbody tr').forEach(r => r.hidden = !r.textContent.toLowerCase().includes(t));
  }));

  // Animated numbers: <b data-count="42">  (also used by the live charts via window.countTo)
  window.countTo = (el, to, ms = 900) => {
    const from = parseFloat(el.dataset.now ?? '0') || 0; el.dataset.now = to;
    if (matchMedia('(prefers-reduced-motion: reduce)').matches || from === to) { el.textContent = to; return; }
    const t0 = performance.now();
    const step = t => { const k = Math.min(1, (t - t0) / ms), e = 1 - Math.pow(1 - k, 3); el.textContent = Math.round(from + (to - from) * e); if (k < 1) requestAnimationFrame(step); };
    requestAnimationFrame(step);
  };
  $$('[data-count]').forEach(el => window.countTo(el, parseInt(el.dataset.count, 10) || 0));

  // Add family member: show passport field only for adults
  const mt = $('#member_type'), pw = $('#passport-wrap');
  if (mt && pw) { const sync = () => { pw.hidden = mt.value !== 'adult'; const d = $('#dob'); if (d) d.title = mt.value === 'adult' ? 'Adults must be 18 or older' : 'Children up to 12 years'; }; mt.addEventListener('input', sync); sync(); }

  // Booking: filter hospitals by chosen vaccine, city and search text
  const sch = $('#schedule'), cards = $$('.hosp');
  if (sch && cards.length) {
    const qi = $('#hq'), city = $('#hcity');
    const run = () => {
      const vid = sch.selectedOptions[0]?.dataset.vaccine, t = qi.value.toLowerCase().trim(), c = city.value; let n = 0;
      cards.forEach(card => {
        const ok = (!vid || card.dataset.vaccines.split(',').includes(vid)) && (!c || card.dataset.city === c) && card.dataset.name.includes(t);
        card.hidden = !ok; if (!ok) card.querySelector('input').checked = false; else n++;
      });
      $('#hnone').hidden = n > 0;
    };
    [sch, qi, city].forEach(x => x.addEventListener('input', run)); run();
  }
});
