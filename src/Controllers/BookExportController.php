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
        require_once __DIR__ . '/../Models/BookAd.php';
        $bookAdModel = new BookAd($this->db);
        $adsStmt = $bookAdModel->readAllByYear($year);
        $ads = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Models/BookActivity.php';
        $activityModel = new BookActivity($this->db);
        $activitiesStmt = $activityModel->readAllByYear($year);
        $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Models/BookPage.php';
        $bookPageModel = new BookPage($this->db);
        $version_id = $_GET['version_id'] ?? null;
        if ($version_id) {
            $bookPages = $bookPageModel->getAllByVersion($version_id);
        } else {
            $book_id = $year;
            $bookPages = $bookPageModel->getAllByBook($book_id);
        }

        $editorBlocks = [];
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
        if (!empty($bookPages)) {
            $editorBlocks = $bookPages;
        }
        require __DIR__ . '/../Views/book/export.php';
    }

    public function generatePdf() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../Models/BookAd.php';
        $bookAdModel = new BookAd($this->db);
        $adsStmt = $bookAdModel->readAllByYear($year);
        $ads = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Models/BookActivity.php';
        $activityModel = new BookActivity($this->db);
        $activitiesStmt = $activityModel->readAllByYear($year);
        $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('GestionSocios');
        $pdf->SetAuthor('Asociación');
        $pdf->SetTitle('Libro de Fiestas ' . $year);
        $pdf->SetSubject('Libro de Fiestas');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 32);
        $pdf->Cell(0, 100, '', 0, 1);
        $pdf->Cell(0, 20, 'Libro de Fiestas', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 24);
        $pdf->Cell(0, 15, $year, 0, 1, 'C');

        require_once __DIR__ . '/../Models/BookPage.php';
        $bookPageModel = new BookPage($this->db);
        $version_id = $_GET['version_id'] ?? null;
        if ($version_id) {
            $pages = $bookPageModel->getAllByVersion($version_id);
        } else {
            $book_id = $year;
            $pages = $bookPageModel->getAllByBook($book_id);
        }

        foreach ($pages as $page) {
            if ($page['position'] === 'full' || $page['position'] === 'top') {
                $pdf->AddPage();
            }
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, $page['content'], 0, 1);
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
                } else {
                    $this->drawDefaultImage($pdf, $page);
                }
            } else {
                $this->drawDefaultImage($pdf, $page);
            }
        }

        AuditLog::log('export_pdf', 'book', $year, null, [
            'year' => $year,
        ]);
        $pdf->Output('libro_fiestas_' . $year . '.pdf', 'D');
        exit;
    }

    private function drawDefaultImage($pdf, $page) {
        $text = $page['content'] ?? 'Sin imagen';
        if ($page['type'] === 'ad') {
            if (isset($page['position']) && ($page['position'] === 'top' || $page['position'] === 'bottom')) {
                $height = 80;
            } else {
                $height = 180;
            }
        } else {
            $height = 180;
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
}
        // Determinar texto identificativo
