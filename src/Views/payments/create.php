<?php ob_start(); ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate_fee'): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        El socio ya ha pagado la cuota anual para el año <?php echo htmlspecialchars($_GET['year'] ?? '', ENT_QUOTES); ?>. No se permiten pagos duplicados.
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<script>
// Annual fees data for auto-fill (fallback)
const annualFees = <?php echo json_encode($fees ?? []); ?>;
// Members with their category fees
const membersFees = <?php echo json_encode(array_map(function($m) {
    return ['id' => $m['id'], 'fee' => $m['category_fee']];
}, $members)); ?>;
</script>

<div class="mb-4">
    <a href="index.php?page=payments" class="btn btn-sm btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
    <h1>Registrar Nuevo Pago</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="index.php?page=payments&action=store" method="POST">
        <?php require_once __DIR__ . '/../../Helpers/CsrfHelper.php'; echo CsrfHelper::getTokenField(); ?>
        <div class="form-group">
            <label class="form-label">Socio</label>
            <select name="member_id" id="member_id" class="form-control" onchange="updateFeeAmount()" required>
                <option value="">Seleccione un socio...</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?php echo $member['id']; ?>" data-fee="<?php echo $member['category_fee']; ?>">
                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        <?php if ($member['category_fee'] > 0): ?>
                            (<?php echo number_format($member['category_fee'], 2); ?>€)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Tipo de Pago</label>
            <select name="payment_type" id="payment_type" class="form-control" onchange="toggleEventSelect()">
                <option value="fee">Cuota Anual</option>
                <option value="event">Evento / Actividad</option>
                <option value="donation">Donación / Otro</option>
            </select>
        </div>

        <div class="form-group" id="event_select_group" style="display: none;">
            <label class="form-label">Seleccionar Evento</label>
            <select name="event_id" id="event_id" class="form-control" onchange="updateAmountFromEvent()">
                <option value="">-- Seleccione un evento --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['id']; ?>" data-price="<?php echo $event['price']; ?>">
                        <?php echo htmlspecialchars($event['name']); ?> (<?php echo number_format($event['price'], 2); ?> €)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Importe (€)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Fecha de Pago</label>
                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Concepto</label>
            <input type="text" name="concept" id="concept" class="form-control" required>
        </div>

        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control">
                <option value="paid">Pagado</option>
                <option value="pending">Pendiente</option>
            </select>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Pago
            </button>
        </div>
    </form>
</div>

<script>
function toggleEventSelect() {
    const type = document.getElementById('payment_type').value;
    const eventGroup = document.getElementById('event_select_group');
    const conceptInput = document.getElementById('concept');
    
    if (type === 'event') {
        eventGroup.style.display = 'block';
        document.getElementById('event_id').required = true;
    } else {
        eventGroup.style.display = 'none';
        document.getElementById('event_id').required = false;
        document.getElementById('event_id').value = '';
        if (type === 'fee') {
            updateFeeAmount(); // Auto-fill when switching to fee
        } else {
            conceptInput.value = '';
        }
    }
}

function updateAmountFromEvent() {
    const eventSelect = document.getElementById('event_id');
    const selectedOption = eventSelect.options[eventSelect.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    const conceptInput = document.getElementById('concept');
    
    if (price) {
        document.getElementById('amount').value = price;
    }
    
    if (selectedOption.value) {
        // Remove price from name for concept
        let name = selectedOption.text.split('(')[0].trim();
        conceptInput.value = name;
    }
}

function updateFeeAmount() {
    const paymentType = document.getElementById('payment_type').value;
    const memberSelect = document.getElementById('member_id');
    const paymentDateInput = document.querySelector('input[name="payment_date"]');
    const amountInput = document.getElementById('amount');
    const conceptInput = document.getElementById('concept');
    
    if (paymentType === 'fee' && memberSelect.value && paymentDateInput.value) {
        const year = new Date(paymentDateInput.value).getFullYear();
        
        // Try to get fee from selected member's category first
        const selectedOption = memberSelect.options[memberSelect.selectedIndex];
        const memberFee = parseFloat(selectedOption.dataset.fee || 0);
        
        if (memberFee > 0) {
            amountInput.value = memberFee.toFixed(2);
            conceptInput.value = 'Cuota Anual ' + year;
        } else {
            // Fallback to annual fee table if member has no category fee
            const fee = annualFees.find(f => f.year == year);
            if (fee) {
                amountInput.value = fee.amount;
                conceptInput.value = 'Cuota Anual ' + year;
            } else {
                // No fee defined
                amountInput.value = '';
                conceptInput.value = 'Cuota Anual ' + year;
            }
        }
    }
}

// Initialize
toggleEventSelect();

// Add event listener to payment date to auto-update fee amount
document.querySelector('input[name="payment_date"]').addEventListener('change', function() {
    if (document.getElementById('payment_type').value === 'fee') {
        updateFeeAmount();
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
