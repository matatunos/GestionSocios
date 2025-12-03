<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=donors" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Nuevo Donante</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=donors&action=store" enctype="multipart/form-data">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <div class="form-group mb-3">
            <label class="form-label">Nombre del Negocio / Donante</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Persona de Contacto</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" id="phone" name="phone" class="form-control">
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Dirección</label>
            <div style="position: relative;">
                <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                <button type="button" id="getLocationBtn" class="btn btn-sm btn-success" 
                        style="position: absolute; bottom: 8px; right: 8px;" 
                        onclick="getLocation()" title="Capturar ubicación GPS">
                    <i class="fas fa-map-marker-alt"></i> GPS
                </button>
            </div>
            <div class="mt-2">
                <small class="text-muted">
                    Puedes capturar la ubicación actual con el botón GPS.
                    <br><strong>Nota:</strong> La geolocalización requiere HTTPS. Si no funciona, introduce las coordenadas manualmente.
                </small>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <input type="text" id="latitudeDisplay" class="form-control form-control-sm" 
                               placeholder="Latitud (ej: 40.416775)"
                               onchange="document.getElementById('latitude').value = this.value">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="longitudeDisplay" class="form-control form-control-sm" 
                               placeholder="Longitud (ej: -3.703790)"
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
            </div>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Logo / Imagen (para Libro de Fiestas)</label>
            <input type="file" id="logo" name="logo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
            <small class="text-muted">Formatos aceptados: JPG, PNG, GIF, WebP. Tamaño máximo: 2MB.</small>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Donante
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

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
