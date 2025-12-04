<?php

/**
 * FinancialDashboardController
 * 
 * Dashboard financiero consolidado que integra:
 * - Facturas emitidas (ingresos)
 * - Subvenciones (ingresos)
 * - Gastos/Proveedores (egresos)
 * - Movimientos bancarios
 * - Conciliación automática
 */

require_once __DIR__ . '/../Models/BankTransaction.php';
require_once __DIR__ . '/../Models/Grant.php';
require_once __DIR__ . '/../Models/GrantApplication.php';
require_once __DIR__ . '/../Models/BankAccount.php';

class FinancialDashboardController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Dashboard financiero principal
     */
    public function index() {
        $period = $_GET['period'] ?? 'month';
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        
        // Calcular fechas según período
        switch ($period) {
            case 'year':
                $startDate = "$year-01-01";
                $endDate = "$year-12-31";
                break;
            case 'quarter':
                $quarter = ceil($month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $startDate = sprintf("%d-%02d-01", $year, $startMonth);
                $endDate = date('Y-m-t', strtotime("$year-$startMonth-01 +2 months"));
                break;
            case 'month':
            default:
                $startDate = "$year-$month-01";
                $endDate = date('Y-m-t', strtotime($startDate));
                break;
        }
        
        // Obtener datos de todos los módulos
        $bankData = $this->getBankData($startDate, $endDate);
        $invoicesData = $this->getInvoicesData($startDate, $endDate);
        $grantsData = $this->getGrantsData($startDate, $endDate);
        $expensesData = $this->getExpensesData($startDate, $endDate);
        
        // Calcular totales y flujo de caja
        $cashFlow = $this->calculateCashFlow($bankData, $invoicesData, $grantsData, $expensesData);
        
        // Obtener alertas de conciliación
        $alerts = $this->getReconciliationAlerts();
        
        // Gráfico de evolución mensual
        $monthlyEvolution = $this->getMonthlyEvolution($year);
        
        require_once __DIR__ . '/../Views/financial/dashboard.php';
    }
    
    /**
     * Obtener datos bancarios del período
     */
    private function getBankData($startDate, $endDate) {
        $query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN transaction_type IN ('credit', 'transfer_in') THEN amount ELSE 0 END) as total_ingresos,
                    SUM(CASE WHEN transaction_type IN ('debit', 'transfer_out', 'fee') THEN ABS(amount) ELSE 0 END) as total_egresos,
                    SUM(CASE WHEN is_reconciled = 0 THEN 1 ELSE 0 END) as unreconciled_count
                  FROM bank_transactions
                  WHERE transaction_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener datos de facturas del período
     */
    private function getInvoicesData($startDate, $endDate) {
        // Verificar si existe la tabla issued_invoices
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'issued_invoices'");
        if ($tableCheck->rowCount() === 0) {
            return [
                'total_invoices' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'pending_amount' => 0
            ];
        }
        
        $query = "SELECT 
                    COUNT(*) as total_invoices,
                    SUM(total_amount) as total_amount,
                    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount
                  FROM issued_invoices
                  WHERE invoice_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener datos de subvenciones del período
     */
    private function getGrantsData($startDate, $endDate) {
        $query = "SELECT 
                    COUNT(DISTINCT g.id) as total_grants,
                    COUNT(DISTINCT ga.id) as total_applications,
                    SUM(CASE WHEN ga.status = 'concedida' THEN ga.granted_amount ELSE 0 END) as granted_amount,
                    SUM(CASE WHEN ga.status = 'concedida' THEN ga.requested_amount ELSE 0 END) as requested_amount
                  FROM grants g
                  LEFT JOIN grant_applications ga ON g.id = ga.grant_id
                  WHERE g.created_at BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener datos de gastos del período
     */
    private function getExpensesData($startDate, $endDate) {
        // Verificar si existe la tabla expenses
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'expenses'");
        if ($tableCheck->rowCount() === 0) {
            return [
                'total_expenses' => 0,
                'total_amount' => 0
            ];
        }
        
        $query = "SELECT 
                    COUNT(*) as total_expenses,
                    SUM(amount) as total_amount
                  FROM expenses
                  WHERE expense_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular flujo de caja consolidado
     */
    private function calculateCashFlow($bankData, $invoicesData, $grantsData, $expensesData) {
        return [
            'bank_ingresos' => (float)($bankData['total_ingresos'] ?? 0),
            'bank_egresos' => (float)($bankData['total_egresos'] ?? 0),
            'bank_balance' => (float)($bankData['total_ingresos'] ?? 0) - (float)($bankData['total_egresos'] ?? 0),
            
            'invoices_total' => (float)($invoicesData['total_amount'] ?? 0),
            'invoices_paid' => (float)($invoicesData['paid_amount'] ?? 0),
            'invoices_pending' => (float)($invoicesData['pending_amount'] ?? 0),
            
            'grants_granted' => (float)($grantsData['granted_amount'] ?? 0),
            'grants_requested' => (float)($grantsData['requested_amount'] ?? 0),
            
            'expenses_total' => (float)($expensesData['total_amount'] ?? 0),
            
            'expected_ingresos' => (float)($invoicesData['pending_amount'] ?? 0) + (float)($grantsData['granted_amount'] ?? 0),
            'net_cashflow' => (float)($bankData['total_ingresos'] ?? 0) - (float)($bankData['total_egresos'] ?? 0)
        ];
    }
    
    /**
     * Obtener alertas de conciliación
     */
    private function getReconciliationAlerts() {
        $alerts = [];
        
        // Transacciones sin emparejar
        $query = "SELECT COUNT(*) as count FROM bank_transactions WHERE is_reconciled = 0";
        $stmt = $this->db->query($query);
        $unmatchedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($unmatchedCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "$unmatchedCount transacciones bancarias sin emparejar",
                'action' => 'index.php?page=bank&subpage=matching&action=auto',
                'action_label' => 'Ejecutar matching automático'
            ];
        }
        
        // Facturas pendientes de cobro antiguas (>30 días)
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'issued_invoices'");
        if ($tableCheck->rowCount() > 0) {
            $query = "SELECT COUNT(*) as count FROM issued_invoices 
                      WHERE payment_status = 'pending' 
                      AND invoice_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $stmt = $this->db->query($query);
            $overdueInvoices = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($overdueInvoices > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => "$overdueInvoices facturas con más de 30 días sin cobrar",
                    'action' => 'index.php?page=invoices&status=pending',
                    'action_label' => 'Ver facturas pendientes'
                ];
            }
        }
        
        // Subvenciones próximas a vencer
        $query = "SELECT COUNT(*) as count FROM grants 
                  WHERE status = 'abierta' 
                  AND application_deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                  AND (alert_sent = 0 OR alert_sent IS NULL)";
        $stmt = $this->db->query($query);
        $expiringGrants = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($expiringGrants > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "$expiringGrants subvenciones vencen en los próximos 15 días",
                'action' => 'index.php?page=grants&subpage=dashboard',
                'action_label' => 'Ver subvenciones'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Obtener evolución mensual del año
     */
    private function getMonthlyEvolution($year) {
        $evolution = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = sprintf("%d-%02d-01", $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $query = "SELECT 
                        SUM(CASE WHEN transaction_type IN ('credit', 'transfer_in') THEN amount ELSE 0 END) as ingresos,
                        SUM(CASE WHEN transaction_type IN ('debit', 'transfer_out', 'fee') THEN ABS(amount) ELSE 0 END) as egresos
                      FROM bank_transactions
                      WHERE transaction_date BETWEEN :start_date AND :end_date";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $evolution[] = [
                'month' => $month,
                'month_name' => date('M', mktime(0, 0, 0, $month, 1)),
                'ingresos' => (float)($data['ingresos'] ?? 0),
                'egresos' => (float)($data['egresos'] ?? 0),
                'balance' => (float)($data['ingresos'] ?? 0) - (float)($data['egresos'] ?? 0)
            ];
        }
        
        return $evolution;
    }
    
    /**
     * Ejecutar matching automático completo
     */
    public function autoMatch() {
        $transactionModel = new BankTransaction($this->db);
        $result = $transactionModel->autoMatch(null, 85);
        
        $_SESSION['message'] = "Matching automático completado: {$result['matched']} emparejadas, {$result['suggested']} sugerencias";
        
        header('Location: index.php?page=financial');
        exit;
    }
    
    /**
     * Exportar reporte financiero
     */
    public function exportReport() {
        $period = $_GET['period'] ?? 'month';
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        $format = $_GET['format'] ?? 'csv';
        
        // Obtener datos (misma lógica que index)
        // ... (implementar exportación CSV/Excel/PDF)
        
        // Por ahora, redirect
        $_SESSION['message'] = "Funcionalidad de exportación próximamente disponible";
        header('Location: index.php?page=financial');
        exit;
    }
}
