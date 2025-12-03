<?php
/**
 * Generador de datos de ejemplo para el sistema de gestión de socios
 * Genera el archivo sample_data_large.sql con datos aleatorios
 */

// ============================================
// CONFIGURACIÓN
// ============================================

$NUM_MEMBERS = 500;           // Número de socios
$NUM_DONORS = 50;             // Número de donantes
$NUM_EVENTS = 30;             // Número de eventos
$NUM_PAYMENTS = 1500;         // Número de pagos
$NUM_ATTENDANCE = 800;        // Número de asistencias
$NUM_EXPENSES = 200;          // Número de gastos
$NUM_TASKS = 50;              // Número de tareas
$NUM_DONATIONS = 100;         // Número de donaciones
$NUM_BOOK_ADS = 80;           // Número de anuncios

// ============================================
// DATOS BASE
// ============================================

$firstNames = [
    'Antonio', 'Maria', 'Jose', 'Ana', 'Francisco', 'Isabel', 'David', 'Carmen',
    'Javier', 'Laura', 'Manuel', 'Elena', 'Carlos', 'Sara', 'Miguel', 'Raquel',
    'Pablo', 'Patricia', 'Alejandro', 'Marta', 'Daniel', 'Lucia', 'Jorge', 'Cristina',
    'Alberto', 'Beatriz', 'Roberto', 'Julia', 'Fernando', 'Teresa', 'Luis', 'Rosa',
    'Pedro', 'Dolores', 'Angel', 'Pilar', 'Ramon', 'Mercedes', 'Vicente', 'Josefa',
    'Andres', 'Francisca', 'Juan', 'Antonia', 'Tomas', 'Concepcion', 'Rafael', 'Rosario'
];

$lastNames = [
    'Garcia', 'Rodriguez', 'Martinez', 'Lopez', 'Sanchez', 'Perez', 'Gomez', 'Martin',
    'Hernandez', 'Diaz', 'Moreno', 'Alvarez', 'Romero', 'Alonso', 'Gutierrez', 'Navarro',
    'Torres', 'Dominguez', 'Vazquez', 'Ramos', 'Gil', 'Serrano', 'Blanco', 'Molina',
    'Morales', 'Ortega', 'Delgado', 'Castro', 'Ortiz', 'Rubio', 'Marin', 'Sanz',
    'Iglesias', 'Nuñez', 'Medina', 'Garrido', 'Santos', 'Castillo', 'Cortes', 'Lozano'
];

$streets = [
    'Calle Mayor', 'Av. Libertad', 'Plaza España', 'Calle Real', 'Av. Constitución',
    'Calle Sol', 'Calle Luna', 'Paseo Marítimo', 'Calle Norte', 'Calle Sur',
    'Plaza Mayor', 'Calle Ancha', 'Av. Principal', 'Calle Nueva', 'Calle Vieja',
    'Plaza del Ayuntamiento', 'Calle San Juan', 'Av. de la Paz', 'Calle del Carmen',
    'Plaza de la Iglesia'
];

$companies = [
    'Bar', 'Restaurante', 'Cafetería', 'Panadería', 'Carnicería', 'Ferretería',
    'Farmacia', 'Librería', 'Peluquería', 'Taller', 'Construcciones', 'Pinturas',
    'Electricidad', 'Fontanería', 'Carpintería', 'Comercio', 'Supermercado', 'Tienda',
    'Autoescuela', 'Gestoría'
];

$companyNames = [
    'El Rincón', 'La Esquina', 'Central', 'La Plaza', 'El Encuentro', 'La Espiga',
    'San José', 'Santa Ana', 'La Moderna', 'El Progreso', 'La Unión', 'La Estrella',
    'El Sol', 'La Luna', 'Los Amigos', 'La Familia', 'El Porvenir', 'La Esperanza'
];

$eventTitles = [
    'Asamblea General', 'Fiesta de Primavera', 'Cena de Verano', 'Excursión',
    'Taller', 'Charla', 'Concierto', 'Torneo', 'Mercadillo', 'Curso',
    'Jornada Cultural', 'Día del Socio', 'Fiesta de Navidad', 'Comida de Hermandad',
    'Actividad Deportiva', 'Exposición', 'Conferencia', 'Visita Guiada'
];

$taskTitles = [
    'Preparar reunión', 'Actualizar base de datos', 'Revisar documentación',
    'Contactar proveedores', 'Organizar evento', 'Enviar comunicado', 'Revisar presupuesto',
    'Actualizar web', 'Preparar informe', 'Gestionar pagos', 'Renovar seguros',
    'Mantenimiento local', 'Campaña de captación', 'Revisión inventario'
];

// ============================================
// FUNCIONES AUXILIARES
// ============================================

function getRandomElement($array) {
    return $array[array_rand($array)];
}

function generateDNI() {
    $num = rand(10000000, 99999999);
    $letters = "TRWAGMYFPDXBNJZSQVHLCKE";
    return $num . $letters[$num % 23];
}

function generateCIF() {
    $letters = "ABCDEFGHJNPQRSUVW";
    return $letters[rand(0, strlen($letters)-1)] . rand(10000000, 99999999);
}

function generateDate($startYear = 2020, $endYear = null) {
    if ($endYear === null) {
        $endYear = date('Y');
    }
    $start = strtotime("$startYear-01-01");
    $end = strtotime("$endYear-12-31");
    $timestamp = mt_rand($start, $end);
    return date("Y-m-d", $timestamp);
}

function generateDateTime($startYear = 2024) {
    $date = generateDate($startYear);
    $hour = rand(8, 22);
    $minute = rand(0, 59);
    return "$date " . sprintf("%02d:%02d:00", $hour, $minute);
}

function sqlEscape($str) {
    return addslashes($str);
}

// ============================================
// INICIO DE GENERACIÓN
// ============================================

echo "-- ============================================\n";
echo "-- Datos de Ejemplo LARGE - Generados Automáticamente\n";
echo "-- ============================================\n";
echo "-- Fecha de generación: " . date('Y-m-d H:i:s') . "\n";
echo "-- Configuración:\n";
echo "--   Socios: $NUM_MEMBERS\n";
echo "--   Donantes: $NUM_DONORS\n";
echo "--   Eventos: $NUM_EVENTS\n";
echo "--   Pagos: $NUM_PAYMENTS\n";
echo "--   Asistencias: $NUM_ATTENDANCE\n";
echo "--   Gastos: $NUM_EXPENSES\n";
echo "--   Tareas: $NUM_TASKS\n";
echo "-- ============================================\n\n";

echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// Limpiar tablas
echo "-- Limpiar tablas existentes\n";
$tables = [
    'event_attendance', 'payments', 'donations', 'book_ads', 'expenses', 'tasks',
    'notifications', 'messages', 'conversation_participants', 'conversations',
    'document_permissions', 'documents', 'members', 'events', 'donors',
    'category_fee_history', 'member_categories', 'expense_categories', 'task_categories',
    'ad_prices', 'annual_fees', 'organization_settings', 'users', 'roles'
];

foreach ($tables as $table) {
    echo "TRUNCATE TABLE $table;\n";
}
echo "\n";

// 1. CONFIGURACIÓN
echo "-- ============================================\n";
echo "-- CONFIGURACIÓN\n";
echo "-- ============================================\n\n";

echo "INSERT INTO organization_settings (category, setting_key, setting_value, setting_type, description) VALUES\n";
$settings = [
    "('general', 'org_name', 'Asociación Demo Large', 'string', 'Nombre completo de la organización')",
    "('general', 'org_short_name', 'ASODEMO', 'string', 'Siglas')",
    "('general', 'org_founded_year', '1995', 'int', 'Año de fundación')",
    "('general', 'org_cif', 'A12345678', 'string', 'CIF/NIF')",
    "('general', 'org_registry_number', 'REG-2025-001', 'string', 'Nº Registro Oficial')",
    "('contact', 'org_address', 'Calle Mayor 1', 'string', 'Dirección')",
    "('contact', 'org_city', 'Ciudad Demo', 'string', 'Ciudad')",
    "('contact', 'org_province', 'Provincia Demo', 'string', 'Provincia')",
    "('contact', 'org_country', 'España', 'string', 'País')",
    "('contact', 'org_phone', '600123456', 'string', 'Teléfono')",
    "('contact', 'org_email', 'info@demo.org', 'string', 'Email')",
    "('contact', 'org_website', 'https://demo.org', 'string', 'Sitio Web')",
    "('branding', 'org_logo', '', 'string', 'Logo')",
    "('branding', 'org_logo_width', '180', 'int', 'Ancho del logo')",
    "('branding', 'org_primary_color', '#6366f1', 'string', 'Color primario')",
    "('branding', 'org_secondary_color', '#8b5cf6', 'string', 'Color secundario')",
    "('legal', 'org_president_name', 'Juan Pérez', 'string', 'Presidente/a')",
    "('legal', 'org_secretary_name', 'Ana García', 'string', 'Secretario/a')",
    "('legal', 'org_treasurer_name', 'Luis Martín', 'string', 'Tesorero/a')",
    "('legal', 'org_legal_text', 'Texto legal de ejemplo.', 'text', 'Texto legal')"
];
echo implode(",\n", $settings) . ";\n\n";

// Cuotas anuales
echo "INSERT INTO annual_fees (year, amount) VALUES\n";
for ($year = 2020; $year <= 2026; $year++) {
    $amount = 15 + ($year - 2020) * 2;
    $fees[] = "($year, " . number_format($amount, 2, '.', '') . ")";
}
echo implode(",\n", $fees) . ";\n\n";

// Precios de anuncios
echo "INSERT INTO ad_prices (year, type, amount) VALUES\n";
$adPrices = [];
for ($year = 2023; $year <= 2026; $year++) {
    $base = 40 + ($year - 2023) * 5;
    $adPrices[] = "($year, 'media', " . number_format($base, 2, '.', '') . ")";
    $adPrices[] = "($year, 'full', " . number_format($base * 2, 2, '.', '') . ")";
    $adPrices[] = "($year, 'cover', " . number_format($base * 3, 2, '.', '') . ")";
    $adPrices[] = "($year, 'back_cover', " . number_format($base * 2.5, 2, '.', '') . ")";
}
echo implode(",\n", $adPrices) . ";\n\n";

// 2. CATEGORÍAS
echo "-- ============================================\n";
echo "-- CATEGORÍAS\n";
echo "-- ============================================\n\n";

echo "INSERT INTO member_categories (name, description, color, is_active, display_order, default_fee) VALUES\n";
$categories = [
    "('General', 'Socios generales', '#3498db', 1, 1, 20.00)",
    "('Joven', 'Socios jóvenes (menores de 25)', '#2ecc71', 1, 2, 10.00)",
    "('Juvenil', 'Socios juveniles (menores de 14)', '#f1c40f', 1, 3, 5.00)",
    "('Honorífico', 'Socios honoríficos', '#9b59b6', 1, 4, 0.00)",
    "('Senior', 'Socios mayores de 65', '#e67e22', 1, 5, 15.00)",
    "('Familiar', 'Unidad familiar', '#e74c3c', 1, 6, 30.00)",
    "('Simpatizante', 'Colaboradores sin voto', '#95a5a6', 1, 7, 10.00)"
];
echo implode(",\n", $categories) . ";\n\n";

echo "INSERT INTO expense_categories (name, description, color, is_active) VALUES\n";
$expenseCategories = [
    "('Material Oficina', 'Gastos de material de oficina', '#e67e22', 1)",
    "('Alquiler', 'Pago de alquiler del local', '#9b59b6', 1)",
    "('Servicios', 'Luz, agua, internet, etc.', '#3498db', 1)",
    "('Eventos', 'Gastos de organización de eventos', '#2ecc71', 1)",
    "('Mantenimiento', 'Reparaciones y mantenimiento', '#e74c3c', 1)",
    "('Marketing', 'Publicidad y promoción', '#f39c12', 1)",
    "('Seguros', 'Pólizas de seguro', '#34495e', 1)"
];
echo implode(",\n", $expenseCategories) . ";\n\n";

echo "INSERT INTO task_categories (name, color, icon, description) VALUES\n";
$taskCategories = [
    "('Administrativo', '#e67e22', 'fa-briefcase', 'Tareas administrativas')",
    "('Gestión', '#9b59b6', 'fa-tasks', 'Tareas de gestión')",
    "('Eventos', '#2ecc71', 'fa-calendar', 'Organización de eventos')",
    "('Comunicación', '#3498db', 'fa-bullhorn', 'Comunicación con socios')",
    "('Mantenimiento', '#e74c3c', 'fa-wrench', 'Mantenimiento del local')"
];
echo implode(",\n", $taskCategories) . ";\n\n";

// 3. USUARIOS
echo "-- ============================================\n";
echo "-- USUARIOS\n";
echo "-- ============================================\n\n";

echo "INSERT INTO users (email, name, password, role, active, status) VALUES\n";
$users = [
    "('admin@demo.com', 'Administrador', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 'active')",
    "('tesorero@demo.com', 'Juan Tesorero', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active')",
    "('secretario@demo.com', 'Ana Secretaria', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active')"
];
echo implode(",\n", $users) . ";\n\n";

// 4. SOCIOS
echo "-- ============================================\n";
echo "-- SOCIOS ($NUM_MEMBERS)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, amount) VALUES\n";
$members = [];
for ($i = 1; $i <= $NUM_MEMBERS; $i++) {
    $firstName = getRandomElement($firstNames);
    $lastName = getRandomElement($lastNames) . ' ' . getRandomElement($lastNames);
    $dni = generateDNI();
    $email = strtolower(str_replace(' ', '.', $firstName . '.' . explode(' ', $lastName)[0])) . $i . '@email.com';
    $phone = '6' . rand(10000000, 99999999);
    $address = getRandomElement($streets) . ' ' . rand(1, 150);
    $categoryId = rand(1, 7);
    $status = (rand(1, 10) > 1) ? 'active' : 'inactive';
    $joinDate = generateDate(2015, 2024);
    $amount = [20, 10, 5, 0, 15, 30, 10][$categoryId - 1];
    
    $members[] = "('$firstName', '" . sqlEscape($lastName) . "', '$dni', '$email', '$phone', '" . sqlEscape($address) . "', $categoryId, '$status', '$joinDate', $amount)";
}
echo implode(",\n", $members) . ";\n\n";

// 5. DONANTES
echo "-- ============================================\n";
echo "-- DONANTES ($NUM_DONORS)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO donors (name, contact_person, phone, email, address) VALUES\n";
$donors = [];
for ($i = 1; $i <= $NUM_DONORS; $i++) {
    $companyType = getRandomElement($companies);
    $companyName = getRandomElement($companyNames);
    $name = "$companyType $companyName";
    $contactPerson = getRandomElement($firstNames) . ' ' . getRandomElement($lastNames);
    $phone = rand(600000000, 699999999);
    $email = strtolower(str_replace(' ', '', $companyName)) . '@email.com';
    $address = getRandomElement($streets) . ' ' . rand(1, 100);
    
    $donors[] = "('" . sqlEscape($name) . "', '" . sqlEscape($contactPerson) . "', '$phone', '$email', '" . sqlEscape($address) . "')";
}
echo implode(",\n", $donors) . ";\n\n";

// 6. EVENTOS
echo "-- ============================================\n";
echo "-- EVENTOS ($NUM_EVENTS)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO events (title, description, event_date, location) VALUES\n";
$events = [];
for ($i = 1; $i <= $NUM_EVENTS; $i++) {
    $title = getRandomElement($eventTitles) . ' ' . rand(2024, 2025);
    $description = 'Descripción del evento: ' . $title;
    $eventDate = generateDateTime(2024);
    $location = getRandomElement(['Salón de Actos', 'Plaza Mayor', 'Centro Cultural', 'Parque Central', 'Sede Social', 'Restaurante Local']);
    
    $events[] = "('" . sqlEscape($title) . "', '" . sqlEscape($description) . "', '$eventDate', '" . sqlEscape($location) . "')";
}
echo implode(",\n", $events) . ";\n\n";

// 7. ASISTENCIAS
echo "-- ============================================\n";
echo "-- ASISTENCIAS A EVENTOS ($NUM_ATTENDANCE)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO event_attendance (event_id, member_id, status, attended, attended_at, registered_at, registration_date) VALUES\n";
$attendances = [];
$attendancePairs = [];
for ($i = 0; $i < $NUM_ATTENDANCE; $i++) {
    $eventId = rand(1, $NUM_EVENTS);
    $memberId = rand(1, $NUM_MEMBERS);
    $key = "$eventId-$memberId";
    
    if (!isset($attendancePairs[$key])) {
        $attendancePairs[$key] = true;
        $status = getRandomElement(['registered', 'confirmed', 'attended', 'cancelled']);
        $attended = ($status == 'attended') ? 1 : 0;
        $registeredAt = generateDateTime(2024);
        $attendedAt = $attended ? generateDateTime(2024) : 'NULL';
        
        $attendances[] = "($eventId, $memberId, '$status', $attended, " . ($attended ? "'$attendedAt'" : "NULL") . ", '$registeredAt', '$registeredAt')";
    }
}
echo implode(",\n", $attendances) . ";\n\n";

// 8. PAGOS
echo "-- ============================================\n";
echo "-- PAGOS ($NUM_PAYMENTS)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type, event_id) VALUES\n";
$payments = [];
for ($i = 0; $i < $NUM_PAYMENTS; $i++) {
    $memberId = rand(1, $NUM_MEMBERS);
    $isEventPayment = rand(1, 10) > 7; // 30% son pagos de eventos
    
    if ($isEventPayment) {
        $eventId = rand(1, $NUM_EVENTS);
        $amount = rand(5, 30);
        $concept = 'Pago de evento';
        $paymentType = 'event';
        $feeYear = 'NULL';
        $eventIdStr = $eventId;
    } else {
        $year = rand(2023, 2025);
        $amount = rand(10, 30);
        $concept = "Cuota anual $year";
        $paymentType = 'fee';
        $feeYear = $year;
        $eventIdStr = 'NULL';
    }
    
    $paymentDate = generateDate(2023, 2025);
    $status = (rand(1, 10) > 1) ? 'paid' : 'pending';
    
    $payments[] = "($memberId, $amount, '$paymentDate', '" . sqlEscape($concept) . "', '$status', $feeYear, '$paymentType', $eventIdStr)";
}
echo implode(",\n", $payments) . ";\n\n";

// 9. ANUNCIOS
echo "-- ============================================\n";
echo "-- ANUNCIOS DEL LIBRO ($NUM_BOOK_ADS)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO book_ads (donor_id, year, ad_type, amount, status) VALUES\n";
$bookAds = [];
for ($i = 0; $i < $NUM_BOOK_ADS; $i++) {
    $donorId = rand(1, $NUM_DONORS);
    $year = rand(2023, 2025);
    $adType = getRandomElement(['media', 'full', 'cover', 'back_cover']);
    $amounts = ['media' => 50, 'full' => 90, 'cover' => 150, 'back_cover' => 120];
    $amount = $amounts[$adType];
    $status = (rand(1, 10) > 2) ? 'paid' : 'pending';
    
    $bookAds[] = "($donorId, $year, '$adType', $amount, '$status')";
}
echo implode(",\n", $bookAds) . ";\n\n";

// 10. DONACIONES
echo "-- ============================================\n";
echo "-- DONACIONES ($NUM_DONATIONS)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO donations (donor_id, amount, type, year, donation_date) VALUES\n";
$donations = [];
for ($i = 0; $i < $NUM_DONATIONS; $i++) {
    $donorId = rand(1, $NUM_DONORS);
    $amount = rand(50, 500);
    $type = getRandomElement(['media', 'full', 'cover', 'monetary']);
    $year = rand(2023, 2025);
    $donationDate = generateDate(2023, 2025);
    
    $donations[] = "($donorId, $amount, '$type', $year, '$donationDate')";
}
echo implode(",\n", $donations) . ";\n\n";

// 11. GASTOS
echo "-- ============================================\n";
echo "-- GASTOS ($NUM_EXPENSES)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO expenses (category_id, description, amount, expense_date, payment_method, invoice_number, provider, created_by) VALUES\n";
$expenses = [];
for ($i = 0; $i < $NUM_EXPENSES; $i++) {
    $categoryId = rand(1, 7);
    $descriptions = [
        'Compra de material',
        'Pago de alquiler',
        'Factura de servicios',
        'Gastos de evento',
        'Reparación',
        'Campaña publicitaria',
        'Renovación de seguro'
    ];
    $description = $descriptions[$categoryId - 1] . ' - ' . date('Y-m', strtotime(generateDate(2023, 2025)));
    $amount = rand(50, 1000) + (rand(0, 99) / 100);
    $expenseDate = generateDate(2023, 2025);
    $paymentMethod = getRandomElement(['transferencia', 'efectivo', 'tarjeta', 'domiciliación']);
    $invoiceNumber = 'FAC-' . rand(1000, 9999);
    $provider = 'Proveedor ' . rand(1, 20);
    $createdBy = rand(1, 3);
    
    $expenses[] = "($categoryId, '" . sqlEscape($description) . "', $amount, '$expenseDate', '$paymentMethod', '$invoiceNumber', '" . sqlEscape($provider) . "', $createdBy)";
}
echo implode(",\n", $expenses) . ";\n\n";

// 12. TAREAS
echo "-- ============================================\n";
echo "-- TAREAS ($NUM_TASKS)\n";
echo "-- ============================================\n\n";

echo "INSERT INTO tasks (title, description, assigned_to, status, due_date, category_id, created_by, priority) VALUES\n";
$tasks = [];
for ($i = 0; $i < $NUM_TASKS; $i++) {
    $title = getRandomElement($taskTitles);
    $description = 'Descripción de la tarea: ' . $title;
    $assignedTo = rand(1, 3);
    $status = getRandomElement(['pending', 'in_progress', 'completed']);
    $dueDate = generateDate(2025, 2026);
    $categoryId = rand(1, 5);
    $createdBy = rand(1, 3);
    $priority = rand(1, 3);
    
    $tasks[] = "('" . sqlEscape($title) . "', '" . sqlEscape($description) . "', $assignedTo, '$status', '$dueDate', $categoryId, $createdBy, $priority)";
}
echo implode(",\n", $tasks) . ";\n\n";

// 13. HISTORIAL DE CUOTAS
echo "-- ============================================\n";
echo "-- HISTORIAL DE CUOTAS POR CATEGORÍA\n";
echo "-- ============================================\n\n";

echo "INSERT INTO category_fee_history (category_id, year, fee_amount) VALUES\n";
$feeHistory = [];
for ($categoryId = 1; $categoryId <= 7; $categoryId++) {
    $baseFee = [20, 10, 5, 0, 15, 30, 10][$categoryId - 1];
    for ($year = 2020; $year <= 2025; $year++) {
        $fee = $baseFee + ($year - 2020) * 1;
        $feeHistory[] = "($categoryId, $year, $fee)";
    }
}
echo implode(",\n", $feeHistory) . ";\n\n";

echo "SET FOREIGN_KEY_CHECKS = 1;\n\n";

echo "-- ============================================\n";
echo "-- FIN DE DATOS DE EJEMPLO\n";
echo "-- ============================================\n";
