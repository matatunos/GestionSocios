<?php ob_start(); ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'generated'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Se han generado <?php echo intval($_GET['count'] ?? 0); ?> pagos pendientes para el año <?php echo htmlspecialchars($_GET['year'] ?? '', ENT_QUOTES); ?>.
    </div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Cuota para el año <?php echo htmlspecialchars($_GET['year'] ?? '', ENT_QUOTES); ?> creada correctamente.
    </div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Cuota para el año <?php echo htmlspecialchars($_GET['year'] ?? '', ENT_QUOTES); ?> actualizada correctamente.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'no_fee_defined'): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        No hay cuota definida para ese año. Por favor, defínela primero.
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="flex justify-between items-center mb-4">
    <h1>Gestión de Cuotas Anuales</h1>
    <?php
    // Check if fee for current year exists
    $currentYear = date('Y');
    $hasFeeThisYear = false;
    foreach ($fees as $fee) {
        if ($fee['year'] == $currentYear) {
            $hasFeeThisYear = true;
            break;
        }
    }
    if ($hasFeeThisYear): ?>
        <a href="index.php?page=fees&action=generate&year=<?php echo $currentYear; ?>" 
           class="btn btn-primary"
           onclick="return confirm('¿Generar pagos pendientes para todos los socios activos para el año <?php echo $currentYear; ?>?');">
            <i class="fas fa-file-invoice-dollar"></i> Generar Pagos <?php echo $currentYear; ?>
        </a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Define New Fee -->
    <div class="card">
        <h2 class="text-lg font-semibold mb-4">Definir Nueva Cuota</h2>
        <form action="index.php?page=fees&action=store" method="POST">
            <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
            <div class="form-group">
                <label class="form-label">Año</label>
                <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Importe (€)</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-full">
                <i class="fas fa-save"></i> Guardar Cuota
            </button>
        </form>
    </div>

    <!-- List Fees -->
    <div class="card">
        <h2 class="text-lg font-semibold mb-4">Cuotas Definidas</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Importe</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fees as $fee): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $fee['year']; ?></td>
                        <td><?php echo number_format($fee['amount'], 2); ?> €</td>
                        <td>
                            <a href="index.php?page=fees&action=generate&year=<?php echo $fee['year']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('¿Generar pagos pendientes para todos los socios activos para el año <?php echo $fee['year']; ?>?');">
                                <i class="fas fa-file-invoice-dollar"></i> Generar Pagos
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
