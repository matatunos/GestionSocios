-- ============================================================================
-- SCHEMA v1.0 - Sistema de Gestión de Asociación
-- Este archivo contiene TODAS las tablas necesarias para la versión 1.0
-- No es necesario ejecutar migraciones adicionales
-- ============================================================================

-- Tabla: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member', 'treasurer', 'secretary', 'viewer') DEFAULT 'member',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: member_categories
CREATE TABLE IF NOT EXISTS member_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    default_fee DECIMAL(10,2) DEFAULT 0.00,
    color VARCHAR(7) DEFAULT '#4f46e5',
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla: members
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_number VARCHAR(50) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE,
    email VARCHAR(150),
    phone VARCHAR(20),
    address TEXT,
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),
    status ENUM('active', 'inactive') DEFAULT 'active',
    category_id INT DEFAULT NULL,
    photo_url VARCHAR(255) DEFAULT NULL,
    deactivated_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES member_categories(id) ON DELETE SET NULL
);

-- Tabla: member_image_history
CREATE TABLE IF NOT EXISTS member_image_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replaced_by INT DEFAULT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (replaced_by) REFERENCES member_image_history(id) ON DELETE SET NULL
);

-- Tabla: annual_fees
CREATE TABLE IF NOT EXISTS annual_fees (
    year INT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: events
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    location VARCHAR(255),
    price DECIMAL(10, 2) DEFAULT 0.00,
    max_participants INT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla: payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT DEFAULT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    concept VARCHAR(255) NOT NULL,
    status ENUM('paid', 'pending') DEFAULT 'paid',
    fee_year INT DEFAULT NULL,
    payment_type ENUM('fee', 'event', 'donation') DEFAULT 'fee',
    event_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
);

-- Tabla: donors
CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),
    logo_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: donor_image_history
CREATE TABLE IF NOT EXISTS donor_image_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replaced_by INT DEFAULT NULL,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (replaced_by) REFERENCES donor_image_history(id) ON DELETE SET NULL
);

-- Tabla: donations
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    donation_date DATE NOT NULL,
    description TEXT,
    type ENUM('media', 'full', 'cover', 'general') DEFAULT 'general',
    year YEAR NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
);

-- Tabla: expenses
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    category VARCHAR(100),
    receipt_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: organization_settings
CREATE TABLE IF NOT EXISTS organization_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla: ad_prices
CREATE TABLE IF NOT EXISTS ad_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_type ENUM('media', 'full', 'cover') NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla: fiesta_book_ads
CREATE TABLE IF NOT EXISTS fiesta_book_ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    year YEAR NOT NULL,
    ad_type ENUM('media', 'full', 'cover') NOT NULL,
    page_number INT,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
);

-- Tabla: book_ad_payments
CREATE TABLE IF NOT EXISTS book_ad_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiesta_book_ad_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fiesta_book_ad_id) REFERENCES fiesta_book_ads(id) ON DELETE CASCADE
);

-- Tabla: task_categories
CREATE TABLE IF NOT EXISTS task_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#3b82f6',
    icon VARCHAR(50) DEFAULT 'fa-tasks',
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla: tasks
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    due_time TIME,
    assigned_to INT,
    created_by INT,
    completed_at TIMESTAMP NULL,
    completed_by INT,
    reminder_sent TINYINT(1) DEFAULT 0,
    related_entity_type VARCHAR(50),
    related_entity_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES task_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla: notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla: messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL DEFAULT NULL,
    parent_message_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE CASCADE
);

-- Tabla: documents
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100),
    category VARCHAR(100) DEFAULT 'general',
    uploaded_by INT NOT NULL,
    is_public TINYINT(1) DEFAULT 0,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla: polls
CREATE TABLE IF NOT EXISTS polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    allow_multiple_votes TINYINT(1) DEFAULT 0,
    show_results_before_end TINYINT(1) DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla: poll_options
CREATE TABLE IF NOT EXISTS poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
);

-- Tabla: poll_votes
CREATE TABLE IF NOT EXISTS poll_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_id INT NOT NULL,
    user_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (poll_id, user_id, option_id)
);

-- Tabla: audit_log
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- DATOS INICIALES
-- ============================================================================

-- Usuario admin por defecto (password: admin123)
INSERT INTO users (email, name, password, role, active) VALUES 
('admin@example.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1)
ON DUPLICATE KEY UPDATE email=email;

-- Categorías de socios por defecto
INSERT INTO member_categories (name, description, default_fee, color, display_order) VALUES
('General', 'Socio regular sin categoría específica', 50.00, '#3b82f6', 1),
('Juvenil', 'Socios menores de 25 años', 25.00, '#10b981', 2),
('Senior', 'Socios mayores de 65 años', 30.00, '#f59e0b', 3),
('Familiar', 'Grupos familiares', 75.00, '#8b5cf6', 4),
('Honorífico', 'Socios de honor, exentos de cuota', 0.00, '#ef4444', 5)
ON DUPLICATE KEY UPDATE name=name;

-- Cuota anual por defecto
INSERT INTO annual_fees (year, amount) VALUES 
(2025, 50.00)
ON DUPLICATE KEY UPDATE year=year;

-- Configuración de organización por defecto
INSERT INTO organization_settings (setting_key, setting_value) VALUES
('association_name', 'Mi Asociación'),
('association_address', ''),
('association_phone', ''),
('association_email', ''),
('association_website', ''),
('logo_url', NULL)
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- Precios de anuncios por defecto
INSERT INTO ad_prices (ad_type, price, description) VALUES
('media', 100.00, 'Anuncio de media página'),
('full', 200.00, 'Anuncio de página completa'),
('cover', 500.00, 'Anuncio en portada o contraportada')
ON DUPLICATE KEY UPDATE ad_type=ad_type;

-- Categorías de tareas por defecto
INSERT INTO task_categories (name, color, icon, description) VALUES
('General', '#3b82f6', 'fa-tasks', 'Tareas generales'),
('Administración', '#8b5cf6', 'fa-file-alt', 'Tareas administrativas'),
('Evento', '#10b981', 'fa-calendar', 'Tareas relacionadas con eventos'),
('Finanzas', '#f59e0b', 'fa-euro-sign', 'Tareas financieras'),
('Mantenimiento', '#ef4444', 'fa-tools', 'Tareas de mantenimiento')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================================
-- FIN DEL SCHEMA v1.0
-- ============================================================================
