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
        }
        
        .announcements-section {
            flex: 1;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
            color: white;
            overflow-y: auto;
        }
        
        .login-section {
            flex: 0 0 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: white;
        }
        
        .announcements-header {
            margin-bottom: 2rem;
        }
        
        .announcements-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .announcement-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .announcement-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .announcement-card.info { border-left-color: #3b82f6; }
        .announcement-card.success { border-left-color: #10b981; }
        .announcement-card.warning { border-left-color: #f59e0b; }
        .announcement-card.danger { border-left-color: #ef4444; }
        
        .announcement-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .announcement-card p {
            font-size: 0.95rem;
            line-height: 1.6;
            opacity: 0.95;
        }
        
        .empty-announcements {
            text-align: center;
            padding: 3rem 1rem;
            opacity: 0.7;
        }
        
        .empty-announcements i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 968px) {
            .login-container {
                flex-direction: column;
            }
            
            .announcements-section {
                flex: none;
                min-height: 40vh;
            }
            
            .login-section {
                flex: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Announcements Section -->
        <div class="announcements-section">
            <div class="announcements-header">
                <h2><i class="fas fa-bullhorn"></i> Tablón de Anuncios</h2>
                <p>Información importante y novedades</p>
            </div>
            
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
        
        <!-- Login Section -->
        <div class="login-section">
            <div class="card" style="width: 100%; max-width: 400px; padding: 2rem; box-sizing: border-box;">
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
