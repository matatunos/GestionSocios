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
            <select name="version_id" onchange="this.form.submit()" class="form-select" style="max-width: 200px;">
                <option value="">Versión Original</option>
                <?php foreach (($bookVersions ?? []) as $v): ?>
                    <option value="<?php echo $v['id']; ?>" <?php echo ($v['id'] == ($version_id ?? 0)) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        
        <?php if (!empty($version_id)): ?>
            <button type="button" class="btn btn-danger" onclick="borrarVersion()" style="margin-left: 0.5rem;" title="Borrar esta versión">
                <i class="fas fa-trash"></i>
            </button>
        <?php endif; ?>

        <button type="button" class="btn btn-secondary" onclick="crearNuevaVersion()" style="margin-left: 0.5rem;">Nueva versión</button>
    </div>
</div>

<!-- ... (rest of the file) ... -->

<script>
window.bookPages = <?php echo json_encode($editorBlocks ?? []); ?>;
window.bookVersions = <?php echo json_encode($bookVersions ?? []); ?>;
window.versionId = <?php echo json_encode($version_id ?? 0); ?>;
window.availableActivities = <?php echo json_encode($activities ?? []); ?>;
window.availableAds = <?php echo json_encode($ads ?? []); ?>;

function crearNuevaVersion() {
    var nombre = prompt('Nombre de la nueva versión:');
    if (!nombre) return;
    
    // Pass year to ensure book is created if it doesn't exist
    const year = <?php echo json_encode($year); ?>;
    
    fetch('index.php?page=book_page_api&action=createVersion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            book_id: <?php echo json_encode($book_id ?? 0); ?>, 
            name: nombre,
            year: year 
        })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('HTTP error! status: ' + res.status);
        }
        return res.json();
    })
    .then(data => {
        if (data.success && data.version_id) {
            window.location.href = 'index.php?page=book_export&year=' + year + '&version_id=' + data.version_id;
        } else {
            alert('Error al crear versión: ' + (data.error || 'Desconocido'));
        }
    })
    .catch(err => {
        console.error('Error creating version:', err);
        alert('Error de red al crear versión: ' + err.message);
    });
}

function borrarVersion() {
    if (!window.versionId) return;
    
    if (!confirm('¿Estás seguro de que quieres borrar esta versión? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch('index.php?page=book_page_api&action=deleteVersion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ version_id: window.versionId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Versión eliminada correctamente');
            // Redirect to original version (no version_id)
            window.location.href = 'index.php?page=book_export&year=<?php echo $year; ?>';
        } else {
            alert('Error al borrar versión: ' + (data.error || 'Desconocido'));
        }
    })
    .catch(err => {
        console.error('Error deleting version:', err);
        alert('Error de red al borrar versión');
    });
}
</script>
<script src="/js/book_pages_editor.js?v=<?php echo time(); ?>"></script>

<!-- Modal para añadir página -->
<div id="add-page-modal" class="modal-overlay">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h3 style="margin:0;">Añadir Página</h3>
            <button onclick="closeAddModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <div style="border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; display: flex; gap: 1rem;">
            <button class="tab-btn active" onclick="switchTab('custom')">Personalizada</button>
            <button class="tab-btn" onclick="switchTab('activities')">Actividades</button>
            <button class="tab-btn" onclick="switchTab('ads')">Anuncios</button>
        </div>

        <div id="tab-custom" class="tab-content">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem;">Título de la página</label>
                <input type="text" id="new-page-title" class="form-control" placeholder="Ej: Introducción">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem;">Posición</label>
                <select id="new-page-pos" class="form-select">
                    <option value="full">Completa</option>
                    <option value="top">Superior (Media)</option>
                    <option value="bottom">Inferior (Media)</option>
                </select>
            </div>
            <button onclick="addCustomPage()" class="btn btn-primary" style="width:100%;">Añadir Página</button>
        </div>

        <div id="tab-activities" class="tab-content" style="display:none;">
            <p style="color:var(--text-muted); font-size:0.9em;">Selecciona una actividad para añadirla al libro.</p>
            <div id="activities-list" class="content-grid"></div>
        </div>

        <div id="tab-ads" class="tab-content" style="display:none;">
            <p style="color:var(--text-muted); font-size:0.9em;">Selecciona un anuncio para añadirlo al libro.</p>
            <div id="ads-list" class="content-grid"></div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout.php'; ?>
