
<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=members" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Editar Socio</h1>
<?php $content = ob_get_clean(); ?>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=members&action=update&id=<?php echo $member->id; ?>" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nombre</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($member->first_name); ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Apellidos</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($member->last_name); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">DNI/NIE</label>
                <input type="text" name="dni" class="form-control" value="<?php echo htmlspecialchars($member->dni ?? ''); ?>" placeholder="Ej: 12345678A">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member->email); ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Teléfono</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($member->phone); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <!-- Spacer -->
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dirección</label>
            <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($member->address); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Foto de Perfil</label>
            <?php if (!empty($member->photo_url)): ?>
<?php include __DIR__ . '/../layout.php'; ?>
                <div class="mb-2">
                    <img src="/<?php echo htmlspecialchars($member->photo_url); ?>" alt="Foto actual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                    <div class="flex gap-2 mt-2">
                        <a href="/<?php echo htmlspecialchars($member->photo_url); ?>" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="/<?php echo htmlspecialchars($member->photo_url); ?>" download class="btn btn-sm btn-secondary">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                        <?php 
                        require_once __DIR__ . '/../../Models/MemberImageHistory.php';
                        $database = new Database();
                        $imageHistory = new MemberImageHistory($database->getConnection());
                        if ($imageHistory->countByMember($member->id) > 0): 
                        ?>
                            <a href="index.php?page=members&action=imageHistory&id=<?php echo $member->id; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-history"></i> Histórico (<?php echo $imageHistory->countByMember($member->id); ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" name="photo" class="form-control" accept="image/*">
            <small style="color: var(--text-muted);">Dejar en blanco para mantener la foto actual.</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Categoría</label>
                <select name="category_id" class="form-control">
                    <option value="">Sin categoría</option>
                    <?php if (isset($categories) && is_array($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($member->category_id) && $member->category_id == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                                (<?php echo number_format($category['default_fee'], 2); ?>€)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo $member->status === 'active' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactive" <?php echo $member->status === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Socio
            </button>
        </div>
    </form>
</div>
<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=members" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Editar Socio</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=members&action=update&id=<?php echo $member->id; ?>" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nombre</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($member->first_name); ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Apellidos</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($member->last_name); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">DNI/NIE</label>
                <input type="text" name="dni" class="form-control" value="<?php echo htmlspecialchars($member->dni ?? ''); ?>" placeholder="Ej: 12345678A">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member->email); ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Teléfono</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($member->phone); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <!-- Spacer -->
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dirección</label>
            <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($member->address); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Foto de Perfil</label>
            <?php if (!empty($member->photo_url)): ?>
                <div class="mb-2">
                    <img src="/<?php echo htmlspecialchars($member->photo_url); ?>" alt="Foto actual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                    <div class="flex gap-2 mt-2">
                        <a href="/<?php echo htmlspecialchars($member->photo_url); ?>" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="/<?php echo htmlspecialchars($member->photo_url); ?>" download class="btn btn-sm btn-secondary">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                        <?php 
                        require_once __DIR__ . '/../../Models/MemberImageHistory.php';
                        $database = new Database();
                        $imageHistory = new MemberImageHistory($database->getConnection());
                        if ($imageHistory->countByMember($member->id) > 0): 
                        ?>
                            <a href="index.php?page=members&action=imageHistory&id=<?php echo $member->id; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-history"></i> Histórico (<?php echo $imageHistory->countByMember($member->id); ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" name="photo" class="form-control" accept="image/*">
            <small style="color: var(--text-muted);">Dejar en blanco para mantener la foto actual.</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Categoría</label>
                <select name="category_id" class="form-control">
                    <option value="">Sin categoría</option>
                    <?php if (isset($categories) && is_array($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($member->category_id) && $member->category_id == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                                (<?php echo number_format($category['default_fee'], 2); ?>€)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo $member->status === 'active' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactive" <?php echo $member->status === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Socio
            </button>
        </div>
    </form>
</div>

<script>
function getLocation() {
    const btn = document.getElementById('getLocationBtn');
    const originalHTML = btn.innerHTML;
    
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización');
        return;
    }
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Capturando...';
    btn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('latitudeDisplay').value = lat.toFixed(6);
            document.getElementById('longitudeDisplay').value = lng.toFixed(6);
            
            // Update button
            btn.innerHTML = '<i class="fas fa-check"></i> ¡Ubicación capturada!';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            
            // Show coordinates
            alert(`Ubicación capturada correctamente:\nLatitud: ${lat.toFixed(6)}\nLongitud: ${lng.toFixed(6)}\n\nGuarda el formulario para conservar la ubicación.`);
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2000);
        }
    );
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
