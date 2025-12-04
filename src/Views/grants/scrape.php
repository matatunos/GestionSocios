<?php
/**
 * Vista de Scraper de Subvenciones
 * Interfaz para buscar y scraper subvenciones de fuentes externas
 */
ob_start();
?>
<div class="content-wrapper">
    <div class="content-header">
        <h1><i class="fas fa-sync"></i> Buscador Automático de Subvenciones</h1>
        <a href="index.php?page=grants" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-search"></i> Configurar Búsqueda</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?page=grants&action=scrape">
                <div class="form-group">
                    <label for="source">Fuente de Datos</label>
                    <select name="source" id="source" class="form-control" required>
                        <option value="bdns">BDNS (Base de Datos Nacional de Subvenciones)</option>
                        <option value="europa">Portal de Financiación de la UE</option>
                        <option value="junta">Junta de Andalucía</option>
                        <option value="diputacion">Diputación Provincial</option>
                        <option value="ayuntamiento">Ayuntamiento Local</option>
                    </select>
                    <small class="form-text text-muted">
                        Selecciona la fuente de donde buscar subvenciones
                    </small>
                </div>

                <div class="form-group">
                    <label for="keywords">Palabras Clave</label>
                    <input type="text" name="keywords" id="keywords" class="form-control" 
                           placeholder="Ej: asociación, cultura, deporte, juventud">
                    <small class="form-text text-muted">
                        Separa múltiples palabras clave con comas
                    </small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="min_amount">Importe Mínimo (€)</label>
                        <input type="number" name="min_amount" id="min_amount" class="form-control" 
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="max_amount">Importe Máximo (€)</label>
                        <input type="number" name="max_amount" id="max_amount" class="form-control" 
                               min="0" step="0.01" placeholder="Sin límite">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="auto_save" name="auto_save" checked>
                        <label class="custom-control-label" for="auto_save">
                            Guardar automáticamente resultados relevantes
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="notify" name="notify" checked>
                        <label class="custom-control-label" for="notify">
                            Notificarme cuando se encuentren nuevas subvenciones
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar Subvenciones
                </button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Información sobre el Scraper</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h5><i class="fas fa-lightbulb"></i> ¿Cómo funciona?</h5>
                <p>
                    El sistema de scraping automático busca subvenciones en las fuentes oficiales seleccionadas
                    utilizando las palabras clave proporcionadas. 
                </p>
                <ul>
                    <li><strong>BDNS:</strong> Base de Datos Nacional de Subvenciones del Gobierno de España</li>
                    <li><strong>Portal UE:</strong> Programa de financiación de la Unión Europea</li>
                    <li><strong>Junta:</strong> Convocatorias de la Junta de Andalucía</li>
                    <li><strong>Diputación:</strong> Ayudas provinciales y locales</li>
                </ul>
            </div>

            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Nota Importante</h5>
                <p>
                    Esta funcionalidad está en fase de desarrollo. En producción, implementará scraping real
                    de las fuentes oficiales. Actualmente muestra una interfaz de configuración.
                </p>
                <p class="mb-0">
                    <strong>Próximas funcionalidades:</strong>
                </p>
                <ul class="mb-0">
                    <li>Integración con APIs oficiales (BDNS, etc.)</li>
                    <li>Web scraping de portales públicos</li>
                    <li>Notificaciones automáticas por email</li>
                    <li>Búsquedas programadas (cron jobs)</li>
                    <li>Matching inteligente con el perfil de la asociación</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .content-wrapper {
        padding: 20px;
    }
    
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header h3 {
        margin: 0;
        font-size: 1.2rem;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: #007bff;
        color: white;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
    }
    
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-danger {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert-info {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
    }
    
    .alert-warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
