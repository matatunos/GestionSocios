// Prototipo frontend para gestión de páginas del libro
// Permite reordenar, añadir y borrar páginas
// Uso: incluir este script en la página de maquetación/exportación

let bookPages = window.bookPages ? [...window.bookPages] : [];
let draggedItem = null;

function renderPages() {
    const container = document.getElementById('book-pages-list');
    if (!container) return;

    // Usar DocumentFragment para minimizar reflows
    const fragment = document.createDocumentFragment();

    bookPages.forEach((page, idx) => {
        const div = document.createElement('div');
        div.className = 'book-page-block';
        div.draggable = true;
        div.dataset.idx = idx;

        // Contenido HTML
        div.innerHTML = `
            <span class="page-title">${page.content}</span>
            <select class="page-type-selector form-select-sm" data-idx="${idx}" style="margin-left:10px; width: auto;">
                <option value="full" ${page.position === 'full' ? 'selected' : ''}>Completa</option>
                <option value="top" ${page.position === 'top' ? 'selected' : ''}>Superior</option>
                <option value="bottom" ${page.position === 'bottom' ? 'selected' : ''}>Inferior</option>
            </select>
            <div class="actions" style="display:flex; gap:5px;">
                <button type="button" class="btn-icon edit-btn" data-idx="${idx}" title="Editar">✏️</button>
                <button type="button" class="btn-icon delete-btn" data-idx="${idx}" title="Eliminar">🗑️</button>
            </div>
        `;

        // Eventos de Drag & Drop nativos optimizados
        div.addEventListener('dragstart', handleDragStart);
        div.addEventListener('dragover', handleDragOver);
        div.addEventListener('dragleave', handleDragLeave);
        div.addEventListener('drop', handleDrop);
        div.addEventListener('dragend', handleDragEnd);

        fragment.appendChild(div);
    });

    container.innerHTML = '';
    container.appendChild(fragment);
}

// Manejadores de eventos Drag & Drop
function handleDragStart(e) {
    draggedItem = this;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', this.dataset.idx);
    this.classList.add('dragging');
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    // Añadir clase visual al destino
    if (this !== draggedItem) {
        this.classList.add('drag-over');
    }
    return false;
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    e.stopPropagation();
    e.preventDefault();

    this.classList.remove('drag-over');

    const fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
    const toIdx = parseInt(this.dataset.idx);

    if (fromIdx !== toIdx) {
        movePage(fromIdx, toIdx);
    }
    return false;
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.book-page-block').forEach(el => {
        el.classList.remove('drag-over');
    });
    draggedItem = null;
}

function getPositionLabel(pos) {
    if (pos === 'top') return 'Superior';
    if (pos === 'bottom') return 'Inferior';
    return 'Completa';
}

function movePage(from, to) {
    if (from === to) return;
    // Mover el elemento en el array
    const page = bookPages.splice(from, 1)[0];
    bookPages.splice(to, 0, page);
    renderPages();
}

function addPage() {
    const name = prompt('Nombre de la nueva página:');
    if (!name) return;
    const position = prompt('Posición (completa, superior, inferior):', 'completa');
    let pos = 'full';
    if (position && position.toLowerCase().startsWith('s')) pos = 'top';
    else if (position && position.toLowerCase().startsWith('i')) pos = 'bottom';

    bookPages.push({ id: Date.now(), content: name, position: pos });
    renderPages();
}

function editPage(idx) {
    const page = bookPages[idx];
    const name = prompt('Editar nombre de la página:', page.content);
    if (!name) return;

    // Mantener la posición actual si no se quiere cambiar
    // O preguntar si se quiere cambiar
    // Simplificado: solo editar nombre por ahora para ser más rápido, 
    // la posición se cambia con el selector

    bookPages[idx] = { ...page, content: name };
    renderPages();
}

function deletePage(idx) {
    if (confirm('¿Eliminar esta página?')) {
        bookPages.splice(idx, 1);
        renderPages();
    }
}

function savePages(bookId) {
    // Add page_number to each page based on current order
    const pagesWithNumbers = bookPages.map((page, index) => ({
        ...page,
        page_number: index + 1,
        book_id: bookId
    }));

    const versionId = window.versionId || null;
    const versionName = versionId ?
        (window.bookVersions?.find(v => v.id == versionId)?.name || 'Versión actual') :
        'Versión Original';

    // Confirm before saving if there's a version selected
    if (versionId) {
        if (!confirm(`¿Guardar cambios en "${versionName}"?\n\nEsto sobrescribirá las páginas existentes de esta versión.`)) {
            return;
        }
    }

    const payload = {
        book_id: bookId,
        pages: pagesWithNumbers,
        version_id: versionId
    };

    // Show loading state
    const saveBtn = event?.target;
    // Buscar el botón si event.target es el icono
    const btn = saveBtn?.closest('button') || saveBtn;

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:0.5rem;"></i> Guardando...';
    }

    fetch('index.php?page=book_page_api&action=savePages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`✓ Páginas guardadas correctamente en "${versionName}"`);
            } else {
                alert('❌ Error al guardar: ' + (data.error || 'Desconocido'));
            }
        })
        .catch(err => {
            console.error('Error saving pages:', err);
            alert('❌ Error de red al guardar páginas');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save" style="margin-right:0.5rem;"></i> Guardar libro';
            }
        });
}

// Inicialización y Event Delegation
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('book-pages-list');

    if (container) {
        // Event delegation para clicks (editar/eliminar)
        container.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const idx = parseInt(btn.dataset.idx);
            if (isNaN(idx)) return;

            if (btn.classList.contains('edit-btn')) {
                editPage(idx);
            } else if (btn.classList.contains('delete-btn')) {
                deletePage(idx);
            }
        });

        // Event delegation para cambios en selectores
        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('page-type-selector')) {
                const idx = parseInt(e.target.dataset.idx);
                if (!isNaN(idx)) {
                    bookPages[idx].position = e.target.value;
                    // No necesitamos re-renderizar todo, solo actualizar el modelo
                    // Visualmente ya cambió el select
                }
            }
        });
    }

    renderPages();

    const addBtn = document.getElementById('add-page-btn');
    if (addBtn) {
        addBtn.onclick = addPage;
    }
});
