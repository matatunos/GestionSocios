<?php

// Configuración
// Configuración
$NUM_MEMBERS = 100;
$NUM_PAYMENTS = 300;
$NUM_ATTENDANCE = 200;
$NUM_EXPENSES = 25;

// Arrays de datos aleatorios
$firstNames = ['Antonio', 'Maria', 'Jose', 'Ana', 'Francisco', 'Isabel', 'David', 'Carmen', 'Javier', 'Laura', 'Manuel', 'Elena', 'Carlos', 'Sara', 'Miguel', 'Raquel', 'Pablo', 'Patricia', 'Alejandro', 'Marta', 'Daniel', 'Lucia', 'Jorge', 'Cristina', 'Alberto', 'Beatriz', 'Roberto', 'Julia', 'Fernando', 'Teresa'];
$lastNames = ['Garcia', 'Rodriguez', 'Martinez', 'Lopez', 'Sanchez', 'Perez', 'Gomez', 'Martin', 'Hernandez', 'Diaz', 'Moreno', 'Alvarez', 'Romero', 'Alonso', 'Gutierrez', 'Navarro', 'Torres', 'Dominguez', 'Vazquez', 'Ramos', 'Gil', 'Serrano', 'Blanco', 'Molina', 'Morales', 'Ortega', 'Delgado', 'Castro', 'Ortiz', 'Rubio'];
$streets = ['Calle Mayor', 'Av. Libertad', 'Plaza España', 'Calle Real', 'Av. Constitución', 'Calle Sol', 'Calle Luna', 'Paseo Marítimo', 'Calle Norte', 'Calle Sur'];

function getRandomElement($array) {
    return $array[array_rand($array)];
}

function generateDNI() {
    $num = rand(10000000, 99999999);
    $letters = "TRWAGMYFPDXBNJZSQVHLCKE";
    return $num . $letters[$num % 23];
}

function generateDate($startYear = 2020) {
    $timestamp = mt_rand(strtotime("$startYear-01-01"), time());
    return date("Y-m-d", $timestamp);
}

// Header
echo "-- Datos de ejemplo generados automáticamente\n";
echo "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// 1. TABLAS INDEPENDIENTES
echo "-- 1. TABLAS INDEPENDIENTES\n";

// Roles
echo "INSERT INTO roles (name, display_name, description) VALUES 
('admin', 'Administrador', 'Acceso total al sistema'),
('editor', 'Editor', 'Puede editar contenido pero no configuración'),
('viewer', 'Visualizador', 'Solo lectura');\n\n";

// Users
echo "INSERT INTO users (email, name, password, role, active, status) VALUES 
('tesorero@asociacion.com', 'Juan Tesorero', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active'),
('secretario@asociacion.com', 'Ana Secretaria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active');\n\n";

// Categories
echo "INSERT INTO member_categories (id, name, description, color, default_fee) VALUES 
(1, 'General', 'Socio estándar', '#3b82f6', 50.00),
(2, 'Jubilado', 'Mayores de 65 años', '#10b981', 25.00),
(3, 'Juvenil', 'Menores de 25 años', '#f59e0b', 20.00),
(4, 'Familiar', 'Unidad familiar completa', '#8b5cf6', 80.00);\n\n";

// Expense Categories
echo "INSERT INTO expense_categories (id, name, description, color) VALUES 
(1, 'Suministros', 'Luz, agua, internet', '#ef4444'),
(2, 'Mantenimiento', 'Reparaciones', '#f97316'),
(3, 'Eventos', 'Gastos eventos', '#8b5cf6'),
(4, 'Administrativo', 'Material oficina', '#64748b');\n\n";

// Suppliers
echo "INSERT INTO suppliers (id, name, cif_nif, email, phone) VALUES 
(1, 'Papelería Central', 'B12345678', 'info@papeleria.com', '912345678'),
(2, 'Limpiezas Rápidas', 'B87654321', 'contacto@limpiezas.com', '600123456'),
(3, 'Eventos SL', 'B11223344', 'hola@eventos.com', '933445566');\n\n";

// Donors
echo "INSERT INTO donors (id, name, contact_person, phone, email) VALUES 
(1, 'Construcciones López', 'Antonio', '666777888', 'antonio@clopez.com'),
(2, 'Bar El Rincón', 'María', '912334455', 'maria@elrincon.com'),
(3, 'Supermercados Ahorro', 'Luis', '900112233', 'contacto@ahorro.com');\n\n";

// 2. MEMBERS
echo "-- 2. SOCIOS ($NUM_MEMBERS)\n";
echo "INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, amount) VALUES \n";

$members = [];
for ($i = 1; $i <= $NUM_MEMBERS; $i++) {
    $catId = rand(1, 4);
    $amount = ($catId == 1) ? 50 : (($catId == 2) ? 25 : (($catId == 3) ? 20 : 80));
    $status = (rand(1, 10) > 1) ? 'active' : 'inactive';
    
    $firstName = getRandomElement($firstNames);
    $lastName = getRandomElement($lastNames) . ' ' . getRandomElement($lastNames);
    $email = strtolower(str_replace(' ', '.', $firstName . '.' . $lastName)) . $i . '@email.com';
    
    $members[] = "('$firstName', '$lastName', '" . generateDNI() . "', '$email', '6" . rand(10000000, 99999999) . "', '" . getRandomElement($streets) . " " . rand(1, 100) . "', $catId, '$status', '" . generateDate() . "', $amount)";
}
echo implode(",\n", $members) . ";\n\n";

// 3. EVENTS
echo "-- 3. EVENTOS\n";
echo "INSERT INTO events (id, title, description, event_date, location) VALUES 
(1, 'Asamblea General', 'Reunión anual', '2024-03-15 10:00:00', 'Salón de Actos'),
(2, 'Fiesta Primavera', 'Celebración anual', '2024-05-20 12:00:00', 'Plaza Mayor'),
(3, 'Cena Verano', 'Cena hermandad', '2024-08-15 21:00:00', 'Restaurante'),
(4, 'Navidad', 'Fiesta fin de año', '2024-12-20 19:00:00', 'Sede');\n\n";

// 4. PAYMENTS
echo "-- 4. PAGOS ($NUM_PAYMENTS)\n";
echo "INSERT INTO payments (amount, payment_date, payment_type, description, member_id) VALUES \n";
$payments = [];
for ($i = 0; $i < $NUM_PAYMENTS; $i++) {
    $memberId = rand(1, $NUM_MEMBERS);
    $amount = rand(20, 80);
    $type = getRandomElement(['efectivo', 'transferencia', 'domiciliacion']);
    $payments[] = "($amount, '" . generateDate(2023) . "', '$type', 'Cuota o Donación', $memberId)";
}
echo implode(",\n", $payments) . ";\n\n";

// 5. ATTENDANCE
echo "-- 5. ASISTENCIA ($NUM_ATTENDANCE)\n";
echo "INSERT INTO event_attendance (event_id, member_id, status, attended) VALUES \n";
$attendance = [];
$pairs = []; // Avoid duplicates
for ($i = 0; $i < $NUM_ATTENDANCE; $i++) {
    $eventId = rand(1, 4);
    $memberId = rand(1, $NUM_MEMBERS);
    $key = "$eventId-$memberId";
    
    if (!isset($pairs[$key])) {
        $pairs[$key] = true;
        $status = getRandomElement(['registered', 'confirmed', 'attended']);
        $attended = ($status == 'attended') ? 1 : 0;
        $attendance[] = "($eventId, $memberId, '$status', $attended)";
    }
}
echo implode(",\n", $attendance) . ";\n\n";

// 6. EXPENSES
echo "-- 6. GASTOS ($NUM_EXPENSES)\n";
echo "INSERT INTO expenses (category_id, description, amount, expense_date, payment_method, provider, created_by) VALUES \n";
$expenses = [];
for ($i = 0; $i < $NUM_EXPENSES; $i++) {
    $catId = rand(1, 4);
    $amount = rand(10, 500) + (rand(0, 99) / 100);
    $expenses[] = "($catId, 'Gasto vario', $amount, '" . generateDate(2023) . "', 'transferencia', 'Proveedor Genérico', 1)";
}
echo implode(",\n", $expenses) . ";\n\n";

echo "SET FOREIGN_KEY_CHECKS = 1;\n";
