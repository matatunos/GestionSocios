<?php

class BookActivityController {
    private $db;
    private $bookActivity;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        require_once __DIR__ . '/../Models/BookActivity.php';
        require_once __DIR__ . '/../Helpers/AuditLog.php';
        $this->bookActivity = new BookActivity($this->db);
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=book_activities');
            exit;
        }
    }

    public function index() {
        $year = $_GET['year'] ?? date('Y');
        $stmt = $this->bookActivity->readAllByYear($year);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require __DIR__ . '/../Views/book/activities/index.php';
    }

    public function create() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');
        require __DIR__ . '/../Views/book/activities/create.php';
    }

    public function store() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->bookActivity->year = $_POST['year'];
            $this->bookActivity->title = $_POST['title'];
            $this->bookActivity->description = $_POST['description'];
            $this->bookActivity->page_number = !empty($_POST['page_number']) ? $_POST['page_number'] : null;
            $this->bookActivity->display_order = !empty($_POST['display_order']) ? $_POST['display_order'] : 0;
            
            // Handle Image Upload
            $this->bookActivity->image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/book_activities/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $fileName = uniqid('activity_') . '.' . $fileExtension;
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $this->bookActivity->image_url = 'uploads/book_activities/' . $fileName;
                }
            }

            if ($this->bookActivity->create()) {
                $activityId = $this->db->lastInsertId();
                AuditLog::logCreate('book_activity', $activityId, [
                    'year' => $this->bookActivity->year,
                    'title' => $this->bookActivity->title,
                    'description' => $this->bookActivity->description,
                    'page_number' => $this->bookActivity->page_number,
                    'display_order' => $this->bookActivity->display_order,
                    'image_url' => $this->bookActivity->image_url
                ]);
                header('Location: index.php?page=book_activities&year=' . $_POST['year'] . '&msg=created');
            } else {
                $error = "Error creando actividad.";
                $year = $_POST['year'];
                require __DIR__ . '/../Views/book/activities/create.php';
            }
        }
    }

    public function edit($id) {
        $this->checkAdmin();
        $this->bookActivity->id = $id;
        if ($this->bookActivity->readOne()) {
            $activity = $this->bookActivity;
            require __DIR__ . '/../Views/book/activities/edit.php';
        } else {
            header('Location: index.php?page=book_activities&error=1');
        }
    }

    public function update($id) {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->bookActivity->id = $id;
            $this->bookActivity->readOne(); // Load existing data (especially image)
            
            // Store old values for audit
            $oldValues = [
                'year' => $this->bookActivity->year,
                'title' => $this->bookActivity->title,
                'description' => $this->bookActivity->description,
                'page_number' => $this->bookActivity->page_number,
                'display_order' => $this->bookActivity->display_order,
                'image_url' => $this->bookActivity->image_url
            ];
            
            $this->bookActivity->year = $_POST['year'];
            $this->bookActivity->title = $_POST['title'];
            $this->bookActivity->description = $_POST['description'];
            $this->bookActivity->page_number = !empty($_POST['page_number']) ? $_POST['page_number'] : null;
            $this->bookActivity->display_order = !empty($_POST['display_order']) ? $_POST['display_order'] : 0;

            // Handle Image Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/book_activities/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $fileName = uniqid('activity_') . '.' . $fileExtension;
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // Delete old image if exists
                    if ($this->bookActivity->image_url && file_exists(__DIR__ . '/../../public/' . $this->bookActivity->image_url)) {
                        unlink(__DIR__ . '/../../public/' . $this->bookActivity->image_url);
                    }
                    $this->bookActivity->image_url = 'uploads/book_activities/' . $fileName;
                }
            }

            if ($this->bookActivity->update()) {
                AuditLog::logUpdate('book_activity', $id, $oldValues, [
                    'year' => $this->bookActivity->year,
                    'title' => $this->bookActivity->title,
                    'description' => $this->bookActivity->description,
                    'page_number' => $this->bookActivity->page_number,
                    'display_order' => $this->bookActivity->display_order,
                    'image_url' => $this->bookActivity->image_url
                ]);
                header('Location: index.php?page=book_activities&year=' . $_POST['year'] . '&msg=updated');
            } else {
                $error = "Error actualizando actividad.";
                $activity = $this->bookActivity;
                require __DIR__ . '/../Views/book/activities/edit.php';
            }
        }
    }

    public function delete($id) {
        $this->checkAdmin();
        $this->bookActivity->id = $id;
        if ($this->bookActivity->readOne()) {
            $year = $this->bookActivity->year;
            $deletedData = [
                'year' => $this->bookActivity->year,
                'title' => $this->bookActivity->title,
                'description' => $this->bookActivity->description,
                'page_number' => $this->bookActivity->page_number,
                'display_order' => $this->bookActivity->display_order,
                'image_url' => $this->bookActivity->image_url
            ];
            if ($this->bookActivity->delete()) {
                AuditLog::logDelete('book_activity', $id, $deletedData);
                header('Location: index.php?page=book_activities&year=' . $year . '&msg=deleted');
                exit;
            }
        }
        header('Location: index.php?page=book_activities&error=1');
    }
}
