<?php

/**
 * BankTransaction Model
 * 
 * Gestión de movimientos bancarios
 * Incluye: CRUD, importación, matching automático con facturas/pagos/subvenciones, conciliación
 */

class BankTransaction {
    private $conn;
    private $table_name = "bank_transactions";

    // Propiedades
    public $id;
    public $account_id;
    public $transaction_date;
    public $value_date;
    public $description;
    public $reference;
    public $amount;
    public $balance_after;
    public $transaction_type;
    public $category;
    public $counterpart;
    public $counterpart_account;
    public $is_reconciled;
    public $reconciliation_date;
    public $reconciliation_id;
    public $is_matched;
    public $matched_with_type;
    public $matched_with_id;
    public $match_confidence;
    public $imported;
    public $import_file;
    public $import_date;
    public $created_by;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo movimiento bancario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET account_id = :account_id,
                      transaction_date = :transaction_date,
                      value_date = :value_date,
                      description = :description,
                      reference = :reference,
                      amount = :amount,
                      balance_after = :balance_after,
                      transaction_type = :transaction_type,
                      category = :category,
                      counterpart = :counterpart,
                      counterpart_account = :counterpart_account,
                      is_reconciled = :is_reconciled,
                      is_matched = :is_matched,
                      matched_with_type = :matched_with_type,
                      matched_with_id = :matched_with_id,
                      match_confidence = :match_confidence,
                      imported = :imported,
                      import_file = :import_file,
                      import_date = :import_date,
                      created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->reference = htmlspecialchars(strip_tags($this->reference));
        $this->counterpart = htmlspecialchars(strip_tags($this->counterpart));

        // Bind
        $stmt->bindParam(":account_id", $this->account_id);
        $stmt->bindParam(":transaction_date", $this->transaction_date);
        $stmt->bindParam(":value_date", $this->value_date);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":reference", $this->reference);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":balance_after", $this->balance_after);
        $stmt->bindParam(":transaction_type", $this->transaction_type);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":counterpart", $this->counterpart);
        $stmt->bindParam(":counterpart_account", $this->counterpart_account);
        $stmt->bindParam(":is_reconciled", $this->is_reconciled);
        $stmt->bindParam(":is_matched", $this->is_matched);
        $stmt->bindParam(":matched_with_type", $this->matched_with_type);
        $stmt->bindParam(":matched_with_id", $this->matched_with_id);
        $stmt->bindParam(":match_confidence", $this->match_confidence);
        $stmt->bindParam(":imported", $this->imported);
        $stmt->bindParam(":import_file", $this->import_file);
        $stmt->bindParam(":import_date", $this->import_date);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Recalcular saldo de la cuenta
            $this->updateAccountBalance();
            
            return true;
        }
        return false;
    }

    /**
     * Leer un movimiento
     */
    public function readOne() {
        $query = "SELECT bt.*, 
                         ba.account_name, ba.account_number, ba.bank_name,
                         u.first_name, u.last_name,
                         br.period_start, br.period_end
                  FROM " . $this->table_name . " bt
                  LEFT JOIN bank_accounts ba ON bt.account_id = ba.id
                  LEFT JOIN users u ON bt.created_by = u.id
                  LEFT JOIN bank_reconciliations br ON bt.reconciliation_id = br.id
                  WHERE bt.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->account_id = $row['account_id'];
            $this->transaction_date = $row['transaction_date'];
            $this->value_date = $row['value_date'];
            $this->description = $row['description'];
            $this->reference = $row['reference'];
            $this->amount = $row['amount'];
            $this->balance_after = $row['balance_after'];
            $this->transaction_type = $row['transaction_type'];
            $this->category = $row['category'];
            $this->counterpart = $row['counterpart'];
            $this->counterpart_account = $row['counterpart_account'];
            $this->is_reconciled = $row['is_reconciled'];
            $this->reconciliation_date = $row['reconciliation_date'];
            $this->reconciliation_id = $row['reconciliation_id'];
            $this->is_matched = $row['is_matched'];
            $this->matched_with_type = $row['matched_with_type'];
            $this->matched_with_id = $row['matched_with_id'];
            $this->match_confidence = $row['match_confidence'];
            $this->imported = $row['imported'];
            $this->import_file = $row['import_file'];
            $this->import_date = $row['import_date'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return $row;
        }
        
        return false;
    }

    /**
     * Leer todas las transacciones con filtros
     */
    public function readAll($filters = [], $limit = null, $offset = 0) {
        $where = [];
        $params = [];

        if (!empty($filters['account_id'])) {
            $where[] = "bt.account_id = :account_id";
            $params[':account_id'] = $filters['account_id'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = "bt.transaction_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "bt.transaction_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['transaction_type'])) {
            $where[] = "bt.transaction_type = :transaction_type";
            $params[':transaction_type'] = $filters['transaction_type'];
        }

        if (!empty($filters['category'])) {
            $where[] = "bt.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (isset($filters['is_reconciled'])) {
            $where[] = "bt.is_reconciled = :is_reconciled";
            $params[':is_reconciled'] = $filters['is_reconciled'];
        }

        if (isset($filters['is_matched'])) {
            $where[] = "bt.is_matched = :is_matched";
            $params[':is_matched'] = $filters['is_matched'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(bt.description LIKE :search OR bt.reference LIKE :search OR bt.counterpart LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderBy = $filters['order_by'] ?? 'transaction_date';
        $orderDir = $filters['order_dir'] ?? 'DESC';

        $query = "SELECT bt.*, 
                         ba.account_name, ba.account_number, ba.bank_name
                  FROM " . $this->table_name . " bt
                  LEFT JOIN bank_accounts ba ON bt.account_id = ba.id
                  $whereClause
                  ORDER BY bt.$orderBy $orderDir";

        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar transacciones con filtros
     */
    public function count($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['account_id'])) {
            $where[] = "account_id = :account_id";
            $params[':account_id'] = $filters['account_id'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = "transaction_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "transaction_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['transaction_type'])) {
            $where[] = "transaction_type = :transaction_type";
            $params[':transaction_type'] = $filters['transaction_type'];
        }

        if (isset($filters['is_reconciled'])) {
            $where[] = "is_reconciled = :is_reconciled";
            $params[':is_reconciled'] = $filters['is_reconciled'];
        }

        if (isset($filters['is_matched'])) {
            $where[] = "is_matched = :is_matched";
            $params[':is_matched'] = $filters['is_matched'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(description LIKE :search OR reference LIKE :search OR counterpart LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " $whereClause";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    /**
     * Actualizar transacción
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET transaction_date = :transaction_date,
                      value_date = :value_date,
                      description = :description,
                      reference = :reference,
                      amount = :amount,
                      transaction_type = :transaction_type,
                      category = :category,
                      counterpart = :counterpart,
                      counterpart_account = :counterpart_account,
                      is_reconciled = :is_reconciled,
                      is_matched = :is_matched,
                      matched_with_type = :matched_with_type,
                      matched_with_id = :matched_with_id,
                      match_confidence = :match_confidence
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->reference = htmlspecialchars(strip_tags($this->reference));
        $this->counterpart = htmlspecialchars(strip_tags($this->counterpart));

        // Bind
        $stmt->bindParam(":transaction_date", $this->transaction_date);
        $stmt->bindParam(":value_date", $this->value_date);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":reference", $this->reference);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":transaction_type", $this->transaction_type);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":counterpart", $this->counterpart);
        $stmt->bindParam(":counterpart_account", $this->counterpart_account);
        $stmt->bindParam(":is_reconciled", $this->is_reconciled);
        $stmt->bindParam(":is_matched", $this->is_matched);
        $stmt->bindParam(":matched_with_type", $this->matched_with_type);
        $stmt->bindParam(":matched_with_id", $this->matched_with_id);
        $stmt->bindParam(":match_confidence", $this->match_confidence);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Eliminar transacción
     */
    public function delete() {
        // No eliminar si está conciliada
        if ($this->is_reconciled) {
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            // Recalcular saldo de la cuenta
            $this->updateAccountBalance();
            return true;
        }
        return false;
    }

    /**
     * Marcar como conciliado
     */
    public function reconcile($reconciliation_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_reconciled = 1,
                      reconciliation_date = CURRENT_DATE,
                      reconciliation_id = :reconciliation_id
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reconciliation_id", $reconciliation_id);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    /**
     * Vincular con operación (factura, pago, subvención, etc.)
     */
    public function match($type, $related_id, $confidence = 100, $match_type = 'manual') {
        $this->is_matched = 1;
        $this->matched_with_type = $type;
        $this->matched_with_id = $related_id;
        $this->match_confidence = $confidence;
        
        $updateResult = $this->update();
        
        if ($updateResult && $match_type !== 'suggested') {
            // Crear registro en transaction_matches
            require_once __DIR__ . '/TransactionMatch.php';
            $match = new TransactionMatch($this->conn);
            $match->bank_transaction_id = $this->id;
            $match->related_type = $type;
            $match->related_id = $related_id;
            $match->match_type = $match_type;
            $match->match_confidence = $confidence;
            $match->matched_amount = abs($this->amount);
            $match->status = 'confirmed';
            $match->matched_by = $this->created_by;
            $match->create();
        }
        
        return $updateResult;
    }

    /**
     * Desvincular matching
     */
    public function unmatch() {
        // Eliminar registros en transaction_matches
        $query = "DELETE FROM transaction_matches WHERE bank_transaction_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        // Actualizar flags
        $query = "UPDATE " . $this->table_name . " 
                  SET is_matched = 0,
                      matched_with_type = NULL,
                      matched_with_id = NULL,
                      match_confidence = 0
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    /**
     * Actualizar saldo de la cuenta
     */
    private function updateAccountBalance() {
        require_once __DIR__ . '/BankAccount.php';
        $account = new BankAccount($this->conn);
        $account->id = $this->account_id;
        $account->recalculateBalance();
    }

    /**
     * Importar transacciones desde array (CSV/OFX)
     */
    public static function importFromArray($db, $account_id, $transactions, $filename, $user_id) {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($transactions as $data) {
            // Verificar duplicados (por fecha + importe + referencia)
            $checkQuery = "SELECT id FROM bank_transactions 
                           WHERE account_id = :account_id
                             AND transaction_date = :date
                             AND amount = :amount
                             AND reference = :reference
                           LIMIT 1";
            
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([
                ':account_id' => $account_id,
                ':date' => $data['transaction_date'],
                ':amount' => $data['amount'],
                ':reference' => $data['reference'] ?? ''
            ]);
            
            if ($checkStmt->rowCount() > 0) {
                $skipped++;
                continue;
            }

            // Crear transacción
            $transaction = new BankTransaction($db);
            $transaction->account_id = $account_id;
            $transaction->transaction_date = $data['transaction_date'];
            $transaction->value_date = $data['value_date'] ?? $data['transaction_date'];
            $transaction->description = $data['description'] ?? '';
            $transaction->reference = $data['reference'] ?? '';
            $transaction->amount = $data['amount'];
            $transaction->balance_after = $data['balance_after'] ?? null;
            $transaction->transaction_type = $data['transaction_type'] ?? ($data['amount'] > 0 ? 'ingreso' : 'gasto');
            $transaction->category = $data['category'] ?? null;
            $transaction->counterpart = $data['counterpart'] ?? '';
            $transaction->counterpart_account = $data['counterpart_account'] ?? '';
            $transaction->is_reconciled = 0;
            $transaction->is_matched = 0;
            $transaction->imported = 1;
            $transaction->import_file = $filename;
            $transaction->import_date = date('Y-m-d H:i:s');
            $transaction->created_by = $user_id;

            if ($transaction->create()) {
                $imported++;
                
                // Intentar matching automático
                $transaction->autoMatch();
            } else {
                $errors[] = "Error importando: " . $data['description'];
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Matching automático con facturas, pagos, subvenciones
     */
    public function autoMatch() {
        // Verificar que auto-matching esté habilitado
        $settingsQuery = "SELECT setting_value FROM organization_settings 
                          WHERE category = 'banking' AND setting_key = 'auto_matching_enabled'
                          LIMIT 1";
        $settingsStmt = $this->conn->prepare($settingsQuery);
        $settingsStmt->execute();
        $settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings || $settings['setting_value'] != '1') {
            return false;
        }

        // Obtener threshold
        $thresholdQuery = "SELECT setting_value FROM organization_settings 
                           WHERE category = 'banking' AND setting_key = 'auto_match_threshold'
                           LIMIT 1";
        $thresholdStmt = $this->conn->prepare($thresholdQuery);
        $thresholdStmt->execute();
        $thresholdRow = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
        $threshold = $thresholdRow ? (int)$thresholdRow['setting_value'] : 85;

        $bestMatch = null;
        $bestScore = 0;

        // Si es ingreso, buscar en facturas emitidas
        if ($this->amount > 0) {
            $bestMatch = $this->matchWithInvoices($threshold);
            if ($bestMatch) {
                $bestScore = $bestMatch['score'];
            }
        }

        // Buscar en pagos de socios (ingresos por cuotas)
        if ($this->amount > 0) {
            $paymentMatch = $this->matchWithPayments($threshold);
            if ($paymentMatch && $paymentMatch['score'] > $bestScore) {
                $bestMatch = $paymentMatch;
                $bestScore = $paymentMatch['score'];
            }
        }

        // Buscar en subvenciones concedidas (ingresos)
        if ($this->amount > 0) {
            $grantMatch = $this->matchWithGrants($threshold);
            if ($grantMatch && $grantMatch['score'] > $bestScore) {
                $bestMatch = $grantMatch;
                $bestScore = $grantMatch['score'];
            }
        }

        // Si encontró match por encima del threshold, aplicarlo
        if ($bestMatch && $bestScore >= $threshold) {
            return $this->match($bestMatch['type'], $bestMatch['id'], $bestScore, 'automatic');
        }

        return false;
    }

    /**
     * Matching con facturas emitidas
     */
    private function matchWithInvoices($threshold) {
        // Buscar facturas cerca de la fecha y con importe similar
        $query = "SELECT id, invoice_number, total_amount, issue_date
                  FROM issued_invoices
                  WHERE status = 'emitida'
                    AND total_amount BETWEEN :min_amount AND :max_amount
                    AND issue_date BETWEEN DATE_SUB(:transaction_date, INTERVAL 30 DAY) AND DATE_ADD(:transaction_date, INTERVAL 30 DAY)
                    AND id NOT IN (SELECT matched_with_id FROM bank_transactions WHERE matched_with_type = 'issued_invoice' AND is_matched = 1)
                  ORDER BY ABS(total_amount - :amount) ASC, ABS(DATEDIFF(issue_date, :transaction_date2)) ASC
                  LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        $amount = abs($this->amount);
        $tolerance = $amount * 0.02; // 2% tolerancia
        
        $stmt->bindValue(':min_amount', $amount - $tolerance);
        $stmt->bindValue(':max_amount', $amount + $tolerance);
        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':transaction_date', $this->transaction_date);
        $stmt->bindValue(':transaction_date2', $this->transaction_date);
        
        $stmt->execute();
        
        $bestMatch = null;
        $bestScore = 0;
        
        while ($invoice = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $score = 0;
            
            // Comparar importes (máx 50 puntos)
            $amountDiff = abs($invoice['total_amount'] - $amount);
            $amountScore = max(0, 50 - ($amountDiff / $amount * 100));
            $score += $amountScore;
            
            // Comparar fechas (máx 30 puntos)
            $daysDiff = abs((strtotime($invoice['issue_date']) - strtotime($this->transaction_date)) / 86400);
            $dateScore = max(0, 30 - $daysDiff);
            $score += $dateScore;
            
            // Buscar número de factura en descripción/referencia (máx 20 puntos)
            if (stripos($this->description . ' ' . $this->reference, $invoice['invoice_number']) !== false) {
                $score += 20;
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'type' => 'issued_invoice',
                    'id' => $invoice['id'],
                    'score' => (int)$score
                ];
            }
        }
        
        return $bestMatch;
    }

    /**
     * Matching con pagos de cuotas
     */
    private function matchWithPayments($threshold) {
        $query = "SELECT id, amount, payment_date, concept
                  FROM payments
                  WHERE amount BETWEEN :min_amount AND :max_amount
                    AND payment_date BETWEEN DATE_SUB(:transaction_date, INTERVAL 15 DAY) AND DATE_ADD(:transaction_date, INTERVAL 15 DAY)
                    AND id NOT IN (SELECT matched_with_id FROM bank_transactions WHERE matched_with_type = 'payment' AND is_matched = 1)
                  ORDER BY ABS(amount - :amount) ASC, ABS(DATEDIFF(payment_date, :transaction_date2)) ASC
                  LIMIT 3";
        
        $stmt = $this->conn->prepare($query);
        $amount = abs($this->amount);
        $tolerance = $amount * 0.01; // 1% tolerancia
        
        $stmt->bindValue(':min_amount', $amount - $tolerance);
        $stmt->bindValue(':max_amount', $amount + $tolerance);
        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':transaction_date', $this->transaction_date);
        $stmt->bindValue(':transaction_date2', $this->transaction_date);
        
        $stmt->execute();
        
        $bestMatch = null;
        $bestScore = 0;
        
        while ($payment = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $score = 0;
            
            // Importe exacto = 60 puntos
            if (abs($payment['amount'] - $amount) < 0.01) {
                $score += 60;
            } else {
                $amountDiff = abs($payment['amount'] - $amount);
                $score += max(0, 40 - ($amountDiff / $amount * 100));
            }
            
            // Fecha cercana = 40 puntos
            $daysDiff = abs((strtotime($payment['payment_date']) - strtotime($this->transaction_date)) / 86400);
            $score += max(0, 40 - $daysDiff * 2);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'type' => 'payment',
                    'id' => $payment['id'],
                    'score' => (int)$score
                ];
            }
        }
        
        return $bestMatch;
    }

    /**
     * Matching con pagos de subvenciones
     */
    private function matchWithGrants($threshold) {
        $query = "SELECT id, granted_amount, resolution_date
                  FROM grant_applications
                  WHERE status = 'concedida'
                    AND granted_amount BETWEEN :min_amount AND :max_amount
                    AND resolution_date IS NOT NULL
                    AND resolution_date <= :transaction_date
                    AND id NOT IN (SELECT matched_with_id FROM bank_transactions WHERE matched_with_type = 'grant_payment' AND is_matched = 1)
                  ORDER BY ABS(granted_amount - :amount) ASC
                  LIMIT 3";
        
        $stmt = $this->conn->prepare($query);
        $amount = abs($this->amount);
        $tolerance = $amount * 0.05; // 5% tolerancia (subvenciones pueden tener retenciones)
        
        $stmt->bindValue(':min_amount', $amount - $tolerance);
        $stmt->bindValue(':max_amount', $amount + $tolerance);
        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':transaction_date', $this->transaction_date);
        
        $stmt->execute();
        
        $bestMatch = null;
        $bestScore = 0;
        
        while ($grant = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $score = 0;
            
            // Importe similar = 70 puntos
            $amountDiff = abs($grant['granted_amount'] - $amount);
            $score += max(0, 70 - ($amountDiff / $amount * 100));
            
            // Buscar "subvención" o "grant" en descripción = 30 puntos
            if (stripos($this->description . ' ' . $this->reference, 'subvencion') !== false ||
                stripos($this->description . ' ' . $this->reference, 'subvención') !== false ||
                stripos($this->description . ' ' . $this->reference, 'grant') !== false) {
                $score += 30;
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'type' => 'grant_payment',
                    'id' => $grant['id'],
                    'score' => (int)$score
                ];
            }
        }
        
        return $bestMatch;
    }

    /**
     * Obtener estadísticas de transacciones
     */
    public static function getStats($db, $filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['account_id'])) {
            $where[] = "account_id = :account_id";
            $params[':account_id'] = $filters['account_id'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = "transaction_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "transaction_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT 
                      COUNT(*) as total_transactions,
                      SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_income,
                      SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_expenses,
                      SUM(CASE WHEN is_reconciled = 1 THEN 1 ELSE 0 END) as reconciled_count,
                      SUM(CASE WHEN is_matched = 1 THEN 1 ELSE 0 END) as matched_count,
                      SUM(CASE WHEN is_matched = 0 AND transaction_date < DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) THEN 1 ELSE 0 END) as unmatched_old
                  FROM bank_transactions
                  $whereClause";
        
        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener transacciones recientes
     */
    public function readRecent($limit = 20) {
        $query = "SELECT bt.*, ba.account_name, ba.account_number
                  FROM bank_transactions bt
                  LEFT JOIN bank_accounts ba ON bt.account_id = ba.id
                  ORDER BY bt.transaction_date DESC, bt.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar transacciones sin vincular
     */
    public function countUnmatched() {
        $query = "SELECT COUNT(*) as count 
                  FROM bank_transactions 
                  WHERE is_matched = 0 
                  AND transaction_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        
        $stmt = $this->db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    /**
     * Contar transacciones sin conciliar
     */
    public function countUnreconciled() {
        $query = "SELECT COUNT(*) as count 
                  FROM bank_transactions 
                  WHERE is_reconciled = 0";
        
        $stmt = $this->db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
