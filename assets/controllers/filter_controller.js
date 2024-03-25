import { Controller } from '@hotwired/stimulus';
import { Filter } from '../javascript/filter';

export default class extends Controller {
    connect() {
        new Filter(this.element);
    }
}