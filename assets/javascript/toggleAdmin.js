const btnToggle = document.querySelector('.btn-toggle-sidebar');
const wrapper = document.querySelector('.wrapper');

btnToggle.addEventListener('click', () => {
    wrapper.classList.toggle('toggled');
});