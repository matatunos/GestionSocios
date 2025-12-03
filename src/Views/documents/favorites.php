<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritos - Documentos</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../partials/nav.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-star"></i> Documentos Favoritos</h1>
            <div class="header-actions">
                <a href="index.php?page=documents" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Documentos
                </a>
            </div>
        </div>

        <?php if (empty($documents)): ?>
            <div class="empty-state">
                <i class="fas fa-star fa-4x"></i>
                <h3>No tienes documentos favoritos</h3>
                <p>Marca documentos como favoritos para acceder rápidamente a ellos</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Título</th>
                            <th>Archivo</th>
                            <th>Tamaño</th>
                            <th>Subido por</th>
                            <th>Fecha</th>
                            <th>Descargas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td class="text-center">
                                    <?php
                                    require_once __DIR__ . '/../../../Helpers/FileTypeHelper.php';
                                    echo FileTypeHelper::renderIcon($doc['file_extension'] ?? pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                    ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($doc['title']); ?></strong>
                                    <?php if ($doc['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($doc['description'], 0, 100)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($doc['file_name']); ?></td>
                                <td>
                                    <?php
                                    require_once __DIR__ . '/../../../Helpers/FileUploadHelper.php';
                                    echo FileUploadHelper::formatBytes($doc['file_size']);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></td>
                                <td>
                                    <?php 
                                    $date = new DateTime($doc['created_at']);
                                    echo $date->format('d/m/Y');
                                    ?>
                                </td>
                                <td><?php echo number_format($doc['downloads']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="index.php?page=documents&action=download&id=<?php echo $doc['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="index.php?page=documents&action=preview&id=<?php echo $doc['id']; ?>" 
                                           class="btn btn-sm btn-secondary" target="_blank" title="Vista previa">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-warning favorite-toggle" 
                                                data-id="<?php echo $doc['id']; ?>"
                                                title="Quitar de favoritos">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state i {
            color: #ddd;
            margin-bottom: 20px;
        }
        .btn-group {
            display: flex;
            gap: 5px;
        }
    </style>

    <script>
        document.querySelectorAll('.favorite-toggle').forEach(btn => {
            btn.addEventListener('click', async function() {
                const docId = this.getAttribute('data-id');
                const formData = new FormData();
                formData.append('id', docId);
                
                try {
                    const response = await fetch('index.php?page=documents&action=favorite', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remover fila de la tabla
                        this.closest('tr').remove();
                        
                        // Si ya no hay documentos, mostrar mensaje vacío
                        const tbody = document.querySelector('tbody');
                        if (tbody && tbody.children.length === 0) {
                            location.reload();
                        }
                    } else {
                        alert('Error al quitar de favoritos: ' + (data.error || 'Error desconocido'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                }
            });
        });
    </script>
</body>
</html>
