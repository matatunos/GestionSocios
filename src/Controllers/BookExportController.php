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

        require __DIR__ . '/../Views/book/export.php';
    }

    public function generatePdf() {
        $this->checkAdmin();
        $year = $_GET['year'] ?? date('Y');

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

        // Add activities
        foreach ($activities as $activity) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, $activity['title'], 0, 1);
            
            if ($activity['description']) {
                $pdf->SetFont('helvetica', '', 11);
                $pdf->MultiCell(0, 5, $activity['description'], 0, 'L');
            }

            if ($activity['image_url']) {
                $imagePath = __DIR__ . '/../../public/' . $activity['image_url'];
                if (file_exists($imagePath)) {
                    $pdf->Ln(5);
                    $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 0, '', '', '', true, 300);
                }
            }
        }

        // Add ads
        foreach ($ads as $ad) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, $ad['donor_name'], 0, 1, 'C');
            
            if ($ad['image_url']) {
                $imagePath = __DIR__ . '/../../public/' . $ad['image_url'];
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, 15, $pdf->GetY(), 180, 0, '', '', '', true, 300);
                }
            }
        }

        // Log PDF generation
        AuditLog::log('export_pdf', 'book', $year, null, [
            'year' => $year,
            'activities_count' => count($activities),
            'ads_count' => count($ads),
            'filename' => 'libro_fiestas_' . $year . '.pdf'
        ]);
        
        // Output PDF
        $pdf->Output('libro_fiestas_' . $year . '.pdf', 'D');
        exit;
    }
}
