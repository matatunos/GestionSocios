// Prototipo frontend para gestión de páginas del libro
// Permite reordenar, añadir y borrar páginas
// Uso: incluir este script en la página de maquetación/exportación

let bookPages = window.bookPages ? [...window.bookPages] : [
    { id: 1, content: 'Portada', position: 'full' },
    { id: 2, content: 'Índice', position: 'full' },
    { id: 3, content: 'Página 1', position: 'full' },
    { id: 4, content: 'Página 2', position: 'full' }
];

function renderPages() {
    const container = document.getElementById('book-pages-list');
    container.innerHTML = '';
    bookPages.forEach((page, idx) => {
        const div = document.createElement('div');
        div.className = 'book-page-block';
        div.draggable = true;
        div.dataset.idx = idx;
        div.innerHTML = `
            <span class="page-title">${page.content}</span>
            <select class="page-type-selector" data-idx="${idx}" style="margin-left:10px;">
                <option value="full" ${page.position === 'full' ? 'selected' : ''}>Completa</option>
                <option value="top" ${page.position === 'top' ? 'selected' : ''}>Superior</option>
                <option value="bottom" ${page.position === 'bottom' ? 'selected' : ''}>Inferior</option>
            </select>
            <button onclick="editPage(${idx})">✏️</button>
            <button onclick="deletePage(${idx})">🗑️</button>
        `;
        // Selector de tipo de página
        setTimeout(() => {
            const selector = div.querySelector('.page-type-selector');
            if (selector) {
                selector.addEventListener('change', function(e) {
                    const idx = parseInt(this.dataset.idx);
                    bookPages[idx].position = this.value;
                    renderPages();
                });
            }
        }, 0);
        div.ondragstart = (e) => {
            e.dataTransfer.setData('text/plain', idx);
        };
        div.ondragover = (e) => e.preventDefault();
        div.ondrop = (e) => {
            e.preventDefault();
            const from = parseInt(e.dataTransfer.getData('text/plain'));
            const to = idx;
            movePage(from, to);
        };
        container.appendChild(div);
    });
}

function getPositionLabel(pos) {
    if (pos === 'top') return 'Superior';
    if (pos === 'bottom') return 'Inferior';
    return 'Completa';
}

function movePage(from, to) {
    if (from === to) return;
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
    const position = prompt('Editar posición (completa, superior, inferior):', getPositionLabel(page.position));
    let pos = 'full';
    if (position && position.toLowerCase().startsWith('s')) pos = 'top';
    else if (position && position.toLowerCase().startsWith('i')) pos = 'bottom';
    bookPages[idx] = { ...page, content: name, position: pos };
    renderPages();
}

function deletePage(idx) {
    if (confirm('¿Eliminar esta página?')) {
        bookPages.splice(idx, 1);
        renderPages();
    }
}

function savePages(bookId) {
    fetch('index.php?page=book_page_api&action=savePages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ book_id: bookId, pages: bookPages })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Páginas guardadas correctamente');
        } else {
            alert('Error al guardar: ' + (data.error || 'Desconocido'));
        }
    })
    .catch(() => alert('Error de red al guardar páginas'));
}

document.addEventListener('DOMContentLoaded', () => {
    renderPages();
    document.getElementById('add-page-btn').onclick = addPage;
    // Botón guardar
    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Guardar cambios';
    saveBtn.className = 'btn btn-success';
    saveBtn.style.marginLeft = '1rem';
    saveBtn.onclick = function() {
        const bookId = window.bookId || prompt('ID del libro:', '2025');
        if (bookId) savePages(bookId);
    };
    document.getElementById('add-page-btn').after(saveBtn);
});
