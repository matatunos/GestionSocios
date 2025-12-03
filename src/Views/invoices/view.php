<?php
$pageTitle = 'Factura ' . htmlspecialchars($this->invoice->full_number);
require_once __DIR__ . '/../layout/header.php';

$statusColors = [
    'draft' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Borrador'],
    'issued' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'Emitida'],
    'paid' => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Pagada'],
    'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Cancelada']
];
$status = $statusColors[$this->invoice->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'label' => $this->invoice->status];
?>

<style>
.invoice-container {
    max-width: 1000px;
    margin: 0 auto;
}

.invoice-header {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.invoice-number {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    font-family: ui-monospace, monospace;
    margin-bottom: 0.5rem;
}

.invoice-status {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.invoice-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.invoice-body {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-section h3 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.info-section p {
    margin: 0.25rem 0;
    font-size: 0.875rem;
    color: #111827;
}

.info-section .label {
    color: #6b7280;
    font-size: 0.8125rem;
}

.description-section {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 2rem;
}

.description-section h3 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.description-section p {
    margin: 0;
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.5;
}

.lines-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}

.lines-table thead {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.lines-table th {
    padding: 0.75rem 0.5rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
}

.lines-table th.text-right {
    text-align: right;
}

.lines-table td {
    padding: 1rem 0.5rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.875rem;
    color: #111827;
}

.lines-table td.text-right {
    text-align: right;
    font-family: ui-monospace, monospace;
}

.lines-table .line-description {
    color: #6b7280;
    font-size: 0.8125rem;
    margin-top: 0.25rem;
}

.totals-section {
    display: flex;
    justify-content: flex-end;
}

.totals-box {
    width: 350px;
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 6px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.875rem;
}

.total-row.final {
    border-top: 2px solid #e5e7eb;
    margin-top: 0.75rem;
    padding-top: 1rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.total-row .amount {
    font-family: ui-monospace, monospace;
    font-weight: 500;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.2s;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-success {
    background: #059669;
    color: white;
}

.btn-success:hover {
    background: #047857;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-info {
    background: #0891b2;
    color: white;
}

.btn-info:hover {
    background: #0e7490;
}

.accounting-link {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1.5rem;
    font-size: 0.875rem;
}

.accounting-link a {
    color: #2563eb;
    font-weight: 500;
    text-decoration: none;
}

.accounting-link a:hover {
    text-decoration: underline;
}

.notes-section {
    background: #fef3c7;
    border: 1px solid #fde047;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1.5rem;
}

.notes-section h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #92400e;
    margin: 0 0 0.5rem 0;
}

.notes-section p {
    margin: 0;
    font-size: 0.875rem;
    color: #78350f;
}
</style>

<div class="invoice-container">
    <div style="margin-bottom: 1.5rem;">
        <a href="index.php?page=invoices" style="color: #6b7280; font-size: 0.875rem; text-decoration: none;">
            ← Volver a facturas
        </a>
    </div>
    
    <div class="invoice-header">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
                <div class="invoice-number"><?= htmlspecialchars($this->invoice->full_number) ?></div>
                <span class="invoice-status" style="background: <?= $status['bg'] ?>; color: <?= $status['text'] ?>;">
                    <?= $status['label'] ?>
                </span>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">Serie</p>
                <p style="margin: 0; font-weight: 600;"><?= htmlspecialchars($this->invoice->series_name) ?></p>
            </div>
        </div>
        
        <div class="invoice-actions">
            <?php if ($this->invoice->status === 'draft'): ?>
                <a href="index.php?page=invoices&action=issue&id=<?= $this->invoice->id ?>" 
                   class="btn btn-primary"
                   onclick="return confirm('¿Emitir esta factura? Se generará el número de factura definitivo.')">
                    Emitir Factura
                </a>
            <?php endif; ?>
            
            <?php if ($this->invoice->status === 'issued'): ?>
                <button class="btn btn-success" onclick="showPaymentModal()">
                    Marcar como Pagada
                </button>
            <?php endif; ?>
            
            <?php if (in_array($this->invoice->status, ['issued', 'paid'])): ?>
                <a href="index.php?page=invoices&action=pdf&id=<?= $this->invoice->id ?>" 
                   class="btn btn-info" target="_blank">
                    Descargar PDF
                </a>
            <?php endif; ?>
            
            <?php if (in_array($this->invoice->status, ['draft', 'issued'])): ?>
                <button class="btn btn-danger" onclick="showCancelModal()">
                    Cancelar Factura
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="invoice-body">
        <!-- Información general -->
        <div class="info-grid">
            <div class="info-section">
                <h3>Fechas</h3>
                <p>
                    <span class="label">Emisión:</span>
                    <?= date('d/m/Y', strtotime($this->invoice->issue_date)) ?>
                </p>
                <?php if ($this->invoice->due_date): ?>
                    <p>
                        <span class="label">Vencimiento:</span>
                        <?= date('d/m/Y', strtotime($this->invoice->due_date)) ?>
                    </p>
                <?php endif; ?>
                <?php if ($this->invoice->status === 'issued' && strtotime($this->invoice->issued_at)): ?>
                    <p>
                        <span class="label">Emitida:</span>
                        <?= date('d/m/Y H:i', strtotime($this->invoice->issued_at)) ?>
                    </p>
                <?php endif; ?>
                <?php if ($this->invoice->status === 'paid' && $this->invoice->paid_at): ?>
                    <p>
                        <span class="label">Pagada:</span>
                        <?= date('d/m/Y H:i', strtotime($this->invoice->paid_at)) ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="info-section">
                <h3>Cliente</h3>
                <p style="font-weight: 600; font-size: 0.9375rem;">
                    <?= htmlspecialchars($this->invoice->customer_name) ?>
                    <?php if ($this->invoice->customer_type === 'member'): ?>
                        <span style="color: #6b7280; font-weight: 400; font-size: 0.8125rem;">
                            (Socio #<?= $this->invoice->member_number ?>)
                        </span>
                    <?php endif; ?>
                </p>
                <?php if ($this->invoice->customer_tax_id): ?>
                    <p><span class="label">NIF/CIF:</span> <?= htmlspecialchars($this->invoice->customer_tax_id) ?></p>
                <?php endif; ?>
                <?php if ($this->invoice->customer_address): ?>
                    <p><?= htmlspecialchars($this->invoice->customer_address) ?></p>
                <?php endif; ?>
                <?php if ($this->invoice->customer_city || $this->invoice->customer_postal_code): ?>
                    <p>
                        <?= htmlspecialchars($this->invoice->customer_postal_code) ?>
                        <?= htmlspecialchars($this->invoice->customer_city) ?>
                    </p>
                <?php endif; ?>
                <?php if ($this->invoice->customer_email): ?>
                    <p><span class="label">Email:</span> <?= htmlspecialchars($this->invoice->customer_email) ?></p>
                <?php endif; ?>
                <?php if ($this->invoice->customer_phone): ?>
                    <p><span class="label">Tel:</span> <?= htmlspecialchars($this->invoice->customer_phone) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Descripción -->
        <div class="description-section">
            <h3>Descripción</h3>
            <p><?= nl2br(htmlspecialchars($this->invoice->description)) ?></p>
        </div>
        
        <!-- Líneas de factura -->
        <table class="lines-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Dto %</th>
                    <th class="text-right">IVA %</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lines as $line): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500;"><?= htmlspecialchars($line['concept']) ?></div>
                            <?php if ($line['description']): ?>
                                <div class="line-description"><?= nl2br(htmlspecialchars($line['description'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($line['quantity'], 2) ?></td>
                        <td class="text-right"><?= number_format($line['unit_price'], 2) ?> €</td>
                        <td class="text-right"><?= number_format($line['discount_rate'], 2) ?>%</td>
                        <td class="text-right"><?= number_format($line['tax_rate'], 2) ?>%</td>
                        <td class="text-right" style="font-weight: 600;"><?= number_format($line['line_total'], 2) ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Totales -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span class="amount"><?= number_format($this->invoice->subtotal, 2) ?> €</span>
                </div>
                <?php if ($this->invoice->discount_amount > 0): ?>
                    <div class="total-row">
                        <span>Descuento:</span>
                        <span class="amount">-<?= number_format($this->invoice->discount_amount, 2) ?> €</span>
                    </div>
                <?php endif; ?>
                <div class="total-row">
                    <span>Base Imponible:</span>
                    <span class="amount"><?= number_format($this->invoice->subtotal - $this->invoice->discount_amount, 2) ?> €</span>
                </div>
                <div class="total-row">
                    <span>IVA (<?= number_format($this->invoice->tax_rate, 2) ?>%):</span>
                    <span class="amount"><?= number_format($this->invoice->tax_amount, 2) ?> €</span>
                </div>
                <div class="total-row final">
                    <span>TOTAL:</span>
                    <span class="amount"><?= number_format($this->invoice->total, 2) ?> €</span>
                </div>
            </div>
        </div>
        
        <!-- Información de pago -->
        <?php if ($this->invoice->payment_method || $this->invoice->reference): ?>
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                <h3 style="font-size: 0.875rem; font-weight: 600; margin: 0 0 0.5rem 0;">Datos de Pago</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <?php if ($this->invoice->payment_method): ?>
                        <p style="margin: 0; font-size: 0.875rem;">
                            <span style="color: #6b7280;">Método:</span>
                            <strong>
                                <?php
                                $methods = [
                                    'transfer' => 'Transferencia',
                                    'cash' => 'Efectivo',
                                    'card' => 'Tarjeta',
                                    'check' => 'Cheque',
                                    'other' => 'Otro'
                                ];
                                echo $methods[$this->invoice->payment_method] ?? $this->invoice->payment_method;
                                ?>
                            </strong>
                        </p>
                    <?php endif; ?>
                    <?php if ($this->invoice->reference): ?>
                        <p style="margin: 0; font-size: 0.875rem;">
                            <span style="color: #6b7280;">Referencia:</span>
                            <strong><?= htmlspecialchars($this->invoice->reference) ?></strong>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Enlace al asiento contable -->
        <?php if ($this->invoice->accounting_entry_id): ?>
            <div class="accounting-link">
                <strong>📊 Asiento Contable:</strong>
                <a href="index.php?page=accounting-entries&action=view&id=<?= $this->invoice->accounting_entry_id ?>">
                    Ver asiento #<?= $this->invoice->accounting_entry_id ?>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Notas internas -->
        <?php if ($this->invoice->notes): ?>
            <div class="notes-section">
                <h4>Notas Internas</h4>
                <p><?= nl2br(htmlspecialchars($this->invoice->notes)) ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Información de auditoría -->
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; font-size: 0.8125rem; color: #6b7280;">
            <p style="margin: 0.25rem 0;">
                Creada por <?= htmlspecialchars($this->invoice->creator_name) ?> 
                el <?= date('d/m/Y H:i', strtotime($this->invoice->created_at)) ?>
            </p>
            <?php if ($this->invoice->updated_at): ?>
                <p style="margin: 0.25rem 0;">
                    Última modificación: <?= date('d/m/Y H:i', strtotime($this->invoice->updated_at)) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para marcar como pagada -->
<div id="paymentModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 90%;">
        <h3 style="margin: 0 0 1rem 0; font-size: 1.125rem;">Marcar como Pagada</h3>
        <form method="POST" action="index.php?page=invoices&action=mark-paid&id=<?= $this->invoice->id ?>">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">
                    Fecha de Pago
                </label>
                <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" 
                       style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px;" required>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">
                    Método de Pago
                </label>
                <select name="payment_method" 
                        style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="transfer">Transferencia</option>
                    <option value="cash">Efectivo</option>
                    <option value="card">Tarjeta</option>
                    <option value="check">Cheque</option>
                    <option value="other">Otro</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" onclick="hidePaymentModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-success">Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para cancelar -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 90%;">
        <h3 style="margin: 0 0 1rem 0; font-size: 1.125rem;">Cancelar Factura</h3>
        <form method="POST" action="index.php?page=invoices&action=cancel&id=<?= $this->invoice->id ?>">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">
                    Motivo de Cancelación
                </label>
                <textarea name="reason" rows="3" 
                          style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px;" 
                          required></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" onclick="hideCancelModal()" class="btn btn-secondary">Volver</button>
                <button type="submit" class="btn btn-danger">Cancelar Factura</button>
            </div>
        </form>
    </div>
</div>

<script>
function showPaymentModal() {
    document.getElementById('paymentModal').style.display = 'flex';
}

function hidePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function showCancelModal() {
    document.getElementById('cancelModal').style.display = 'flex';
}

function hideCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

// Cerrar modales con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hidePaymentModal();
        hideCancelModal();
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
