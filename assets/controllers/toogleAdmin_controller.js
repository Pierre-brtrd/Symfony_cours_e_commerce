import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const btnToggle = this.element.querySelector('.btn-toggle-sidebar');

        btnToggle.addEventListener('click', () => {
            this.element.classList.toggle('toggled');
        });
    }
}