/**
 * JavaScript para gestión avanzada de documentos
 * Incluye: favoritos, búsqueda en tiempo real, tooltips
 */

// Manejar favoritos
document.addEventListener('DOMContentLoaded', function() {
    
    // Toggle de favoritos
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const docId = this.getAttribute('data-id');
            const icon = this.querySelector('i');
            
            const formData = new FormData();
            formData.append('id', docId);
            
            try {
                const response = await fetch('index.php?page=documents&action=favorite', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Cambiar icono
                    if (data.action === 'added') {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.classList.add('favorited');
                        this.title = 'Quitar de favoritos';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.classList.remove('favorited');
                        this.title = 'Agregar a favoritos';
                    }
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            }
        });
    });
    
    // Búsqueda en tiempo real (opcional - debounce)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }
    
    // Confirmación de eliminación
    document.querySelectorAll('.delete-document').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de eliminar este documento? Se moverá a la papelera.')) {
                e.preventDefault();
            }
        });
    });
    
    // Preview hover (opcional)
    document.querySelectorAll('.document-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            const preview = this.querySelector('.document-preview');
            if (preview) {
                preview.style.display = 'block';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const preview = this.querySelector('.document-preview');
            if (preview) {
                preview.style.display = 'none';
            }
        });
    });
});
