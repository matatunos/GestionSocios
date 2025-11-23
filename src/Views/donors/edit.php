<?php ob_start(); ?>

<div class="flex justify-between items-center mb-4">
    <h1>Editar Donante</h1>
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

    <form method="POST" action="index.php?page=donors&action=update&id=<?php echo $donor->id; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nombre del Negocio / Donante</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($donor->name); ?>" required>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="contact_person">Persona de Contacto</label>
                <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($donor->contact_person); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($donor->phone); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($donor->email); ?>">
        </div>

        <div class="form-group">
            <label for="address">Dirección</label>
            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($donor->address); ?></textarea>
        </div>


    
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
            
            alert(`Ubicación capturada correctamente:\\nLatitud: ${lat.toFixed(6)}\\nLongitud: ${lng.toFixed(6)}\\n\\nGuarda el formulario para conservar la ubicación.`);
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
            }, 3000);
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
