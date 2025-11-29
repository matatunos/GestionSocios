<?php
// Fetch association name if not already set
if (!isset($associationName)) {
    $associationName = 'Gestión Asociación';
    try {
        $db = (new Database())->getConnection();
        if ($db) {
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'association_name'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $associationName = $row['setting_value'];
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist or other error, keep default
    }
}

// Fetch active announcements
$announcements = [];
try {
    if (isset($db) && $db) {
        require_once __DIR__ . '/../Models/PublicAnnouncement.php';
        $announcementModel = new PublicAnnouncement($db);
        $stmt = $announcementModel->readActive();
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Table doesn't exist yet, keep empty
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($associationName); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-body);
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1000px;
            gap: 2rem;
            align-items: stretch;
        }
        
        .announcements-section {
            flex: 1;
            padding: 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #2d3748;
            overflow-y: auto;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .login-section {
            flex: 0 0 400px;
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .announcements-header {
            margin-bottom: 1.5rem;
            flex-shrink: 0;
        }
        
        .announcements-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .announcements-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .announcements-content {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        
        .announcement-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .announcement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        
        .announcement-card.info { border-left-color: #3b82f6; }
        .announcement-card.success { border-left-color: #10b981; }
        .announcement-card.warning { border-left-color: #f59e0b; }
        .announcement-card.danger { border-left-color: #ef4444; }
        
        .announcement-card h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .announcement-card p {
            font-size: 0.875rem;
            line-height: 1.5;
            opacity: 0.9;
            margin: 0;
        }
        
        .empty-announcements {
            text-align: center;
            padding: 2rem 1rem;
            opacity: 0.7;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .empty-announcements i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 968px) {
            .login-wrapper {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
            
            .announcements-section, .login-section {
                width: 100%;
                max-width: 400px;
                flex: none;
            }
            
            .announcements-section {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <!-- Announcements Section -->
            <div class="announcements-section">
                <div class="announcements-header">
                    <h2><i class="fas fa-bullhorn"></i> Tablón de Anuncios</h2>
                    <p>Información importante y novedades</p>
                </div>
                
                <div class="announcements-content">
                    <?php if (empty($announcements)): ?>
                        <div class="empty-announcements">
                            <i class="fas fa-info-circle"></i>
                            <p>No hay anuncios en este momento</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): ?>
                            <div class="announcement-card <?php echo htmlspecialchars($ann['type']); ?>">
                                <h3>
                                    <?php
                                    $icons = [
                                        'info' => 'fa-info-circle',
                                        'success' => 'fa-check-circle',
                                        'warning' => 'fa-exclamation-triangle',
                                        'danger' => 'fa-exclamation-circle'
                                    ];
                                    $icon = $icons[$ann['type']] ?? 'fa-info-circle';
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                    <?php echo htmlspecialchars($ann['title']); ?>
                                </h3>
                                <p><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Login Section -->
            <div class="login-section">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="font-size: 3rem; color: var(--primary-600); margin-bottom: 1rem;">
                        <i class="fas fa-users-rectangle"></i>
                    </div>
                    <h1 style="color: var(--primary-700); font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($associationName); ?></h1>
                    <p style="color: var(--text-light);">Acceso Socios</p>
                </div>

                <?php if (isset($error)): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=login&action=login" method="POST">
                    <?php require_once __DIR__ . '/../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
                    <div class="form-group">
                        <label class="form-label">Usuario</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username" class="form-control" style="padding-left: 2.5rem; width: 100%; box-sizing: border-box;" placeholder="admin" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light);">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" style="padding-left: 2.5rem; width: 100%; box-sizing: border-box;" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="margin-top: 1rem;">
                        Iniciar Sesión <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
