<?php
// Igual que create.php pero con campos pre-rellenados
ob_start();
?>
<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Editar Subvención</h1>
        <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <form method="POST" action="index.php?page=grants&action=update&id=<?php echo $grant['id']; ?>" class="form-card">
        <!-- Igual estructura que create.php pero con value="<?php echo htmlspecialchars($grant['campo']); ?>" -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
            <a href="index.php?page=grants&action=view&id=<?php echo $grant['id']; ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
