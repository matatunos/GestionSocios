<?php

require_once __DIR__ . '/../Models/Event.php';
require_once __DIR__ . '/../Models/EventAttendance.php';

class CalendarController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        // Get current month and year from query params or use current
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
        
        // Validate month and year
        if ($month < 1) {
            $month = 12;
            $year--;
        } elseif ($month > 12) {
            $month = 1;
            $year++;
        }
        
        // Get events for the month
        $eventModel = new Event($this->db);
        $stmt = $eventModel->readByMonth($year, $month);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get events for previous and next month for better calendar display
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        
        require_once __DIR__ . '/../Views/calendar/index.php';
    }

    public function api() {
        // API endpoint for AJAX requests
        header('Content-Type: application/json');
        
        // FullCalendar sends start and end dates in the query
        $start = isset($_GET['start']) ? $_GET['start'] : null;
        $end = isset($_GET['end']) ? $_GET['end'] : null;
        
        $eventModel = new Event($this->db);
        
        // If start and end are provided, use date range
        if ($start && $end) {
            $stmt = $eventModel->readByDateRange($start, $end);
        } else {
            // Fallback to month-based query
            $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
            $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
            $stmt = $eventModel->readByMonth($year, $month);
        }
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format events for FullCalendar
        $calendarEvents = [];
        foreach ($events as $event) {
            $calendarEvents[] = [
                'id' => $event['id'],
                'title' => $event['name'],
                'start' => $event['date'] . ($event['start_time'] ? 'T' . $event['start_time'] : ''),
                'end' => $event['date'] . ($event['end_time'] ? 'T' . $event['end_time'] : ''),
                'color' => $event['color'] ?? '#6366f1',
                'description' => $event['description'],
                'location' => $event['location'],
                'price' => $event['price'],
                'type' => $event['event_type'] ?? 'other',
                'url' => 'index.php?page=calendar&action=viewEvent&id=' . $event['id']
            ];
        }
        
        echo json_encode($calendarEvents);
        exit;
    }

    public function viewEvent() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=calendar');
            exit;
        }

        $eventModel = new Event($this->db);
        $eventModel->id = $_GET['id'];
        
        if (!$eventModel->readOne()) {
            header('Location: index.php?page=calendar');
            exit;
        }

        // Get attendance stats
        $attendanceModel = new EventAttendance($this->db);
        $attendanceStats = $attendanceModel->getStatsByEvent($_GET['id']);
        
        // Get attendees list
        $stmt = $attendanceModel->getAttendeesByEvent($_GET['id']);
        $attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/calendar/view_event.php';
    }

    public function registerAttendance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=calendar');
            exit;
        }

        $event_id = $_POST['event_id'] ?? null;
        $member_id = $_POST['member_id'] ?? null;
        $status = $_POST['status'] ?? 'registered';
        $notes = $_POST['notes'] ?? null;

        if (!$event_id || !$member_id) {
            $_SESSION['error'] = "Datos incompletos para el registro";
            header('Location: index.php?page=calendar&action=viewEvent&id=' . $event_id);
            exit;
        }

        $attendanceModel = new EventAttendance($this->db);
        $attendanceModel->event_id = $event_id;
        $attendanceModel->member_id = $member_id;
        $attendanceModel->status = $status;
        $attendanceModel->notes = $notes;

        if ($attendanceModel->register()) {
            $_SESSION['success'] = "Asistencia registrada correctamente";
        } else {
            $_SESSION['error'] = "Error al registrar la asistencia";
        }

        header('Location: index.php?page=calendar&action=viewEvent&id=' . $event_id);
        exit;
    }

    public function updateAttendanceStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=calendar');
            exit;
        }

        $attendance_id = $_POST['attendance_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $event_id = $_POST['event_id'] ?? null;

        if (!$attendance_id || !$status) {
            $_SESSION['error'] = "Datos incompletos";
            header('Location: index.php?page=calendar&action=viewEvent&id=' . $event_id);
            exit;
        }

        $attendanceModel = new EventAttendance($this->db);
        $attendanceModel->id = $attendance_id;
        $attendanceModel->status = $status;

        if ($attendanceModel->updateStatus()) {
            $_SESSION['success'] = "Estado actualizado correctamente";
        } else {
            $_SESSION['error'] = "Error al actualizar el estado";
        }

        header('Location: index.php?page=calendar&action=viewEvent&id=' . $event_id);
        exit;
    }

    public function deleteAttendance() {
        if (!isset($_GET['id']) || !isset($_GET['event_id'])) {
            header('Location: index.php?page=calendar');
            exit;
        }

        $attendanceModel = new EventAttendance($this->db);
        $attendanceModel->id = $_GET['id'];

        if ($attendanceModel->delete()) {
            $_SESSION['success'] = "Asistencia eliminada correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar la asistencia";
        }

        header('Location: index.php?page=calendar&action=viewEvent&id=' . $_GET['event_id']);
        exit;
    }
}
