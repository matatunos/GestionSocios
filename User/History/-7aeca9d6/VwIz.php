<?php
$pageTitle = 'Detalles del Proveedor';
ob_start();
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-truck"></i> <?php echo htmlspecialchars($this->supplier->name); ?></h1>
        <p class="text-muted">Detalles y gestión de facturas</p>
    </div>
    <div class="header-actions">
        <a href="index.php?page=suppliers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <a href="index.php?page=suppliers&action=edit&id=<?php echo $this->supplier->id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Supplier Info Sidebar -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <?php if (!empty($this->supplier->logo_path) && file_exists($this->supplier->logo_path)): ?>
                    <img src="<?php echo htmlspecialchars($this->supplier->logo_path); ?>" alt="Logo" class="img-fluid rounded mb-3" style="max-height: 150px;">
                <?php else: ?>
                    <div class="bg-light rounded p-4 mb-3 d-inline-block">
                        <i class="fas fa-building fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                
                <h3 class="h5 mb-1"><?php echo htmlspecialchars($this->supplier->name); ?></h3>
                <p class="text-muted mb-3"><?php echo htmlspecialchars($this->supplier->cif_nif); ?></p>
                
                <div class="text-left mt-4">
                    <?php if (!empty($this->supplier->email)): ?>
                        <div class="mb-2"><i class="fas fa-envelope w-6 text-center mr-2 text-muted"></i> <a href="mailto:<?php echo htmlspecialchars($this->supplier->email); ?>"><?php echo htmlspecialchars($this->supplier->email); ?></a></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($this->supplier->phone)): ?>
                        <div class="mb-2"><i class="fas fa-phone w-6 text-center mr-2 text-muted"></i> <?php echo htmlspecialchars($this->supplier->phone); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($this->supplier->website)): ?>
                        <div class="mb-2"><i class="fas fa-globe w-6 text-center mr-2 text-muted"></i> <a href="<?php echo htmlspecialchars($this->supplier->website); ?>" target="_blank">Sitio Web</a></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($this->supplier->address)): ?>
                        <div class="mb-2"><i class="fas fa-map-marker-alt w-6 text-center mr-2 text-muted"></i> <?php echo nl2br(htmlspecialchars($this->supplier->address)); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($this->supplier->notes)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title h6 mb-0">Notas Internas</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($this->supplier->notes)); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Invoices Section -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title h5 mb-0"><i class="fas fa-file-invoice-dollar mr-2"></i> Facturas y Documentos</h3>
                <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('uploadModal').style.display='block'">
                    <i class="fas fa-upload"></i> Subir Factura
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Nº Factura</th>
                                <th>Importe</th>
                                <th>Estado</th>
                                <th>Archivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No hay facturas registradas
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?></td>
                                        <td class="font-weight-bold"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                        <td><?php echo number_format($inv['amount'], 2, ',', '.'); ?> €</td>
                                        <td>
                                            <?php
                                            $statusClass = 'badge-secondary';
                                            $statusLabel = 'Pendiente';
                                            if ($inv['status'] === 'paid') {
                                                $statusClass = 'badge-success';
                                                $statusLabel = 'Pagada';
                                            } elseif ($inv['status'] === 'cancelled') {
                                                $statusClass = 'badge-danger';
                                                $statusLabel = 'Cancelada';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($inv['file_path']) && file_exists($inv['file_path'])): ?>
                                                <a href="<?php echo htmlspecialchars($inv['file_path']); ?>" target="_blank" class="text-primary" title="Ver archivo">
                                                    <i class="fas fa-file-pdf fa-lg"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($inv['status'] !== 'paid'): ?>
                                                <a href="index.php?page=suppliers&action=updateInvoiceStatus&id=<?php echo $inv['id']; ?>&status=paid" 
                                                   class="btn btn-sm btn-success mr-2" 
                                                   onclick="return confirm('¿Marcar esta factura como pagada? Se registrará en contabilidad.')"
                                                   title="Marcar como pagada">
                                                    <i class="fas fa-check"></i> Pagar
                                                </a>
                                            <?php endif; ?>
                                            <a href="index.php?page=suppliers&action=deleteInvoice&id=<?php echo $inv['id']; ?>" 
                                               class="text-danger" 
                                               onclick="return confirm('¿Eliminar esta factura?')"
                                               title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 500px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div class="modal-header d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Subir Nueva Factura</h4>
            <span class="close" style="cursor: pointer; font-size: 28px; font-weight: bold;" onclick="document.getElementById('uploadModal').style.display='none'">&times;</span>
        </div>
        
        <form action="index.php?page=suppliers&action=uploadInvoice" method="POST" enctype="multipart/form-data">
            <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
            <input type="hidden" name="supplier_id" value="<?php echo $this->supplier->id; ?>">
            
            <div class="form-group mb-3">
                <label class="form-label">Número de Factura <span class="text-danger">*</span></label>
                <input type="text" name="invoice_number" class="form-control" required placeholder="Ej: F2024-001">
                <small class="text-muted">Este número se usará para nombrar el archivo.</small>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="invoice_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Importe (€)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control">
                    <option value="pending">Pendiente</option>
                    <option value="paid">Pagada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
            </div>
            
            <div class="form-group mb-3">
                <label class="form-label">Archivo de Factura <span class="text-danger">*</span></label>
                <input type="file" name="invoice_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                <small class="text-muted">PDF, Imágenes o Word.</small>
            </div>
            
            <div class="form-group mb-3">
                <label class="form-label">Notas</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="text-right mt-4">
                <button type="button" class="btn btn-secondary mr-2" onclick="document.getElementById('uploadModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Subir Factura</button>
            </div>
        </form>
    </div>
</div>

<script>
// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('uploadModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
