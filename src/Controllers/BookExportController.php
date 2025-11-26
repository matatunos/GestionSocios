<?php

class BookExportController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function index() {
        global $page; // Make $page available for layout.php
        $year = $_GET['year'] ?? date('Y');
        
        try {
            require_once __DIR__ . '/../Models/BookPage.php';
            $bookPageModel = new BookPage($this->db);
            
            require_once __DIR__ . '/../Models/BookActivity.php';
            $activityModel = new BookActivity($this->db);
            $activitiesStmt = $activityModel->readAllByYear($year);
            $activities = $activitiesStmt ? $activitiesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            require_once __DIR__ . '/../Models/BookAd.php';
            $bookAdModel = new BookAd($this->db);
            $adsStmt = $bookAdModel->readAllByYear($year);
            $ads = $adsStmt ? $adsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            require_once __DIR__ . '/../Models/BookVersion.php';
            $bookVersions = [];
            try {
                $stmt = $this->db->prepare("SELECT * FROM book_versions WHERE book_id = (SELECT id FROM books WHERE year = ? LIMIT 1)");
                $stmt->execute([$year]);
                $bookVersions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // Ignore if table doesn't exist yet
                error_log("BookVersion query failed: " . $e->getMessage());
            }

            $version_id = $_GET['version_id'] ?? null;
            
            // Get book_id for the current year
            $book_id = null;
            try {
                $stmt = $this->db->prepare("SELECT id FROM books WHERE year = ?");
                $stmt->execute([$year]);
                $book = $stmt->fetch(PDO::FETCH_ASSOC);
                $book_id = $book['id'] ?? null;
            } catch (Exception $e) {
                error_log("Failed to get book_id: " . $e->getMessage());
            }

            if ($version_id) {
                $pages = $bookPageModel->getAllByVersion($version_id);
            } elseif ($book_id) {
                $pages = $bookPageModel->getAllByBook($book_id);
            } else {
                $pages = [];
            }

            $editorBlocks = [];
            // If no pages exist, we might want to show default blocks based on activities and ads
            if (empty($pages)) {
                $editorBlocks[] = [
                    'id' => 'cover',
                    'content' => 'Portada',
                    'position' => 'full',
                    'type' => 'cover'
                ];
                foreach ($activities as $activity) {
                    $editorBlocks[] = [
                        'id' => 'activity_' . $activity['id'],
                        'content' => $activity['title'],
                        'position' => 'full',
                        'type' => 'activity',
                        'image_url' => $activity['image_url'] ?? null
                    ];
                }
                foreach ($ads as $ad) {
                    if (($ad['status'] ?? '') === 'paid') {
                        $editorBlocks[] = [
                            'id' => 'ad_' . $ad['id'],
                            'content' => $ad['donor_name'] ?? '',
                            'position' => 'full',
                            'type' => 'ad',
                            'image_url' => $ad['image_url'] ?? null
                        ];
                    }
                }
            } else {
                $editorBlocks = $pages;
            }

            // Ensure all variables are available in view scope
            // (PHP's require includes the file in the current scope, so local variables are accessible)
            require __DIR__ . '/../Views/book/export.php';
        } catch (Exception $e) {
            error_log("BookExportController::index() error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            die("Error loading export view: " . $e->getMessage());
        }
    }

    public function generatePdf() {
        $year = $_GET['year'] ?? date('Y');
        
        try {
            $this->checkAdmin();
            
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            // Fetch Data
            require_once __DIR__ . '/../Models/BookPage.php';
            $bookPageModel = new BookPage($this->db);
            
            $version_id = $_GET['version_id'] ?? null;
            // Handle empty string as null
            if ($version_id === '') {
                $version_id = null;
            }
            
            // Get book_id
            $book_id = null;
            try {
                $stmt = $this->db->prepare("SELECT id FROM books WHERE year = ?");
                $stmt->execute([$year]);
                $book = $stmt->fetch(PDO::FETCH_ASSOC);
                $book_id = $book['id'] ?? null;
            } catch (Exception $e) {
                error_log("Failed to get book_id in generatePdf: " . $e->getMessage());
            }

            if ($version_id) {
                $pages = $bookPageModel->getAllByVersion($version_id);
            } elseif ($book_id) {
                $pages = $bookPageModel->getAllByBook($book_id);
            } else {
                $pages = [];
            }

            // If no pages, generate from source
            if (empty($pages)) {
                require_once __DIR__ . '/../Models/BookActivity.php';
                $activityModel = new BookActivity($this->db);
                $activitiesStmt = $activityModel->readAllByYear($year);
                $activities = $activitiesStmt ? $activitiesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

                require_once __DIR__ . '/../Models/BookAd.php';
                $bookAdModel = new BookAd($this->db);
                $adsStmt = $bookAdModel->readAllByYear($year);
                $ads = $adsStmt ? $adsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

                $pages = [];
                $pages[] = [
                    'content' => 'Portada',
                    'position' => 'full',
                    'type' => 'cover'
                ];
                foreach ($activities as $activity) {
                    $pages[] = [
                        'content' => $activity['title'] ?? 'Sin título',
                        'position' => 'full',
                        'type' => 'activity',
                        'image_url' => $activity['image_url'] ?? null
                    ];
                }
                foreach ($ads as $ad) {
                    if (($ad['status'] ?? '') === 'paid') {
                        $pages[] = [
                            'content' => $ad['donor_name'] ?? 'Sin nombre',
                            'position' => 'full',
                            'type' => 'ad',
                            'image_url' => $ad['image_url'] ?? null
                        ];
                    }
                }
            }

            // Create PDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('GestionSocios');
            $pdf->SetAuthor('Asociación');
            $pdf->SetTitle('Libro de Fiestas ' . $year);
            $pdf->SetSubject('Libro de Fiestas');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);

            // Portada
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 32);
            $pdf->Cell(0, 100, '', 0, 1);
            $pdf->Cell(0, 20, 'Libro de Fiestas', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 24);
            $pdf->Cell(0, 15, $year, 0, 1, 'C');

            // Pages
            foreach ($pages as $page) {
                if (isset($page['type']) && $page['type'] === 'cover') continue; // Skip cover if it's in the list

                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 10, $page['content'] ?? '', 0, 1);
                
                if (!empty($page['image_url'])) {
                    $imagePath = __DIR__ . '/../../public/' . $page['image_url'];
                    if (file_exists($imagePath)) {
                        if (isset($page['position']) && $page['position'] === 'top') {
                            $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 80, '', '', '', true, 300);
                        } else if (isset($page['position']) && $page['position'] === 'bottom') {
                            $pdf->SetY(-100);
                            $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 80, '', '', '', true, 300);
                        } else {
                            $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 0, '', '', '', true, 300);
                        }
                    } else {
                        $this->drawDefaultImage($pdf, $page);
                    }
                } else {
                    $this->drawDefaultImage($pdf, $page);
                }
            }

            require_once __DIR__ . '/../Models/AuditLog.php';
            AuditLog::log('export_pdf', 'book', $year, null, ['year' => $year]);

            $pdf->Output('libro_fiestas_' . $year . '.pdf', 'D');
            exit;
        } catch (Exception $e) {
            error_log("BookExportController::generatePdf() error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            die("Error generando PDF: " . $e->getMessage());
        }
    }

    private function drawDefaultImage($pdf, $page) {
        $text = $page['content'] ?? 'Sin imagen';
        $height = 180;
        
        if (isset($page['type']) && $page['type'] === 'ad') {
            if (isset($page['position']) && ($page['position'] === 'top' || $page['position'] === 'bottom')) {
                $height = 80;
            }
        }
        
        $x = 15;
        $y = $pdf->GetY();
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Rect($x, $y, 180, $height, 'F');
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($x, $y + ($height/2) - 10);
        $pdf->Cell(180, 20, $text, 0, 0, 'C');
    }

    private function checkAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }
}
