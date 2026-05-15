const key = 'thrivewell-theme';
const saved = localStorage.getItem(key);
if (saved) document.documentElement.classList.toggle('dark', saved === 'dark');

const announce = (message, type = 'success') => {
  const success = document.querySelector('.success-state');
  const error = document.querySelector('.error-state');
  if (!success || !error) return;
  success.hidden = type !== 'success';
  error.hidden = type !== 'error';
  (type === 'success' ? success : error).textContent = message;
};

document.getElementById('themeToggle')?.addEventListener('click', () => {
  const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
  document.documentElement.classList.toggle('dark', next === 'dark');
  localStorage.setItem(key, next);
  announce(`${next[0].toUpperCase() + next.slice(1)} theme saved.`);
});

const palette = document.getElementById('commandPalette');
document.getElementById('commandToggle')?.addEventListener('click', () => {
  if (!palette) return;
  palette.hidden = !palette.hidden;
  if (!palette.hidden) document.getElementById('commandSearch')?.focus();
});

document.addEventListener('keydown', (event) => {
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    document.getElementById('commandToggle')?.click();
  }
  if (event.key === 'Escape' && palette) palette.hidden = true;
});

document.querySelectorAll('[data-async-form]').forEach((form) => {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json' },
      });
      const payload = await response.json();
      announce(payload.message || 'Saved gently and securely.', payload.ok ? 'success' : 'error');
      if (payload.ok && payload.hold) {
        const confirmForm = document.querySelector('[data-confirm-hold-form]');
        const holdInput = confirmForm?.querySelector('input[name="booking_hold_id"]');
        if (confirmForm && holdInput) {
          holdInput.value = payload.hold.id;
          confirmForm.hidden = false;
          confirmForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    } catch (error) {
      announce('Something did not load. Nothing is lost; please try again.', 'error');
    }
  });
});

document.querySelector('[data-table-filter]')?.addEventListener('input', (event) => {
  const query = event.target.value.toLowerCase();
  document.querySelectorAll('.data-table-pro tbody tr').forEach((row) => {
    row.hidden = !row.textContent.toLowerCase().includes(query);
  });
});

document.querySelector('.filter-bar input')?.addEventListener('input', (event) => {
  const query = event.target.value.toLowerCase();
  document.querySelectorAll('.client-signal-card, .resource-row, .task-row').forEach((item) => {
    item.hidden = query && !item.textContent.toLowerCase().includes(query);
  });
});

document.querySelectorAll('.filter-bar button').forEach((button) => {
  button.addEventListener('click', () => announce(`${button.textContent} filter staged for the next dashboard query.`));
});

const onboardingForm = document.querySelector('[data-onboarding-form]');
document.querySelector('[data-save-draft]')?.addEventListener('click', async () => {
  if (!onboardingForm) return;
  try {
    const response = await fetch('/onboarding/draft', {
      method: 'POST',
      body: new FormData(onboardingForm),
      headers: { 'Accept': 'application/json' },
    });
    const payload = await response.json();
    announce(payload.message || 'Draft saved gently.', payload.ok ? 'success' : 'error');
  } catch (error) {
    announce('We could not save the draft. Nothing has been submitted.', 'error');
  }
});
