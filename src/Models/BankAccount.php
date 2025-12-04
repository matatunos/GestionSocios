<?php

/**
 * BankAccount Model
 * 
 * Gestión de cuentas bancarias de la organización
 * Incluye: CRUD, saldos, vinculación contable, estadísticas
 */

class BankAccount {
    private $conn;
    private $table_name = "bank_accounts";

    // Propiedades
    public $id;
    public $account_name;
    public $account_number;
    public $iban;
    public $swift_bic;
    public $bank_name;
    public $bank_branch;
    public $account_type;
    public $currency;
    public $initial_balance;
    public $current_balance;
    public $balance_date;
    public $overdraft_limit;
    public $monthly_fee;
    public $accounting_account_id;
    public $is_active;
    public $is_default;
    public $last_reconciliation_date;
    public $last_reconciliation_balance;
    public $notes;
    public $created_by;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nueva cuenta bancaria
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET account_name = :account_name,
                      account_number = :account_number,
                      iban = :iban,
                      swift_bic = :swift_bic,
                      bank_name = :bank_name,
                      bank_branch = :bank_branch,
                      account_type = :account_type,
                      currency = :currency,
                      initial_balance = :initial_balance,
                      current_balance = :initial_balance,
                      balance_date = :balance_date,
                      overdraft_limit = :overdraft_limit,
                      monthly_fee = :monthly_fee,
                      accounting_account_id = :accounting_account_id,
                      is_active = :is_active,
                      is_default = :is_default,
                      notes = :notes,
                      created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->account_name = htmlspecialchars(strip_tags($this->account_name));
        $this->account_number = htmlspecialchars(strip_tags($this->account_number));
        $this->iban = htmlspecialchars(strip_tags($this->iban));
        $this->bank_name = htmlspecialchars(strip_tags($this->bank_name));

        // Bind
        $stmt->bindParam(":account_name", $this->account_name);
        $stmt->bindParam(":account_number", $this->account_number);
        $stmt->bindParam(":iban", $this->iban);
        $stmt->bindParam(":swift_bic", $this->swift_bic);
        $stmt->bindParam(":bank_name", $this->bank_name);
        $stmt->bindParam(":bank_branch", $this->bank_branch);
        $stmt->bindParam(":account_type", $this->account_type);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":initial_balance", $this->initial_balance);
        $stmt->bindParam(":balance_date", $this->balance_date);
        $stmt->bindParam(":overdraft_limit", $this->overdraft_limit);
        $stmt->bindParam(":monthly_fee", $this->monthly_fee);
        $stmt->bindParam(":accounting_account_id", $this->accounting_account_id);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":is_default", $this->is_default);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Si es cuenta por defecto, quitar flag de otras
            if ($this->is_default) {
                $this->unsetOtherDefaults();
            }
            
            return true;
        }
        return false;
    }

    /**
     * Leer una cuenta bancaria
     */
    public function readOne() {
        $query = "SELECT ba.*, 
                         aa.code as accounting_code, aa.name as accounting_name,
                         u.first_name, u.last_name,
                         (SELECT COUNT(*) FROM bank_transactions WHERE bank_account_id = ba.id) as transaction_count
                  FROM " . $this->table_name . " ba
                  LEFT JOIN accounting_accounts aa ON ba.accounting_account_id = aa.id
                  LEFT JOIN users u ON ba.created_by = u.id
                  WHERE ba.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->account_name = $row['account_name'];
            $this->account_number = $row['account_number'];
            $this->iban = $row['iban'];
            $this->swift_bic = $row['swift_bic'];
            $this->bank_name = $row['bank_name'];
            $this->bank_branch = $row['bank_branch'];
            $this->account_type = $row['account_type'];
            $this->currency = $row['currency'];
            $this->initial_balance = $row['initial_balance'];
            $this->current_balance = $row['current_balance'];
            $this->balance_date = $row['balance_date'];
            $this->overdraft_limit = $row['overdraft_limit'];
            $this->monthly_fee = $row['monthly_fee'];
            $this->accounting_account_id = $row['accounting_account_id'];
            $this->is_active = $row['is_active'];
            $this->is_default = $row['is_default'];
            $this->last_reconciliation_date = $row['last_reconciliation_date'];
            $this->last_reconciliation_balance = $row['last_reconciliation_balance'];
            $this->notes = $row['notes'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return $row;
        }
        
        return false;
    }

    /**
     * Leer todas las cuentas con filtros
     */
    public function readAll($filters = [], $limit = null, $offset = 0) {
        $where = [];
        $params = [];

        if (!empty($filters['account_type'])) {
            $where[] = "ba.account_type = :account_type";
            $params[':account_type'] = $filters['account_type'];
        }

        if (!empty($filters['is_active'])) {
            $where[] = "ba.is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(ba.account_name LIKE :search OR ba.account_number LIKE :search OR ba.iban LIKE :search OR ba.bank_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT ba.*, 
                         aa.code as accounting_code, aa.name as accounting_name,
                         (SELECT COUNT(*) FROM bank_transactions WHERE account_id = ba.id) as transaction_count,
                         (SELECT COUNT(*) FROM bank_transactions WHERE account_id = ba.id AND is_reconciled = 0) as unreconciled_count
                  FROM " . $this->table_name . " ba
                  LEFT JOIN accounting_accounts aa ON ba.accounting_account_id = aa.id
                  $whereClause
                  ORDER BY ba.is_default DESC, ba.account_name ASC";

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
     * Contar cuentas con filtros
     */
    public function count($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['account_type'])) {
            $where[] = "account_type = :account_type";
            $params[':account_type'] = $filters['account_type'];
        }

        if (!empty($filters['is_active'])) {
            $where[] = "is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(account_name LIKE :search OR account_number LIKE :search OR iban LIKE :search OR bank_name LIKE :search)";
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
     * Actualizar cuenta bancaria
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET account_name = :account_name,
                      account_number = :account_number,
                      iban = :iban,
                      swift_bic = :swift_bic,
                      bank_name = :bank_name,
                      bank_branch = :bank_branch,
                      account_type = :account_type,
                      currency = :currency,
                      overdraft_limit = :overdraft_limit,
                      monthly_fee = :monthly_fee,
                      accounting_account_id = :accounting_account_id,
                      is_active = :is_active,
                      is_default = :is_default,
                      notes = :notes
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->account_name = htmlspecialchars(strip_tags($this->account_name));
        $this->account_number = htmlspecialchars(strip_tags($this->account_number));
        $this->iban = htmlspecialchars(strip_tags($this->iban));
        $this->bank_name = htmlspecialchars(strip_tags($this->bank_name));

        // Bind
        $stmt->bindParam(":account_name", $this->account_name);
        $stmt->bindParam(":account_number", $this->account_number);
        $stmt->bindParam(":iban", $this->iban);
        $stmt->bindParam(":swift_bic", $this->swift_bic);
        $stmt->bindParam(":bank_name", $this->bank_name);
        $stmt->bindParam(":bank_branch", $this->bank_branch);
        $stmt->bindParam(":account_type", $this->account_type);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":overdraft_limit", $this->overdraft_limit);
        $stmt->bindParam(":monthly_fee", $this->monthly_fee);
        $stmt->bindParam(":accounting_account_id", $this->accounting_account_id);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":is_default", $this->is_default);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            // Si se marca como default, quitar flag de otras
            if ($this->is_default) {
                $this->unsetOtherDefaults();
            }
            return true;
        }
        return false;
    }

    /**
     * Eliminar cuenta bancaria (solo si no tiene transacciones)
     */
    public function delete() {
        // Verificar que no tenga transacciones
        $query = "SELECT COUNT(*) as count FROM bank_transactions WHERE bank_account_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['count'] > 0) {
            return false; // No eliminar si tiene transacciones
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    /**
     * Recalcular saldo actual desde transacciones
     */
    public function recalculateBalance() {
        $query = "SELECT COALESCE(SUM(amount), 0) as total
                  FROM bank_transactions
                  WHERE bank_account_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $calculated = $this->initial_balance + $row['total'];
        
        // Actualizar current_balance
        $updateQuery = "UPDATE " . $this->table_name . " 
                        SET current_balance = :balance,
                            balance_date = CURRENT_DATE
                        WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(":balance", $calculated);
        $updateStmt->bindParam(":id", $this->id);
        $updateStmt->execute();
        
        return $calculated;
    }

    /**
     * Obtener cuenta por defecto
     */
    public static function getDefault($db) {
        $query = "SELECT * FROM bank_accounts WHERE is_default = 1 AND is_active = 1 LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Quitar flag de default de otras cuentas
     */
    private function unsetOtherDefaults() {
        $query = "UPDATE " . $this->table_name . " SET is_default = 0 WHERE id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    /**
     * Estadísticas de la cuenta
     */
    public static function getStats($db) {
        $query = "SELECT 
                      COUNT(*) as total_accounts,
                      SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_accounts,
                      SUM(current_balance) as total_balance,
                      (SELECT COUNT(*) FROM bank_transactions WHERE is_reconciled = 0) as unreconciled_transactions,
                      (SELECT COUNT(*) FROM bank_transactions WHERE is_matched = 0 AND transaction_date > DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)) as unmatched_recent
                  FROM bank_accounts";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener movimientos de la cuenta con filtros
     */
    public function getTransactions($filters = [], $limit = 50, $offset = 0) {
        $where = ["bank_account_id = :account_id"];
        $params = [':account_id' => $this->id];

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

        $whereClause = implode(' AND ', $where);

        $query = "SELECT * FROM bank_transactions 
                  WHERE $whereClause
                  ORDER BY transaction_date DESC, id DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener saldo a una fecha determinada
     */
    public function getBalanceAtDate($date) {
        $query = "SELECT COALESCE(SUM(amount), 0) as total
                  FROM bank_transactions
                  WHERE bank_account_id = :id
                    AND transaction_date <= :date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $this->initial_balance + $row['total'];
    }
}
