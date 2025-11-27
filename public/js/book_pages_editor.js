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
        div.className = `book-page-block type-${page.type || 'default'}`;
        div.draggable = true;
        div.dataset.idx = idx;

        // Icono según tipo
        let icon = '📄';
        if (page.type === 'activity') icon = '📅';
        else if (page.type === 'ad') icon = '📢';
        else if (page.type === 'cover') icon = 'book';

        // Contenido HTML
        div.innerHTML = `
            <span style="font-size:1.2em; margin-right:8px;">${icon}</span>
            <span class="page-title">
                ${page.content}
                ${page.image_url ? '<i class="fas fa-image" style="font-size:0.8em; color:#aaa; margin-left:5px;" title="Tiene imagen"></i>' : ''}
            </span>
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

function movePage(from, to) {
    if (from === to) return;
    // Mover el elemento en el array
    const page = bookPages.splice(from, 1)[0];
    bookPages.splice(to, 0, page);
    renderPages();
}

// --- MODAL LOGIC ---

function openAddModal() {
    const modal = document.getElementById('add-page-modal');
    if (modal) {
        modal.classList.add('active');
        renderActivitiesList();
        renderAdsList();
    }
}

function closeAddModal() {
    const modal = document.getElementById('add-page-modal');
    if (modal) modal.classList.remove('active');
}

function switchTab(tabName) {
    // Hide all contents
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    // Deactivate all buttons
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

    // Show selected
    document.getElementById('tab-' + tabName).style.display = 'block';
    // Activate button (need to find the button, simple way is by text or order, but let's assume onclick works)
    // Actually, we can just use event.target if called from click
    if (event && event.target) {
        event.target.classList.add('active');
    }
}

function addCustomPage() {
    const titleInput = document.getElementById('new-page-title');
    const posInput = document.getElementById('new-page-pos');

    const name = titleInput.value.trim();
    if (!name) {
        alert('Por favor escribe un título');
        return;
    }

    bookPages.push({
        id: 'custom_' + Date.now(),
        content: name,
        position: posInput.value,
        type: 'custom'
    });

    titleInput.value = '';
    renderPages();
    closeAddModal();
}

function renderActivitiesList() {
    const container = document.getElementById('activities-list');
    if (!container) return;

    const activities = window.availableActivities || [];

    container.innerHTML = activities.map(act => {
        // Check if used by ID (newly added) or by Content+Type (saved in DB)
        const isUsed = bookPages.some(p =>
            (p.id === 'activity_' + act.id) ||
            (p.type === 'activity' && p.content === act.title)
        );

        const img = act.image_url ? `<img src="${act.image_url}">` : '<div style="height:80px; background:#eee; display:flex; align-items:center; justify-content:center; margin-bottom:0.5rem;">📅</div>';

        return `
            <div class="content-item ${isUsed ? 'used' : ''}" onclick="${isUsed ? '' : `addContentPage('activity', '${act.id}', '${act.title.replace(/'/g, "\\'")}', '${act.image_url || ''}')`}">
                ${img}
                <div style="font-size:0.9em; font-weight:500;">${act.title}</div>
                ${isUsed ? '<div style="font-size:0.8em; color:green;">Ya añadido</div>' : ''}
            </div>
        `;
    }).join('');
}

function renderAdsList() {
    const container = document.getElementById('ads-list');
    if (!container) return;

    const ads = window.availableAds || [];

    container.innerHTML = ads.map(ad => {
        if (ad.status !== 'paid') return ''; // Skip unpaid

        // Check if used by ID (newly added) or by Content+Type (saved in DB)
        const isUsed = bookPages.some(p =>
            (p.id === 'ad_' + ad.id) ||
            (p.type === 'ad' && p.content === ad.donor_name)
        );

        const img = ad.image_url ? `<img src="${ad.image_url}">` : '<div style="height:80px; background:#eee; display:flex; align-items:center; justify-content:center; margin-bottom:0.5rem;">📢</div>';

        return `
            <div class="content-item ${isUsed ? 'used' : ''}" onclick="${isUsed ? '' : `addContentPage('ad', '${ad.id}', '${ad.donor_name.replace(/'/g, "\\'")}', '${ad.image_url || ''}')`}">
                ${img}
                <div style="font-size:0.9em; font-weight:500;">${ad.donor_name}</div>
                ${isUsed ? '<div style="font-size:0.8em; color:green;">Ya añadido</div>' : ''}
            </div>
        `;
    }).join('');
}

function addContentPage(type, id, title, imageUrl) {
    bookPages.push({
        id: type + '_' + id,
        content: title,
        position: 'full', // Default to full, user can change
        type: type,
        image_url: imageUrl || null
    });
    renderPages();
    closeAddModal();
}

// --- END MODAL LOGIC ---

function editPage(idx) {
    const page = bookPages[idx];
    const name = prompt('Editar nombre de la página:', page.content);
    if (!name) return;

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
        addBtn.onclick = openAddModal; // Changed to open modal
    }
});
