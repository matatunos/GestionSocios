<?php ob_start(); ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
            if ($_GET['msg'] === 'deleted') echo 'Anuncio eliminado correctamente.';
        ?>
    </div>
<?php endif; ?>

<div class="flex justify-between items-center mb-4">
    <div class="flex items-center gap-4">
        <h1>Libro de Fiestas</h1>
        <form action="index.php" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="page" value="book">
            <select name="year" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.25rem 0.5rem;">
                <?php 
                $currentYear = date('Y');
                for($y = $currentYear + 1; $y >= $currentYear - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
    <a href="index.php?page=book&action=create&year=<?php echo $year; ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Anuncio
    </a>
</div>

<div class="grid grid-3 mb-4">
    <div class="card bg-primary-50">
        <div class="text-sm text-muted">Total Anuncios</div>
        <div class="text-2xl font-bold text-primary-700"><?php echo count($ads); ?></div>
    </div>
    <div class="card bg-success-50">
        <div class="text-sm text-muted">Ingresos Totales</div>
        <div class="text-2xl font-bold text-success-700"><?php echo number_format($totalRevenue, 2); ?> €</div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Donante / Negocio</th>
                    <th>Tipo de Anuncio</th>
                    <th>Importe</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ads)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay anuncios registrados para este año.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ads as $row): ?>
                        <tr>
                            <td style="font-weight: 500;">
                                <?php echo htmlspecialchars($row['donor_name']); ?>
                            </td>
                            <td>
                                <?php 
                                    $types = [
                                        'media' => 'Media Página',
                                        'full' => 'Página Completa',
                                        'cover' => 'Portada',
                                        'back_cover' => 'Contraportada'
                                    ];
                                    echo $types[$row['ad_type']] ?? $row['ad_type']; 
                                ?>
                            </td>
                            <td style="font-family: monospace; font-size: 1rem;">
                                <?php echo number_format($row['amount'], 2); ?> €
                            </td>
                            <td>
                                <span class="badge <?php echo $row['status'] === 'paid' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $row['status'] === 'paid' ? 'Pagado' : 'Pendiente'; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="index.php?page=book&action=delete&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Estás seguro de eliminar este anuncio?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
