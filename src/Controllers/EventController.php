<?php

class EventController {
    private $db;
    private $event;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->event = new Event($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    public function index() {
        $this->checkAdmin();
        $stmt = $this->event->readAll();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/events/index.php';
    }

    public function create() {
        $this->checkAdmin();
        require __DIR__ . '/../Views/events/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->event->name = $_POST['name'];
            $this->event->description = $_POST['description'];
            $this->event->date = $_POST['date'];
            $this->event->price = $_POST['price'];
            $this->event->is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($this->event->create()) {
                header('Location: index.php?page=events');
            } else {
                $error = "Error creating event.";
                require __DIR__ . '/../Views/events/create.php';
            }
        }
    }

    public function edit($id) {
        $this->checkAdmin();
        $this->event->id = $id;
        $this->event->readOne();
        $event = $this->event;
        require __DIR__ . '/../Views/events/edit.php';
    }

    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->event->id = $id;
            $this->event->name = $_POST['name'];
            $this->event->description = $_POST['description'];
            $this->event->date = $_POST['date'];
            $this->event->price = $_POST['price'];
            $this->event->is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($this->event->update()) {
                header('Location: index.php?page=events');
            } else {
                $error = "Error updating event.";
                $event = $this->event;
                require __DIR__ . '/../Views/events/edit.php';
            }
        }
    }
}
