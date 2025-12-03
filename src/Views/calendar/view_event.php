<?php 
$page_title = htmlspecialchars($eventModel->name);
ob_start(); 
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <a href="index.php?page=calendar" class="btn btn-sm btn-secondary" style="margin-bottom: 0.5rem;">
            <i class="fas fa-arrow-left"></i> Volver al Calendario
        </a>
        <h1><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($eventModel->name); ?></h1>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="index.php?page=events&action=edit&id=<?php echo $eventModel->id; ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Editar Evento
        </a>
    </div>
</div>

<!-- Event Details Card -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="width: 32px; height: 32px; background: <?php echo htmlspecialchars($eventModel->color); ?>; border-radius: 8px;"></div>
                <div>
                    <span class="badge" style="background: <?php echo htmlspecialchars($eventModel->color); ?>; color: white;">
                        <?php 
                        $types = ['meeting' => 'Reunión', 'celebration' => 'Celebración', 'activity' => 'Actividad', 'assembly' => 'Asamblea', 'other' => 'Otro'];
                        echo $types[$eventModel->event_type] ?? 'Otro';
                        ?>
                    </span>
                </div>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem;">Descripción</h3>
                <p style="color: var(--text-muted); white-space: pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($eventModel->description)); ?>
                </p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">
                        <i class="fas fa-calendar" style="width: 20px;"></i> Fecha
                    </div>
                    <div style="font-weight: 500;">
                        <?php echo date('d/m/Y', strtotime($eventModel->date)); ?>
                    </div>
                </div>
                
                <?php if ($eventModel->start_time): ?>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">
                        <i class="fas fa-clock" style="width: 20px;"></i> Horario
                    </div>
                    <div style="font-weight: 500;">
                        <?php echo date('H:i', strtotime($eventModel->start_time)); ?>
                        <?php if ($eventModel->end_time): ?>
                            - <?php echo date('H:i', strtotime($eventModel->end_time)); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($eventModel->location): ?>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">
                        <i class="fas fa-map-marker-alt" style="width: 20px;"></i> Ubicación
                    </div>
                    <div style="font-weight: 500;">
                        <?php echo htmlspecialchars($eventModel->location); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($eventModel->price > 0): ?>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">
                        <i class="fas fa-euro-sign" style="width: 20px;"></i> Precio
                    </div>
                    <div style="font-weight: 500;">
                        <?php echo number_format($eventModel->price, 2); ?> €
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($eventModel->max_attendees): ?>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">
                        <i class="fas fa-users" style="width: 20px;"></i> Plazas Máximas
                    </div>
                    <div style="font-weight: 500;">
                        <?php echo $eventModel->max_attendees; ?> personas
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($eventModel->requires_registration && $eventModel->registration_deadline): ?>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;">
                        <i class="fas fa-calendar-check" style="width: 20px;"></i> Fecha Límite Inscripción
                    </div>
                    <div style="font-weight: 500;">
                        <?php echo date('d/m/Y', strtotime($eventModel->registration_deadline)); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Stats Card -->
        <div style="background: var(--primary-50); padding: 1.5rem; border-radius: var(--radius-lg);">
            <h3 style="margin-bottom: 1rem; color: var(--primary-700);">Estadísticas de Asistencia</h3>
            
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-600);">
                    <?php echo $attendanceStats['total'] ?? 0; ?>
                </div>
                <div style="color: var(--text-muted); font-size: 0.875rem;">Total inscritos</div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Registrados:</span>
                    <span style="font-weight: 600;"><?php echo $attendanceStats['registered'] ?? 0; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Confirmados:</span>
                    <span style="font-weight: 600; color: var(--secondary-600);"><?php echo $attendanceStats['confirmed'] ?? 0; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Asistieron:</span>
                    <span style="font-weight: 600; color: var(--secondary-600);"><?php echo $attendanceStats['attended'] ?? 0; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Cancelados:</span>
                    <span style="font-weight: 600; color: var(--danger-600);"><?php echo $attendanceStats['cancelled'] ?? 0; ?></span>
                </div>
            </div>
            
            <?php if ($eventModel->max_attendees): ?>
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--primary-200);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.875rem; color: var(--text-muted);">Disponibles:</span>
                    <span style="font-weight: 600;"><?php echo max(0, $eventModel->max_attendees - ($attendanceStats['total'] ?? 0)); ?></span>
                </div>
                <div style="background: white; height: 8px; border-radius: 4px; overflow: hidden;">
                    <?php 
                    $percentage = $eventModel->max_attendees > 0 ? (($attendanceStats['total'] ?? 0) / $eventModel->max_attendees) * 100 : 0;
                    $percentage = min(100, $percentage);
                    ?>
                    <div style="background: var(--primary-600); height: 100%; width: <?php echo $percentage; ?>%; transition: width 0.3s;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Register New Attendee -->
<div class="card" style="margin-bottom: 1.5rem;">
    <h3 style="margin-bottom: 1rem;">Registrar Asistencia</h3>
    <form action="index.php?page=calendar&action=registerAttendance" method="POST" style="display: grid; grid-template-columns: 2fr 1fr 2fr 1fr; gap: 1rem; align-items: end;">
        <input type="hidden" name="event_id" value="<?php echo $eventModel->id; ?>">
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Socio</label>
            <select name="member_id" class="form-control" required>
                <option value="">Seleccionar socio...</option>
                <?php
                $memberQuery = "SELECT id, first_name, last_name FROM members WHERE status = 'active' ORDER BY last_name, first_name";
                $memberStmt = $this->db->prepare($memberQuery);
                $memberStmt->execute();
                while ($member = $memberStmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                    <option value="<?php echo $member['id']; ?>">
                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control">
                <option value="registered">Registrado</option>
                <option value="confirmed">Confirmado</option>
                <option value="attended">Asistió</option>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Notas (opcional)</label>
            <input type="text" name="notes" class="form-control" placeholder="Comentarios adicionales">
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-plus"></i> Registrar
        </button>
    </form>
</div>

<!-- Attendees List -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-light);">
        <h3 style="margin: 0;">Lista de Asistentes (<?php echo count($attendees); ?>)</h3>
    </div>
    
    <?php if (empty($attendees)): ?>
        <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p>No hay asistentes registrados para este evento</p>
        </div>
    <?php else: ?>
        <div class="table-container" style="border: none; border-radius: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Socio</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Notas</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendees as $attendee): ?>
                        <tr>
                            <td style="font-weight: 500;">
                                <?php echo htmlspecialchars($attendee['first_name'] . ' ' . $attendee['last_name']); ?>
                            </td>
                            <td style="font-size: 0.875rem;">
                                <div><i class="fas fa-envelope" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($attendee['email']); ?></div>
                                <?php if ($attendee['phone']): ?>
                                    <div style="margin-top: 0.25rem;"><i class="fas fa-phone" style="color: var(--text-light); width: 20px;"></i> <?php echo htmlspecialchars($attendee['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'registered' => 'background: #dbeafe; color: #1e40af;',
                                    'confirmed' => 'background: #dcfce7; color: #166534;',
                                    'attended' => 'background: #d1fae5; color: #065f46;',
                                    'cancelled' => 'background: #fee2e2; color: #991b1b;'
                                ];
                                $statusLabels = [
                                    'registered' => 'Registrado',
                                    'confirmed' => 'Confirmado',
                                    'attended' => 'Asistió',
                                    'cancelled' => 'Cancelado'
                                ];
                                ?>
                                <span class="badge" style="<?php echo $statusColors[$attendee['status']] ?? ''; ?>">
                                    <?php echo $statusLabels[$attendee['status']] ?? $attendee['status']; ?>
                                </span>
                            </td>
                            <td style="font-size: 0.875rem; color: var(--text-muted);">
                                <?php echo date('d/m/Y H:i', strtotime($attendee['registration_date'])); ?>
                            </td>
                            <td style="font-size: 0.875rem; color: var(--text-muted); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo htmlspecialchars($attendee['notes'] ?? '-'); ?>
                            </td>
                            <td style="text-align: right;">
                                <form action="index.php?page=calendar&action=updateAttendanceStatus" method="POST" style="display: inline-block; margin-right: 0.5rem;">
                                    <input type="hidden" name="attendance_id" value="<?php echo $attendee['id']; ?>">
                                    <input type="hidden" name="event_id" value="<?php echo $eventModel->id; ?>">
                                    <select name="status" class="form-control" style="width: auto; display: inline-block; padding: 0.25rem 0.5rem; font-size: 0.875rem;" onchange="this.form.submit()">
                                        <option value="registered" <?php echo $attendee['status'] === 'registered' ? 'selected' : ''; ?>>Registrado</option>
                                        <option value="confirmed" <?php echo $attendee['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmado</option>
                                        <option value="attended" <?php echo $attendee['status'] === 'attended' ? 'selected' : ''; ?>>Asistió</option>
                                        <option value="cancelled" <?php echo $attendee['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </form>
                                
                                <a href="index.php?page=calendar&action=deleteAttendance&id=<?php echo $attendee['id']; ?>&event_id=<?php echo $eventModel->id; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Eliminar esta asistencia?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
