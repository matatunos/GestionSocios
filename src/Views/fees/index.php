<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Gestión de Cuotas Anuales</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Define New Fee -->
    <div class="card">
        <h2 class="text-lg font-semibold mb-4">Definir Nueva Cuota</h2>
        <form action="index.php?page=fees&action=store" method="POST">
            <div class="form-group">
                <label class="form-label">Año</label>
                <input type="number" name="year" class="form-control" value="<?php echo date('Y') + 1; ?>" required>
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
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                Se han generado <?php echo $_GET['count']; ?> pagos pendientes.
            </div>
        <?php endif; ?>

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
