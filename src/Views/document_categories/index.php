<?php ob_start(); ?>
<div class="mb-4">
    <a href="index.php?page=documents" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver a documentos
    </a>
    <h1>Gestión de Categorías de Documentos</h1>
</div>
<div class="card" style="max-width: 600px;">
    <h3 class="mb-3">Añadir nueva categoría</h3>
    <form method="POST" action="index.php?page=document_categories&action=create">
        <div class="form-group mb-2">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" name="name" id="name" class="form-control" required maxlength="100">
        </div>
        <div class="form-group mb-2">
            <label for="description" class="form-label">Descripción</label>
            <input type="text" name="description" id="description" class="form-control" maxlength="255">
        </div>
        <div class="form-group mb-2">
            <label for="color" class="form-label">Color</label>
            <input type="color" name="color" id="color" class="form-control" value="#6366f1">
        </div>
        <button type="submit" class="btn btn-primary">Añadir Categoría</button>
    </form>
</div>
<div class="card mt-4" style="max-width: 600px;">
    <h3 class="mb-3">Categorías existentes</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Color</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                <td><span style="background:<?php echo htmlspecialchars($cat['color']); ?>;padding:0.5em 1em;border-radius:8px;display:inline-block;"></span></td>
                <td>
                    <form method="POST" action="index.php?page=document_categories&action=delete" style="display:inline;" onsubmit="return confirm('¿Eliminar esta categoría?');">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>
