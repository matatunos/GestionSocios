<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Nuevo Donante</h1>
    <a href="index.php?page=donors" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=donors&action=store">
        <div class="form-group">
            <label for="name">Nombre del Negocio / Donante</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="contact_person">Persona de Contacto</label>
                <input type="text" id="contact_person" name="contact_person" class="form-control">
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control">
        </div>

        <div class="form-group">
            <label for="address">Dirección</label>
            <textarea id="address" name="address" class="form-control" rows="3"></textarea>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Donante
            </button>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
