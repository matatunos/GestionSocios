<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=members" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Editar Socio</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=members&action=update&id=<?php echo $member->id; ?>" method="POST" enctype="multipart/form-data">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <div class="row mb-3">
                <div class="form-group">
                    <label class="form-label">Fecha de alta</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="date" name="created_at" id="created_at" class="form-control" value="<?php echo date('Y-m-d', strtotime($member->created_at)); ?>">
                        <button type="button" id="calendarBtn" style="background: none; border: none; position: absolute; right: 10px; cursor: pointer;">
                            <i class="fas fa-calendar-alt" style="font-size: 1.2rem; color: #555;"></i>
                        </button>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var btn = document.getElementById('calendarBtn');
                            var input = document.getElementById('created_at');
                            btn.addEventListener('click', function(e) {
                                input.focus();
                                // Intenta abrir el selector de fecha si el navegador lo soporta
                                if (typeof input.showPicker === 'function') {
                                    input.showPicker();
                                } else {
                                    // Para navegadores que no soportan showPicker, simula el click
                                    var evt = document.createEvent('MouseEvents');
                                    evt.initEvent('click', true, true);
                                    input.dispatchEvent(evt);
                                }
                            });
                        });
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($member->first_name); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($member->last_name); ?>" required>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">DNI/NIE</label>
                    <input type="text" name="dni" class="form-control" value="<?php echo htmlspecialchars($member->dni ?? ''); ?>" placeholder="Ej: 12345678A">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member->email); ?>">
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($member->phone); ?>">
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Dirección</label>
            <div style="position: relative;">
                <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($member->address); ?></textarea>
                <button type="button" id="getLocationBtn" class="btn btn-sm btn-success" 
                        style="position: absolute; bottom: 8px; right: 8px;" 
                        onclick="getLocation()" title="Capturar ubicación GPS">
                    <i class="fas fa-map-marker-alt"></i> GPS
                </button>
            </div>
            <div class="mt-2">
                <small class="text-muted">
                    Puedes capturar tu ubicación actual con el botón GPS.
                    <br><strong>Nota:</strong> La geolocalización requiere HTTPS. Si no funciona, introduce las coordenadas manualmente.
                </small>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <input type="text" id="latitudeDisplay" class="form-control form-control-sm" 
                               placeholder="Latitud (ej: 40.416775)"
                               value="<?php echo !empty($member->latitude) ? number_format($member->latitude, 6, '.', '') : ''; ?>"
                               onchange="document.getElementById('latitude').value = this.value">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="longitudeDisplay" class="form-control form-control-sm" 
                               placeholder="Longitud (ej: -3.703790)"
                               value="<?php echo !empty($member->longitude) ? number_format($member->longitude, 6, '.', '') : ''; ?>"
                               onchange="document.getElementById('longitude').value = this.value">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-secondary" id="reverseGeoBtn" style="margin-top: 4px;">
                        <i class="fas fa-search-location"></i> Obtener dirección por coordenadas
                    </button>
                </div>
                <script>
                document.getElementById('reverseGeoBtn').addEventListener('click', function() {
                    var lat = document.getElementById('latitudeDisplay').value;
                    var lng = document.getElementById('longitudeDisplay').value;
                    if (!lat || !lng) {
                        alert('Introduce latitud y longitud válidas.');
                        return;
                    }
                    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng))
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.address) {
                                var address = '';
                                if (data.address.road) address += data.address.road + ', ';
                                if (data.address.house_number) address += data.address.house_number + ', ';
                                if (data.address.postcode) address += data.address.postcode + ', ';
                                if (data.address.city) address += data.address.city + ', ';
                                if (data.address.town) address += data.address.town + ', ';
                                if (data.address.village) address += data.address.village + ', ';
                                if (data.address.state) address += data.address.state + ', ';
                                if (data.address.country) address += data.address.country;
                                document.getElementById('address').value = address.trim().replace(/, $/, '');
                            } else {
                                alert('No se pudo obtener la dirección.');
                            }
                        })
                        .catch(() => alert('Error al consultar el servicio de geolocalización.'));
                });
                </script>
                <?php if (!empty($member->latitude) && !empty($member->longitude)): ?>
                    <div class="mt-2">
                        <a href="https://www.google.com/maps?q=<?php echo $member->latitude; ?>,<?php echo $member->longitude; ?>" 
                           target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-map-marked-alt"></i> Ver en Google Maps
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <input type="hidden" name="latitude" id="latitude" value="<?php echo htmlspecialchars($member->latitude ?? ''); ?>">
            <input type="hidden" name="longitude" id="longitude" value="<?php echo htmlspecialchars($member->longitude ?? ''); ?>">
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Foto de Perfil</label>
            <?php if (!empty($member->photo_url)): ?>
                <div class="mb-2">
                    <img src="/<?php echo htmlspecialchars($member->photo_url); ?>" alt="Foto actual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                    <div class="d-flex gap-2 mt-2">
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
            <small class="text-muted">Dejar en blanco para mantener la foto actual.</small>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
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
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-control">
                        <option value="active" <?php echo $member->status === 'active' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactive" <?php echo $member->status === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
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
            
            // Perform reverse geocoding
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obteniendo dirección...';
            
            fetch(`index.php?page=geo&action=reverse&lat=${lat}&lng=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error en geocoding:', data.error);
                        alert(`Ubicación capturada correctamente:\nLatitud: ${lat.toFixed(6)}\nLongitud: ${lng.toFixed(6)}\n\nNo se pudo obtener la dirección automáticamente.`);
                    } else {
                        // Fill address field with the complete address
                        const addressField = document.getElementById('address');
                        if (data.direccion_completa) {
                            addressField.value = data.direccion_completa;
                        }
                        
                        alert(`Ubicación y dirección capturadas:\nLatitud: ${lat.toFixed(6)}\nLongitud: ${lng.toFixed(6)}\nDirección: ${data.direccion_completa || 'No disponible'}\n\nGuarda el formulario para conservar los cambios.`);
                    }
                    
                    btn.innerHTML = '<i class="fas fa-check"></i> ¡Completado!';
                    btn.disabled = false;
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-success');
                    }, 3000);
                })
                .catch(error => {
                    console.error('Error al obtener dirección:', error);
                    alert(`Ubicación capturada correctamente:\nLatitud: ${lat.toFixed(6)}\nLongitud: ${lng.toFixed(6)}\n\nNo se pudo obtener la dirección automáticamente.`);
                    
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                });
        },
        function(error) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            alert('Error al obtener la ubicación: ' + error.message);
        }
    );
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
