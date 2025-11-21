<?php ob_start(); ?>

<div class="card" style="max-width: 600px; margin: 2rem auto; text-align: center;">
    <h1>Actualización de Base de Datos</h1>
    <p class="mb-4">Se aplicarán los cambios necesarios para las nuevas funcionalidades (Fotos, Cuotas, Eventos).</p>

    <?php if (isset($message) && $message): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; text-align: left;">
            <?php echo $message; ?>
        </div>
        <a href="index.php?page=dashboard" class="btn btn-primary">Ir al Dashboard</a>
    <?php else: ?>
        <form action="index.php?page=update" method="POST">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync"></i> Actualizar Ahora
            </button>
        </form>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
