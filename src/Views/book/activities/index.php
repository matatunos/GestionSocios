<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-calendar-day mr-2 text-purple-600"></i> Actividades Libro <?php echo $year; ?>
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Gestión de contenidos, saludas y programa</p>
    </div>
    
    <div class="flex gap-2">
        <form action="index.php" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="page" value="book_activities">
            <select name="year" onchange="this.form.submit()" class="form-select rounded-lg border-gray-300">
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
    <div class="alert alert-success mb-4">
        <i class="fas fa-check-circle"></i>
        <?php 
        if ($_GET['msg'] == 'created') echo 'Actividad creada correctamente.';
        elseif ($_GET['msg'] == 'updated') echo 'Actividad actualizada correctamente.';
        elseif ($_GET['msg'] == 'deleted') echo 'Actividad eliminada correctamente.';
        ?>
    </div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                <tr>
                    <th class="px-6 py-3 w-16">Orden</th>
                    <th class="px-6 py-3 w-24">Imagen</th>
                    <th class="px-6 py-3">Título / Descripción</th>
                    <th class="px-6 py-3 w-24">Página</th>
                    <th class="px-6 py-3 w-32 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($activities)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No hay actividades registradas para este año.
                            <br>
                            <a href="index.php?page=book_activities&action=create&year=<?php echo $year; ?>" class="text-indigo-600 hover:underline mt-2 inline-block">
                                Crear la primera actividad
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <td class="px-6 py-4 text-gray-500">
                                <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    <?php echo $activity['display_order']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($activity['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($activity['image_url']); ?>" 
                                         alt="Imagen" 
                                         class="h-12 w-12 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                                <?php else: ?>
                                    <div class="h-12 w-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-400">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($activity['title']); ?>
                                </div>
                                <?php if ($activity['description']): ?>
                                    <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                <?php echo $activity['page_number'] ? 'Pág. ' . $activity['page_number'] : '-'; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="index.php?page=book_activities&action=edit&id=<?php echo $activity['id']; ?>" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?page=book_activities&action=delete&id=<?php echo $activity['id']; ?>" 
                                       class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                       onclick="return confirm('¿Estás seguro de eliminar esta actividad?')"
                                       title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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
