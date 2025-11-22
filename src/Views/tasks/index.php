<?php 
$pageTitle = 'Gestión de Tareas';
ob_start(); 
?>

<div class="dashboard-header">
    <div>
        <h1><i class="fas fa-tasks"></i> Gestión de Tareas</h1>
        <p class="text-muted">Organiza y da seguimiento a las tareas pendientes</p>
    </div>
    <?php if ($authHelper->hasPermission('tasks.create')): ?>
    <a href="/index.php?page=tasks&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Tarea
    </a>
    <?php endif; ?>
</div>

<!-- Estadísticas -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
            <div class="stat-label">Total Tareas</div>
        </div>
    </div>
    
    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= $stats['pending'] ?? 0 ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
    </div>
    
    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-spinner"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= $stats['in_progress'] ?? 0 ?></div>
            <div class="stat-label">En Progreso</div>
        </div>
    </div>
    
    <div class="stat-card stat-danger">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= $stats['overdue'] ?? 0 ?></div>
            <div class="stat-label">Vencidas</div>
        </div>
    </div>
    
    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= $stats['completed'] ?? 0 ?></div>
            <div class="stat-label">Completadas</div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/index.php" class="filter-form">
            <input type="hidden" name="page" value="tasks">
            
            <div class="filter-group">
                <label>Filtros rápidos:</label>
                <div class="btn-group">
                    <a href="/index.php?page=tasks" class="btn btn-sm btn-outline-secondary <?= !isset($_GET['filter']) ? 'active' : '' ?>">
                        Todas
                    </a>
                    <a href="/index.php?page=tasks&filter=mytasks" class="btn btn-sm btn-outline-secondary <?= ($_GET['filter'] ?? '') === 'mytasks' ? 'active' : '' ?>">
                        Mis Tareas
                    </a>
                    <a href="/index.php?page=tasks&filter=today" class="btn btn-sm btn-outline-secondary <?= ($_GET['filter'] ?? '') === 'today' ? 'active' : '' ?>">
                        Hoy
                    </a>
                    <a href="/index.php?page=tasks&filter=overdue" class="btn btn-sm btn-outline-danger <?= ($_GET['filter'] ?? '') === 'overdue' ? 'active' : '' ?>">
                        Vencidas
                    </a>
                </div>
            </div>
            
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Estado: Todos</option>
                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                        <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completada</option>
                        <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Prioridad: Todas</option>
                        <option value="urgent" <?= ($_GET['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                        <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Alta</option>
                        <option value="medium" <?= ($_GET['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Media</option>
                        <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Baja</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Categoría: Todas</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($_GET['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <select name="assigned_to" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Asignado a: Todos</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($_GET['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Tareas -->
<div class="card">
    <div class="card-body">
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <p class="text-muted">No hay tareas que coincidan con los filtros seleccionados</p>
                <?php if ($authHelper->hasPermission('tasks.create')): ?>
                    <a href="/index.php?page=tasks&action=create" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Crear Primera Tarea
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th>Tarea</th>
                            <th>Categoría</th>
                            <th>Prioridad</th>
                            <th>Asignado a</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): 
                            $isOverdue = $task['due_date'] && $task['due_date'] < date('Y-m-d') && !in_array($task['status'], ['completed', 'cancelled']);
                            $isDueToday = $task['due_date'] === date('Y-m-d');
                        ?>
                        <tr class="<?= $isOverdue ? 'table-danger' : ($isDueToday ? 'table-warning' : '') ?>">
                            <td>
                                <?php if ($authHelper->hasPermission('tasks.complete') && $task['status'] !== 'completed'): ?>
                                    <button class="btn btn-sm btn-link text-success complete-task" data-id="<?= $task['id'] ?>" title="Completar">
                                        <i class="far fa-square"></i>
                                    </button>
                                <?php elseif ($task['status'] === 'completed'): ?>
                                    <i class="fas fa-check-square text-success"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/index.php?page=tasks&action=view&id=<?= $task['id'] ?>" class="text-decoration-none">
                                    <strong><?= htmlspecialchars($task['title']) ?></strong>
                                </a>
                                <?php if ($task['description']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($task['description'], 0, 60)) ?><?= strlen($task['description']) > 60 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($task['category_name']): ?>
                                    <span class="badge" style="background-color: <?= $task['category_color'] ?>">
                                        <i class="<?= $task['category_icon'] ?>"></i> <?= htmlspecialchars($task['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $priorityColors = [
                                    'urgent' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'info',
                                    'low' => 'secondary'
                                ];
                                $priorityLabels = [
                                    'urgent' => 'Urgente',
                                    'high' => 'Alta',
                                    'medium' => 'Media',
                                    'low' => 'Baja'
                                ];
                                ?>
                                <span class="badge bg-<?= $priorityColors[$task['priority']] ?>">
                                    <?= $priorityLabels[$task['priority']] ?>
                                </span>
                            </td>
                            <td>
                                <?= $task['assigned_to_name'] ? htmlspecialchars($task['assigned_to_name']) : '<span class="text-muted">Sin asignar</span>' ?>
                            </td>
                            <td>
                                <?php if ($task['due_date']): ?>
                                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : ($isDueToday ? 'text-warning fw-bold' : '') ?>">
                                        <i class="far fa-calendar"></i> <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                                        <?php if ($task['due_time']): ?>
                                            <br><small><i class="far fa-clock"></i> <?= date('H:i', strtotime($task['due_time'])) ?></small>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'secondary'
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'in_progress' => 'En Progreso',
                                    'completed' => 'Completada',
                                    'cancelled' => 'Cancelada'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$task['status']] ?>">
                                    <?= $statusLabels[$task['status']] ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/index.php?page=tasks&action=view&id=<?= $task['id'] ?>" 
                                       class="btn btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($authHelper->hasPermission('tasks.edit')): ?>
                                    <a href="/index.php?page=tasks&action=edit&id=<?= $task['id'] ?>" 
                                       class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($authHelper->hasPermission('tasks.delete')): ?>
                                    <a href="/index.php?page=tasks&action=delete&id=<?= $task['id'] ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('¿Eliminar esta tarea?')"
                                       title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Completar tarea con AJAX
document.querySelectorAll('.complete-task').forEach(btn => {
    btn.addEventListener('click', function() {
        const taskId = this.dataset.id;
        
        if (confirm('¿Marcar esta tarea como completada?')) {
            fetch('/index.php?page=tasks&action=complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + taskId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
