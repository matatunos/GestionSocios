<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-book-open mr-2 text-indigo-600"></i> Libro de Fiestas <?php echo $year; ?>
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Resumen financiero y gestión de contenidos</p>
    </div>
    
    <div class="flex gap-2">
        <form action="index.php" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="page" value="book">
            <input type="hidden" name="action" value="dashboard">
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

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Ingresos Totales -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos Confirmados</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($incomeStats['total_income'] ?? 0, 2); ?> €
                </h3>
            </div>
            <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <i class="fas fa-coins text-green-600 dark:text-green-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            <span class="text-yellow-600 font-medium">
                <?php echo number_format($incomeStats['pending_income'] ?? 0, 2); ?> €
            </span> pendientes
        </div>
    </div>

    <!-- Gastos -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gastos (Imprenta/Otros)</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($expenseStats['total_cost'] ?? 0, 2); ?> €
                </h3>
            </div>
            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <i class="fas fa-receipt text-red-600 dark:text-red-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            <?php echo $expenseStats['total_expenses'] ?? 0; ?> movimientos registrados
        </div>
    </div>

    <!-- Resultado Neto -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 <?php echo $netResult >= 0 ? 'border-indigo-500' : 'border-orange-500'; ?>">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Resultado Neto</p>
                <h3 class="text-2xl font-bold <?php echo $netResult >= 0 ? 'text-indigo-600' : 'text-orange-600'; ?> mt-1">
                    <?php echo number_format($netResult, 2); ?> €
                </h3>
            </div>
            <div class="p-2 <?php echo $netResult >= 0 ? 'bg-indigo-50' : 'bg-orange-50'; ?> rounded-lg">
                <i class="fas fa-chart-line <?php echo $netResult >= 0 ? 'text-indigo-600' : 'text-orange-600'; ?> text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Balance final estimado
        </div>
    </div>

    <!-- Estadísticas Anuncios -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Anuncios</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo $incomeStats['total_ads'] ?? 0; ?>
                </h3>
            </div>
            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <i class="fas fa-ad text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex gap-2 text-xs">
            <span class="px-2 py-1 bg-gray-100 rounded text-gray-600">Full: <?php echo $incomeStats['full_page_count']; ?></span>
            <span class="px-2 py-1 bg-gray-100 rounded text-gray-600">Media: <?php echo $incomeStats['half_page_count']; ?></span>
        </div>
    </div>
</div>

<!-- Main Actions Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Gestión Anunciantes -->
    <a href="index.php?page=book&year=<?php echo $year; ?>" class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-blue-100 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <i class="fas fa-users text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Anunciantes</h3>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
            Gestionar empresas, anuncios contratados y estados de pago.
        </p>
        <div class="text-blue-600 text-sm font-medium group-hover:translate-x-1 transition-transform inline-flex items-center">
            Gestionar anuncios <i class="fas fa-arrow-right ml-1"></i>
        </div>
    </a>

    <!-- Gestión Actividades -->
    <a href="index.php?page=book_activities&year=<?php echo $year; ?>" class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-purple-100 text-purple-600 rounded-lg group-hover:bg-purple-600 group-hover:text-white transition-colors">
                <i class="fas fa-calendar-day text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Actividades</h3>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
            Añadir páginas de contenido, saludas, programa de actos y fotos.
        </p>
        <div class="text-purple-600 text-sm font-medium group-hover:translate-x-1 transition-transform inline-flex items-center">
            Gestionar contenido <i class="fas fa-arrow-right ml-1"></i>
        </div>
    </a>

    <!-- Maquetación y Exportación -->
    <a href="index.php?page=book_export&year=<?php echo $year; ?>" class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all p-6 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-gray-800 group-hover:text-white transition-colors">
                <i class="fas fa-file-pdf text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Maquetación</h3>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
            Organizar el orden de las páginas y generar el PDF final para imprenta.
        </p>
        <div class="text-gray-600 text-sm font-medium group-hover:translate-x-1 transition-transform inline-flex items-center">
            Generar PDF <i class="fas fa-arrow-right ml-1"></i>
        </div>
    </a>
</div>

<!-- Recent Activity -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <h3 class="font-bold text-gray-900 dark:text-white">Últimos Anuncios Registrados</h3>
        <a href="index.php?page=book&year=<?php echo $year; ?>" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todos</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                <tr>
                    <th class="px-6 py-3">Donante</th>
                    <th class="px-6 py-3">Tipo</th>
                    <th class="px-6 py-3">Importe</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($recentAds)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No hay anuncios registrados para este año.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentAds as $ad): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($ad['donor_name']); ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                <?php 
                                $types = [
                                    'full' => 'Página Completa',
                                    'media' => 'Media Página',
                                    'cover' => 'Portada',
                                    'back_cover' => 'Contraportada'
                                ];
                                echo $types[$ad['ad_type']] ?? $ad['ad_type']; 
                                ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <?php echo number_format($ad['amount'], 2); ?> €
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($ad['status'] === 'paid'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Pagado
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                <?php echo date('d/m/Y', strtotime($ad['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout.php'; ?>
