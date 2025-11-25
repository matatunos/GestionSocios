<?php
// Gestión de usuarios administradores
require_once __DIR__ . '/../../Config/Database.php';
$db = (new Database())->getConnection();

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
                <form method="post" style="display:contents;">
                <td style="font-weight:600; color:var(--primary-600);">
                    <?= $admin['id'] ?>
                    <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                </td>
                <td><input type="text" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control" style="width:180px;"></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="form-control" style="width:140px;"></td>
                <td style="display:flex; gap:0.5rem;">
                    <input type="password" name="password" placeholder="Nueva clave" class="form-control" style="width:120px;">
                    <button type="submit" name="edit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este usuario?')"><i class="fas fa-trash"></i> Eliminar</button>
                </td>
                </form>
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
