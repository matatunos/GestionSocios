<?php

class ReportController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    private function checkAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    public function eventsAttendance() {
        $this->checkAdmin();
        require_once __DIR__ . '/../Models/Event.php';
        require_once __DIR__ . '/../Models/EventAttendance.php';
        $eventModel = new Event($this->db);
        $stmt = $eventModel->readAll();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $attendanceModel = new EventAttendance($this->db);
        foreach ($events as &$event) {
            $event['stats'] = $attendanceModel->getStatsByEvent($event['id']);
        }
        include __DIR__ . '/../Views/reports/events_attendance.php';
    }

    public function exportMembers() {
        $this->checkAdmin();
        require_once __DIR__ . '/../Models/Member.php';
        $member = new Member($this->db);
        $stmt = $member->readAll();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="socios_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Nombre', 'Apellidos', 'Email', 'Teléfono', 'Dirección', 'Estado', 'Fecha Alta', 'Fecha Baja']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                $row['phone'],
                $row['address'],
                $row['status'],
                $row['created_at'],
                $row['deactivated_at'] ?? ''
            ]);
        }
        fclose($output);
        exit;
    }

    public function exportDonors() {
        $this->checkAdmin();
        require_once __DIR__ . '/../Models/Donor.php';
        $donor = new Donor($this->db);
        $stmt = $donor->readAll();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="donantes_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Nombre', 'Contacto', 'Email', 'Teléfono', 'Dirección']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['contact_person'],
                $row['email'],
                $row['phone'],
                $row['address']
            ]);
        }
        fclose($output);
        exit;
    }

    public function exportMovements() {
        $this->checkAdmin();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="movimientos_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Fecha', 'Tipo', 'Subtipo', 'Concepto', 'Importe', 'Usuario/Entidad']);
        // Fetch Payments
        $paymentsStmt = $this->db->prepare(
            "SELECT p.created_at, 'Ingreso' as type, p.payment_type as subtype, p.concept, p.amount, CONCAT(m.first_name, ' ', m.last_name) as entity FROM payments p JOIN members m ON p.member_id = m.id ORDER BY p.created_at DESC"
        );
        $paymentsStmt->execute();
        while ($row = $paymentsStmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        // Fetch Donations
        $donationsStmt = $this->db->prepare(
            "SELECT d.created_at, 'Donación' as type, d.type as subtype, 'Donación' as concept, d.amount, do.name as entity FROM donations d JOIN donors do ON d.donor_id = do.id ORDER BY d.created_at DESC"
        );
        $donationsStmt->execute();
        while ($row = $donationsStmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        // Fetch Book Ads
        $adsStmt = $this->db->prepare(
            "SELECT b.created_at, 'Libro Fiestas' as type, b.ad_type as subtype, 'Anuncio' as concept, b.amount, do.name as entity FROM book_ads b JOIN donors do ON b.donor_id = do.id ORDER BY b.created_at DESC"
        );
        $adsStmt->execute();
        while ($row = $adsStmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
?>

                        public function __construct() {
                            $database = new Database();
                            $this->db = $database->getConnection();
                        }

                        private function checkAdmin() {
                            if (($_SESSION['role'] ?? '') !== 'admin') {
                                header('Location: index.php?page=dashboard');
                            }
                        }

                        public function eventsAttendance() {
                            $this->checkAdmin();
                            require_once __DIR__ . '/../Models/Event.php';
                            require_once __DIR__ . '/../Models/EventAttendance.php';
                            $eventModel = new Event($this->db);
                            $stmt = $eventModel->readAll();
                            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $attendanceModel = new EventAttendance($this->db);
                            foreach ($events as &$event) {
                                $event['stats'] = $attendanceModel->getStatsByEvent($event['id']);
                            }
                            include __DIR__ . '/../Views/reports/events_attendance.php';
                        }

                        public function exportMembers() {
                            $this->checkAdmin();
                            $member = new Member($this->db);
                            $stmt = $member->readAll();
                            header('Content-Type: text/csv');
                            header('Content-Disposition: attachment; filename="socios_' . date('Y-m-d') . '.csv"');
                            $output = fopen('php://output', 'w');
                            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                            fputcsv($output, ['ID', 'Nombre', 'Apellidos', 'Email', 'Teléfono', 'Dirección', 'Estado', 'Fecha Alta', 'Fecha Baja']);
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                fputcsv($output, [
                                    $row['id'],
                                    $row['first_name'],
                                    $row['last_name'],
                                    $row['email'],
                                    $row['phone'],
                                    $row['address'],
                                    $row['status'],
                                    $row['created_at'],
                                    $row['deactivated_at'] ?? ''
                                ]);
                            }
                            fclose($output);
                            exit;
                        }

                        public function exportDonors() {
                            $this->checkAdmin();
                            require_once __DIR__ . '/../Models/Donor.php';
                            $donor = new Donor($this->db);
                            $stmt = $donor->readAll();
                            header('Content-Type: text/csv');
                            header('Content-Disposition: attachment; filename="donantes_' . date('Y-m-d') . '.csv"');
                            $output = fopen('php://output', 'w');
                            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                            fputcsv($output, ['ID', 'Nombre', 'Contacto', 'Email', 'Teléfono', 'Dirección']);
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                fputcsv($output, [
                                    $row['id'],
                                    $row['name'],
                                    $row['contact_person'],
                                    $row['email'],
                                    $row['phone'],
                                    $row['address']
                                ]);
                            }
                            fclose($output);
                            exit;
                        }

                        public function exportMovements() {
                            $this->checkAdmin();
                            header('Content-Type: text/csv');
                            header('Content-Disposition: attachment; filename="movimientos_' . date('Y-m-d') . '.csv"');
                            $output = fopen('php://output', 'w');
                            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                            fputcsv($output, ['Fecha', 'Tipo', 'Subtipo', 'Concepto', 'Importe', 'Usuario/Entidad']);
                            // Fetch Payments
                            $paymentsStmt = $this->db->prepare(
                                "SELECT p.created_at, 'Ingreso' as type, p.payment_type as subtype, p.concept, p.amount, CONCAT(m.first_name, ' ', m.last_name) as entity FROM payments p JOIN members m ON p.member_id = m.id ORDER BY p.created_at DESC"
                            );
                            $paymentsStmt->execute();
                            while ($row = $paymentsStmt->fetch(PDO::FETCH_ASSOC)) {
                                fputcsv($output, $row);
                            }
                            // Fetch Donations
                            $donationsStmt = $this->db->prepare(
                                "SELECT d.created_at, 'Donación' as type, d.type as subtype, 'Donación' as concept, d.amount, do.name as entity FROM donations d JOIN donors do ON d.donor_id = do.id ORDER BY d.created_at DESC"
                            );
                            $donationsStmt->execute();
                            while ($row = $donationsStmt->fetch(PDO::FETCH_ASSOC)) {
                                fputcsv($output, $row);
                            }
                            // Fetch Book Ads
                            $adsStmt = $this->db->prepare(
                                "SELECT b.created_at, 'Libro Fiestas' as type, b.ad_type as subtype, 'Anuncio' as concept, b.amount, do.name as entity FROM book_ads b JOIN donors do ON b.donor_id = do.id ORDER BY b.created_at DESC"
                            );
                            $adsStmt->execute();
                            while ($row = $adsStmt->fetch(PDO::FETCH_ASSOC)) {
                                fputcsv($output, $row);
                            }
                            fclose($output);
                            exit;
                        }
                    }
                    ?>
