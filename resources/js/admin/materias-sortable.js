import Sortable from 'sortablejs';

function filasDelSemestre(tbody, semestre) {
    return [...tbody.querySelectorAll('tr[data-semestre]')].filter(
        (fila) => fila.dataset.semestre === String(semestre),
    );
}

function idsEnOrden(filas) {
    return filas.map((fila) => Number(fila.dataset.id));
}

async function guardarOrden(tbody, semestre) {
    const filas = filasDelSemestre(tbody, semestre);
    const url = tbody.dataset.reorderUrl;
    const licenciaturaId = tbody.dataset.licenciaturaId;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    const response = await fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({
            licenciatura_id: Number(licenciaturaId),
            semestre: Number(semestre),
            materias: idsEnOrden(filas),
        }),
    });

    if (! response.ok) {
        const data = await response.json().catch(() => ({}));
        throw new Error(data.message ?? 'No se pudo guardar el orden.');
    }
}

export function initMateriasSortable() {
    const tbody = document.getElementById('materias-sortable');

    if (! tbody || tbody.dataset.canReorder !== '1') {
        return;
    }

    Sortable.create(tbody, {
        animation: 150,
        handle: '.materia-drag-handle',
        draggable: 'tr[data-id]',
        ghostClass: 'materia-sortable-ghost',
        chosenClass: 'materia-sortable-chosen',
        dragClass: 'materia-sortable-drag',
        onMove(event) {
            return event.dragged.dataset.semestre === event.related.dataset.semestre;
        },
        async onEnd(event) {
            if (event.oldIndex === event.newIndex) {
                return;
            }

            const semestre = event.item.dataset.semestre;
            const filas = [...tbody.querySelectorAll('tr[data-id]')];
            const referencia = filas[event.oldIndex] ?? null;

            tbody.classList.add('materia-sortable-pending');

            try {
                await guardarOrden(tbody, semestre);
                window.dispatchEvent(new CustomEvent('materias-orden-guardado'));
            } catch (error) {
                if (referencia && referencia !== event.item) {
                    tbody.insertBefore(event.item, referencia);
                } else if (! referencia) {
                    tbody.appendChild(event.item);
                }

                window.dispatchEvent(
                    new CustomEvent('materias-orden-error', {
                        detail: { message: error.message },
                    }),
                );
            } finally {
                tbody.classList.remove('materia-sortable-pending');
            }
        },
    });
}
