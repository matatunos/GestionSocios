<?php

require_once __DIR__ . '/../Models/PublicAnnouncement.php';
require_once __DIR__ . '/../Helpers/CsrfHelper.php';

class AnnouncementController {
    private $db;
    private $announcement;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->announcement = new PublicAnnouncement($this->db);
    }

    public function index() {
        $stmt = $this->announcement->readAll();
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/announcements/index.php';
    }

    public function create() {
        require __DIR__ . '/../Views/announcements/create.php';
    }

    public function store() {
        CsrfHelper::validateRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->announcement->title = $_POST['title'] ?? '';
            $this->announcement->content = $_POST['content'] ?? '';
            $this->announcement->type = $_POST['type'] ?? 'info';
            $this->announcement->is_active = isset($_POST['is_active']) ? 1 : 0;
            $this->announcement->priority = intval($_POST['priority'] ?? 0);
            $this->announcement->created_by = $_SESSION['user_id'] ?? null;
            $this->announcement->expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

            if ($this->announcement->create()) {
                $_SESSION['success'] = "Anuncio creado correctamente.";
                header("Location: index.php?page=announcements");
            } else {
                $_SESSION['error'] = "No se pudo crear el anuncio.";
                header("Location: index.php?page=announcements&action=create");
            }
            exit;
        }
    }

    public function edit() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : die('ERROR: ID no encontrado.');
        $this->announcement->id = $id;
        $this->announcement->readOne();
        require __DIR__ . '/../Views/announcements/edit.php';
    }

    public function update() {
        CsrfHelper::validateRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->announcement->id = intval($_POST['id']);
            $this->announcement->title = $_POST['title'];
            $this->announcement->content = $_POST['content'];
            $this->announcement->type = $_POST['type'];
            $this->announcement->is_active = isset($_POST['is_active']) ? 1 : 0;
            $this->announcement->priority = intval($_POST['priority']);
            $this->announcement->expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

            if ($this->announcement->update()) {
                $_SESSION['success'] = "Anuncio actualizado.";
                header("Location: index.php?page=announcements");
            } else {
                $_SESSION['error'] = "Error al actualizar.";
                header("Location: index.php?page=announcements&action=edit&id=" . $this->announcement->id);
            }
            exit;
        }
    }

    public function delete() {
        CsrfHelper::validateRequest();
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            $_SESSION['error'] = "ID inválido.";
            header("Location: index.php?page=announcements");
            exit;
        }
        
        $this->announcement->id = $id;
        if ($this->announcement->delete()) {
            $_SESSION['success'] = "Anuncio eliminado.";
        } else {
            $_SESSION['error'] = "No se pudo eliminar.";
        }
        header("Location: index.php?page=announcements");
        exit;
    }

    public function toggleActive() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            $_SESSION['error'] = "ID inválido.";
            header("Location: index.php?page=announcements");
            exit;
        }
        
        $this->announcement->id = $id;
        if ($this->announcement->toggleActive()) {
            $_SESSION['success'] = "Estado actualizado.";
        } else {
            $_SESSION['error'] = "Error al actualizar estado.";
        }
        header("Location: index.php?page=announcements");
        exit;
    }
}
?>
