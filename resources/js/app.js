
import Alpine from 'alpinejs';
import { initMateriasSortable } from './admin/materias-sortable';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', initMateriasSortable);
