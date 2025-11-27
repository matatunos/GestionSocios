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

            require_once __DIR__ . '/../Helpers/AuditLog.php';
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
        $type = $page['type'] ?? 'default';
        $height = 180;
        
        if ($type === 'ad') {
            if (isset($page['position']) && ($page['position'] === 'top' || $page['position'] === 'bottom')) {
                $height = 80;
            }
        }
        
        $x = 15;
        $y = $pdf->GetY();
        
        // Define colors based on type
        if ($type === 'activity') {
            // Purple gradient for activities
            $colorR = 163;
            $colorG = 85;
            $colorB = 247;
            $bgR = 243;
            $bgG = 232;
            $bgB = 255;
            $icon = '📅';
        } elseif ($type === 'ad') {
            // Blue gradient for ads
            $colorR = 59;
            $colorG = 130;
            $colorB = 246;
            $bgR = 219;
            $bgG = 234;
            $bgB = 254;
            $icon = '📢';
        } else {
            // Gray for others
            $colorR = 100;
            $colorG = 100;
            $colorB = 100;
            $bgR = 243;
            $bgG = 244;
            $bgB = 246;
            $icon = '📄';
        }
        
        // Draw gradient background
        $pdf->SetFillColor($bgR, $bgG, $bgB);
        $pdf->Rect($x, $y, 180, $height, 'F');
        
        // Draw border
        $pdf->SetDrawColor($colorR, $colorG, $colorB);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect($x, $y, 180, $height, 'D');
        
        // Draw decorative top bar
        $pdf->SetFillColor($colorR, $colorG, $colorB);
        $pdf->Rect($x, $y, 180, 3, 'F');
        
        // Add icon/emoji at top (if supported)
        $pdf->SetFont('helvetica', 'B', 32);
        $pdf->SetTextColor($colorR, $colorG, $colorB);
        $pdf->SetXY($x, $y + 15);
        $pdf->Cell(180, 15, $icon, 0, 0, 'C');
        
        // Add main text
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(30, 41, 59); // Dark slate
        $pdf->SetXY($x, $y + ($height/2) - 5);
        
        // Wrap text if too long
        $maxWidth = 160;
        if ($pdf->GetStringWidth($text) > $maxWidth) {
            // Split into multiple lines
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';
            
            foreach ($words as $word) {
                $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
                if ($pdf->GetStringWidth($testLine) > $maxWidth) {
                    if ($currentLine) {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine) {
                $lines[] = $currentLine;
            }
            
            // Limit to 3 lines
            $lines = array_slice($lines, 0, 3);
            $lineHeight = 8;
            $startY = $y + ($height/2) - (count($lines) * $lineHeight / 2);
            
            foreach ($lines as $i => $line) {
                $pdf->SetXY($x, $startY + ($i * $lineHeight));
                if ($i === 2 && count($lines) === 3 && strlen($line) > 30) {
                    $line = substr($line, 0, 27) . '...';
                }
                $pdf->Cell(180, $lineHeight, $line, 0, 0, 'C');
            }
        } else {
            $pdf->Cell(180, 10, $text, 0, 0, 'C');
        }
        
        // Add type label at bottom
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor($colorR, $colorG, $colorB);
        $pdf->SetXY($x, $y + $height - 15);
        $typeLabel = $type === 'activity' ? 'ACTIVIDAD' : ($type === 'ad' ? 'ANUNCIO' : 'CONTENIDO');
        $pdf->Cell(180, 5, $typeLabel, 0, 0, 'C');
        
        // Reset colors
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
    }

    public function generateDocx() {
        $year = $_GET['year'] ?? date('Y');
        
        try {
            $this->checkAdmin();
            
            // Fetch Data (Same logic as PDF)
            require_once __DIR__ . '/../Models/BookPage.php';
            $bookPageModel = new BookPage($this->db);
            
            $version_id = $_GET['version_id'] ?? null;
            if ($version_id === '') $version_id = null;
            
            // Get book_id
            $book_id = null;
            try {
                $stmt = $this->db->prepare("SELECT id FROM books WHERE year = ?");
                $stmt->execute([$year]);
                $book = $stmt->fetch(PDO::FETCH_ASSOC);
                $book_id = $book['id'] ?? null;
            } catch (Exception $e) {
                error_log("Failed to get book_id in generateDocx: " . $e->getMessage());
            }

            if ($version_id) {
                $pages = $bookPageModel->getAllByVersion($version_id);
            } elseif ($book_id) {
                $pages = $bookPageModel->getAllByBook($book_id);
            } else {
                $pages = [];
            }

            // Fallback if no pages (same as PDF)
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
                $pages[] = ['content' => 'Portada', 'position' => 'full', 'type' => 'cover'];
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

            // Generate HTML for Word
            $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
            $html .= '<head><meta charset="utf-8"><title>Libro de Fiestas ' . $year . '</title>';
            $html .= '<style>
                body { font-family: Arial, sans-serif; }
                .page-break { page-break-after: always; }
                .page-container { width: 100%; height: 100%; position: relative; border: 1px solid #eee; padding: 20px; box-sizing: border-box; }
                .page-title { font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 20px; }
                .page-image { text-align: center; margin: 20px 0; }
                .page-image img { max-width: 100%; max-height: 500px; }
                .page-type { font-size: 12px; color: #666; text-align: center; margin-top: 20px; text-transform: uppercase; }
                .cover-title { font-size: 48px; font-weight: bold; text-align: center; margin-top: 200px; }
                .cover-year { font-size: 36px; text-align: center; margin-top: 20px; }
            </style>';
            $html .= '</head><body>';

            // Cover
            $html .= '<div class="page-container">';
            $html .= '<div class="cover-title">Libro de Fiestas</div>';
            $html .= '<div class="cover-year">' . $year . '</div>';
            $html .= '</div>';

            // Pages
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            
            foreach ($pages as $page) {
                if (isset($page['type']) && $page['type'] === 'cover') continue;

                // Explicit page break for Word
                $html .= '<br style="page-break-before: always; clear: both;" />';
                
                $html .= '<div class="page-container">';
                $html .= '<div class="page-title">' . htmlspecialchars($page['content'] ?? '') . '</div>';
                
                if (!empty($page['image_url'])) {
                    // For Word, absolute URLs are better, or base64. 
                    // Let's try absolute URL if accessible, otherwise relative might fail if not localhost.
                    // Ideally we embed as base64 for portability.
                    $imagePath = __DIR__ . '/../../public/' . $page['image_url'];
                    if (file_exists($imagePath)) {
                        $imageData = base64_encode(file_get_contents($imagePath));
                        $src = 'data:image/' . pathinfo($imagePath, PATHINFO_EXTENSION) . ';base64,' . $imageData;
                        $html .= '<div class="page-image"><img src="' . $src . '"></div>';
                    } else {
                        $html .= '<div style="text-align:center; padding: 50px; background: #f0f0f0;">[Imagen no encontrada]</div>';
                    }
                } else {
                    $html .= '<div style="text-align:center; padding: 50px; background: #f0f0f0;">[Sin imagen]</div>';
                }

                $typeLabel = isset($page['type']) ? ($page['type'] === 'activity' ? 'Actividad' : ($page['type'] === 'ad' ? 'Anuncio' : 'Contenido')) : 'Página';
                $html .= '<div class="page-type">' . $typeLabel . '</div>';
                $html .= '</div>';
            }

            $html .= '</body></html>';

            // Headers for download
            header("Content-type: application/vnd.ms-word");
            header("Content-Disposition: attachment;Filename=libro_fiestas_" . $year . ".doc");
            
            echo $html;
            exit;

        } catch (Exception $e) {
            error_log("BookExportController::generateDocx() error: " . $e->getMessage());
            http_response_code(500);
            die("Error generando DOCX: " . $e->getMessage());
        }
    }

    private function checkAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }
}
