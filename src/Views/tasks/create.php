<?php 
$pageTitle = 'Nueva Tarea';
ob_start(); 
?>

<div class="dashboard-header">
    <div>
        <h1><i class="fas fa-plus"></i> Nueva Tarea</h1>
        <p class="text-muted">Crea una nueva tarea pendiente</p>
    </div>
    <a href="/index.php?page=tasks" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/index.php?page=tasks&action=create">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título de la Tarea <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Categoría</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Sin categoría</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Prioridad <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low">Baja</option>
                                <option value="medium" selected>Media</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" id="due_date" name="due_date">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="due_time" class="form-label">Hora de Vencimiento</label>
                            <input type="time" class="form-control" id="due_time" name="due_time">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label">Asignar a</label>
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">Sin asignar</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>">
                                        <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" selected>Pendiente</option>
                                <option value="in_progress">En Progreso</option>
                                <option value="completed">Completada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="related_entity_type" class="form-label">Relacionado con</label>
                            <select class="form-select" id="related_entity_type" name="related_entity_type">
                                <option value="">Ninguno</option>
                                <option value="member">Socio</option>
                                <option value="donor">Donante</option>
                                <option value="event">Evento</option>
                                <option value="payment">Pago</option>
                                <option value="expense">Gasto</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="related_entity_id" class="form-label">ID de Entidad Relacionada</label>
                            <input type="number" class="form-control" id="related_entity_id" name="related_entity_id" 
                                   placeholder="Opcional">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas Adicionales</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/index.php?page=tasks" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Tarea
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle"></i> Ayuda</h5>
                <hr>
                <h6>Prioridades:</h6>
                <ul class="small">
                    <li><strong class="text-danger">Urgente:</strong> Requiere atención inmediata</li>
                    <li><strong class="text-warning">Alta:</strong> Importante, pero no crítica</li>
                    <li><strong class="text-info">Media:</strong> Prioridad normal</li>
                    <li><strong class="text-secondary">Baja:</strong> Puede esperar</li>
                </ul>
                
                <h6 class="mt-3">Estados:</h6>
                <ul class="small">
                    <li><strong>Pendiente:</strong> Aún no iniciada</li>
                    <li><strong>En Progreso:</strong> Se está trabajando</li>
                    <li><strong>Completada:</strong> Finalizada con éxito</li>
                    <li><strong>Cancelada:</strong> Ya no es necesaria</li>
                </ul>
                
                <h6 class="mt-3">Asignación:</h6>
                <p class="small">Puedes asignar la tarea a un usuario específico o dejarla sin asignar para que cualquiera pueda tomarla.</p>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
