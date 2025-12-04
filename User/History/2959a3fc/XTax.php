<?php

/**
 * TransactionMatch Model
 * 
 * Vinculaciones entre movimientos bancarios y operaciones del sistema
 * (facturas, pagos, subvenciones, donaciones, gastos)
 */

class TransactionMatch {
    private $conn;
    private $table_name = "transaction_matches";

    // Propiedades
    public $id;
    public $bank_transaction_id;
    public $related_type;
    public $related_id;
    public $match_type;
    public $match_confidence;
    public $match_criteria;
    public $matched_amount;
    public $status;
    public $notes;
    public $matched_by;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo match
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET bank_transaction_id = :bank_transaction_id,
                      related_type = :related_type,
                      related_id = :related_id,
                      match_type = :match_type,
                      match_confidence = :match_confidence,
                      match_criteria = :match_criteria,
                      matched_amount = :matched_amount,
                      status = :status,
                      notes = :notes,
                      matched_by = :matched_by";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind
        $stmt->bindParam(":bank_transaction_id", $this->bank_transaction_id);
        $stmt->bindParam(":related_type", $this->related_type);
        $stmt->bindParam(":related_id", $this->related_id);
        $stmt->bindParam(":match_type", $this->match_type);
        $stmt->bindParam(":match_confidence", $this->match_confidence);
        $stmt->bindParam(":match_criteria", $this->match_criteria);
        $stmt->bindParam(":matched_amount", $this->matched_amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":matched_by", $this->matched_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Leer un match
     */
    public function readOne() {
        $query = "SELECT tm.*, 
                         bt.amount, bt.transaction_date, bt.description as transaction_description,
                         u.first_name, u.last_name
                  FROM " . $this->table_name . " tm
                  LEFT JOIN bank_transactions bt ON tm.bank_transaction_id = bt.id
                  LEFT JOIN users u ON tm.matched_by = u.id
                  WHERE tm.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }

    /**
     * Leer todos los matches de una transacción
     */
    public function readByTransaction($transaction_id) {
        $query = "SELECT tm.*, 
                         u.first_name, u.last_name
                  FROM " . $this->table_name . " tm
                  LEFT JOIN users u ON tm.matched_by = u.id
                  WHERE tm.bank_transaction_id = :transaction_id
                  ORDER BY tm.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Leer matches por tipo y ID de operación relacionada
     */
    public function readByRelated($related_type, $related_id) {
        $query = "SELECT tm.*, 
                         bt.amount, bt.transaction_date, bt.description, bt.bank_account_id,
                         ba.account_name
                  FROM " . $this->table_name . " tm
                  LEFT JOIN bank_transactions bt ON tm.bank_transaction_id = bt.id
                  LEFT JOIN bank_accounts ba ON bt.bank_account_id = ba.id
                  WHERE tm.related_type = :related_type
                    AND tm.related_id = :related_id
                  ORDER BY bt.transaction_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":related_type", $related_type);
        $stmt->bindParam(":related_id", $related_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar match
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status,
                      notes = :notes,
                      match_confidence = :match_confidence
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->notes = htmlspecialchars(strip_tags($this->notes));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":match_confidence", $this->match_confidence);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Eliminar match
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    /**
     * Obtener información detallada de la operación vinculada
     */
    public function getRelatedDetails() {
        $details = [];

        switch ($this->related_type) {
            case 'issued_invoice':
                $query = "SELECT invoice_number, issue_date, total_amount, status, customer_name
                          FROM issued_invoices WHERE id = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $this->related_id);
                $stmt->execute();
                $details = $stmt->fetch(PDO::FETCH_ASSOC);
                $details['type_label'] = 'Factura Emitida';
                break;

            case 'payment':
                $query = "SELECT p.amount, p.payment_date, p.concept, 
                                 CONCAT(m.first_name, ' ', m.last_name) as member_name
                          FROM payments p
                          LEFT JOIN members m ON p.member_id = m.id
                          WHERE p.id = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $this->related_id);
                $stmt->execute();
                $details = $stmt->fetch(PDO::FETCH_ASSOC);
                $details['type_label'] = 'Pago de Cuota';
                break;

            case 'grant_payment':
                $query = "SELECT ga.granted_amount, ga.resolution_date, g.title
                          FROM grant_applications ga
                          LEFT JOIN grants g ON ga.grant_id = g.id
                          WHERE ga.id = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $this->related_id);
                $stmt->execute();
                $details = $stmt->fetch(PDO::FETCH_ASSOC);
                $details['type_label'] = 'Pago de Subvención';
                break;

            case 'donation':
                $query = "SELECT d.amount, d.donation_date, d.type,
                                 dn.name as donor_name
                          FROM donations d
                          LEFT JOIN donors dn ON d.donor_id = dn.id
                          WHERE d.id = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $this->related_id);
                $stmt->execute();
                $details = $stmt->fetch(PDO::FETCH_ASSOC);
                $details['type_label'] = 'Donación';
                break;

            case 'expense':
                $query = "SELECT e.amount, e.expense_date, e.concept, e.description,
                                 s.name as supplier_name
                          FROM expenses e
                          LEFT JOIN suppliers s ON e.supplier_id = s.id
                          WHERE e.id = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $this->related_id);
                $stmt->execute();
                $details = $stmt->fetch(PDO::FETCH_ASSOC);
                $details['type_label'] = 'Gasto';
                break;
        }

        return $details;
    }

    /**
     * Sugerir matches automáticos para transacciones sin vincular
     */
    public static function suggestMatches($db, $limit = 20) {
        // Obtener transacciones sin match recientes
        $query = "SELECT * FROM bank_transactions
                  WHERE is_matched = 0
                    AND transaction_date > DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)
                  ORDER BY transaction_date DESC
                  LIMIT :limit";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $suggestions = [];
        
        while ($transaction = $stmt->fetch(PDO::FETCH_ASSOC)) {
            require_once __DIR__ . '/BankTransaction.php';
            $bt = new BankTransaction($db);
            $bt->id = $transaction['id'];
            $bt->amount = $transaction['amount'];
            $bt->transaction_date = $transaction['transaction_date'];
            $bt->description = $transaction['description'];
            $bt->reference = $transaction['reference'];
            
            // Intentar auto-match (sin aplicarlo, solo sugerir)
            // TODO: Implementar lógica de sugerencias
            
            $suggestions[] = [
                'transaction' => $transaction,
                'suggestions' => [] // Aquí irían las sugerencias
            ];
        }
        
        return $suggestions;
    }

    /**
     * Estadísticas de matching
     */
    public static function getStats($db) {
        $query = "SELECT 
                      COUNT(*) as total_matches,
                      SUM(CASE WHEN match_type = 'automatic' THEN 1 ELSE 0 END) as automatic_matches,
                      SUM(CASE WHEN match_type = 'manual' THEN 1 ELSE 0 END) as manual_matches,
                      SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_matches,
                      SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending_review,
                      AVG(match_confidence) as avg_confidence
                  FROM transaction_matches";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
