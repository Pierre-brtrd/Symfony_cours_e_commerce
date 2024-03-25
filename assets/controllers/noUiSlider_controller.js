import { Controller } from '@hotwired/stimulus';
import noUiSlider from 'nouislider'
import 'nouislider/dist/nouislider.css'
import '../styles/Frontend/_noUiSlider.scss';

export default class extends Controller {
    connect() {
        const slider = this.element.querySelector('#price-slider');
        const min = this.element.querySelector('#min');
        const max = this.element.querySelector('#max');

        const minValue = Math.floor(parseInt(slider.dataset.min, 10) / 10) * 10;
        const maxValue = Math.ceil(parseInt(slider.dataset.max, 10) / 10) * 10;

        const range = noUiSlider.create(slider, {
            start: [min.value || minValue, max.value || maxValue],
            connect: true,
            step: 10,
            range: {
                'min': minValue,
                'max': maxValue
            }
        });

        range.on('slide', function (values, handle) {
            if (handle === 0) {
                min.value = Math.round(values[0])
            }
            if (handle === 1) {
                max.value = Math.round(values[1])
            }
        })

        range.on('end', function (values, handle) {
            if (handle === 0) {
                min.dispatchEvent(new Event('change'))
            } else {
                max.dispatchEvent(new Event('change'))
            }
        })
    }
}