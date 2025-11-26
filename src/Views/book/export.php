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
                    <?php echo count($activities); ?>
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
                    <?php echo count($ads); ?>
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
                    <?php echo count($activities) + count($ads) + 1; ?>
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
        <a href="index.php?page=book_export&action=generatePdf&year=<?php echo $year; ?>" 
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

<!-- Content Preview -->
<div class="card" style="padding: 0; overflow: hidden;">
<!-- Editor de páginas del libro -->
<div class="card" style="margin: 2rem 0; padding: 1.5rem;">
    <h3 style="margin-top:0;">Editor de páginas del libro</h3>
    <div id="book-pages-list" style="margin-bottom: 1rem; min-height: 60px; background: #f8fafc; border: 1px dashed #bbb; padding: 1rem; border-radius: 8px;"></div>
        <button onclick="savePages(<?php echo $book_id ?? 0; ?>)" class="btn btn-primary" style="margin-top: 1rem;">
            <i class="fas fa-save" style="margin-right:0.5rem;"></i> Guardar libro
        </button>
    <button id="add-page-btn" class="btn btn-primary" type="button">Añadir página</button>
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
        transition: box-shadow 0.2s;
    }
    .book-page-block:active {
        box-shadow: 0 2px 8px #aaa;
        background: #f3f4f6;
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
    }
</style>
<script>
window.bookPages = <?php echo json_encode($editorBlocks ?? []); ?>;
window.bookVersions = <?php echo json_encode($bookVersions ?? []); ?>;
window.versionId = <?php echo json_encode($version_id ?? 0); ?>;
function crearNuevaVersion() {
    var nombre = prompt('Nombre de la nueva versión:');
    if (!nombre) return;
    // Aquí deberías hacer una petición AJAX para crear la versión y recargar la página
    fetch('index.php?page=book_page_api&action=createVersion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ book_id: <?php echo json_encode($book_id ?? 0); ?>, name: nombre })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.version_id) {
            window.location.href = 'index.php?page=book_export&year=<?php echo $year; ?>&version_id=' + data.version_id;
        } else {
            alert('Error al crear versión: ' + (data.error || 'Desconocido'));
        }
    })
    .catch(() => alert('Error de red al crear versión'));
}
</script>
<script src="/js/book_pages_editor.js"></script>
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-light);">
        <h3 style="margin: 0;">Vista Previa del Contenido</h3>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0.5rem 0 0 0;">El PDF se generará en el siguiente orden:</p>
    </div>
    
    <div style="padding: 1.5rem;">
        <ol style="list-style: none; padding: 0; margin: 0;">
            <li style="display: flex; align-items: start; gap: 1rem; padding: 0.75rem; border-bottom: 1px solid var(--border-light);">
                <span style="flex-shrink: 0; width: 32px; height: 32px; background: var(--primary-100); color: var(--primary-600); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;">1</span>
                <div>
                    <p style="margin: 0; font-weight: 500; color: var(--text-main);">Portada</p>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: var(--text-muted);">Título y año del libro</p>
                </div>
            </li>
            
            <?php $pageNum = 2; ?>
            <?php foreach ($activities as $activity): ?>
                <li style="display: flex; align-items: start; gap: 1rem; padding: 0.75rem; border-bottom: 1px solid var(--border-light);">
                    <span style="flex-shrink: 0; width: 32px; height: 32px; background: #f3e8ff; color: #a855f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;"><?php echo $pageNum++; ?></span>
                    <div style="flex: 1;">
                        <p style="margin: 0; font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($activity['title']); ?></p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: var(--text-muted);">Actividad</p>
                    </div>
                    <?php if ($activity['image_url']): ?>
                        <i class="fas fa-image" style="color: #a855f7;"></i>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            
            <?php foreach ($ads as $ad): ?>
                <li style="display: flex; align-items: start; gap: 1rem; padding: 0.75rem; border-bottom: 1px solid var(--border-light);">
                    <span style="flex-shrink: 0; width: 32px; height: 32px; background: #dbeafe; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;"><?php echo $pageNum++; ?></span>
                    <div style="flex: 1;">
                        <p style="margin: 0; font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($ad['donor_name']); ?></p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: var(--text-muted);">Anuncio - <?php echo ucfirst($ad['ad_type']); ?></p>
                    </div>
                    <?php if ($ad['image_url']): ?>
                        <i class="fas fa-image" style="color: #3b82f6;"></i>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
        
        <?php if (empty($activities) && empty($ads)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <p style="margin: 0;">No hay contenido para exportar en este año.</p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">Añade actividades o anuncios para generar el libro.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout.php'; ?>
