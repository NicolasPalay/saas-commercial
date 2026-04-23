import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['modal'];

  open() {
    this.modalTarget.classList.remove('hidden');
    this.modalTarget.classList.add('flex');
  }

  close(event) {
    // Ferme uniquement si on clique sur l’arrière-plan
    if (event.target === this.modalTarget) {
      this.hide();
    }
  }

  hide() {
    this.modalTarget.classList.add('hidden');
    this.modalTarget.classList.remove('flex');
  }
}
