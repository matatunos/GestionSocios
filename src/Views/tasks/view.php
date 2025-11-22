<?php 
$pageTitle = 'Detalle de Tarea';
ob_start(); 
?>

<div class="dashboard-header">
    <div>
        <h1><i class="fas fa-tasks"></i> Detalle de Tarea</h1>
        <p class="text-muted"><?= htmlspecialchars($task['title']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($authHelper->hasPermission('tasks.edit')): ?>
        <a href="/index.php?page=tasks&action=edit&id=<?= $task['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
        <a href="/index.php?page=tasks" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Información Principal -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3><?= htmlspecialchars($task['title']) ?></h3>
                        <?php if ($task['category_name']): ?>
                            <span class="badge" style="background-color: <?= $task['category_color'] ?>">
                                <i class="<?= $task['category_icon'] ?>"></i> <?= htmlspecialchars($task['category_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php
                        $priorityColors = ['urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary'];
                        $priorityLabels = ['urgent' => 'Urgente', 'high' => 'Alta', 'medium' => 'Media', 'low' => 'Baja'];
                        ?>
                        <span class="badge bg-<?= $priorityColors[$task['priority']] ?> me-2">
                            <?= $priorityLabels[$task['priority']] ?>
                        </span>
                        <?php
                        $statusColors = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'secondary'];
                        $statusLabels = ['pending' => 'Pendiente', 'in_progress' => 'En Progreso', 'completed' => 'Completada', 'cancelled' => 'Cancelada'];
                        ?>
                        <span class="badge bg-<?= $statusColors[$task['status']] ?>">
                            <?= $statusLabels[$task['status']] ?>
                        </span>
                    </div>
                </div>
                
                <hr>
                
                <?php if ($task['description']): ?>
                <div class="mb-3">
                    <h5>Descripción</h5>
                    <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-user"></i> Asignado a</h6>
                        <p><?= $task['assigned_to_name'] ? htmlspecialchars($task['assigned_to_name']) : '<span class="text-muted">Sin asignar</span>' ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="far fa-calendar"></i> Vencimiento</h6>
                        <p>
                            <?php if ($task['due_date']): ?>
                                <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                                <?php if ($task['due_time']): ?>
                                    a las <?= date('H:i', strtotime($task['due_time'])) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Sin fecha límite</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <?php if ($task['notes']): ?>
                <div class="alert alert-info">
                    <strong><i class="fas fa-sticky-note"></i> Notas:</strong><br>
                    <?= nl2br(htmlspecialchars($task['notes'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Comentarios -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-comments"></i> Comentarios (<?= count($comments) ?>)</h5>
                <hr>
                
                <!-- Formulario nuevo comentario -->
                <form id="commentForm" class="mb-4">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <div class="mb-3">
                        <textarea class="form-control" name="comment" rows="3" 
                                  placeholder="Escribe un comentario..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-paper-plane"></i> Añadir Comentario
                    </button>
                </form>
                
                <!-- Lista de comentarios -->
                <div id="commentsList">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted">No hay comentarios aún</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($comment['user_name']) ?></strong>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                                </div>
                                <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Acciones Rápidas -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                <hr>
                <?php if ($task['status'] !== 'completed' && $authHelper->hasPermission('tasks.complete')): ?>
                <button class="btn btn-success btn-block mb-2 w-100" onclick="completeTask(<?= $task['id'] ?>)">
                    <i class="fas fa-check"></i> Marcar como Completada
                </button>
                <?php endif; ?>
                <?php if ($authHelper->hasPermission('tasks.edit')): ?>
                <a href="/index.php?page=tasks&action=edit&id=<?= $task['id'] ?>" class="btn btn-warning btn-block mb-2 w-100">
                    <i class="fas fa-edit"></i> Editar Tarea
                </a>
                <?php endif; ?>
                <?php if ($authHelper->hasPermission('tasks.delete')): ?>
                <a href="/index.php?page=tasks&action=delete&id=<?= $task['id'] ?>" 
                   class="btn btn-danger btn-block w-100"
                   onclick="return confirm('¿Eliminar esta tarea?')">
                    <i class="fas fa-trash"></i> Eliminar Tarea
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información Adicional -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle"></i> Información</h5>
                <hr>
                <p class="small mb-2">
                    <strong>Creado por:</strong><br>
                    <?= htmlspecialchars($task['created_by_name']) ?>
                </p>
                <p class="small mb-2">
                    <strong>Fecha creación:</strong><br>
                    <?= date('d/m/Y H:i', strtotime($task['created_at'])) ?>
                </p>
                <?php if ($task['completed_at']): ?>
                <p class="small mb-2">
                    <strong>Completado por:</strong><br>
                    <?= htmlspecialchars($task['completed_by_name']) ?>
                </p>
                <p class="small mb-2">
                    <strong>Fecha completado:</strong><br>
                    <?= date('d/m/Y H:i', strtotime($task['completed_at'])) ?>
                </p>
                <?php endif; ?>
                <?php if ($task['related_entity_type']): ?>
                <p class="small mb-2">
                    <strong>Relacionado con:</strong><br>
                    <?= ucfirst($task['related_entity_type']) ?>
                    <?php if ($task['related_entity_id']): ?>
                        (ID: <?= $task['related_entity_id'] ?>)
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Completar tarea
function completeTask(taskId) {
    if (confirm('¿Marcar esta tarea como completada?')) {
        fetch('/index.php?page=tasks&action=complete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
}

// Añadir comentario
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/index.php?page=tasks&action=addComment', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
