import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const form = this.element.closest('.form-checkout-address');

        if (form) {
            this.element.querySelectorAll('.dropdown-menu .dropdown-item').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    form.querySelector('input[name="address[address]"]').value = e.target.dataset.address;
                    form.querySelector('input[name="address[zipCode]"]').value = e.target.dataset.zipCode;
                    form.querySelector('input[name="address[city]"]').value = e.target.dataset.city;
                });
            });
        }
    }
}