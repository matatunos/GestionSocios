<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h1><i class="fas fa-file-pdf" style="color: var(--text-muted); margin-right: 0.5rem;"></i> Maquetación y Exportación <?php echo $year; ?></h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">Organiza el contenido y genera el PDF para imprenta</p>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
        <form action="index.php" method="GET" style="display: inline-flex; align-items: center;">
            <input type="hidden" name="page" value="book_export">
            <select name="year" onchange="this.form.submit()" class="form-select">
                <?php 
                $currentYear = date('Y');
                for($y = $currentYear + 1; $y >= $currentYear - 5; $y--): 
                ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
        <!-- Selector de versión -->
        <form action="index.php" method="GET" style="display: inline-flex; align-items: center;">
            <input type="hidden" name="page" value="book_export">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <select name="version_id" onchange="this.form.submit()" class="form-select">
                <option value="">Versión Original</option>
                <?php foreach (($bookVersions ?? []) as $v): ?>
                    <option value="<?php echo $v['id']; ?>" <?php echo ($v['id'] == ($version_id ?? 0)) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <button type="button" class="btn btn-secondary" onclick="crearNuevaVersion()" style="margin-left: 0.5rem;">Nueva versión</button>
    </div>
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Actividades</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--text-main);">
                    <?php echo count($activities ?? []); ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: #f3e8ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-day" style="font-size: 1.5rem; color: #a855f7;"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            Páginas de contenido
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Anuncios</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--text-main);">
                    <?php echo count($ads ?? []); ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: #dbeafe; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-ad" style="font-size: 1.5rem; color: #3b82f6;"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            Páginas publicitarias
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Total Páginas</p>
                <h2 style="margin: 0.5rem 0; font-size: 2rem; color: var(--text-main);">
                    <?php echo count($activities ?? []) + count($ads ?? []) + 1; ?>
                </h2>
            </div>
            <div style="width: 48px; height: 48px; background: var(--secondary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-book" style="font-size: 1.5rem; color: var(--secondary-600);"></i>
            </div>
        </div>
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            Incluyendo portada
        </div>
    </div>
</div>

<!-- Export Actions -->
<div class="card" style="margin-bottom: 2rem;">
    <h3 style="margin-bottom: 1.5rem;">
        <i class="fas fa-download" style="margin-right: 0.5rem;"></i> Generar Documento
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
        <a href="index.php?page=book_export&action=generatePdf&year=<?php echo $year; ?>&version_id=<?php echo $version_id ?? ''; ?>" 
           class="card" 
           style="text-decoration: none; display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 2px solid var(--border-light); transition: all 0.2s; cursor: pointer;"
           onmouseover="this.style.borderColor='var(--primary-600)'; this.style.background='var(--primary-50)';"
           onmouseout="this.style.borderColor='var(--border-light)'; this.style.background='';">
            <div style="width: 48px; height: 48px; background: #fee2e2; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-file-pdf" style="font-size: 1.5rem; color: #dc2626;"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0; color: var(--text-main);">Descargar PDF</h4>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: var(--text-muted);">Documento listo para imprenta</p>
            </div>
            <i class="fas fa-arrow-right" style="color: var(--text-muted);"></i>
        </a>

        <div class="card" 
             style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border: 2px solid var(--border-light); opacity: 0.5;">
            <div style="width: 48px; height: 48px; background: var(--bg-glass); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-file-word" style="font-size: 1.5rem; color: var(--text-muted);"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0; color: var(--text-main);">Exportar DOCX</h4>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: var(--text-muted);">Próximamente</p>
            </div>
        </div>
    </div>
</div>

<!-- Editor de páginas del libro -->
<div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
    <h3 style="margin-top:0;">Editor de páginas del libro</h3>
    <div id="book-pages-list" style="margin-bottom: 1rem; min-height: 60px; background: #f8fafc; border: 1px dashed #bbb; padding: 1rem; border-radius: 8px;"></div>
    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <button onclick="savePages(<?php echo $book_id ?? 0; ?>)" class="btn btn-primary">
            <i class="fas fa-save" style="margin-right:0.5rem;"></i> Guardar libro
        </button>
        <button id="add-page-btn" class="btn btn-secondary" type="button">
            <i class="fas fa-plus" style="margin-right:0.5rem;"></i> Añadir página
        </button>
    </div>
    <p style="color: var(--text-muted); margin-top: 1rem;">Arrastra los bloques para reordenar. Haz clic en ✏️ para editar o 🗑️ para eliminar.</p>
    <div style="color: #888; font-size: 0.95em; margin-top: 0.5rem;">Si no ves bloques, añade una página o revisa que existan páginas en el libro.</div>
</div>


<style>
    .book-page-block {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        margin-bottom: 8px;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: grab;
        transition: all 0.2s ease;
    }
    .book-page-block:hover {
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .book-page-block.dragging {
        opacity: 0.5;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
    }
    .book-page-block.drag-over {
        border-top: 2px solid var(--primary-500);
        background: var(--primary-50);
        transform: translateY(2px);
    }
    .book-page-block .page-title {
        flex: 1;
        font-weight: 500;
    }
    .book-page-block button {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.1em;
        margin-left: 4px;
        padding: 4px;
        border-radius: 4px;
        transition: background 0.2s;
    }
    .book-page-block button:hover {
        background: rgba(0,0,0,0.05);
    }
</style>
<script>
window.bookPages = <?php echo json_encode($editorBlocks ?? []); ?>;
window.bookVersions = <?php echo json_encode($bookVersions ?? []); ?>;
window.versionId = <?php echo json_encode($version_id ?? 0); ?>;
function crearNuevaVersion() {
    var nombre = prompt('Nombre de la nueva versión:');
    if (!nombre) return;
    fetch('index.php?page=book_page_api&action=createVersion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ book_id: <?php echo json_encode($book_id ?? 0); ?>, name: nombre })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('HTTP error! status: ' + res.status);
        }
        return res.json();
    })
    .then(data => {
        console.log('Response:', data);
        if (data.success && data.version_id) {
            window.location.href = 'index.php?page=book_export&year=<?php echo $year; ?>&version_id=' + data.version_id;
        } else {
            alert('Error al crear versión: ' + (data.error || 'Desconocido') + (data.details ? '\n\nDetalles: ' + data.details : ''));
        }
    })
    .catch(err => {
        console.error('Error creating version:', err);
        alert('Error de red al crear versión: ' + err.message);
    });
}
</script>
<script src="/js/book_pages_editor.js"></script>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout.php'; ?>
