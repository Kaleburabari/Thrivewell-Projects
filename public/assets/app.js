const key = 'thrivewell-theme';
const saved = localStorage.getItem(key);
if (saved) document.documentElement.classList.toggle('dark', saved === 'dark');
document.getElementById('themeToggle')?.addEventListener('click', () => {
  const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
  document.documentElement.classList.toggle('dark', next === 'dark');
  localStorage.setItem(key, next);
});
