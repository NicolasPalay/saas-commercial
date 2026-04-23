document.addEventListener('turbo:load', () => {
  const sidebar = document.getElementById('sidebar');
  const openMenu = document.getElementById('openMenu');
  const closeMenu = document.getElementById('closeMenu');

  if (!sidebar) return;

  if (openMenu) {
    openMenu.addEventListener('click', () => {
      sidebar.classList.remove('-translate-x-full');
    });
  }

  if (closeMenu) {
    closeMenu.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full');
    });
  }
});
