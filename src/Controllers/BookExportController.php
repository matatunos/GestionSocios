<?php

class BookExportController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        require_once __DIR__ . '/../Helpers/AuditLog.php';
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=book&action=dashboard');
            exit;
        }
    }

    public function index() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');
        $version_id = $_GET['version_id'] ?? null;

        // Get all ads for the year
        require_once __DIR__ . '/../Models/BookAd.php';
        $bookAdModel = new BookAd($this->db);
        $adsStmt = $bookAdModel->readAllByYear($year);
        $ads = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all activities for the year
        require_once __DIR__ . '/../Models/BookActivity.php';
        $activityModel = new BookActivity($this->db);
        $activitiesStmt = $activityModel->readAllByYear($year);
        $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Cargar versiones del libro
        require_once __DIR__ . '/../Models/BookVersion.php';
        $bookVersionModel = new BookVersion($this->db);
        $book_id = $year; // Ajusta si el id del libro es diferente
        $bookVersions = $bookVersionModel->getAllByBook($book_id);

        // Cargar páginas de la versión seleccionada
        require_once __DIR__ . '/../Models/BookPage.php';
        $bookPageModel = new BookPage($this->db);
        $bookPages = [];
        if ($version_id) {
            $bookPages = $bookPageModel->getAllByVersion($version_id);
        }

        // Preparar bloques iniciales para el editor
        $editorBlocks = [];
        // Portada
        $editorBlocks[] = [
            'id' => 'cover',
            'content' => 'Portada',
            'position' => 'full',
            'type' => 'cover'
        ];
        // Actividades
        foreach ($activities as $activity) {
            $editorBlocks[] = [
                'id' => 'activity_' . $activity['id'],
                'content' => $activity['title'],
                'position' => 'full',
                'type' => 'activity',
                'image_url' => $activity['image_url'] ?? null
            ];
        }
        // Donaciones pagadas / anuncios
        foreach ($ads as $ad) {
            if ($ad['paid'] ?? true) {
                $editorBlocks[] = [
                    'id' => 'ad_' . $ad['id'],
                    'content' => $ad['donor_name'],
                    'position' => 'full',
                    'type' => 'ad',
                    'image_url' => $ad['image_url'] ?? null
                ];
            }
        }

        // Si hay páginas guardadas en la versión, úsalas como orden inicial
        if (!empty($bookPages)) {
            $editorBlocks = $bookPages;
        }

        require __DIR__ . '/../Views/book/export.php';
    }

    public function generatePdf() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');
        $version_id = $_GET['version_id'] ?? null;

        // Load TCPDF
        require_once __DIR__ . '/../../vendor/autoload.php';

        // Get all content
        require_once __DIR__ . '/../Models/BookAd.php';
        $bookAdModel = new BookAd($this->db);
        $adsStmt = $bookAdModel->readAllByYear($year);
        $ads = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Models/BookActivity.php';
        $activityModel = new BookActivity($this->db);
        $activitiesStmt = $activityModel->readAllByYear($year);
        $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('GestionSocios');
        $pdf->SetAuthor('Asociación');
        $pdf->SetTitle('Libro de Fiestas ' . $year);
        $pdf->SetSubject('Libro de Fiestas');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add cover page
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 32);
        $pdf->Cell(0, 100, '', 0, 1); // Spacer
        $pdf->Cell(0, 20, 'Libro de Fiestas', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 24);
        $pdf->Cell(0, 15, $year, 0, 1, 'C');

        // Obtener páginas personalizadas de la versión
        require_once __DIR__ . '/../Models/BookPage.php';
        $bookPageModel = new BookPage($this->db);
        $pages = [];
        if ($version_id) {
            $pages = $bookPageModel->getAllByVersion($version_id);
        }

        foreach ($pages as $page) {
            // Nueva página si es completa o superior
            if ($page['position'] === 'full' || $page['position'] === 'top') {
                $pdf->AddPage();
            }
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, $page['content'], 0, 1);
            // Si hay imagen, colocar según posición
            if (!empty($page['image_url'])) {
                $imagePath = __DIR__ . '/../../public/' . $page['image_url'];
                if (file_exists($imagePath)) {
                    if ($page['position'] === 'top') {
                        $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 80, '', '', '', true, 300);
                    } else if ($page['position'] === 'bottom') {
                        $pdf->SetY(-100);
                        $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 80, '', '', '', true, 300);
                    } else {
                        $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 0, '', '', '', true, 300);
                    }
                }
            }
        }

        // Log PDF generation
        AuditLog::log('export_pdf', 'book', $year, null, [
            'year' => $year,
            'version_id' => $version_id,
            'activities_count' => count($activities),
            'ads_count' => count($ads),
            'filename' => 'libro_fiestas_' . $year . '_v' . $version_id . '.pdf'
        ]);
        
        // Output PDF
        $pdf->Output('libro_fiestas_' . $year . '_v' . $version_id . '.pdf', 'D');
        exit;
    }
}
