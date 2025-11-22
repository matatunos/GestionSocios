<?php ob_start(); ?>

<div class="mb-4">
    <h1><i class="fas fa-map-marked-alt"></i> Mapa de Socios</h1>
    <p class="text-muted">Visualiza la ubicación de los socios que han compartido su geolocalización</p>
</div>

<div class="card">
    <div id="map" style="width: 100%; height: 600px; border-radius: 8px;"></div>
    
    <div class="mt-3">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Total de socios geolocalizados:</strong> 
            <span id="memberCount"><?php echo count($members); ?></span>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
            <?php foreach ($members as $member): ?>
                <div class="card" style="padding: 0.75rem;">
                    <div class="d-flex align-items-center gap-2">
                        <?php if (!empty($member['photo_url'])): ?>
                            <img src="/<?php echo htmlspecialchars($member['photo_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($member['first_name']); ?>" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                <?php echo strtoupper(substr($member['first_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                <?php echo htmlspecialchars($member['address'] ?? 'Sin dirección'); ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.25rem;">
                            <a href="https://www.google.com/maps?q=<?php echo $member['latitude']; ?>,<?php echo $member['longitude']; ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-primary" 
                               title="Ver en Google Maps">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-success" 
                                    onclick="focusMember(<?php echo $member['latitude']; ?>, <?php echo $member['longitude']; ?>, '<?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name'], ENT_QUOTES); ?>')"
                                    title="Centrar en el mapa">
                                <i class="fas fa-crosshairs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
// Initialize the map
const map = L.map('map').setView([40.4168, -3.7038], 6); // Center on Spain by default

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

// Member data
const members = <?php echo json_encode($members); ?>;

// Add markers for each member
const markers = [];
members.forEach(function(member) {
    const lat = parseFloat(member.latitude);
    const lng = parseFloat(member.longitude);
    
    if (lat && lng) {
        const marker = L.marker([lat, lng]).addTo(map);
        
        const popupContent = `
            <div style="min-width: 200px;">
                <strong>${member.first_name} ${member.last_name}</strong><br>
                ${member.address || 'Sin dirección'}<br>
                ${member.phone ? '<i class="fas fa-phone"></i> ' + member.phone + '<br>' : ''}
                ${member.email ? '<i class="fas fa-envelope"></i> ' + member.email + '<br>' : ''}
                <div class="mt-2">
                    <a href="index.php?page=members&action=edit&id=${member.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="btn btn-sm btn-success">
                        <i class="fas fa-map"></i> Google Maps
                    </a>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        markers.push({marker: marker, lat: lat, lng: lng, name: `${member.first_name} ${member.last_name}`});
    }
});

// Adjust map bounds to show all markers
if (markers.length > 0) {
    const group = new L.featureGroup(markers.map(m => m.marker));
    map.fitBounds(group.getBounds().pad(0.1));
}

// Function to focus on a specific member
function focusMember(lat, lng, name) {
    map.setView([lat, lng], 16);
    
    // Find and open the marker's popup
    markers.forEach(function(m) {
        if (m.lat === lat && m.lng === lng) {
            m.marker.openPopup();
        }
    });
}
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
