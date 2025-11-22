<?php

require_once __DIR__ . '/../Models/Task.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';

class TaskController {
    private $db;
    private $authHelper;

    public function __construct($db) {
        $this->db = $db;
        $this->authHelper = new AuthHelper($db);
    }

    /**
     * Listar tareas
     */
    public function index() {
        // Verificar autenticación
        if (!$this->authHelper->checkAuth()) {
            header('Location: /index.php?page=login');
            exit;
        }

        // Verificar permiso
        if (!$this->authHelper->hasPermission('tasks.view')) {
            $_SESSION['error'] = 'No tienes permiso para ver tareas';
            header('Location: /index.php?page=dashboard');
            exit;
        }

        $taskModel = new Task($this->db);
        
        // Obtener filtros de la URL
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['priority'])) {
            $filters['priority'] = $_GET['priority'];
        }
        if (isset($_GET['assigned_to'])) {
            $filters['assigned_to'] = $_GET['assigned_to'];
        }
        if (isset($_GET['category_id'])) {
            $filters['category_id'] = $_GET['category_id'];
        }
        if (isset($_GET['filter'])) {
            if ($_GET['filter'] === 'overdue') {
                $filters['overdue'] = true;
            } elseif ($_GET['filter'] === 'today') {
                $filters['today'] = true;
            } elseif ($_GET['filter'] === 'mytasks') {
                $filters['assigned_to'] = $_SESSION['user_id'];
            }
        }

        $stmt = $taskModel->readAll($filters);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener categorías
        $categoryStmt = $this->db->query("SELECT * FROM task_categories ORDER BY name");
        $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener usuarios para asignación
        $userStmt = $this->db->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener estadísticas
        $stats = $taskModel->getStatistics();

        require __DIR__ . '/../Views/tasks/index.php';
    }

    /**
     * Ver detalle de tarea
     */
    public function view() {
        if (!$this->authHelper->checkAuth()) {
            header('Location: /index.php?page=login');
            exit;
        }

        if (!$this->authHelper->hasPermission('tasks.view')) {
            $_SESSION['error'] = 'No tienes permiso para ver tareas';
            header('Location: /index.php?page=dashboard');
            exit;
        }

        $taskModel = new Task($this->db);
        $taskModel->id = $_GET['id'] ?? 0;
        $task = $taskModel->readOne();

        if (!$task) {
            $_SESSION['error'] = 'Tarea no encontrada';
            header('Location: /index.php?page=tasks');
            exit;
        }

        // Obtener comentarios
        $comments = $taskModel->getComments($task['id']);

        require __DIR__ . '/../Views/tasks/view.php';
    }

    /**
     * Crear nueva tarea
     */
    public function create() {
        if (!$this->authHelper->checkAuth()) {
            header('Location: /index.php?page=login');
            exit;
        }

        if (!$this->authHelper->hasPermission('tasks.create')) {
            $_SESSION['error'] = 'No tienes permiso para crear tareas';
            header('Location: /index.php?page=tasks');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskModel = new Task($this->db);
            
            $taskModel->title = $_POST['title'] ?? '';
            $taskModel->description = $_POST['description'] ?? '';
            $taskModel->category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            $taskModel->priority = $_POST['priority'] ?? 'medium';
            $taskModel->status = $_POST['status'] ?? 'pending';
            $taskModel->due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $taskModel->due_time = !empty($_POST['due_time']) ? $_POST['due_time'] : null;
            $taskModel->assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
            $taskModel->created_by = $_SESSION['user_id'];
            $taskModel->related_entity_type = !empty($_POST['related_entity_type']) ? $_POST['related_entity_type'] : null;
            $taskModel->related_entity_id = !empty($_POST['related_entity_id']) ? $_POST['related_entity_id'] : null;
            $taskModel->notes = $_POST['notes'] ?? '';

            if ($taskModel->create()) {
                $_SESSION['success'] = 'Tarea creada exitosamente';
                header('Location: /index.php?page=tasks');
                exit;
            } else {
                $_SESSION['error'] = 'Error al crear la tarea';
            }
        }

        // Obtener categorías
        $categoryStmt = $this->db->query("SELECT * FROM task_categories ORDER BY name");
        $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener usuarios
        $userStmt = $this->db->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/tasks/create.php';
    }

    /**
     * Editar tarea
     */
    public function edit() {
        if (!$this->authHelper->checkAuth()) {
            header('Location: /index.php?page=login');
            exit;
        }

        if (!$this->authHelper->hasPermission('tasks.edit')) {
            $_SESSION['error'] = 'No tienes permiso para editar tareas';
            header('Location: /index.php?page=tasks');
            exit;
        }

        $taskModel = new Task($this->db);
        $taskModel->id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskModel->title = $_POST['title'] ?? '';
            $taskModel->description = $_POST['description'] ?? '';
            $taskModel->category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            $taskModel->priority = $_POST['priority'] ?? 'medium';
            $taskModel->status = $_POST['status'] ?? 'pending';
            $taskModel->due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $taskModel->due_time = !empty($_POST['due_time']) ? $_POST['due_time'] : null;
            $taskModel->assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
            $taskModel->related_entity_type = !empty($_POST['related_entity_type']) ? $_POST['related_entity_type'] : null;
            $taskModel->related_entity_id = !empty($_POST['related_entity_id']) ? $_POST['related_entity_id'] : null;
            $taskModel->notes = $_POST['notes'] ?? '';

            if ($taskModel->update()) {
                $_SESSION['success'] = 'Tarea actualizada exitosamente';
                header('Location: /index.php?page=tasks');
                exit;
            } else {
                $_SESSION['error'] = 'Error al actualizar la tarea';
            }
        }

        $task = $taskModel->readOne();
        
        if (!$task) {
            $_SESSION['error'] = 'Tarea no encontrada';
            header('Location: /index.php?page=tasks');
            exit;
        }

        // Obtener categorías
        $categoryStmt = $this->db->query("SELECT * FROM task_categories ORDER BY name");
        $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener usuarios
        $userStmt = $this->db->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/tasks/edit.php';
    }

    /**
     * Completar tarea
     */
    public function complete() {
        if (!$this->authHelper->checkAuth()) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        if (!$this->authHelper->hasPermission('tasks.complete')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            exit;
        }

        $taskModel = new Task($this->db);
        $taskModel->id = $_POST['id'] ?? 0;

        if ($taskModel->complete($_SESSION['user_id'])) {
            echo json_encode(['success' => true, 'message' => 'Tarea completada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al completar tarea']);
        }
        exit;
    }

    /**
     * Eliminar tarea
     */
    public function delete() {
        if (!$this->authHelper->checkAuth()) {
            header('Location: /index.php?page=login');
            exit;
        }

        if (!$this->authHelper->hasPermission('tasks.delete')) {
            $_SESSION['error'] = 'No tienes permiso para eliminar tareas';
            header('Location: /index.php?page=tasks');
            exit;
        }

        $taskModel = new Task($this->db);
        $taskModel->id = $_GET['id'] ?? 0;

        if ($taskModel->delete()) {
            $_SESSION['success'] = 'Tarea eliminada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar la tarea';
        }

        header('Location: /index.php?page=tasks');
        exit;
    }

    /**
     * Añadir comentario a tarea
     */
    public function addComment() {
        if (!$this->authHelper->checkAuth()) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        $taskModel = new Task($this->db);
        $taskId = $_POST['task_id'] ?? 0;
        $comment = $_POST['comment'] ?? '';

        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Comentario vacío']);
            exit;
        }

        if ($taskModel->addComment($taskId, $_SESSION['user_id'], $comment)) {
            echo json_encode(['success' => true, 'message' => 'Comentario añadido']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al añadir comentario']);
        }
        exit;
    }
}
