<?php
$pageTitle = 'Nueva Factura';
require_once __DIR__ . '/../layout/header.php';
?>

<style>
.form-container {
    max-width: 1200px;
    margin: 0 auto;
}

.form-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-card h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

.form-group label .required {
    color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.875rem;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.customer-type-selector {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.customer-type-option {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
}

.customer-type-option:hover {
    border-color: #2563eb;
}

.customer-type-option.active {
    border-color: #2563eb;
    background: #eff6ff;
}

.customer-type-option input[type="radio"] {
    display: none;
}

.lines-section {
    margin-top: 1.5rem;
}

.lines-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
}

.lines-table th {
    background: #f9fafb;
    padding: 0.75rem 0.5rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
}

.lines-table td {
    padding: 0.5rem;
    border-bottom: 1px solid #f3f4f6;
}

.lines-table input,
.lines-table textarea {
    width: 100%;
    padding: 0.375rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.875rem;
}

.lines-table textarea {
    resize: vertical;
    min-height: 40px;
}

.line-total {
    font-family: ui-monospace, monospace;
    font-weight: 500;
    text-align: right;
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

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-success {
    background: #059669;
    color: white;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
}

.totals-summary {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1rem;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.875rem;
}

.totals-row.total {
    font-size: 1.125rem;
    font-weight: 700;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
    color: #111827;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}
</style>

<div class="form-container">
    <div style="margin-bottom: 1.5rem;">
        <a href="index.php?page=invoices" style="color: #6b7280; font-size: 0.875rem; text-decoration: none;">
            ← Volver a facturas
        </a>
    </div>
    
    <form method="POST" action="index.php?page=invoices&action=store" id="invoiceForm">
        <!-- Datos básicos -->
        <div class="form-card">
            <h2>Datos de la Factura</h2>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Serie <span class="required">*</span></label>
                    <select name="series_id" required>
                        <?php foreach ($series as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $s['is_default'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['prefix']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Fecha de Emisión <span class="required">*</span></label>
                    <input type="date" name="issue_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Fecha de Vencimiento</label>
                    <input type="date" name="due_date">
                </div>
                
                <div class="form-group">
                    <label>Método de Pago</label>
                    <select name="payment_method">
                        <option value="transfer">Transferencia</option>
                        <option value="cash">Efectivo</option>
                        <option value="card">Tarjeta</option>
                        <option value="check">Cheque</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Cliente -->
        <div class="form-card">
            <h2>Datos del Cliente</h2>
            
            <div class="customer-type-selector">
                <label class="customer-type-option active">
                    <input type="radio" name="customer_type" value="member" checked onchange="toggleCustomerFields()">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Socio</div>
                    <div style="font-size: 0.75rem; color: #6b7280;">Seleccionar de la lista de socios</div>
                </label>
                <label class="customer-type-option">
                    <input type="radio" name="customer_type" value="external" onchange="toggleCustomerFields()">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Cliente Externo</div>
                    <div style="font-size: 0.75rem; color: #6b7280;">Introducir datos manualmente</div>
                </label>
            </div>
            
            <div id="memberFields">
                <div class="form-group">
                    <label>Socio <span class="required">*</span></label>
                    <select name="member_id" id="memberSelect" onchange="fillMemberData()">
                        <option value="">Seleccionar socio...</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id'] ?>" 
                                    data-name="<?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>"
                                    data-tax="<?= htmlspecialchars($member['dni'] ?? '') ?>"
                                    data-email="<?= htmlspecialchars($member['email']) ?>"
                                    data-phone="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                                #<?= $member['member_number'] ?> - <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-grid" style="margin-top: 1rem;">
                <div class="form-group">
                    <label>Nombre/Razón Social <span class="required">*</span></label>
                    <input type="text" name="customer_name" id="customerName" required>
                </div>
                
                <div class="form-group">
                    <label>NIF/CIF</label>
                    <input type="text" name="customer_tax_id" id="customerTaxId">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="customer_email" id="customerEmail">
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="customer_phone" id="customerPhone">
                </div>
                
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="customer_address" id="customerAddress">
                </div>
                
                <div class="form-group">
                    <label>Código Postal</label>
                    <input type="text" name="customer_postal_code" id="customerPostalCode">
                </div>
                
                <div class="form-group">
                    <label>Ciudad</label>
                    <input type="text" name="customer_city" id="customerCity">
                </div>
                
                <div class="form-group">
                    <label>País</label>
                    <input type="text" name="customer_country" id="customerCountry" value="España">
                </div>
            </div>
        </div>
        
        <!-- Descripción -->
        <div class="form-card">
            <h2>Descripción y Notas</h2>
            
            <div class="form-group">
                <label>Descripción <span class="required">*</span></label>
                <textarea name="description" required placeholder="Descripción general de la factura..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Notas Adicionales</label>
                <textarea name="notes" placeholder="Notas internas (no aparecen en la factura)..."></textarea>
            </div>
        </div>
        
        <!-- Líneas de factura -->
        <div class="form-card">
            <h2>Líneas de Factura</h2>
            
            <div class="lines-section">
                <table class="lines-table" id="linesTable">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Concepto</th>
                            <th style="width: 30%;">Descripción</th>
                            <th style="width: 10%;">Cantidad</th>
                            <th style="width: 10%;">Precio</th>
                            <th style="width: 8%;">Dto %</th>
                            <th style="width: 8%;">IVA %</th>
                            <th style="width: 12%;">Total</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        <tr class="line-row">
                            <td><input type="text" name="lines[0][concept]" required></td>
                            <td><textarea name="lines[0][description]"></textarea></td>
                            <td><input type="number" name="lines[0][quantity]" step="0.01" value="1" min="0" onchange="calculateLineTotal(this)"></td>
                            <td><input type="number" name="lines[0][unit_price]" step="0.01" value="0" min="0" onchange="calculateLineTotal(this)"></td>
                            <td><input type="number" name="lines[0][discount_rate]" step="0.01" value="0" min="0" max="100" onchange="calculateLineTotal(this)"></td>
                            <td><input type="number" name="lines[0][tax_rate]" step="0.01" value="21" min="0" onchange="calculateLineTotal(this)"></td>
                            <td class="line-total">0.00 €</td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeLine(this)" style="display: none;">×</button></td>
                        </tr>
                    </tbody>
                </table>
                
                <button type="button" class="btn btn-secondary btn-sm" onclick="addLine()">+ Añadir Línea</button>
                
                <div class="totals-summary">
                    <div class="totals-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">0.00 €</span>
                    </div>
                    <div class="totals-row">
                        <span>Descuento:</span>
                        <span id="discount">0.00 €</span>
                    </div>
                    <div class="totals-row">
                        <span>Base Imponible:</span>
                        <span id="taxable">0.00 €</span>
                    </div>
                    <div class="totals-row">
                        <span>IVA:</span>
                        <span id="tax">0.00 €</span>
                    </div>
                    <div class="totals-row total">
                        <span>TOTAL:</span>
                        <span id="total">0.00 €</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="form-card">
            <div class="form-actions">
                <a href="index.php?page=invoices" class="btn btn-secondary">Cancelar</a>
                <button type="submit" name="status" value="draft" class="btn btn-secondary">Guardar Borrador</button>
                <button type="submit" name="status" value="issued" class="btn btn-primary">Emitir Factura</button>
            </div>
        </div>
    </form>
</div>

<script>
let lineCounter = 1;

function toggleCustomerFields() {
    const customerType = document.querySelector('input[name="customer_type"]:checked').value;
    const memberFields = document.getElementById('memberFields');
    const memberSelect = document.getElementById('memberSelect');
    
    // Actualizar estilos de selector
    document.querySelectorAll('.customer-type-option').forEach(opt => {
        opt.classList.remove('active');
    });
    document.querySelector(`input[value="${customerType}"]`).closest('.customer-type-option').classList.add('active');
    
    if (customerType === 'member') {
        memberFields.style.display = 'block';
        memberSelect.required = true;
    } else {
        memberFields.style.display = 'none';
        memberSelect.required = false;
        memberSelect.value = '';
        clearCustomerFields();
    }
}

function fillMemberData() {
    const select = document.getElementById('memberSelect');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('customerName').value = option.dataset.name || '';
        document.getElementById('customerTaxId').value = option.dataset.tax || '';
        document.getElementById('customerEmail').value = option.dataset.email || '';
        document.getElementById('customerPhone').value = option.dataset.phone || '';
    } else {
        clearCustomerFields();
    }
}

function clearCustomerFields() {
    document.getElementById('customerName').value = '';
    document.getElementById('customerTaxId').value = '';
    document.getElementById('customerEmail').value = '';
    document.getElementById('customerPhone').value = '';
    document.getElementById('customerAddress').value = '';
    document.getElementById('customerPostalCode').value = '';
    document.getElementById('customerCity').value = '';
}

function addLine() {
    const tbody = document.getElementById('linesBody');
    const newRow = document.createElement('tr');
    newRow.className = 'line-row';
    newRow.innerHTML = `
        <td><input type="text" name="lines[${lineCounter}][concept]" required></td>
        <td><textarea name="lines[${lineCounter}][description]"></textarea></td>
        <td><input type="number" name="lines[${lineCounter}][quantity]" step="0.01" value="1" min="0" onchange="calculateLineTotal(this)"></td>
        <td><input type="number" name="lines[${lineCounter}][unit_price]" step="0.01" value="0" min="0" onchange="calculateLineTotal(this)"></td>
        <td><input type="number" name="lines[${lineCounter}][discount_rate]" step="0.01" value="0" min="0" max="100" onchange="calculateLineTotal(this)"></td>
        <td><input type="number" name="lines[${lineCounter}][tax_rate]" step="0.01" value="21" min="0" onchange="calculateLineTotal(this)"></td>
        <td class="line-total">0.00 €</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeLine(this)">×</button></td>
    `;
    tbody.appendChild(newRow);
    lineCounter++;
    
    // Mostrar botones de eliminar si hay más de una línea
    updateDeleteButtons();
}

function removeLine(button) {
    const row = button.closest('tr');
    row.remove();
    calculateTotals();
    updateDeleteButtons();
}

function updateDeleteButtons() {
    const rows = document.querySelectorAll('.line-row');
    rows.forEach((row, index) => {
        const deleteBtn = row.querySelector('.btn-danger');
        deleteBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
    });
}

function calculateLineTotal(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const price = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const discountRate = parseFloat(row.querySelector('input[name*="[discount_rate]"]').value) || 0;
    const taxRate = parseFloat(row.querySelector('input[name*="[tax_rate]"]').value) || 0;
    
    const subtotal = quantity * price;
    const discount = subtotal * (discountRate / 100);
    const taxable = subtotal - discount;
    const tax = taxable * (taxRate / 100);
    const total = taxable + tax;
    
    row.querySelector('.line-total').textContent = total.toFixed(2) + ' €';
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;
    let totalTax = 0;
    
    document.querySelectorAll('.line-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
        const discountRate = parseFloat(row.querySelector('input[name*="[discount_rate]"]').value) || 0;
        const taxRate = parseFloat(row.querySelector('input[name*="[tax_rate]"]').value) || 0;
        
        const lineSubtotal = quantity * price;
        const lineDiscount = lineSubtotal * (discountRate / 100);
        const lineTaxable = lineSubtotal - lineDiscount;
        const lineTax = lineTaxable * (taxRate / 100);
        
        subtotal += lineSubtotal;
        totalDiscount += lineDiscount;
        totalTax += lineTax;
    });
    
    const taxable = subtotal - totalDiscount;
    const total = taxable + totalTax;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' €';
    document.getElementById('discount').textContent = totalDiscount.toFixed(2) + ' €';
    document.getElementById('taxable').textContent = taxable.toFixed(2) + ' €';
    document.getElementById('tax').textContent = totalTax.toFixed(2) + ' €';
    document.getElementById('total').textContent = total.toFixed(2) + ' €';
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    toggleCustomerFields();
    calculateTotals();
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
