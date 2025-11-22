<?php ob_start(); ?>

<div class="mb-4">
    <h1><i class="fas fa-map-marked-alt"></i> Mapa de Socios y Donantes</h1>
    <p class="text-muted">Visualiza la ubicación de socios y donantes que han compartido su geolocalización</p>
</div>

<div class="card">
    <div class="mb-3" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary" id="filterAll" onclick="filterMap('all')">
            <i class="fas fa-globe"></i> Todos
        </button>
        <button type="button" class="btn btn-outline-primary" id="filterMembers" onclick="filterMap('members')">
            <i class="fas fa-users"></i> Solo Socios
        </button>
        <button type="button" class="btn btn-outline-primary" id="filterDonors" onclick="filterMap('donors')">
            <i class="fas fa-hand-holding-heart"></i> Solo Donantes
        </button>
    </div>
    
    <div id="map" style="width: 100%; height: 600px; border-radius: 8px;"></div>
    
    <div class="mt-3">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Total geolocalizados:</strong> 
            <span id="memberCount"><?php echo count($members); ?></span> socios (azul) · 
            <span id="donorCount"><?php echo count($donors ?? []); ?></span> donantes (verde)
        </div>
        
        <h5 class="mb-3"><i class="fas fa-users" style="color: #3388ff;"></i> Socios</h5>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <?php foreach ($members as $member): ?>
                <div class="card" style="padding: 0.75rem; border-left: 4px solid #3388ff;">
                    <div class="d-flex align-items-center gap-2">
                        <?php if (!empty($member['photo_url'])): ?>
                            <img src="/<?php echo htmlspecialchars($member['photo_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($member['first_name']); ?>" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #3388ff; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
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
                                    onclick="focusLocation(<?php echo $member['latitude']; ?>, <?php echo $member['longitude']; ?>)"
                                    title="Centrar en el mapa">
                                <i class="fas fa-crosshairs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($donors) && count($donors) > 0): ?>
        <h5 class="mb-3"><i class="fas fa-hand-holding-heart" style="color: #28a745;"></i> Donantes</h5>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
            <?php foreach ($donors as $donor): ?>
                <div class="card" style="padding: 0.75rem; border-left: 4px solid #28a745;">
                    <div class="d-flex align-items-center gap-2">
                        <?php if (!empty($donor['logo_url'])): ?>
                            <img src="/<?php echo htmlspecialchars($donor['logo_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($donor['name']); ?>" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #28a745; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                <?php echo strtoupper(substr($donor['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($donor['name']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                <?php echo htmlspecialchars($donor['address'] ?? 'Sin dirección'); ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.25rem;">
                            <a href="https://www.google.com/maps?q=<?php echo $donor['latitude']; ?>,<?php echo $donor['longitude']; ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-primary" 
                               title="Ver en Google Maps">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-success" 
                                    onclick="focusLocation(<?php echo $donor['latitude']; ?>, <?php echo $donor['longitude']; ?>)"
                                    title="Centrar en el mapa">
                                <i class="fas fa-crosshairs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
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

// Create custom icons for members (blue) and donors (green)
const memberIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNSIgaGVpZ2h0PSI0MSIgdmlld0JveD0iMCAwIDI1IDQxIj48cGF0aCBmaWxsPSIjMzM4OGZmIiBzdHJva2U9IiNmZmYiIHN0cm9rZS13aWR0aD0iMiIgZD0iTTEyLjUgMEMyMC41MDggMCAyNyA1LjYyIDI3IDEyLjU1N2MwIDIuNjc0LS42NiA1LjE5LTEuODI0IDcuMzk2TDEyLjUgNDEgMCA5Ljk1M0MwIDUuNjIgNS40OTIgMCAxMi41IDB6bTAgNS43MjhjLTMuODM1IDAtNi45NDQgMy4wNTQtNi45NDQgNi44MjkgMCAzLjc3NCAzLjEwOSA2LjgyOCA2Ljk0NCA2LjgyOHM2Ljk0NC0zLjA1NCA2Ljk0NC02LjgyOGMwLTMuNzc1LTMuMTA5LTYuODI5LTYuOTQ0LTYuODI5eiIvPjwvc3ZnPg==',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});

const donorIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNSIgaGVpZ2h0PSI0MSIgdmlld0JveD0iMCAwIDI1IDQxIj48cGF0aCBmaWxsPSIjMjhhNzQ1IiBzdHJva2U9IiNmZmYiIHN0cm9rZS13aWR0aD0iMiIgZD0iTTEyLjUgMEMyMC41MDggMCAyNyA1LjYyIDI3IDEyLjU1N2MwIDIuNjc0LS42NiA1LjE5LTEuODI0IDcuMzk2TDEyLjUgNDEgMCA5Ljk1M0MwIDUuNjIgNS40OTIgMCAxMi41IDB6bTAgNS43MjhjLTMuODM1IDAtNi45NDQgMy4wNTQtNi45NDQgNi44MjkgMCAzLjc3NCAzLjEwOSA2LjgyOCA2Ljk0NCA2LjgyOHM2Ljk0NC0zLjA1NCA2Ljk0NC02LjgyOGMwLTMuNzc1LTMuMTA5LTYuODI5LTYuOTQ0LTYuODI5eiIvPjwvc3ZnPg==',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});

// Member and donor data
const members = <?php echo json_encode($members); ?>;
const donors = <?php echo json_encode($donors ?? []); ?>;

// Separate arrays for member and donor markers
const memberMarkers = [];
const donorMarkers = [];
const allMarkers = [];

// Add markers for each member (blue)
members.forEach(function(member) {
    const lat = parseFloat(member.latitude);
    const lng = parseFloat(member.longitude);
    
    if (lat && lng) {
        const marker = L.marker([lat, lng], {icon: memberIcon}).addTo(map);
        
        const popupContent = `
            <div style="min-width: 200px;">
                <div style="background: #3388ff; color: white; padding: 4px 8px; margin: -10px -10px 8px -10px; border-radius: 4px 4px 0 0;">
                    <strong><i class="fas fa-user"></i> SOCIO</strong>
                </div>
                <strong>${member.first_name} ${member.last_name}</strong><br>
                ${member.address || 'Sin dirección'}<br>
                ${member.phone ? '<i class="fas fa-phone"></i> ' + member.phone + '<br>' : ''}
                ${member.email ? '<i class="fas fa-envelope"></i> ' + member.email + '<br>' : ''}
                <div class="mt-2">
                    <a href="index.php?page=members&action=edit&id=${member.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="btn btn-sm btn-success">
                        <i class="fas fa-map"></i> Maps
                    </a>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        const markerData = {marker: marker, lat: lat, lng: lng, type: 'member'};
        memberMarkers.push(markerData);
        allMarkers.push(markerData);
    }
});

// Add markers for each donor (green)
donors.forEach(function(donor) {
    const lat = parseFloat(donor.latitude);
    const lng = parseFloat(donor.longitude);
    
    if (lat && lng) {
        const marker = L.marker([lat, lng], {icon: donorIcon}).addTo(map);
        
        const popupContent = `
            <div style="min-width: 200px;">
                <div style="background: #28a745; color: white; padding: 4px 8px; margin: -10px -10px 8px -10px; border-radius: 4px 4px 0 0;">
                    <strong><i class="fas fa-hand-holding-heart"></i> DONANTE</strong>
                </div>
                <strong>${donor.name}</strong><br>
                ${donor.contact_person ? '<i class="fas fa-user"></i> ' + donor.contact_person + '<br>' : ''}
                ${donor.address || 'Sin dirección'}<br>
                ${donor.phone ? '<i class="fas fa-phone"></i> ' + donor.phone + '<br>' : ''}
                ${donor.email ? '<i class="fas fa-envelope"></i> ' + donor.email + '<br>' : ''}
                <div class="mt-2">
                    <a href="index.php?page=donors&action=edit&id=${donor.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="btn btn-sm btn-success">
                        <i class="fas fa-map"></i> Maps
                    </a>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        const markerData = {marker: marker, lat: lat, lng: lng, type: 'donor'};
        donorMarkers.push(markerData);
        allMarkers.push(markerData);
    }
});

// Adjust map bounds to show all markers
if (allMarkers.length > 0) {
    const group = new L.featureGroup(allMarkers.map(m => m.marker));
    map.fitBounds(group.getBounds().pad(0.1));
}

// Function to filter map markers
let currentFilter = 'all';

function filterMap(filterType) {
    currentFilter = filterType;
    
    // Update button styles
    document.getElementById('filterAll').className = filterType === 'all' ? 'btn btn-primary' : 'btn btn-outline-primary';
    document.getElementById('filterMembers').className = filterType === 'members' ? 'btn btn-primary' : 'btn btn-outline-primary';
    document.getElementById('filterDonors').className = filterType === 'donors' ? 'btn btn-primary' : 'btn btn-outline-primary';
    
    let visibleMarkers = [];
    
    if (filterType === 'all') {
        // Show all markers
        memberMarkers.forEach(m => {
            map.addLayer(m.marker);
            visibleMarkers.push(m);
        });
        donorMarkers.forEach(m => {
            map.addLayer(m.marker);
            visibleMarkers.push(m);
        });
    } else if (filterType === 'members') {
        // Show only members
        memberMarkers.forEach(m => {
            map.addLayer(m.marker);
            visibleMarkers.push(m);
        });
        donorMarkers.forEach(m => map.removeLayer(m.marker));
    } else if (filterType === 'donors') {
        // Show only donors
        donorMarkers.forEach(m => {
            map.addLayer(m.marker);
            visibleMarkers.push(m);
        });
        memberMarkers.forEach(m => map.removeLayer(m.marker));
    }
    
    // Adjust map bounds to show visible markers
    if (visibleMarkers.length > 0) {
        const group = new L.featureGroup(visibleMarkers.map(m => m.marker));
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// Function to focus on a specific location
function focusLocation(lat, lng) {
    map.setView([lat, lng], 16);
    
    // Find and open the marker's popup
    allMarkers.forEach(function(m) {
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
