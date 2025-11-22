<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Nuevo Donante</h1>
    <a href="index.php?page=donors" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <?php if (isset($error)): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=donors&action=store" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nombre del Negocio / Donante</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="contact_person">Persona de Contacto</label>
                <input type="text" id="contact_person" name="contact_person" class="form-control">
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control">
        </div>

        <div class="form-group">
            <label for="address">Dirección</label>
            <div style="position: relative;">
                <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                <button type="button" id="getLocationBtn" class="btn btn-sm btn-success" 
                        style="position: absolute; bottom: 8px; right: 8px;" 
                        onclick="getLocation()" title="Capturar ubicación GPS">
                    <i class="fas fa-map-marker-alt"></i> GPS
                </button>
            </div>
            <small class="text-muted">
                Puedes capturar la ubicación actual con el botón GPS.
                <br><strong>Nota:</strong> La geolocalización requiere HTTPS. Si no funciona, introduce las coordenadas manualmente.
            </small>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.5rem;">
                <input type="text" id="latitudeDisplay" class="form-control form-control-sm" placeholder="Latitud (ej: 40.416775)" 
                       onchange="document.getElementById('latitude').value = this.value">
                <input type="text" id="longitudeDisplay" class="form-control form-control-sm" placeholder="Longitud (ej: -3.703790)"
                       onchange="document.getElementById('longitude').value = this.value">
            </div>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
        </div>

        <div class="form-group">
            <label for="logo">Logo / Imagen (para Libro de Fiestas)</label>
            <input type="file" id="logo" name="logo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                Formatos aceptados: JPG, PNG, GIF, WebP.
            </small>
        </div>

        <div class="flex justify-end gap-2 mt-4">
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
            
            btn.innerHTML = '<i class="fas fa-check"></i> ¡Ubicación capturada!';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
            }, 2000);
        },
        function(error) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            let errorMsg = 'Error al obtener ubicación';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Permiso de ubicación denegado. Actívalo en la configuración del navegador.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Ubicación no disponible';
                    break;
                case error.TIMEOUT:
                    errorMsg = 'Tiempo de espera agotado';
                    break;
            }
            alert(errorMsg);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
