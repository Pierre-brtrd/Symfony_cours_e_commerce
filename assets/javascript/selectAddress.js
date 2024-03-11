const dropdownContent = document.querySelector('.dropdown-content-address');
const form = document.querySelector('.form-checkout-address');

if (dropdownContent && form) {
    dropdownContent.querySelectorAll('.dropdown-menu .dropdown-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            form.querySelector('input[name="address[address]"]').value = e.target.dataset.address;
            form.querySelector('input[name="address[zipCode]"]').value = e.target.dataset.zipCode;
            form.querySelector('input[name="address[city]"]').value = e.target.dataset.city;
        });
    });
}

