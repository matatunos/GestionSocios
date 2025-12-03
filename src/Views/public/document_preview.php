<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($document['title']); ?> - Documento Compartido</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .content {
            padding: 2rem;
        }
        
        .document-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-item {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
        }
        
        .info-item i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 600;
            color: #1e293b;
        }
        
        .description {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .description h3 {
            color: #1e293b;
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }
        
        .description p {
            color: #64748b;
            line-height: 1.6;
        }
        
        .limitations {
            background: #fff7ed;
            border-left: 4px solid #f59e0b;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .limitations h4 {
            color: #ea580c;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .limitations ul {
            list-style: none;
            color: #92400e;
            font-size: 0.875rem;
        }
        
        .limitations li {
            padding: 0.25rem 0;
        }
        
        .limitations li i {
            margin-right: 0.5rem;
            color: #f59e0b;
        }
        
        .download-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.125rem;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            width: 100%;
            justify-content: center;
        }
        
        .download-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .footer {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            text-align: center;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .document-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($document['title']); ?></h1>
            <p>Documento compartido públicamente</p>
        </div>
        
        <div class="content">
            <div class="document-info">
                <div class="info-item">
                    <i class="<?php echo FileTypeHelper::getIcon($document['file_extension'] ?? pathinfo($document['file_name'], PATHINFO_EXTENSION)); ?>"></i>
                    <div class="info-label">Tipo de Archivo</div>
                    <div class="info-value"><?php echo strtoupper($document['file_extension'] ?? pathinfo($document['file_name'], PATHINFO_EXTENSION)); ?></div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-hdd"></i>
                    <div class="info-label">Tamaño</div>
                    <div class="info-value">
                        <?php 
                        require_once __DIR__ . '/../../Helpers/FileUploadHelper.php';
                        echo FileUploadHelper::formatBytes($document['file_size']); 
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <div class="info-label">Compartido por</div>
                    <div class="info-value"><?php echo htmlspecialchars($document['first_name'] . ' ' . $document['last_name']); ?></div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-calendar"></i>
                    <div class="info-label">Fecha de subida</div>
                    <div class="info-value">
                        <?php 
                        $date = new DateTime($document['created_at']);
                        echo $date->format('d/m/Y'); 
                        ?>
                    </div>
                </div>
            </div>
            
            <?php if ($document['description']): ?>
                <div class="description">
                    <h3><i class="fas fa-info-circle"></i> Descripción</h3>
                    <p><?php echo nl2br(htmlspecialchars($document['description'])); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($document['public_download_limit'] || $document['token_expires_at']): ?>
                <div class="limitations">
                    <h4><i class="fas fa-exclamation-triangle"></i> Limitaciones del enlace</h4>
                    <ul>
                        <?php if ($document['public_download_limit']): ?>
                            <li>
                                <i class="fas fa-download"></i>
                                Descargas: <?php echo $document['public_downloads']; ?> / <?php echo $document['public_download_limit']; ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($document['token_expires_at']): ?>
                            <li>
                                <i class="fas fa-clock"></i>
                                Expira: <?php 
                                    $expiry = new DateTime($document['token_expires_at']);
                                    echo $expiry->format('d/m/Y H:i'); 
                                ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <a href="index.php?page=public_document&action=download&token=<?php echo htmlspecialchars($document['public_token']); ?>" 
               class="download-button">
                <i class="fas fa-download"></i>
                Descargar Documento
            </a>
        </div>
        
        <div class="footer">
            <p>Este es un enlace privado. No lo compartas con personas no autorizadas.</p>
        </div>
    </div>
</body>
</html>
