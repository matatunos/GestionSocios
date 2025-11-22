<?php ob_start(); ?>

<style>
.gallery-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 2rem;
}

.gallery-header {
    text-align: center;
    margin-bottom: 3rem;
}

.gallery-header h1 {
    font-size: 2.5rem;
    color: var(--text-color);
    margin-bottom: 0.5rem;
}

.gallery-header p {
    font-size: 1.125rem;
    color: var(--text-muted);
}

/* Tabs Navigation */
.tabs-container {
    margin-bottom: 3rem;
}

.tabs-nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    border-bottom: 3px solid #e5e7eb;
    padding-bottom: 0;
}

.tab-button {
    background: none;
    border: none;
    padding: 1.25rem 2.5rem;
    font-size: 1.125rem;
    font-weight: 700;
    color: #6b7280;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    border-bottom: 4px solid transparent;
    margin-bottom: -3px;
}

.tab-button:hover {
    color: var(--primary-color);
    background: #f9fafb;
}

.tab-button.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.tab-button i {
    margin-right: 0.75rem;
    font-size: 1.25rem;
}

.tab-badge {
    background: #e5e7eb;
    color: #6b7280;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    margin-left: 0.5rem;
    font-weight: 600;
}

.tab-button.active .tab-badge {
    background: var(--primary-color);
    color: white;
}

/* Tab Content */
.tab-content {
    display: none;
    animation: fadeIn 0.3s ease-in;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Gallery Grid */
.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

@media (max-width: 768px) {
    .image-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }
}

.image-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
}

.image-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    border-color: var(--primary-color);
}

.image-wrapper {
    width: 100%;
    height: 250px;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}

.image-wrapper img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    padding: 1rem;
}

.member-photo {
    object-fit: cover !important;
    width: 100%;
    height: 100%;
    padding: 0 !important;
}

.image-info {
    padding: 1.25rem;
    border-top: 2px solid #e5e7eb;
}

.image-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.image-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.3;
    color: #9ca3af;
}

.empty-state h3 {
    font-size: 1.75rem;
    margin-bottom: 0.75rem;
    color: #1f2937;
}

.empty-state p {
    font-size: 1.125rem;
}

/* Image Modal */
.image-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
}

.image-modal.active {
    display: flex;
}

.modal-content {
    max-width: 90%;
    max-height: 90vh;
    position: relative;
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.modal-content img {
    max-width: 100%;
    height: auto;
    object-fit: contain;
}

.modal-close {
    position: absolute;
    top: 2rem;
    right: 2rem;
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.75rem;
    color: #1f2937;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: #f3f4f6;
    transform: scale(1.1);
}

.modal-info {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-top: 1rem;
    text-align: center;
}

.modal-info h3 {
    font-size: 1.5rem;
    color: #1f2937;
    margin: 0;
}

.stats-bar {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: center;
    gap: 3rem;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.25rem;
}
</style>

<div class="gallery-container">
    <div class="gallery-header">
        <h1><i class="fas fa-images"></i> Galería de Imágenes</h1>
        <p>Logos de donantes y fotos de socios</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-button active" onclick="switchTab('donors')" id="tab-donors">
                <i class="fas fa-building"></i>
                Logos Donantes
                <span class="tab-badge"><?php echo count($donors); ?></span>
            </button>
            <button class="tab-button" onclick="switchTab('members')" id="tab-members">
                <i class="fas fa-users"></i>
                Fotos Socios
                <span class="tab-badge"><?php echo count($members); ?></span>
            </button>
        </div>
    </div>

    <!-- Donors Tab -->
    <div class="tab-content active" id="content-donors">
        <?php if (count($donors) > 0): ?>
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($donors); ?></div>
                    <div class="stat-label">Donantes con Logo</div>
                </div>
            </div>

            <div class="image-grid">
                <?php foreach ($donors as $donor): ?>
                    <div class="image-card" onclick="openModal('/<?php echo htmlspecialchars($donor['logo_url']); ?>', '<?php echo htmlspecialchars($donor['name']); ?>')">
                        <div class="image-wrapper">
                            <img src="/<?php echo htmlspecialchars($donor['logo_url']); ?>" 
                                 alt="Logo <?php echo htmlspecialchars($donor['name']); ?>">
                        </div>
                        <div class="image-info">
                            <div class="image-title"><?php echo htmlspecialchars($donor['name']); ?></div>
                            <div class="image-subtitle">
                                <i class="fas fa-building"></i> Donante
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-building"></i>
                <h3>Sin Logos de Donantes</h3>
                <p>No hay donantes con logos registrados.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Members Tab -->
    <div class="tab-content" id="content-members">
        <?php if (count($members) > 0): ?>
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($members); ?></div>
                    <div class="stat-label">Socios con Foto</div>
                </div>
            </div>

            <div class="image-grid">
                <?php foreach ($members as $member): ?>
                    <div class="image-card" onclick="openModal('/<?php echo htmlspecialchars($member['photo_url']); ?>', '<?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>')">
                        <div class="image-wrapper">
                            <img src="/<?php echo htmlspecialchars($member['photo_url']); ?>" 
                                 alt="Foto <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>"
                                 class="member-photo">
                        </div>
                        <div class="image-info">
                            <div class="image-title"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></div>
                            <div class="image-subtitle">
                                <i class="fas fa-user"></i> Socio
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Sin Fotos de Socios</h3>
                <p>No hay socios con fotos registradas.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Image Modal -->
<div class="image-modal" id="imageModal" onclick="closeModal()">
    <button class="modal-close" onclick="closeModal(); event.stopPropagation();">
        <i class="fas fa-times"></i>
    </button>
    <div class="modal-content" onclick="event.stopPropagation();">
        <img src="" alt="" id="modalImage">
        <div class="modal-info">
            <h3 id="modalName"></h3>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('content-' + tabName).classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function openModal(imageUrl, name) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalName').textContent = name;
    document.getElementById('imageModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('imageModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
