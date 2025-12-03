<?php
// Gestión de usuarios administradores
if (isset($this) && property_exists($this, 'db')) {
    $db = $this->db;
} else {
    require_once __DIR__ . '/../../Config/database.php';
    $db = (new Database())->getConnection();
}

// Procesar acciones: añadir, editar, borrar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../Models/AuditLog.php';
    $auditLog = new AuditLog($db);
    $adminId = $_SESSION['user_id'] ?? null;

    if (isset($_POST['add'])) {
        $stmt = $db->prepare('INSERT INTO users (email, name, password, role) VALUES (?, ?, ?, "admin")');
        $stmt->execute([
            $_POST['email'],
            $_POST['name'],
            password_hash($_POST['password'], PASSWORD_DEFAULT)
        ]);
        $newUserId = $db->lastInsertId();
        $details = json_encode(['email' => $_POST['email'], 'name' => $_POST['name']]);
        $auditLog->create($adminId, 'create', 'user', $newUserId, $details);
    }
    if (isset($_POST['edit'])) {
        $stmt = $db->prepare('UPDATE users SET email=?, name=?, password=? WHERE id=?');
        $stmt->execute([
            $_POST['email'],
            $_POST['name'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['id']
        ]);
        $details = json_encode(['email' => $_POST['email'], 'name' => $_POST['name']]);
        $auditLog->create($adminId, 'update', 'user', $_POST['id'], $details);
    }
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare('DELETE FROM users WHERE id=?');
        $stmt->execute([$_POST['id']]);
        $details = json_encode(['user_id' => $_POST['id']]);
        $auditLog->create($adminId, 'delete', 'user', $_POST['id'], $details);
    }
}

$admins = $db->query('SELECT id, email, name FROM users WHERE role="admin"')->fetchAll(PDO::FETCH_ASSOC);
?>
<h2 class="section-title"><i class="fas fa-user-shield"></i> Administradores</h2>
<div class="card mb-4">
    <table class="table w-full" style="min-width: 400px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td style="font-weight:600; color:var(--primary-600);">
                        <?= $admin['id'] ?>
                    </td>
                    <td style="font-family:monospace; color:var(--primary-700);">
                        <?= htmlspecialchars($admin['email']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($admin['name']) ?>
                    </td>
                    <td style="display:flex; gap:0.5rem;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('edit-admin-<?= $admin['id'] ?>').style.display='block'">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="document.getElementById('change-pass-<?= $admin['id'] ?>').style.display='block'">
                            <i class="fas fa-key"></i> Cambiar contraseña
                        </button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este usuario?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                <!-- Formulario de edición -->
                <tr id="edit-admin-<?= $admin['id'] ?>" style="display:none; background:#f9fafb;">
                    <td colspan="4">
                        <form method="post" style="display:flex; gap:1rem; align-items:center;">
                            <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                            <input type="text" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control" placeholder="Email" style="width:200px;">
                            <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="form-control" placeholder="Nombre" style="width:160px;">
                            <button type="submit" name="edit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar cambios</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('edit-admin-<?= $admin['id'] ?>').style.display='none'">Cancelar</button>
                        </form>
                    </td>
                </tr>
                <!-- Formulario de cambio de contraseña -->
                <tr id="change-pass-<?= $admin['id'] ?>" style="display:none; background:#fef3c7;">
                    <td colspan="4">
                        <form method="post" style="display:flex; gap:1rem; align-items:center;">
                            <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                            <input type="password" name="password" class="form-control" placeholder="Nueva contraseña" style="width:200px;">
                            <button type="submit" name="edit" class="btn btn-sm btn-warning"><i class="fas fa-key"></i> Cambiar contraseña</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('change-pass-<?= $admin['id'] ?>').style.display='none'">Cancelar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <form method="post" style="display:contents;">
                <td style="font-weight:600; color:var(--primary-600);">Nuevo</td>
                <td><input type="text" name="email" class="form-control" style="width:180px;"></td>
                <td><input type="text" name="name" class="form-control" style="width:140px;"></td>
                <td style="display:flex; gap:0.5rem;">
                    <input type="password" name="password" placeholder="Clave" class="form-control" style="width:120px;">
                    <button type="submit" name="add" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Añadir</button>
                </td>
                </form>
            </tr>
        </tbody>
    </table>
</div>
