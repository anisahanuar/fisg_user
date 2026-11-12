const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
menuToggle.addEventListener('click', () => {
  const opened = navLinks.classList.toggle('open');
  menuToggle.setAttribute('aria-expanded', String(opened));
});
navLinks.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
  navLinks.classList.remove('open');
  menuToggle.setAttribute('aria-expanded', 'false');
}));

const toast = document.getElementById('toast');
let toastTimer;
function showToast(message) {
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
}
document.querySelectorAll('.copy-btn').forEach(button => {
  button.addEventListener('click', () => showToast('Demo sahaja: sambungan Copy Trade belum disediakan.'));
});

let reviewShift = 0;
const reviewGrid = document.getElementById('reviewGrid');
document.getElementById('nextReview').addEventListener('click', () => {
  const cards = [...reviewGrid.children];
  reviewShift = (reviewShift + 1) % cards.length;
  reviewGrid.append(cards[0]);
});
document.getElementById('prevReview').addEventListener('click', () => {
  const cards = [...reviewGrid.children];
  reviewGrid.prepend(cards[cards.length - 1]);
});
