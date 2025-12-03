<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h1><i class="fas fa-calendar-day" style="color: #a855f7; margin-right: 0.5rem;"></i> Actividades Libro <?php echo $year; ?></h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">Gestión de contenidos, saludas y programa</p>
    </div>
    
    <div style="display: flex; gap: 0.5rem;">
        <form action="index.php" method="GET" style="display: inline-flex; align-items: center;">
            <input type="hidden" name="page" value="book_activities">
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
        
        <a href="index.php?page=book_activities&action=create&year=<?php echo $year; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Actividad
        </a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
        <i class="fas fa-check-circle"></i>
        <?php 
        if ($_GET['msg'] == 'created') echo 'Actividad creada correctamente.';
        elseif ($_GET['msg'] == 'updated') echo 'Actividad actualizada correctamente.';
        elseif ($_GET['msg'] == 'deleted') echo 'Actividad eliminada correctamente.';
        ?>
    </div>
<?php endif; ?>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container" style="border: none; border-radius: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">Orden</th>
                    <th style="width: 100px;">Imagen</th>
                    <th>Título / Descripción</th>
                    <th style="width: 100px;">Página</th>
                    <th style="width: 120px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                            No hay actividades registradas para este año.
                            <br><br>
                            <a href="index.php?page=book_activities&action=create&year=<?php echo $year; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Crear la primera actividad
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td>
                                <span class="badge badge-secondary" style="font-family: monospace;">
                                    <?php echo $activity['display_order']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($activity['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($activity['image_url']); ?>" 
                                         alt="Imagen" 
                                         style="height: 48px; width: 48px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-light);">
                                <?php else: ?>
                                    <div style="height: 48px; width: 48px; background: var(--bg-glass); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text-main);">
                                    <?php echo htmlspecialchars($activity['title']); ?>
                                </div>
                                <?php if ($activity['description']): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; max-width: 500px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--text-muted);">
                                <?php echo $activity['page_number'] ? 'Pág. ' . $activity['page_number'] : '-'; ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="index.php?page=book_activities&action=edit&id=<?php echo $activity['id']; ?>" 
                                   class="btn btn-sm btn-secondary"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="index.php?page=book_activities&action=delete&id=<?php echo $activity['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Estás seguro de eliminar esta actividad?')"
                                   title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layout.php'; ?>
