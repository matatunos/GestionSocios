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
<h2>Administradores</h2>
<table border="1" cellpadding="6">
    <tr><th>ID</th><th>Email</th><th>Nombre</th><th>Acciones</th></tr>
    <?php foreach ($admins as $admin): ?>
    <tr>
        <form method="post">
        <td><?= $admin['id'] ?><input type="hidden" name="id" value="<?= $admin['id'] ?>"></td>
        <td><input type="text" name="email" value="<?= htmlspecialchars($admin['email']) ?>"></td>
        <td><input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>"></td>
        <td>
            <input type="password" name="password" placeholder="Nueva clave">
            <button type="submit" name="edit">Guardar</button>
            <button type="submit" name="delete" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</button>
        </td>
        </form>
    </tr>
    <?php endforeach; ?>
    <tr>
        <form method="post">
        <td>Nuevo</td>
        <td><input type="text" name="email"></td>
        <td><input type="text" name="name"></td>
        <td>
            <input type="password" name="password" placeholder="Clave">
            <button type="submit" name="add">Añadir</button>
        </td>
        </form>
    </tr>
</table>
