

// const modal = document.getElementById('default-modal');

document.addEventListener('turbo:load', () => {
    const modal = document.getElementById('default-modal');
    if (!modal) return;

    const toggleModalButtons = document.querySelectorAll('.openModal');

    // Ouvrir / fermer via boutons
    toggleModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        });
    });

    // Fermer en cliquant sur l’arrière-plan
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});