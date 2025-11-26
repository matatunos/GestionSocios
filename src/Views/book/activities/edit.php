<?php ob_start(); ?>

<div class="mb-6">
    <a href="index.php?page=book_activities&year=<?php echo $activity->year; ?>" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Editar Actividad</h1>
</div>

<div class="card max-w-2xl">
    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=book_activities&action=update&id=<?php echo $activity->id; ?>" enctype="multipart/form-data">
        <input type="hidden" name="year" value="<?php echo $activity->year; ?>">

        <div class="form-group mb-4">
            <label class="form-label">Título <span class="text-red-500">*</span></label>
            <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($activity->title); ?>">
        </div>

        <div class="form-group mb-4">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($activity->description); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="form-group">
                <label class="form-label">Número de Página</label>
                <input type="number" name="page_number" class="form-control" value="<?php echo $activity->page_number; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Orden de Visualización</label>
                <input type="number" name="display_order" class="form-control" value="<?php echo $activity->display_order; ?>">
                <small class="text-gray-500 text-xs">Menor número aparece primero</small>
            </div>
        </div>

        <div class="form-group mb-6">
            <label class="form-label">Imagen / Foto</label>
            
            <?php if ($activity->image_url): ?>
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Imagen Actual:</p>
                    <img src="<?php echo htmlspecialchars($activity->image_url); ?>" alt="Current Image" class="h-48 w-auto rounded-lg shadow-sm object-cover border border-gray-200 dark:border-gray-600">
                </div>
            <?php endif; ?>

            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-indigo-500 transition-colors">
                <div class="space-y-1 text-center">
                    <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                        <label for="image-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                            <span>Cambiar imagen</span>
                            <input id="image-upload" name="image" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                        </label>
                        <p class="pl-1">o arrastrar y soltar</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 5MB</p>
                </div>
            </div>
            <div id="image-preview" class="mt-4 hidden">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vista previa nueva imagen:</p>
                <img src="" alt="Preview" class="h-48 w-auto rounded-lg shadow-sm object-cover">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
            <a href="index.php?page=book_activities&year=<?php echo $activity->year; ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Actividad
            </button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}
</script>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../../layout.php'; ?>
