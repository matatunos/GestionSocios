<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versiones - <?php echo htmlspecialchars($document['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../partials/nav.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Historial de Versiones</h1>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadVersionModal">
                    <i class="fas fa-upload"></i> Subir Nueva Versión
                </button>
                <a href="index.php?page=documents" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="document-info-card">
            <h3><?php echo htmlspecialchars($document['title']); ?></h3>
            <p class="text-muted"><?php echo htmlspecialchars($document['description']); ?></p>
            <span class="badge badge-info">Versión actual: <?php echo $document['version']; ?></span>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="versions-timeline">
            <?php foreach ($versions as $idx => $version): ?>
                <div class="version-item <?php echo $version['is_latest_version'] ? 'is-latest' : ''; ?>">
                    <div class="version-marker">
                        <?php if ($version['is_latest_version']): ?>
                            <i class="fas fa-star"></i>
                        <?php else: ?>
                            <span><?php echo $version['version']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="version-content">
                        <div class="version-header">
                            <h4>
                                Versión <?php echo $version['version']; ?>
                                <?php if ($version['is_latest_version']): ?>
                                    <span class="badge badge-success">Actual</span>
                                <?php endif; ?>
                            </h4>
                            <span class="text-muted">
                                <?php 
                                $date = new DateTime($version['created_at']);
                                echo $date->format('d/m/Y H:i');
                                ?>
                            </span>
                        </div>
                        <div class="version-details">
                            <div class="detail-row">
                                <span class="detail-label">Archivo:</span>
                                <span><?php echo htmlspecialchars($version['file_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Tamaño:</span>
                                <span>
                                    <?php
                                    require_once __DIR__ . '/../../../Helpers/FileUploadHelper.php';
                                    echo FileUploadHelper::formatBytes($version['file_size']);
                                    ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Subido por:</span>
                                <span><?php echo htmlspecialchars($version['first_name'] . ' ' . $version['last_name']); ?></span>
                            </div>
                        </div>
                        <div class="version-actions">
                            <a href="index.php?page=documents&action=download&id=<?php echo $version['id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                            <?php if (!$version['is_latest_version']): ?>
                                <a href="index.php?page=documents&action=preview&id=<?php echo $version['id']; ?>" 
                                   class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal para subir nueva versión -->
    <div class="modal" id="uploadVersionModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Nueva Versión</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="index.php?page=documents&action=upload_version" enctype="multipart/form-data">
                    <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
                    <input type="hidden" name="document_id" value="<?php echo $document['id']; ?>">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Archivo <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" required>
                            <small class="form-text text-muted">
                                Tamaño máximo: 10MB. Tipos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, etc.
                            </small>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Se creará automáticamente una nueva versión del documento.
                            La versión actual se mantendrá en el historial.
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Subir Versión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .document-info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .document-info-card h3 {
            margin-top: 0;
        }
        
        .versions-timeline {
            position: relative;
            padding-left: 60px;
        }
        .versions-timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }
        
        .version-item {
            position: relative;
            margin-bottom: 30px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .version-item.is-latest {
            border-color: #28a745;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.1);
        }
        
        .version-marker {
            position: absolute;
            left: -45px;
            top: 20px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
        .is-latest .version-marker {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .version-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .version-header h4 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .version-details {
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            padding: 5px 0;
        }
        .detail-label {
            font-weight: bold;
            min-width: 120px;
        }
        
        .version-actions {
            display: flex;
            gap: 10px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-dialog {
            max-width: 500px;
            width: 90%;
        }
        .modal-content {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
    </style>

    <script>
        // Modal handling
        document.querySelectorAll('[data-toggle="modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const modal = document.querySelector(targetId);
                if (modal) modal.classList.add('show');
            });
        });
        
        document.querySelectorAll('[data-dismiss="modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) modal.classList.remove('show');
            });
        });
        
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
