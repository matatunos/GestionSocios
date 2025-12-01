<?php ob_start(); ?>

<div class="mb-4">
    <a href="index.php?page=members" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Registrar Nuevo Socio</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=members&action=store" method="POST" enctype="multipart/form-data">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nombre</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Apellidos</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">DNI/NIE</label>
                <input type="text" name="dni" class="form-control" placeholder="Ej: 12345678A">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Teléfono</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <!-- Spacer -->
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dirección</label>
            <textarea name="address" id="address" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Ubicación GPS</label>
            <div style="display: flex; gap: 0.5em; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-success" id="getLocationBtn" onclick="getLocation(this)">
                    <i class="fas fa-location-arrow"></i> Capturar ubicación
                </button>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="text" id="latitudeDisplay" class="form-control" style="width: 120px;" placeholder="Latitud" readonly>
                <input type="text" id="longitudeDisplay" class="form-control" style="width: 120px;" placeholder="Longitud" readonly>
                <a href="#" id="mapLink" class="btn btn-info" style="margin-left:0.5em;display:none;" target="_blank">
                    <i class="fas fa-map-marked-alt"></i> Ver en mapa
                </a>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Socio</button>
    </form>
</div>

<script>
function getLocation(btn) {
    if (!navigator.geolocation) {
        alert('Geolocalización no soportada por este navegador.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-location-arrow"></i> Capturar ubicación';
        return false;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localizando...';
    navigator.geolocation.getCurrentPosition(function(position) {
        setLocation(position.coords.latitude, position.coords.longitude, btn);
    }, function(error) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-location-arrow"></i> Capturar ubicación';
        alert('No se pudo obtener la ubicación: ' + error.message);
    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
}
function setLocation(lat, lng, btn) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('latitudeDisplay').value = lat.toFixed(6);
    document.getElementById('longitudeDisplay').value = lng.toFixed(6);
    btn.innerHTML = '<i class="fas fa-check"></i> ¡Ubicación capturada!';
    btn.classList.remove('btn-success');
    btn.classList.add('btn-primary');
    const addressField = document.getElementById('address');
    if (!addressField.value || addressField.value.trim() === '') {
        addressField.placeholder = `Ubicación capturada: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }
    // Mostrar link al mapa
    const mapLink = document.getElementById('mapLink');
    mapLink.href = `https://www.google.com/maps?q=${lat},${lng}`;
    mapLink.style.display = 'inline-block';
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-location-arrow"></i> Capturar ubicación';
        btn.disabled = false;
    }, 2000);
}
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
