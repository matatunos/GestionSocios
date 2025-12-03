<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - <?php echo htmlspecialchars($document['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #2c3e50;
        }
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .preview-header {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-header h2 {
            margin: 0;
            font-size: 18px;
        }
        .preview-content {
            background: white;
            border-radius: 8px;
            padding: 20px;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .preview-content iframe {
            width: 100%;
            height: 800px;
            border: none;
        }
        .preview-content img {
            max-width: 100%;
            height: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .text-preview {
            text-align: left;
            max-width: 800px;
            width: 100%;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .no-preview {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .no-preview i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <div>
                <h2><?php echo htmlspecialchars($document['title']); ?></h2>
                <small class="text-muted"><?php echo htmlspecialchars($document['file_name']); ?></small>
            </div>
            <div class="btn-group">
                <a href="index.php?page=documents&action=download&id=<?php echo $document['id']; ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-download"></i> Descargar
                </a>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
        
        <div class="preview-content">
            <?php
            $extension = $document['file_extension'] ?? pathinfo($document['file_name'], PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            $mime_type = $document['mime_type_verified'] ?? $document['file_type'];
            
            // Determinar tipo de previsualización
            $canPreview = false;
            $previewType = 'none';
            
            // PDFs
            if ($extension === 'pdf' || $mime_type === 'application/pdf') {
                $canPreview = true;
                $previewType = 'pdf';
            }
            // Imágenes
            elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']) || 
                    strpos($mime_type, 'image/') === 0) {
                $canPreview = true;
                $previewType = 'image';
            }
            // Texto
            elseif (in_array($extension, ['txt', 'md', 'csv', 'log', 'json', 'xml', 'html', 'css', 'js', 'php', 'sql']) ||
                    strpos($mime_type, 'text/') === 0) {
                $canPreview = true;
                $previewType = 'text';
            }
            
            if ($canPreview):
                if ($previewType === 'pdf'):
                    // Vista previa de PDF con iframe
                    ?>
                    <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>#view=FitH&toolbar=1&navpanes=0"></iframe>
                    <?php
                elseif ($previewType === 'image'):
                    // Vista previa de imagen
                    ?>
                    <img src="<?php echo htmlspecialchars($document['file_path']); ?>" 
                         alt="<?php echo htmlspecialchars($document['title']); ?>">
                    <?php
                elseif ($previewType === 'text'):
                    // Vista previa de texto
                    $filePath = __DIR__ . '/../../../public/' . $document['file_path'];
                    if (file_exists($filePath) && filesize($filePath) < 1048576): // Máximo 1MB
                        $content = file_get_contents($filePath);
                        ?>
                        <div class="text-preview">
                            <code><?php echo htmlspecialchars($content); ?></code>
                        </div>
                        <?php
                    else:
                        ?>
                        <div class="no-preview">
                            <i class="fas fa-file-alt"></i>
                            <h3>Archivo muy grande para vista previa</h3>
                            <p>Descarga el archivo para verlo</p>
                        </div>
                        <?php
                    endif;
                endif;
            else:
                // No se puede previsualizar
                $icon = 'fa-file';
                $color = '#94a3b8';
                
                // Iconos según extensión
                $iconMap = [
                    'doc' => ['fa-file-word', '#2b579a'],
                    'docx' => ['fa-file-word', '#2b579a'],
                    'xls' => ['fa-file-excel', '#217346'],
                    'xlsx' => ['fa-file-excel', '#217346'],
                    'ppt' => ['fa-file-powerpoint', '#d24726'],
                    'pptx' => ['fa-file-powerpoint', '#d24726'],
                    'zip' => ['fa-file-zipper', '#f59e0b'],
                    'rar' => ['fa-file-zipper', '#f59e0b'],
                    '7z' => ['fa-file-zipper', '#f59e0b'],
                    'mp3' => ['fa-file-audio', '#8b5cf6'],
                    'wav' => ['fa-file-audio', '#8b5cf6'],
                    'mp4' => ['fa-file-video', '#ec4899'],
                    'avi' => ['fa-file-video', '#ec4899'],
                    'mov' => ['fa-file-video', '#ec4899']
                ];
                
                if (isset($iconMap[$extension])) {
                    $icon = $iconMap[$extension][0];
                    $color = $iconMap[$extension][1];
                }
                ?>
                <div class="no-preview">
                    <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i>
                    <h3>Vista previa no disponible</h3>
                    <p>Este tipo de archivo (<?php echo strtoupper($extension); ?>) no admite vista previa</p>
                    <p>Descarga el archivo para abrirlo con la aplicación adecuada</p>
                    <br>
                    <a href="index.php?page=documents&action=download&id=<?php echo $document['id']; ?>" 
                       class="btn btn-lg btn-primary">
                        <i class="fas fa-download"></i> Descargar Archivo
                    </a>
                </div>
                <?php
            endif;
            ?>
        </div>
    </div>
</body>
</html>
