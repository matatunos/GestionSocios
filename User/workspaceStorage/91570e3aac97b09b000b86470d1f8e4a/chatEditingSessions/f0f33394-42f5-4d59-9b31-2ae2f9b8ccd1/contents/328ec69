<?php

/**
 * GrantController
 * 
 * Gestión completa de subvenciones:
 * - Dashboard con estadísticas y alertas
 * - CRUD de convocatorias
 * - Gestión de solicitudes
 * - Tracking de documentos y justificaciones
 */

require_once __DIR__ . '/../Models/Grant.php';
require_once __DIR__ . '/../Models/GrantApplication.php';
require_once __DIR__ . '/../Models/AuditLog.php';

class GrantController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Dashboard principal de subvenciones
     */
    public function dashboard() {
        // Estadísticas generales
        $stats = Grant::getStats($this->db);
        $applicationStats = GrantApplication::getStats($this->db);
        
        // Subvenciones próximas a vencer
        $expiring = Grant::getExpiring($this->db, 30);
        
        // Justificaciones pendientes
        $pendingJustifications = GrantApplication::getPendingJustifications($this->db, 30);
        
        // Subvenciones recientes (últimas 10)
        $grantModel = new Grant($this->db);
        $recentGrants = $grantModel->readAll(['order_by' => 'created_at', 'order_dir' => 'DESC'], 10, 0);
        
        require_once __DIR__ . '/../Views/grants/dashboard.php';
    }

    /**
     * Listar subvenciones con filtros
     */
    public function index() {
        $filters = [
            'grant_type' => $_GET['grant_type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'our_status' => $_GET['our_status'] ?? '',
            'category' => $_GET['category'] ?? '',
            'province' => $_GET['province'] ?? '',
            'min_amount' => $_GET['min_amount'] ?? '',
            'search' => $_GET['search'] ?? '',
            'auto_discovered' => $_GET['auto_discovered'] ?? '',
            'order_by' => $_GET['order_by'] ?? 'deadline',
            'order_dir' => $_GET['order_dir'] ?? 'ASC'
        ];

        $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $grantModel = new Grant($this->db);
        $grants = $grantModel->readAll($filters, $limit, $offset);
        $total = $grantModel->count($filters);
        $totalPages = ceil($total / $limit);

        require_once __DIR__ . '/../Views/grants/index.php';
    }

    /**
     * Formulario de crear subvención
     */
    public function create() {
        // Obtener configuración de provincia/municipio
        $settingsQuery = "SELECT setting_key, setting_value FROM organization_settings 
                          WHERE category = 'location' AND setting_key IN ('province', 'municipality')";
        $stmt = $this->db->prepare($settingsQuery);
        $stmt->execute();
        $locationSettings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $locationSettings[$row['setting_key']] = $row['setting_value'];
        }
        
        require_once __DIR__ . '/../Views/grants/create.php';
    }

    /**
     * Guardar nueva subvención
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=grants');
            exit;
        }

        $grantModel = new Grant($this->db);
        
        // Datos básicos
        $grantModel->title = $_POST['title'] ?? '';
        $grantModel->description = $_POST['description'] ?? '';
        $grantModel->organization = $_POST['organization'] ?? '';
        
        // Tipo y ámbito
        $grantModel->grant_type = $_POST['grant_type'] ?? 'estatal';
        $grantModel->scope = $_POST['scope'] ?? '';
        $grantModel->category = $_POST['category'] ?? '';
        
        // Importes
        $grantModel->min_amount = !empty($_POST['min_amount']) ? $_POST['min_amount'] : null;
        $grantModel->max_amount = !empty($_POST['max_amount']) ? $_POST['max_amount'] : null;
        $grantModel->total_budget = !empty($_POST['total_budget']) ? $_POST['total_budget'] : null;
        
        // Plazos
        $grantModel->announcement_date = !empty($_POST['announcement_date']) ? $_POST['announcement_date'] : null;
        $grantModel->open_date = !empty($_POST['open_date']) ? $_POST['open_date'] : null;
        $grantModel->deadline = $_POST['deadline'] ?? null;
        $grantModel->resolution_date = !empty($_POST['resolution_date']) ? $_POST['resolution_date'] : null;
        
        // URLs
        $grantModel->url = $_POST['url'] ?? '';
        $grantModel->official_document = $_POST['official_document'] ?? '';
        $grantModel->reference_code = $_POST['reference_code'] ?? '';
        
        // Requisitos
        $grantModel->requirements = $_POST['requirements'] ?? '';
        $grantModel->eligibility = $_POST['eligibility'] ?? '';
        $grantModel->excluded_activities = $_POST['excluded_activities'] ?? '';
        $grantModel->required_documents = $_POST['required_documents'] ?? '';
        
        // Estado
        $grantModel->status = $_POST['status'] ?? 'prospecto';
        $grantModel->our_status = $_POST['our_status'] ?? 'identificada';
        
        // Localización
        $grantModel->province = $_POST['province'] ?? '';
        $grantModel->municipality = $_POST['municipality'] ?? '';
        
        // Alertas
        $grantModel->alert_days_before = !empty($_POST['alert_days_before']) ? $_POST['alert_days_before'] : 7;
        
        // Metadatos
        $grantModel->auto_discovered = isset($_POST['auto_discovered']) ? 1 : 0;
        $grantModel->search_keywords = $_POST['search_keywords'] ?? '';
        
        // Calcular relevancia
        $orgProfile = [
            'province' => $_POST['province'] ?? '',
            'municipality' => $_POST['municipality'] ?? '',
            'categories' => [] // TODO: obtener de configuración
        ];
        $grantModel->relevance_score = $grantModel->calculateRelevance([
            'province' => $grantModel->province,
            'municipality' => $grantModel->municipality,
            'category' => $grantModel->category,
            'min_amount' => $grantModel->min_amount,
            'max_amount' => $grantModel->max_amount,
            'title' => $grantModel->title,
            'description' => $grantModel->description
        ], $orgProfile);
        
        $grantModel->created_by = $_SESSION['user_id'];

        if ($grantModel->create()) {
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'create', 'grant', $grantModel->id, 'Subvención creada: ' . $grantModel->title);
            
            $_SESSION['success'] = 'Subvención creada correctamente';
            header('Location: index.php?page=grants&action=view&id=' . $grantModel->id);
        } else {
            $_SESSION['error'] = 'Error al crear la subvención';
            header('Location: index.php?page=grants&action=create');
        }
        exit;
    }

    /**
     * Ver detalle de subvención
     */
    public function view() {
        $id = $_GET['id'] ?? 0;
        
        $grantModel = new Grant($this->db);
        $grant = $grantModel->readOne($id);
        
        if (!$grant) {
            $_SESSION['error'] = 'Subvención no encontrada';
            header('Location: index.php?page=grants');
            exit;
        }
        
        // Obtener solicitudes asociadas
        $applicationModel = new GrantApplication($this->db);
        $applications = $applicationModel->readAll(['grant_id' => $id]);
        
        require_once __DIR__ . '/../Views/grants/view.php';
    }

    /**
     * Formulario de editar subvención
     */
    public function edit() {
        $id = $_GET['id'] ?? 0;
        
        $grantModel = new Grant($this->db);
        $grant = $grantModel->readOne($id);
        
        if (!$grant) {
            $_SESSION['error'] = 'Subvención no encontrada';
            header('Location: index.php?page=grants');
            exit;
        }
        
        require_once __DIR__ . '/../Views/grants/edit.php';
    }

    /**
     * Actualizar subvención
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=grants');
            exit;
        }

        $id = $_GET['id'] ?? 0;
        
        $grantModel = new Grant($this->db);
        $grantModel->id = $id;
        
        if (!$grantModel->readOne($id)) {
            $_SESSION['error'] = 'Subvención no encontrada';
            header('Location: index.php?page=grants');
            exit;
        }

        // Actualizar datos (igual que en store)
        $grantModel->title = $_POST['title'] ?? '';
        $grantModel->description = $_POST['description'] ?? '';
        $grantModel->organization = $_POST['organization'] ?? '';
        $grantModel->grant_type = $_POST['grant_type'] ?? 'estatal';
        $grantModel->scope = $_POST['scope'] ?? '';
        $grantModel->category = $_POST['category'] ?? '';
        $grantModel->min_amount = !empty($_POST['min_amount']) ? $_POST['min_amount'] : null;
        $grantModel->max_amount = !empty($_POST['max_amount']) ? $_POST['max_amount'] : null;
        $grantModel->total_budget = !empty($_POST['total_budget']) ? $_POST['total_budget'] : null;
        $grantModel->announcement_date = !empty($_POST['announcement_date']) ? $_POST['announcement_date'] : null;
        $grantModel->open_date = !empty($_POST['open_date']) ? $_POST['open_date'] : null;
        $grantModel->deadline = $_POST['deadline'] ?? null;
        $grantModel->resolution_date = !empty($_POST['resolution_date']) ? $_POST['resolution_date'] : null;
        $grantModel->url = $_POST['url'] ?? '';
        $grantModel->official_document = $_POST['official_document'] ?? '';
        $grantModel->reference_code = $_POST['reference_code'] ?? '';
        $grantModel->requirements = $_POST['requirements'] ?? '';
        $grantModel->eligibility = $_POST['eligibility'] ?? '';
        $grantModel->excluded_activities = $_POST['excluded_activities'] ?? '';
        $grantModel->required_documents = $_POST['required_documents'] ?? '';
        $grantModel->status = $_POST['status'] ?? 'prospecto';
        $grantModel->our_status = $_POST['our_status'] ?? 'identificada';
        $grantModel->province = $_POST['province'] ?? '';
        $grantModel->municipality = $_POST['municipality'] ?? '';
        $grantModel->alert_days_before = !empty($_POST['alert_days_before']) ? $_POST['alert_days_before'] : 7;

        if ($grantModel->update()) {
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'update', 'grant', $id, 'Subvención actualizada');
            
            $_SESSION['success'] = 'Subvención actualizada correctamente';
            header('Location: index.php?page=grants&action=view&id=' . $id);
        } else {
            $_SESSION['error'] = 'Error al actualizar la subvención';
            header('Location: index.php?page=grants&action=edit&id=' . $id);
        }
        exit;
    }

    /**
     * Eliminar subvención
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=grants');
            exit;
        }

        $id = $_POST['id'] ?? 0;
        
        $grantModel = new Grant($this->db);
        $grantModel->id = $id;
        
        // Verificar que no tenga solicitudes
        $checkQuery = "SELECT COUNT(*) as count FROM grant_applications WHERE grant_id = :id";
        $stmt = $this->db->prepare($checkQuery);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['count'] > 0) {
            $_SESSION['error'] = 'No se puede eliminar una subvención con solicitudes asociadas';
            header('Location: index.php?page=grants&action=view&id=' . $id);
            exit;
        }

        if ($grantModel->delete()) {
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'delete', 'grant', $id, 'Subvención eliminada');
            
            $_SESSION['success'] = 'Subvención eliminada correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar la subvención';
        }
        
        header('Location: index.php?page=grants');
        exit;
    }

    // ========== GESTIÓN DE SOLICITUDES ==========

    /**
     * Crear solicitud para una subvención
     */
    public function createApplication() {
        $grant_id = $_GET['grant_id'] ?? 0;
        
        $grantModel = new Grant($this->db);
        $grant = $grantModel->readOne($grant_id);
        
        if (!$grant) {
            $_SESSION['error'] = 'Subvención no encontrada';
            header('Location: index.php?page=grants');
            exit;
        }
        
        // Obtener usuarios para responsable
        $usersQuery = "SELECT id, first_name, last_name FROM users WHERE is_active = 1 ORDER BY first_name";
        $usersStmt = $this->db->prepare($usersQuery);
        $usersStmt->execute();
        $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/grants/applications/create.php';
    }

    /**
     * Guardar solicitud
     */
    public function storeApplication() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=grants');
            exit;
        }

        $applicationModel = new GrantApplication($this->db);
        
        $applicationModel->grant_id = $_POST['grant_id'] ?? 0;
        $applicationModel->application_number = $_POST['application_number'] ?? '';
        $applicationModel->application_date = $_POST['application_date'] ?? date('Y-m-d');
        $applicationModel->requested_amount = $_POST['requested_amount'] ?? 0;
        $applicationModel->status = $_POST['status'] ?? 'borrador';
        $applicationModel->justification_deadline = !empty($_POST['justification_deadline']) ? $_POST['justification_deadline'] : null;
        $applicationModel->payment_type = $_POST['payment_type'] ?? 'unico';
        $applicationModel->notes = $_POST['notes'] ?? '';
        $applicationModel->internal_notes = $_POST['internal_notes'] ?? '';
        $applicationModel->responsible_user_id = !empty($_POST['responsible_user_id']) ? $_POST['responsible_user_id'] : null;
        $applicationModel->created_by = $_SESSION['user_id'];

        if ($applicationModel->create()) {
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'create', 'grant_application', $applicationModel->id, 'Solicitud de subvención creada');
            
            $_SESSION['success'] = 'Solicitud creada correctamente';
            header('Location: index.php?page=grants&action=viewApplication&id=' . $applicationModel->id);
        } else {
            $_SESSION['error'] = 'Error al crear la solicitud';
            header('Location: index.php?page=grants&action=createApplication&grant_id=' . ($_POST['grant_id'] ?? 0));
        }
        exit;
    }

    /**
     * Ver detalle de solicitud
     */
    public function viewApplication() {
        $id = $_GET['id'] ?? 0;
        
        $applicationModel = new GrantApplication($this->db);
        $application = $applicationModel->readOne($id);
        
        if (!$application) {
            $_SESSION['error'] = 'Solicitud no encontrada';
            header('Location: index.php?page=grants');
            exit;
        }
        
        // Obtener documentos
        $docsQuery = "SELECT * FROM grant_documents WHERE application_id = :id ORDER BY document_type, document_name";
        $docsStmt = $this->db->prepare($docsQuery);
        $docsStmt->bindParam(':id', $id);
        $docsStmt->execute();
        $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener hitos
        $milestonesQuery = "SELECT * FROM grant_milestones WHERE application_id = :id ORDER BY due_date";
        $milestonesStmt = $this->db->prepare($milestonesQuery);
        $milestonesStmt->bindParam(':id', $id);
        $milestonesStmt->execute();
        $milestones = $milestonesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener pagos bancarios vinculados
        $paymentsQuery = "SELECT bt.*, ba.account_name 
                          FROM bank_transactions bt
                          LEFT JOIN bank_accounts ba ON bt.bank_account_id = ba.id
                          WHERE bt.matched_with_type = 'grant_payment' AND bt.matched_with_id = :id
                          ORDER BY bt.transaction_date DESC";
        $paymentsStmt = $this->db->prepare($paymentsQuery);
        $paymentsStmt->bindParam(':id', $id);
        $paymentsStmt->execute();
        $bankPayments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../Views/grants/applications/view.php';
    }

    /**
     * Actualizar estado de solicitud
     */
    public function updateApplicationStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=grants');
            exit;
        }

        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $granted_amount = !empty($_POST['granted_amount']) ? $_POST['granted_amount'] : null;
        $resolution_date = !empty($_POST['resolution_date']) ? $_POST['resolution_date'] : null;
        $resolution_text = $_POST['resolution_text'] ?? '';

        $applicationModel = new GrantApplication($this->db);
        $application = $applicationModel->readOne($id);
        
        if (!$application) {
            $_SESSION['error'] = 'Solicitud no encontrada';
            header('Location: index.php?page=grants');
            exit;
        }

        $applicationModel->id = $id;
        $applicationModel->status = $status;
        $applicationModel->granted_amount = $granted_amount;
        $applicationModel->resolution_date = $resolution_date;
        $applicationModel->resolution_text = $resolution_text;
        
        // Si se concede, actualizar estado de justificación
        if ($status === 'concedida') {
            $applicationModel->justification_status = 'pendiente';
        }

        if ($applicationModel->update()) {
            $audit = new AuditLog($this->db);
            $audit->create($_SESSION['user_id'], 'update', 'grant_application', $id, 'Estado actualizado: ' . $status);
            
            $_SESSION['success'] = 'Estado de solicitud actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el estado';
        }
        
        header('Location: index.php?page=grants&action=viewApplication&id=' . $id);
        exit;
    }

    /**
     * Marcar alerta como enviada
     */
    public function markAlertSent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $id = $_POST['id'] ?? 0;
        
        $grantModel = new Grant($this->db);
        $grantModel->id = $id;
        
        if ($grantModel->markAlertSent()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al marcar alerta']);
        }
        exit;
    }
}
