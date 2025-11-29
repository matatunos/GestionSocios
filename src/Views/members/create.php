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


            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('latitudeDisplay').value = lat.toFixed(6);
            document.getElementById('longitudeDisplay').value = lng.toFixed(6);
            
            // Update button
            btn.innerHTML = '<i class="fas fa-check"></i> ¡Ubicación capturada!';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            
            // Show success message
            const addressField = document.getElementById('address');
            if (!addressField.value || addressField.value.trim() === '') {
                addressField.placeholder = `Ubicación capturada: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
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
