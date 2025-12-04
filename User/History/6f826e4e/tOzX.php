<?php
// This view will be wrapped by layout.php
$content = '
<div class="map-container">
    <div class="map-controls card">
        <h2><i class="fas fa-map-marked-alt"></i> Mapa Global - Año ' . date('Y') . '</h2>
        <div class="filter-buttons">
            <button class="btn btn-primary active" id="filterAll" onclick="filterMarkers(\'all\')">
                <i class="fas fa-globe"></i> Todos
            </button>
            <button class="btn btn-secondary" id="filterMembers" onclick="filterMarkers(\'member\')">
                <i class="fas fa-users"></i> Solo Socios
            </button>
            <button class="btn btn-secondary" id="filterDonors" onclick="filterMarkers(\'donor\')">
                <i class="fas fa-hand-holding-heart"></i> Solo Donantes
            </button>
            <button class="btn btn-secondary" id="filterPending" onclick="filterMarkers(\'pending\')">
                <i class="fas fa-exclamation-triangle"></i> Pendientes de Pago
            </button>
        </div>
        <div class="map-legend">
            <div class="legend-title"><strong>Leyenda:</strong></div>
            <div class="legend-items">
                <span class="legend-item">
                    <span class="legend-marker" style="background-color: #22c55e;"></span>
                    <span>Al día (Socios)</span>
                </span>
                <span class="legend-item">
                    <span class="legend-marker" style="background-color: #f59e0b;"></span>
                    <span>Pendiente (Socios)</span>
                </span>
                <span class="legend-item">
                    <span class="legend-marker" style="background-color: #10b981;"></span>
                    <span>Ha donado (Donantes)</span>
                </span>
                <span class="legend-item">
                    <span class="legend-marker" style="background-color: #f97316;"></span>
                    <span>Sin donar (Donantes)</span>
                </span>
            </div>
        </div>
        <div class="map-stats">
            <span class="stat-badge stat-paid" id="paidCount">Al día: 0</span>
            <span class="stat-badge stat-pending" id="pendingCount">Pendientes: 0</span>
        </div>
    </div>
    <div id="map" style="height: calc(100vh - 200px); width: 100%; border-radius: var(--radius-lg); overflow: hidden;"></div>
</div>

<style>
.map-container {
    padding: 1rem;
}

.map-controls {
    margin-bottom: 1.5rem;
    padding: 1.5rem;
}

.map-controls h2 {
    margin-bottom: 1rem;
    color: var(--primary-600);
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.filter-buttons .btn {
    flex: 1;
    min-width: 150px;
}

.map-legend {
    margin-bottom: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-light);
}

.map-legend .legend-title {
    margin-bottom: 0.5rem;
    color: var(--text-main);
}

.legend-items {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
}

.legend-marker {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.map-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-badge {
    background: var(--primary-50);
    color: var(--primary-700);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 0.9rem;
}

.stat-badge.stat-paid {
    background: #d1fae5;
    color: #065f46;
}

.stat-badge.stat-pending {
    background: #fef3c7;
    color: #92400e;
}

.leaflet-popup-content {
    font-family: \'Inter\', sans-serif;
}

.popup-content h3 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-600);
    font-size: 1.1rem;
}

.popup-content p {
    margin: 0.25rem 0;
    font-size: 0.9rem;
}

.popup-content .type-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-md);
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.popup-content .type-badge.member {
    background: #3b82f6;
    color: white;
}

.popup-content .type-badge.donor {
    background: #ef4444;
    color: white;
}

.popup-content .status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-md);
    font-size: 0.75rem;
    font-weight: 600;
}

.popup-content .status-badge.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.popup-content .status-badge.status-pending {
    background: #fef3c7;
    color: #92400e;
}

/* Marker labels that appear on zoom */
.marker-label {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-main);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    white-space: nowrap;
}

.marker-label::before {
    display: none;
}
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let markers = [];
let allLocations = [];
let currentFilter = \'all\';

// Initialize map
document.addEventListener(\'DOMContentLoaded\', function() {
    // Create map centered on Spain
    map = L.map(\'map\').setView([40.4168, -3.7038], 6);
    
    // Define base layers
    const streetMap = L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
        attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',
        maxZoom: 19
    });
    
    const satelliteMap = L.tileLayer(\'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}\', {
        attribution: \'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community\',
        maxZoom: 19
    });
    
    const hybridBase = L.tileLayer(\'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}\', {
        maxZoom: 19
    });
    
    const labels = L.tileLayer(\'https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}.png\', {
        maxZoom: 19,
        pane: \'shadowPane\'
    });
    
    // Add default layer (street map)
    streetMap.addTo(map);
    
    // Create layer control
    const baseMaps = {
        "🗺️ Mapa de calles": streetMap,
        "🛰️ Satélite": satelliteMap,
        "🌍 Híbrido": L.layerGroup([hybridBase, labels])
    };
    
    L.control.layers(baseMaps).addTo(map);
    
    // Load locations
    loadLocations();
    
    // Listen to zoom and move changes to update label visibility
    map.on(\'zoomend moveend\', updateLabelVisibility);
});

function loadLocations() {
    fetch(\'index.php?page=map&action=getLocations\')
        .then(response => response.json())
        .then(data => {
            allLocations = data;
            displayMarkers(data);
            updateStats();
        })
        .catch(error => {
            console.error(\'Error loading locations:\', error);
            alert(\'Error al cargar las ubicaciones\');
        });
}

function displayMarkers(locations) {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
    
    if (locations.length === 0) {
        return;
    }
    
    // Create bounds to fit all markers
    let bounds = [];
    
    locations.forEach(location => {
        const lat = parseFloat(location.lat);
        const lng = parseFloat(location.lng);
        
        if (isNaN(lat) || isNaN(lng)) return;
        
        // Choose icon color based on type and payment status
        let iconColor;
        let statusLabel;
        let statusClass;
        
        if (location.type === \'member\') {
            iconColor = location.paid ? \'#22c55e\' : \'#f59e0b\'; // Verde si pagó, naranja si no
            statusLabel = location.paid ? \'Al día\' : \'Pendiente de pago\';
            statusClass = location.paid ? \'status-paid\' : \'status-pending\';
        } else {
            iconColor = location.paid ? \'#10b981\' : \'#f97316\'; // Verde oscuro si donó, naranja oscuro si no
            statusLabel = location.paid ? \'Ha donado este año\' : \'Sin donar este año\';
            statusClass = location.paid ? \'status-paid\' : \'status-pending\';
        }
        
        const icon = L.divIcon({
            className: \'custom-marker\',
            html: `<div style="background-color: ${iconColor}; width: 25px; height: 25px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
            iconSize: [25, 25],
            iconAnchor: [12, 12]
        });
        
        const marker = L.marker([lat, lng], { icon: icon })
            .bindPopup(`
                <div class="popup-content">
                    <span class="type-badge ${location.type}">
                        ${location.type === \'member\' ? \'Socio\' : \'Donante\'}
                    </span>
                    <span class="status-badge ${statusClass}" style="margin-left: 0.5rem;">
                        ${statusLabel}
                    </span>
                    <h3>${location.name}</h3>
                    ${location.address ? `<p><i class="fas fa-map-marker-alt"></i> ${location.address}</p>` : \'\'}
                    <p><a href="index.php?page=${location.type === \'member\' ? \'members\' : \'donors\'}&action=edit&id=${location.id}" class="btn btn-sm btn-primary" style="margin-top: 0.5rem;">
                        <i class="fas fa-edit"></i> Ver detalles
                    </a></p>
                </div>
            `);
        
        marker.locationData = location;
        marker.addTo(map);
        markers.push(marker);
        bounds.push([lat, lng]);
    });
    
    // Fit map to show all markers
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
}

// Optimized function to show/hide labels only for visible markers
function updateLabelVisibility() {
    const zoom = map.getZoom();
    const showLabels = zoom >= 14; // Show labels at zoom level 14 and above
    
    if (!showLabels) {
        // Remove all tooltips when zoom is too low
        markers.forEach(marker => {
            if (marker.getTooltip()) {
                marker.unbindTooltip();
            }
        });
        return;
    }
    
    // Get map bounds
    const bounds = map.getBounds();
    
    // Only process visible markers
    markers.forEach(marker => {
        const markerLatLng = marker.getLatLng();
        const isVisible = bounds.contains(markerLatLng);
        
        if (isVisible) {
            // Add tooltip if it doesn\'t exist
            if (!marker.getTooltip()) {
                marker.bindTooltip(marker.locationData.name, {
                    permanent: true,
                    direction: \'top\',
                    className: \'marker-label\',
                    offset: [0, -15]
                });
                marker.openTooltip();
            }
        } else {
            // Remove tooltip if marker is not visible
            if (marker.getTooltip()) {
                marker.unbindTooltip();
            }
        }
    });
}

function filterMarkers(type) {
    currentFilter = type;
    
    // Update button states
    document.querySelectorAll(\'.filter-buttons .btn\').forEach(btn => {
        btn.classList.remove(\'active\');
        btn.classList.add(\'btn-secondary\');
        btn.classList.remove(\'btn-primary\');
    });
    
    let activeBtn;
    if (type === \'all\') activeBtn = \'filterAll\';
    else if (type === \'member\') activeBtn = \'filterMembers\';
    else if (type === \'donor\') activeBtn = \'filterDonors\';
    else if (type === \'pending\') activeBtn = \'filterPending\';
    
    document.getElementById(activeBtn).classList.add(\'active\');
    document.getElementById(activeBtn).classList.add(\'btn-primary\');
    document.getElementById(activeBtn).classList.remove(\'btn-secondary\');
    
    // Filter locations
    let filteredLocations;
    if (type === \'all\') {
        filteredLocations = allLocations;
    } else if (type === \'pending\') {
        filteredLocations = allLocations.filter(loc => !loc.paid);
    } else {
        filteredLocations = allLocations.filter(loc => loc.type === type);
    }
    
    displayMarkers(filteredLocations);
}

function updateStats() {
    const paidCount = allLocations.filter(loc => loc.paid).length;
    const pendingCount = allLocations.filter(loc => !loc.paid).length;
    
    document.getElementById(\'paidCount\').textContent = `Al día: ${paidCount}`;
    document.getElementById(\'pendingCount\').textContent = `Pendientes: ${pendingCount}`;
}
</script>
';

require __DIR__ . '/../layout.php';
?>
