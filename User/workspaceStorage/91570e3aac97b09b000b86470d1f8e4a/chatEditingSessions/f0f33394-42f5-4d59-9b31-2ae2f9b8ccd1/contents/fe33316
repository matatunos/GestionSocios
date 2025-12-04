<?php

/**
 * BankReconciliation Model
 * 
 * Gestión de conciliaciones bancarias periódicas
 * Cuadre de extracto bancario vs. movimientos registrados
 */

class BankReconciliation {
    private $conn;
    private $table_name = "bank_reconciliations";

    // Propiedades
    public $id;
    public $bank_account_id;
    public $period_start;
    public $period_end;
    public $reconciliation_date;
    public $opening_balance;
    public $closing_balance;
    public $calculated_balance;
    public $total_credits;
    public $total_debits;
    public $transactions_reconciled;
    public $transactions_pending;
    public $difference;
    public $is_balanced;
    public $status;
    public $notes;
    public $adjustments;
    public $reconciled_by;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nueva conciliación
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET bank_account_id = :bank_account_id,
                      period_start = :period_start,
                      period_end = :period_end,
                      reconciliation_date = :reconciliation_date,
                      opening_balance = :opening_balance,
                      closing_balance = :closing_balance,
                      calculated_balance = :calculated_balance,
                      total_credits = :total_credits,
                      total_debits = :total_debits,
                      transactions_reconciled = :transactions_reconciled,
                      transactions_pending = :transactions_pending,
                      difference = :difference,
                      is_balanced = :is_balanced,
                      status = :status,
                      notes = :notes,
                      adjustments = :adjustments,
                      reconciled_by = :reconciled_by";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind
        $stmt->bindParam(":bank_account_id", $this->bank_account_id);
        $stmt->bindParam(":period_start", $this->period_start);
        $stmt->bindParam(":period_end", $this->period_end);
        $stmt->bindParam(":reconciliation_date", $this->reconciliation_date);
        $stmt->bindParam(":opening_balance", $this->opening_balance);
        $stmt->bindParam(":closing_balance", $this->closing_balance);
        $stmt->bindParam(":calculated_balance", $this->calculated_balance);
        $stmt->bindParam(":total_credits", $this->total_credits);
        $stmt->bindParam(":total_debits", $this->total_debits);
        $stmt->bindParam(":transactions_reconciled", $this->transactions_reconciled);
        $stmt->bindParam(":transactions_pending", $this->transactions_pending);
        $stmt->bindParam(":difference", $this->difference);
        $stmt->bindParam(":is_balanced", $this->is_balanced);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":adjustments", $this->adjustments);
        $stmt->bindParam(":reconciled_by", $this->reconciled_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Leer una conciliación
     */
    public function readOne() {
        $query = "SELECT br.*, 
                         ba.account_name, ba.account_number, ba.bank_name,
                         u.first_name, u.last_name
                  FROM " . $this->table_name . " br
                  LEFT JOIN bank_accounts ba ON br.bank_account_id = ba.id
                  LEFT JOIN users u ON br.reconciled_by = u.id
                  WHERE br.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->bank_account_id = $row['bank_account_id'];
            $this->period_start = $row['period_start'];
            $this->period_end = $row['period_end'];
            $this->reconciliation_date = $row['reconciliation_date'];
            $this->opening_balance = $row['opening_balance'];
            $this->closing_balance = $row['closing_balance'];
            $this->calculated_balance = $row['calculated_balance'];
            $this->total_credits = $row['total_credits'];
            $this->total_debits = $row['total_debits'];
            $this->transactions_reconciled = $row['transactions_reconciled'];
            $this->transactions_pending = $row['transactions_pending'];
            $this->difference = $row['difference'];
            $this->is_balanced = $row['is_balanced'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->adjustments = $row['adjustments'];
            $this->reconciled_by = $row['reconciled_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return $row;
        }
        
        return false;
    }

    /**
     * Leer todas las conciliaciones
     */
    public function readAll($filters = [], $limit = null, $offset = 0) {
        $where = [];
        $params = [];

        if (!empty($filters['bank_account_id'])) {
            $where[] = "br.bank_account_id = :bank_account_id";
            $params[':bank_account_id'] = $filters['bank_account_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "br.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = "br.period_end >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT br.*, 
                         ba.account_name, ba.account_number, ba.bank_name
                  FROM " . $this->table_name . " br
                  LEFT JOIN bank_accounts ba ON br.bank_account_id = ba.id
                  $whereClause
                  ORDER BY br.period_end DESC";

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
     * Actualizar conciliación
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET closing_balance = :closing_balance,
                      calculated_balance = :calculated_balance,
                      total_credits = :total_credits,
                      total_debits = :total_debits,
                      transactions_reconciled = :transactions_reconciled,
                      transactions_pending = :transactions_pending,
                      difference = :difference,
                      is_balanced = :is_balanced,
                      status = :status,
                      notes = :notes,
                      adjustments = :adjustments
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->notes = htmlspecialchars(strip_tags($this->notes));

        $stmt->bindParam(":closing_balance", $this->closing_balance);
        $stmt->bindParam(":calculated_balance", $this->calculated_balance);
        $stmt->bindParam(":total_credits", $this->total_credits);
        $stmt->bindParam(":total_debits", $this->total_debits);
        $stmt->bindParam(":transactions_reconciled", $this->transactions_reconciled);
        $stmt->bindParam(":transactions_pending", $this->transactions_pending);
        $stmt->bindParam(":difference", $this->difference);
        $stmt->bindParam(":is_balanced", $this->is_balanced);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":adjustments", $this->adjustments);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Iniciar proceso de conciliación
     * Calcula automáticamente los totales del período
     */
    public function startReconciliation($bank_account_id, $period_start, $period_end, $closing_balance_from_bank, $user_id) {
        $this->bank_account_id = $bank_account_id;
        $this->period_start = $period_start;
        $this->period_end = $period_end;
        $this->reconciliation_date = date('Y-m-d');
        $this->closing_balance = $closing_balance_from_bank;
        $this->reconciled_by = $user_id;
        $this->status = 'en_proceso';

        // Obtener saldo inicial
        require_once __DIR__ . '/BankAccount.php';
        $account = new BankAccount($this->conn);
        $account->id = $bank_account_id;
        
        // Saldo al inicio del período
        $prevDay = date('Y-m-d', strtotime($period_start . ' -1 day'));
        $this->opening_balance = $account->getBalanceAtDate($prevDay);

        // Calcular totales del período
        $statsQuery = "SELECT 
                           SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as credits,
                           SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as debits,
                           SUM(CASE WHEN is_reconciled = 1 THEN 1 ELSE 0 END) as reconciled,
                           SUM(CASE WHEN is_reconciled = 0 THEN 1 ELSE 0 END) as pending
                       FROM bank_transactions
                       WHERE bank_account_id = :account_id
                         AND transaction_date BETWEEN :start_date AND :end_date";
        
        $statsStmt = $this->conn->prepare($statsQuery);
        $statsStmt->execute([
            ':account_id' => $bank_account_id,
            ':start_date' => $period_start,
            ':end_date' => $period_end
        ]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        $this->total_credits = $stats['credits'] ?? 0;
        $this->total_debits = $stats['debits'] ?? 0;
        $this->transactions_reconciled = $stats['reconciled'] ?? 0;
        $this->transactions_pending = $stats['pending'] ?? 0;

        // Calcular saldo calculado
        $this->calculated_balance = $this->opening_balance + $this->total_credits - $this->total_debits;

        // Calcular diferencia
        $this->difference = $this->closing_balance - $this->calculated_balance;
        $this->is_balanced = (abs($this->difference) < 0.01) ? 1 : 0;

        if ($this->is_balanced) {
            $this->status = 'completada';
        } else if (abs($this->difference) > 1) {
            $this->status = 'con_diferencias';
        }

        return $this->create();
    }

    /**
     * Completar conciliación
     * Marca todas las transacciones del período como conciliadas
     */
    public function complete() {
        if ($this->status === 'completada') {
            return true; // Ya está completada
        }

        // Marcar todas las transacciones del período como conciliadas
        $query = "UPDATE bank_transactions 
                  SET is_reconciled = 1,
                      reconciliation_date = :recon_date,
                      reconciliation_id = :recon_id
                  WHERE bank_account_id = :account_id
                    AND transaction_date BETWEEN :start_date AND :end_date
                    AND is_reconciled = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':recon_date' => $this->reconciliation_date,
            ':recon_id' => $this->id,
            ':account_id' => $this->bank_account_id,
            ':start_date' => $this->period_start,
            ':end_date' => $this->period_end
        ]);

        // Actualizar cuenta bancaria
        $updateAccountQuery = "UPDATE bank_accounts 
                                SET last_reconciliation_date = :date,
                                    last_reconciliation_balance = :balance
                                WHERE id = :account_id";
        $updateStmt = $this->conn->prepare($updateAccountQuery);
        $updateStmt->execute([
            ':date' => $this->reconciliation_date,
            ':balance' => $this->closing_balance,
            ':account_id' => $this->bank_account_id
        ]);

        // Actualizar estado de conciliación
        $this->status = 'completada';
        return $this->update();
    }

    /**
     * Obtener transacciones del período de conciliación
     */
    public function getTransactions() {
        $query = "SELECT * FROM bank_transactions
                  WHERE bank_account_id = :account_id
                    AND transaction_date BETWEEN :start_date AND :end_date
                  ORDER BY transaction_date ASC, id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':account_id' => $this->bank_account_id,
            ':start_date' => $this->period_start,
            ':end_date' => $this->period_end
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estadísticas de conciliaciones
     */
    public static function getStats($db, $bank_account_id = null) {
        $where = $bank_account_id ? "WHERE bank_account_id = :account_id" : "";
        
        $query = "SELECT 
                      COUNT(*) as total_reconciliations,
                      SUM(CASE WHEN status = 'completada' THEN 1 ELSE 0 END) as completed,
                      SUM(CASE WHEN status = 'con_diferencias' THEN 1 ELSE 0 END) as with_differences,
                      SUM(CASE WHEN status = 'en_proceso' THEN 1 ELSE 0 END) as in_progress,
                      MAX(period_end) as last_reconciliation
                  FROM bank_reconciliations
                  $where";
        
        $stmt = $db->prepare($query);
        if ($bank_account_id) {
            $stmt->bindParam(':account_id', $bank_account_id);
        }
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
