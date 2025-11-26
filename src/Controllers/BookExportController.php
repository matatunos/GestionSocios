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
                                                    // Página de depuración antes de la portada
                                                    $pdf->AddPage();
                                                    $pdf->SetFont('courier', '', 8);
                                                    $debugText = print_r($pages, true);
                                                    $pdf->MultiCell(0, 100, "DEBUG pages:\n" . $debugText, 0, 'L');
                                                    $pdf->SetFont('helvetica', 'B', 32);
                                                // Depuración visual: mostrar contenido de $pages en la primera página del PDF
                                                $pdf->SetFont('courier', '', 8);
                                                $debugText = print_r($pages, true);
                                                $pdf->MultiCell(0, 100, "DEBUG pages:\n" . $debugText, 0, 'L');
                                                $pdf->SetFont('helvetica', 'B', 16);
                                            // Depuración: volcado antes de checkAdmin
                                            $debugPath = '/opt/GestionSocios/public/debug_pages.log';
                                            @file_put_contents($debugPath, "ANTES DE CHECKADMIN\n");
                                            $this->checkAdmin();
                                        // Depuración: texto fijo para confirmar ejecución
                                        $debugPath = '/opt/GestionSocios/public/debug_pages.log';
                                        @file_put_contents($debugPath, "DEPURACION PDF INICIO\n");
                                        try {
                                            // ...existing code...
                                        } catch (Exception $e) {
                                            @file_put_contents($debugPath, "EXCEPCION: " . $e->getMessage() . "\n", FILE_APPEND);
                                            throw $e;
                                        }
                                // Depuración: volcar $pages al inicio del método
                                $debugPath = '/opt/GestionSocios/public/debug_pages.log';
                                $year = $_GET['year'] ?? date('Y');
                                require_once __DIR__ . '/../Models/BookPage.php';
                                $bookPageModel = new BookPage($this->db);
                                $version_id = $_GET['version_id'] ?? null;
                                if ($version_id) {
                                    $pages = $bookPageModel->getAllByVersion($version_id);
                                } else {
                                    $book_id = $year;
                                    $pages = $bookPageModel->getAllByBook($book_id);
                                }

                                    // Página de depuración antes de la portada
                                    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                                    $pdf->AddPage();
                                    $pdf->SetFont('courier', '', 8);
                                    $debugText = print_r($pages, true);
                                    $pdf->MultiCell(0, 100, "DEBUG pages:\n" . $debugText, 0, 'L');
                                    $pdf->SetFont('helvetica', 'B', 32);
                                $debugWrite = @file_put_contents($debugPath, print_r($pages, true));
                                if ($debugWrite === false) {
                                    error_log('No se pudo escribir el log en ' . $debugPath);
                                }
                        // Depuración: inicio del método
                        $debugPath = __DIR__ . '/../../public/debug_pages.log';
                        @file_put_contents($debugPath, "INICIO generatePdf\n");
                    // Depuración: comprobar ejecución del método y permisos de escritura
                    $debugPath = __DIR__ . '/../../public/debug_pages.log';
                    @file_put_contents($debugPath, "INICIO generatePdf\n");
        // Depuración: comprobar ejecución del método y permisos de escritura
        $debugPath = '/opt/GestionSocios/public/debug_pages.log';
        $debugWrite = @file_put_contents($debugPath, "INICIO generatePdf\n");
        if ($debugWrite === false) {
            echo '<pre style="color:red">No se pudo escribir el log en ' . $debugPath . '</pre>';
        }
        @file_put_contents($debugPath, "INICIO generatePdf\n");
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
        // Página de depuración
        $pdf->AddPage();
        $pdf->SetFont('courier', '', 8);
        $debugText = print_r($pages, true);
        $pdf->MultiCell(0, 100, "DEBUG pages:\n" . $debugText, 0, 'L');
        // Portada
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
        // Si no hay páginas en la tabla, construir $pages a partir de actividades y anuncios
        if (empty($pages)) {
            $pages = [];
            $pages[] = [
                'id' => 'cover',
                'content' => 'Portada',
                'position' => 'full',
                'type' => 'cover'
            ];
            foreach ($activities as $activity) {
                $pages[] = [
                    'id' => 'activity_' . $activity['id'],
                    'content' => $activity['title'],
                    'position' => 'full',
                    'type' => 'activity',
                    'image_url' => $activity['image_url'] ?? null
                ];
            }
            foreach ($ads as $ad) {
                if ($ad['paid'] ?? true) {
                    $pages[] = [
                        'id' => 'ad_' . $ad['id'],
                        'content' => $ad['donor_name'],
                        'position' => 'full',
                        'type' => 'ad',
                        'image_url' => $ad['image_url'] ?? null
                    ];
                }
            }
        }

        // ...existing code...
        // Bucle de generación de páginas PDF
        $first = true;
        foreach ($pages as $page) {
            if ($first) {
                $first = false;
            } else {
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

        // Depuración: volcar $pages justo antes de la salida
        @file_put_contents($debugPath, "ANTES DE OUTPUT\n", FILE_APPEND);
        $debugResult = @file_put_contents($debugPath, print_r($pages, true), FILE_APPEND);
        if ($debugResult === false) {
            error_log('No se pudo crear el archivo de depuración: ' . $debugPath);
        }

        foreach ($pages as $idx => $page) {
            if ($idx > 0) {
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
        // Depuración: justo antes de exit
        $debugWrite2 = @file_put_contents($debugPath, "ANTES DE EXIT\n", FILE_APPEND);
        if ($debugWrite2 === false) {
            echo '<pre style="color:red">No se pudo escribir el log en ' . $debugPath . ' (antes de exit)</pre>';
        }
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
