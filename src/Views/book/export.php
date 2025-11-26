<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-file-pdf mr-2 text-gray-600"></i> Maquetación y Exportación <?php echo $year; ?>
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Organiza el contenido y genera el PDF para imprenta</p>
    </div>
    
    <div class="flex gap-2">
        <form action="index.php" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="page" value="book_export">
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
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Actividades</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count($activities); ?>
                </h3>
            </div>
            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <i class="fas fa-calendar-day text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Páginas de contenido
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Anuncios</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count($ads); ?>
                </h3>
            </div>
            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <i class="fas fa-ad text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Páginas publicitarias
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Páginas</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo count($activities) + count($ads) + 1; ?>
                </h3>
            </div>
            <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <i class="fas fa-book text-green-600 dark:text-green-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Incluyendo portada
        </div>
    </div>
</div>

<!-- Export Actions -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-8">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
        <i class="fas fa-download mr-2"></i> Generar Documento
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="index.php?page=book_export&action=generatePdf&year=<?php echo $year; ?>" 
           class="group flex items-center gap-4 p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all">
            <div class="p-3 bg-red-100 text-red-600 rounded-lg group-hover:bg-red-600 group-hover:text-white transition-colors">
                <i class="fas fa-file-pdf text-2xl"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-gray-900 dark:text-white">Descargar PDF</h4>
                <p class="text-sm text-gray-500">Documento listo para imprenta</p>
            </div>
            <i class="fas fa-arrow-right text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
        </a>

        <div class="flex items-center gap-4 p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg opacity-50">
            <div class="p-3 bg-gray-100 text-gray-400 rounded-lg">
                <i class="fas fa-file-word text-2xl"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-gray-900 dark:text-white">Exportar DOCX</h4>
                <p class="text-sm text-gray-500">Próximamente</p>
            </div>
        </div>
    </div>
</div>

<!-- Content Preview -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <h3 class="font-bold text-gray-900 dark:text-white">Vista Previa del Contenido</h3>
        <p class="text-sm text-gray-500 mt-1">El PDF se generará en el siguiente orden:</p>
    </div>
    
    <div class="p-6">
        <ol class="space-y-3">
            <li class="flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-sm">1</span>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Portada</p>
                    <p class="text-sm text-gray-500">Título y año del libro</p>
                </div>
            </li>
            
            <?php $pageNum = 2; ?>
            <?php foreach ($activities as $activity): ?>
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-sm"><?php echo $pageNum++; ?></span>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['title']); ?></p>
                        <p class="text-sm text-gray-500">Actividad</p>
                    </div>
                    <?php if ($activity['image_url']): ?>
                        <i class="fas fa-image text-purple-400"></i>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            
            <?php foreach ($ads as $ad): ?>
                <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm"><?php echo $pageNum++; ?></span>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($ad['donor_name']); ?></p>
                        <p class="text-sm text-gray-500">Anuncio - <?php echo ucfirst($ad['ad_type']); ?></p>
                    </div>
                    <?php if ($ad['image_url']): ?>
                        <i class="fas fa-image text-blue-400"></i>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
        
        <?php if (empty($activities) && empty($ads)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>No hay contenido para exportar en este año.</p>
                <p class="text-sm mt-2">Añade actividades o anuncios para generar el libro.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout.php'; ?>
