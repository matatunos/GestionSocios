<?php

class PollController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // List all polls
    public function index() {
        require_once __DIR__ . '/../Models/Poll.php';
        
        $pollModel = new Poll($this->db);
        $status = $_GET['status'] ?? 'all';
        
        if ($status === 'active') {
            $polls = $pollModel->getActivePolls();
        } else if ($status === 'closed') {
            $polls = $pollModel->getClosedPolls();
        } else {
            $polls = $pollModel->readAll();
        }
        
        require_once __DIR__ . '/../Views/polls/index.php';
    }

    // Show create form
    public function create() {
        require_once __DIR__ . '/../Views/polls/create.php';
    }

    // Store new poll
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=polls');
            exit;
        }

        require_once __DIR__ . '/../Models/Poll.php';
        
        $pollModel = new Poll($this->db);
        $pollModel->title = $_POST['title'] ?? '';
        $pollModel->description = $_POST['description'] ?? '';
        $pollModel->start_date = $_POST['start_date'] ?? null;
        $pollModel->end_date = $_POST['end_date'] ?? null;
        $pollModel->is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        $pollModel->allow_multiple = isset($_POST['allow_multiple']) ? 1 : 0;
        $pollModel->created_by = $_SESSION['user_id'];

        try {
            $poll_id = $pollModel->create();
            
            // Add options
            if (isset($_POST['options']) && is_array($_POST['options'])) {
                foreach ($_POST['options'] as $option_text) {
                    $option_text = trim($option_text);
                    if (!empty($option_text)) {
                        $pollModel->addOption($poll_id, $option_text);
                    }
                }
            }

            $_SESSION['success'] = 'Votación creada exitosamente';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear la votación: ' . $e->getMessage();
            header('Location: index.php?page=polls&action=create');
        }
        exit;
    }

    // View poll details and vote
    public function view() {
        $poll_id = $_GET['id'] ?? 0;
        
        require_once __DIR__ . '/../Models/Poll.php';
        
        $pollModel = new Poll($this->db);
        $poll = $pollModel->readOne($poll_id);
        
        if (!$poll) {
            $_SESSION['error'] = 'Votación no encontrada';
            header('Location: index.php?page=polls');
            exit;
        }

        $options = $pollModel->getOptions($poll_id);
        $hasVoted = $pollModel->hasUserVoted($poll_id, $_SESSION['user_id']);
        $results = $pollModel->getResults($poll_id);
        
        require_once __DIR__ . '/../Views/polls/view.php';
    }

    // Submit vote
    public function vote() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=polls');
            exit;
        }

        $poll_id = $_POST['poll_id'] ?? 0;
        $option_ids = $_POST['option_ids'] ?? [];

        require_once __DIR__ . '/../Models/Poll.php';
        
        $pollModel = new Poll($this->db);
        $poll = $pollModel->readOne($poll_id);

        if (!$poll) {
            $_SESSION['error'] = 'Votación no encontrada';
            header('Location: index.php?page=polls');
            exit;
        }

        // Check if poll is active
        if (!$pollModel->isActive($poll_id)) {
            $_SESSION['error'] = 'Esta votación no está activa';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
            exit;
        }

        // Check if already voted
        if ($pollModel->hasUserVoted($poll_id, $_SESSION['user_id'])) {
            $_SESSION['error'] = 'Ya has votado en esta encuesta';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
            exit;
        }

        // Validate options
        if (empty($option_ids) || !is_array($option_ids)) {
            $_SESSION['error'] = 'Debes seleccionar al menos una opción';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
            exit;
        }

        // If not allow_multiple, ensure only one option
        if (!$poll['allow_multiple'] && count($option_ids) > 1) {
            $_SESSION['error'] = 'Solo puedes seleccionar una opción en esta votación';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
            exit;
        }

        try {
            // Cast votes
            foreach ($option_ids as $option_id) {
                $pollModel->castVote($poll_id, $option_id, $_SESSION['user_id']);
            }

            $_SESSION['success'] = 'Tu voto ha sido registrado';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al registrar el voto: ' . $e->getMessage();
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
        }
        exit;
    }

    // Close poll
    public function close() {
        $poll_id = $_GET['id'] ?? 0;
        
        require_once __DIR__ . '/../Models/Poll.php';
        
        $pollModel = new Poll($this->db);
        $poll = $pollModel->readOne($poll_id);

        if (!$poll) {
            $_SESSION['error'] = 'Votación no encontrada';
            header('Location: index.php?page=polls');
            exit;
        }

        // Check permission
        if ($poll['created_by'] != $_SESSION['user_id'] && $_SESSION['role_name'] !== 'admin') {
            $_SESSION['error'] = 'No tienes permisos para cerrar esta votación';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
            exit;
        }

        try {
            $pollModel->closePoll($poll_id);
            $_SESSION['success'] = 'Votación cerrada exitosamente';
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al cerrar la votación: ' . $e->getMessage();
            header('Location: index.php?page=polls&action=view&id=' . $poll_id);
        }
        exit;
    }

    // Delete poll
    public function delete() {
        $poll_id = $_GET['id'] ?? 0;
        
        require_once __DIR__ . '/../Models/Poll.php';
        
        $pollModel = new Poll($this->db);
        $poll = $pollModel->readOne($poll_id);

        if (!$poll) {
            $_SESSION['error'] = 'Votación no encontrada';
            header('Location: index.php?page=polls');
            exit;
        }

        // Check permission
        if ($poll['created_by'] != $_SESSION['user_id'] && $_SESSION['role_name'] !== 'admin') {
            $_SESSION['error'] = 'No tienes permisos para eliminar esta votación';
            header('Location: index.php?page=polls');
            exit;
        }

        try {
            $pollModel->delete($poll_id);
            $_SESSION['success'] = 'Votación eliminada exitosamente';
            header('Location: index.php?page=polls');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar la votación: ' . $e->getMessage();
            header('Location: index.php?page=polls');
        }
        exit;
    }
}
