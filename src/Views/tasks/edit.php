<?php 
$pageTitle = 'Editar Tarea';
ob_start(); 
?>

<div class="dashboard-header">
    <div>
        <h1><i class="fas fa-edit"></i> Editar Tarea</h1>
        <p class="text-muted">Modifica los detalles de la tarea</p>
    </div>
    <a href="/index.php?page=tasks" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/index.php?page=tasks&action=edit&id=<?= $task['id'] ?>">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título de la Tarea <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($task['title']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Categoría</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Sin categoría</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $task['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Baja</option>
                                <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Media</option>
                                <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>Alta</option>
                                <option value="urgent" <?= $task['priority'] === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" 
                                   value="<?= $task['due_date'] ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="due_time" class="form-label">Hora de Vencimiento</label>
                            <input type="time" class="form-control" id="due_time" name="due_time" 
                                   value="<?= $task['due_time'] ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label">Asignar a</label>
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">Sin asignar</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $task['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                                <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completada</option>
                                <option value="cancelled" <?= $task['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="related_entity_type" class="form-label">Relacionado con</label>
                            <select class="form-select" id="related_entity_type" name="related_entity_type">
                                <option value="">Ninguno</option>
                                <option value="member" <?= $task['related_entity_type'] === 'member' ? 'selected' : '' ?>>Socio</option>
                                <option value="donor" <?= $task['related_entity_type'] === 'donor' ? 'selected' : '' ?>>Donante</option>
                                <option value="event" <?= $task['related_entity_type'] === 'event' ? 'selected' : '' ?>>Evento</option>
                                <option value="payment" <?= $task['related_entity_type'] === 'payment' ? 'selected' : '' ?>>Pago</option>
                                <option value="expense" <?= $task['related_entity_type'] === 'expense' ? 'selected' : '' ?>>Gasto</option>
                                <option value="other" <?= $task['related_entity_type'] === 'other' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="related_entity_id" class="form-label">ID de Entidad Relacionada</label>
                            <input type="number" class="form-control" id="related_entity_id" name="related_entity_id" 
                                   value="<?= $task['related_entity_id'] ?>" placeholder="Opcional">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas Adicionales</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($task['notes'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/index.php?page=tasks" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle"></i> Información</h5>
                <hr>
                <p class="small"><strong>Creado por:</strong> <?= htmlspecialchars($task['created_by_name']) ?></p>
                <p class="small"><strong>Fecha creación:</strong> <?= date('d/m/Y H:i', strtotime($task['created_at'])) ?></p>
                <?php if ($task['completed_at']): ?>
                    <p class="small"><strong>Completado por:</strong> <?= htmlspecialchars($task['completed_by_name']) ?></p>
                    <p class="small"><strong>Fecha completado:</strong> <?= date('d/m/Y H:i', strtotime($task['completed_at'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
