-- ============================================
-- Schema para Sistema de Gestión de Socios
-- ============================================
-- IMPORTANTE: Las tablas están ordenadas por dependencias
-- Las tablas independientes se crean primero

-- Tabla de categorías de gastos
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(20) DEFAULT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración de la organización
CREATE TABLE IF NOT EXISTS organization_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT NULL,
    password_min_length INT DEFAULT 8,
    password_require_uppercase BOOLEAN DEFAULT 0,
    password_require_lowercase BOOLEAN DEFAULT 1,
    password_require_numbers BOOLEAN DEFAULT 1,
    password_require_special BOOLEAN DEFAULT 0,
    login_max_attempts INT DEFAULT 5,
    login_lockout_duration INT DEFAULT 15,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración general
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    display_name VARCHAR(100),
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    active TINYINT(1) DEFAULT 1,
    status ENUM('active', 'inactive') DEFAULT 'active',
    locked_until DATETIME NULL,
    failed_attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de categorías de socios
CREATE TABLE IF NOT EXISTS member_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(30) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    default_fee DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS members (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    dni VARCHAR(20),
    email VARCHAR(150),
    phone VARCHAR(20),
    address TEXT,
    latitude DECIMAL(9,6) DEFAULT NULL,
    longitude DECIMAL(9,6) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    photo_url VARCHAR(255) DEFAULT NULL,
    join_date DATE DEFAULT NULL,
    deactivated_at DATE DEFAULT NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    member_number INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES member_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de cuotas por categoría
CREATE TABLE IF NOT EXISTS category_fee_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    year INT NOT NULL,
    fee_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES member_categories(id) ON DELETE CASCADE,
    INDEX idx_year (year),
    INDEX idx_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de eventos
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    discarded TINYINT(1) DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de estados de asistencia a eventos
CREATE TABLE IF NOT EXISTS event_attendance_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status_key VARCHAR(32) NOT NULL UNIQUE,
    status_name VARCHAR(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de asistencias a eventos
CREATE TABLE IF NOT EXISTS event_attendance (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    event_id INT(11) NOT NULL,
    member_id INT(11) NOT NULL,
    status ENUM('registered','confirmed','attended','cancelled') NOT NULL,
    attended TINYINT(1) DEFAULT 0,
    attended_at DATETIME DEFAULT NULL,
    registered_at DATETIME DEFAULT NULL,
    registration_date DATETIME DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_member_id (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de donantes
CREATE TABLE IF NOT EXISTS donors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    latitude DECIMAL(9,6) DEFAULT NULL,
    longitude DECIMAL(9,6) DEFAULT NULL,
    logo_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de anuncios del libro de fiestas
CREATE TABLE IF NOT EXISTS book_ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    ad_type ENUM('media', 'full', 'cover', 'back_cover') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('paid', 'pending') DEFAULT 'pending',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_book_ads_donor FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de donaciones
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT UNSIGNED DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    donation_date DATE NOT NULL,
    type VARCHAR(50) DEFAULT NULL,
    year INT DEFAULT NULL,
    method VARCHAR(50) DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year YEAR NOT NULL,
    type ENUM('media', 'full', 'cover', 'back_cover') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_year_type (year, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NULL,
    payment_type VARCHAR(20),
    concept TEXT,
    status ENUM('paid', 'pending', 'cancelled') DEFAULT 'paid',
    fee_year INT DEFAULT NULL,
    member_id INT DEFAULT NULL,
    event_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de categorías de tareas
CREATE TABLE IF NOT EXISTS task_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deactivated_at DATE DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(30),
    icon VARCHAR(50) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de gastos
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    payment_method VARCHAR(50),
    invoice_number VARCHAR(50),
    provider VARCHAR(100),
    notes TEXT,
    receipt_file VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type VARCHAR(50) DEFAULT 'info',
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de conversaciones
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mensajes
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    body TEXT,
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de participantes en conversaciones
CREATE TABLE IF NOT EXISTS conversation_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    member_id INT DEFAULT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_at DATETIME DEFAULT NULL,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de categorías de documentos
CREATE TABLE IF NOT EXISTS document_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category_id INT DEFAULT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL COMMENT 'Tamaño en bytes',
    file_type VARCHAR(100) NOT NULL COMMENT 'MIME type',
    uploaded_by INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE COMMENT 'Si es visible para todos los socios',
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de permisos de documentos
CREATE TABLE IF NOT EXISTS document_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    member_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla relacional documentos-categorías (muchos a muchos)
CREATE TABLE IF NOT EXISTS document_category_rel (
    document_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (document_id, category_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de encuestas
CREATE TABLE IF NOT EXISTS polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    allow_multiple_choices TINYINT(1) DEFAULT 0,
    is_anonymous TINYINT(1) DEFAULT 1,
    results_visible TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de opciones de encuestas
CREATE TABLE IF NOT EXISTS poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de libros de fiestas
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year YEAR NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para guardar versiones del libro de fiestas
CREATE TABLE IF NOT EXISTS book_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para páginas del libro de fiestas (con soporte de versiones y posición)
CREATE TABLE IF NOT EXISTS book_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    version_id INT DEFAULT NULL,
    page_number INT NOT NULL,
    position ENUM('full', 'top', 'bottom') DEFAULT 'full',
    type VARCHAR(50) DEFAULT 'custom',
    image_url VARCHAR(255) DEFAULT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (version_id) REFERENCES book_versions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de actividades del libro de fiestas
CREATE TABLE IF NOT EXISTS book_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year YEAR NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    page_number INT DEFAULT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_year (year),
    INDEX idx_order (year, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tipos de tareas
CREATE TABLE IF NOT EXISTS task_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(100) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tareas
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    priority INT DEFAULT 0,
    status ENUM('pending','in_progress','completed') DEFAULT 'pending',
    due_date DATE DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    completed_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES task_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de comentarios en tareas
CREATE TABLE IF NOT EXISTS task_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de imágenes de donantes
CREATE TABLE IF NOT EXISTS donor_image_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_current TINYINT(1) DEFAULT 1,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT DEFAULT NULL,
    replaced_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_donor_current (donor_id, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de imágenes de socios
CREATE TABLE IF NOT EXISTS member_image_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_current TINYINT(1) DEFAULT 1,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT DEFAULT NULL,
    replaced_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_member_current (member_id, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de registro de actividad (audit log)
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    cif_nif VARCHAR(20),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    logo_path VARCHAR(255),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Tabla de cuotas anuales
CREATE TABLE IF NOT EXISTS annual_fees (
     year INT PRIMARY KEY,
     amount DECIMAL(10,2) NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supplier_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    invoice_date DATE NOT NULL,
    amount DECIMAL(10, 2),
    status ENUM('paid', 'pending', 'cancelled') DEFAULT 'pending',
    file_path VARCHAR(255),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier (supplier_id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_invoice_date (invoice_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de anuncios públicos
CREATE TABLE IF NOT EXISTS public_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    is_active BOOLEAN DEFAULT 1,
    priority INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active_priority (is_active, priority, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de intentos de login
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT 0,
    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Insertar configuración por defecto
INSERT INTO settings (setting_key, setting_value) 
VALUES ('association_name', 'Mi Asociación') 
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- Insertar usuario administrador por defecto (username: admin, password: admin123)
INSERT INTO users (email, name, password, role, active, status) 
VALUES ('admin@admin.com', 'admin', '$2y$10$BXk2d.oBMUer9kKl/acSTO0LP93AstPq1cMfNuxTOOmcIXaOJlBnS', 'admin', 1, 'active') 
ON DUPLICATE KEY UPDATE id=id;

-- Insertar categorías de documentos por defecto
INSERT INTO document_categories (name, description, color) VALUES
('Estatutos', 'Documentos legales y estatutos de la asociación', '#6366f1'),
('Actas', 'Actas de reuniones y asambleas', '#10b981'),
('Informes', 'Informes de gestión, económicos, etc.', '#f59e0b'),
('Convocatorias', 'Convocatorias a reuniones y eventos', '#ef4444'),
('Certificados', 'Certificados y acreditaciones', '#3b82f6'),
('Comunicados', 'Comunicados oficiales y notas informativas', '#8b5cf6'),
('Otros', 'Otros documentos relevantes', '#94a3b8')
ON DUPLICATE KEY UPDATE id=id;

-- ============================================
-- Advanced Accounting Module Migration
-- ============================================
-- This migration adds advanced accounting features including:
-- - Chart of accounts
-- - Double-entry bookkeeping (journal entries)
-- - Accounting periods
-- - Budget management
-- ============================================

-- Tabla de períodos contables
CREATE TABLE IF NOT EXISTS accounting_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    fiscal_year INT NOT NULL,
    status ENUM('open', 'closed', 'locked') DEFAULT 'open',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de plan de cuentas (Chart of Accounts)
CREATE TABLE IF NOT EXISTS accounting_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    account_type ENUM('asset', 'liability', 'equity', 'income', 'expense') NOT NULL,
    parent_id INT DEFAULT NULL,
    level INT DEFAULT 0,
    balance_type ENUM('debit', 'credit') NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    is_system BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES accounting_accounts(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_type (account_type),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de asientos contables (Journal Entries)
CREATE TABLE IF NOT EXISTS accounting_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_number VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    period_id INT NOT NULL,
    description TEXT NOT NULL,
    reference VARCHAR(100),
    entry_type ENUM('manual', 'automatic') DEFAULT 'manual',
    source_type ENUM('expense', 'payment', 'donation', 'manual') DEFAULT 'manual',
    source_id INT DEFAULT NULL,
    status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    posted_by INT DEFAULT NULL,
    posted_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (period_id) REFERENCES accounting_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entry_number (entry_number),
    INDEX idx_entry_date (entry_date),
    INDEX idx_period (period_id),
    INDEX idx_status (status),
    INDEX idx_source (source_type, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de líneas de asiento (Entry Lines - double entry)
CREATE TABLE IF NOT EXISTS accounting_entry_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT NOT NULL,
    account_id INT NOT NULL,
    description TEXT,
    debit DECIMAL(10,2) DEFAULT 0.00,
    credit DECIMAL(10,2) DEFAULT 0.00,
    line_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES accounting_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounting_accounts(id) ON DELETE CASCADE,
    INDEX idx_entry (entry_id),
    INDEX idx_account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de presupuestos
CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    fiscal_year INT NOT NULL,
    account_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    period_type ENUM('yearly', 'monthly', 'quarterly') DEFAULT 'yearly',
    period_number INT DEFAULT NULL,
    status ENUM('draft', 'approved', 'active', 'closed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounting_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_account (account_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar plan de cuentas básico (Spanish Chart of Accounts - PGC simplified)
INSERT INTO accounting_accounts (code, name, account_type, balance_type, level, is_system) VALUES
-- ACTIVO (Assets)
('100', 'Capital Social', 'equity', 'credit', 0, 1),
('129', 'Resultados del Ejercicio', 'equity', 'credit', 0, 1),
('430', 'Clientes', 'asset', 'debit', 0, 1),
('440', 'Deudores', 'asset', 'debit', 0, 1),
('470', 'Hacienda Pública, Deudora', 'asset', 'debit', 0, 1),
('570', 'Caja', 'asset', 'debit', 0, 1),
('572', 'Bancos e Instituciones de Crédito', 'asset', 'debit', 0, 1),

-- PASIVO (Liabilities)
('400', 'Proveedores', 'liability', 'credit', 0, 1),
('410', 'Acreedores', 'liability', 'credit', 0, 1),
('475', 'Hacienda Pública, Acreedora', 'liability', 'credit', 0, 1),

-- INGRESOS (Income)
('700', 'Ventas de Mercaderías', 'income', 'credit', 0, 1),
('705', 'Prestaciones de Servicios', 'income', 'credit', 0, 1),
('720', 'Cuotas de Socios', 'income', 'credit', 0, 1),
('721', 'Subvenciones', 'income', 'credit', 0, 1),
('722', 'Donaciones', 'income', 'credit', 0, 1),
('759', 'Otros Ingresos', 'income', 'credit', 0, 1),

-- GASTOS (Expenses)
('600', 'Compras', 'expense', 'debit', 0, 1),
('621', 'Arrendamientos', 'expense', 'debit', 0, 1),
('622', 'Reparaciones y Conservación', 'expense', 'debit', 0, 1),
('623', 'Servicios de Profesionales Independientes', 'expense', 'debit', 0, 1),
('624', 'Transportes', 'expense', 'debit', 0, 1),
('625', 'Primas de Seguros', 'expense', 'debit', 0, 1),
('626', 'Servicios Bancarios', 'expense', 'debit', 0, 1),
('627', 'Publicidad y Propaganda', 'expense', 'debit', 0, 1),
('628', 'Suministros', 'expense', 'debit', 0, 1),
('629', 'Otros Servicios', 'expense', 'debit', 0, 1),
('640', 'Sueldos y Salarios', 'expense', 'debit', 0, 1),
('642', 'Seguridad Social a cargo de la Empresa', 'expense', 'debit', 0, 1),
('649', 'Otros Gastos Sociales', 'expense', 'debit', 0, 1),
('678', 'Gastos Excepcionales', 'expense', 'debit', 0, 1);

-- Insertar período contable por defecto para año actual
-- Solo si existe al menos un usuario en el sistema
INSERT IGNORE INTO accounting_periods (name, start_date, end_date, fiscal_year, status, created_by) 
SELECT 
    CONCAT('Ejercicio ', YEAR(CURDATE())), 
    CONCAT(YEAR(CURDATE()), '-01-01'), 
    CONCAT(YEAR(CURDATE()), '-12-31'),
    YEAR(CURDATE()),
    'open',
    MIN(id)
FROM users
WHERE EXISTS (SELECT 1 FROM users LIMIT 1);

-- ============================================================================
-- Social Media Sharing Integration
-- ============================================================================
INSERT INTO organization_settings (category, setting_key, setting_value, setting_type, description) VALUES
('social_media', 'facebook_enabled', '0', 'boolean', 'Habilitar compartir en Facebook'),
('social_media', 'facebook_app_id', '', 'text', 'Facebook App ID para compartir contenido'),
('social_media', 'twitter_enabled', '0', 'boolean', 'Habilitar compartir en Twitter/X'),
('social_media', 'linkedin_enabled', '0', 'boolean', 'Habilitar compartir en LinkedIn'),
('social_media', 'instagram_enabled', '0', 'boolean', 'Habilitar compartir en Instagram'),
('social_media', 'share_default_image', '', 'text', 'URL de imagen por defecto para compartir'),
('social_media', 'share_site_name', '', 'text', 'Nombre del sitio para metadatos Open Graph'),
('social_media', 'share_description', '', 'text', 'Descripción por defecto para compartir')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- ============================================================================
-- MÓDULO DE DOCUMENTOS MEJORADO
-- ============================================================================
-- Mejoras: Versionado, Carpetas, Tags, Metadatos, Comentarios,
--          Soft Delete, Compartir por enlace y Auditoría completa
-- ============================================================================

-- Modificaciones a la tabla documents existente
ALTER TABLE `documents`
ADD COLUMN IF NOT EXISTS `version` INT DEFAULT 1 AFTER `downloads`,
ADD COLUMN IF NOT EXISTS `parent_document_id` INT DEFAULT NULL AFTER `version`,
ADD COLUMN IF NOT EXISTS `is_latest_version` BOOLEAN DEFAULT TRUE AFTER `parent_document_id`,
ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN IF NOT EXISTS `deleted_by` INT NULL AFTER `deleted_at`,
ADD COLUMN IF NOT EXISTS `folder_id` INT DEFAULT NULL AFTER `category_id`,
ADD COLUMN IF NOT EXISTS `file_extension` VARCHAR(10) AFTER `file_type`,
ADD COLUMN IF NOT EXISTS `mime_type_verified` VARCHAR(100) AFTER `file_extension`,
ADD COLUMN IF NOT EXISTS `public_token` VARCHAR(64) UNIQUE DEFAULT NULL AFTER `is_public`,
ADD COLUMN IF NOT EXISTS `token_expires_at` DATETIME NULL AFTER `public_token`,
ADD COLUMN IF NOT EXISTS `public_download_limit` INT NULL COMMENT 'Límite de descargas públicas (NULL = ilimitado)' AFTER `token_expires_at`,
ADD COLUMN IF NOT EXISTS `public_downloads` INT DEFAULT 0 COMMENT 'Contador de descargas públicas' AFTER `public_download_limit`,
ADD COLUMN IF NOT EXISTS `public_enabled` BOOLEAN DEFAULT FALSE COMMENT 'Si el enlace público está activo' AFTER `public_downloads`,
ADD COLUMN IF NOT EXISTS `public_created_at` DATETIME NULL COMMENT 'Fecha de creación del enlace público' AFTER `public_enabled`,
ADD COLUMN IF NOT EXISTS `public_created_by` INT NULL COMMENT 'Usuario que creó el enlace público' AFTER `public_created_at`,
ADD COLUMN IF NOT EXISTS `public_last_access` DATETIME NULL COMMENT 'Última vez que se accedió al enlace' AFTER `public_created_by`,
ADD COLUMN IF NOT EXISTS `status` ENUM('draft', 'published', 'archived') DEFAULT 'published' AFTER `is_public`,
ADD COLUMN IF NOT EXISTS `extracted_text` LONGTEXT AFTER `description`;

-- Agregar índices para optimizar consultas
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_folder');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_folder (folder_id)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_parent');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_parent (parent_document_id)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_deleted');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_deleted (deleted_at)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_status');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_status (status)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_version');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_version (version, is_latest_version)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_public_token');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_public_token (public_token)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_public_enabled');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_public_enabled (public_enabled)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'documents' AND index_name = 'idx_token_expires');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE documents ADD INDEX idx_token_expires (token_expires_at)', 'SELECT "Index already exists"');
PREPARE stmt FROM @sqlstmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tabla de historial de versiones
CREATE TABLE IF NOT EXISTS `document_versions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento padre',
    `version_number` INT NOT NULL COMMENT 'Número de versión',
    `file_name` VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo',
    `file_path` VARCHAR(500) NOT NULL COMMENT 'Ruta del archivo en servidor',
    `file_size` INT NOT NULL COMMENT 'Tamaño en bytes',
    `file_type` VARCHAR(100) NOT NULL COMMENT 'MIME type',
    `uploaded_by` INT NOT NULL COMMENT 'Usuario que subió esta versión',
    `change_notes` TEXT COMMENT 'Notas sobre los cambios en esta versión',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_version` (`document_id`, `version_number`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de versiones de documentos';

-- Tabla de carpetas jerárquicas
CREATE TABLE IF NOT EXISTS `document_folders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nombre de la carpeta',
    `description` TEXT COMMENT 'Descripción de la carpeta',
    `parent_id` INT DEFAULT NULL COMMENT 'ID de la carpeta padre (NULL = raíz)',
    `path` VARCHAR(500) COMMENT 'Ruta completa ej: /Estatutos/2024',
    `level` INT DEFAULT 0 COMMENT 'Nivel de profundidad (0 = raíz)',
    `color` VARCHAR(20) DEFAULT '#6366f1' COMMENT 'Color para UI',
    `icon` VARCHAR(50) DEFAULT 'fa-folder' COMMENT 'Icono FontAwesome',
    `created_by` INT NOT NULL COMMENT 'Usuario que creó la carpeta',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `document_folders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_parent` (`parent_id`),
    INDEX `idx_path` (`path`),
    INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Carpetas para organizar documentos';

-- Tabla de etiquetas libres
CREATE TABLE IF NOT EXISTS `document_tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nombre de la etiqueta',
    `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Slug para URLs',
    `color` VARCHAR(20) DEFAULT '#6366f1' COMMENT 'Color hex para UI',
    `description` TEXT COMMENT 'Descripción de la etiqueta',
    `usage_count` INT DEFAULT 0 COMMENT 'Contador de uso',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_usage` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Etiquetas para clasificar documentos';

-- Tabla relacional muchos a muchos: documentos ↔ tags
CREATE TABLE IF NOT EXISTS `document_tag_rel` (
    `document_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`document_id`, `tag_id`),
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `document_tags`(`id`) ON DELETE CASCADE,
    INDEX `idx_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación documentos-etiquetas';

-- Tabla de campos personalizados
CREATE TABLE IF NOT EXISTS `document_metadata` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento',
    `meta_key` VARCHAR(100) NOT NULL COMMENT 'Clave del metadato (ej: autor, expediente)',
    `meta_value` TEXT COMMENT 'Valor del metadato',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_key` (`document_id`, `meta_key`),
    INDEX `idx_meta_key` (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Metadatos personalizados de documentos';

-- Tabla de comentarios en documentos
CREATE TABLE IF NOT EXISTS `document_comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento comentado',
    `user_id` INT NOT NULL COMMENT 'Usuario que comentó',
    `comment` TEXT NOT NULL COMMENT 'Texto del comentario',
    `parent_comment_id` INT DEFAULT NULL COMMENT 'ID del comentario padre (para respuestas)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_comment_id`) REFERENCES `document_comments`(`id`) ON DELETE CASCADE,
    INDEX `idx_document` (`document_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_parent` (`parent_comment_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comentarios en documentos';

-- Tabla de compartir por enlace público
CREATE TABLE IF NOT EXISTS `document_shares` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento compartido',
    `token` VARCHAR(64) UNIQUE NOT NULL COMMENT 'Token único para acceso',
    `password_hash` VARCHAR(255) DEFAULT NULL COMMENT 'Hash de contraseña opcional',
    `expires_at` DATETIME DEFAULT NULL COMMENT 'Fecha de expiración del enlace',
    `max_downloads` INT DEFAULT NULL COMMENT 'Máximo de descargas permitidas',
    `download_count` INT DEFAULT 0 COMMENT 'Contador de descargas realizadas',
    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Si el enlace está activo',
    `created_by` INT NOT NULL COMMENT 'Usuario que compartió',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_accessed_at` DATETIME NULL COMMENT 'Última vez que se accedió',
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expires` (`expires_at`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Enlaces públicos para compartir documentos';

-- Tabla de log de actividad detallado
CREATE TABLE IF NOT EXISTS `document_activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL COMMENT 'ID del documento',
    `user_id` INT DEFAULT NULL COMMENT 'Usuario que realizó la acción',
    `action` ENUM('view','download','edit','delete','restore','share','comment','upload_version','uploaded','created','updated','moved','copied','favorited','unfavorited','previewed','public_link_created','public_link_revoked','trashed') NOT NULL COMMENT 'Tipo de acción',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP del usuario',
    `user_agent` TEXT DEFAULT NULL COMMENT 'User agent del navegador',
    `details` TEXT COMMENT 'Detalles adicionales en JSON',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_document` (`document_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log detallado de actividad en documentos';

-- Tabla de documentos favoritos por usuario
CREATE TABLE IF NOT EXISTS `document_favorites` (
    `user_id` INT NOT NULL COMMENT 'Usuario',
    `document_id` INT NOT NULL COMMENT 'Documento marcado como favorito',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `document_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos favoritos de usuarios';

-- Tabla de log de accesos públicos
CREATE TABLE IF NOT EXISTS `document_public_access_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL,
    `access_token` VARCHAR(64) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `referer` VARCHAR(255) NULL,
    `downloaded` BOOLEAN DEFAULT FALSE,
    `access_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_document_id` (`document_id`),
    INDEX `idx_access_token` (`access_token`),
    INDEX `idx_access_date` (`access_date`),
    FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar carpetas por defecto
INSERT IGNORE INTO `document_folders` (`name`, `description`, `parent_id`, `path`, `level`, `color`, `icon`, `created_by`) VALUES
('Estatutos', 'Estatutos y normativas de la asociación', NULL, '/Estatutos', 0, '#6366f1', 'fa-balance-scale', 1),
('Actas', 'Actas de reuniones y asambleas', NULL, '/Actas', 0, '#10b981', 'fa-file-signature', 1),
('Informes', 'Informes y reportes', NULL, '/Informes', 0, '#f59e0b', 'fa-chart-line', 1),
('Convocatorias', 'Convocatorias de eventos y reuniones', NULL, '/Convocatorias', 0, '#ef4444', 'fa-bell', 1),
('Certificados', 'Certificados emitidos', NULL, '/Certificados', 0, '#3b82f6', 'fa-certificate', 1),
('Contratos', 'Contratos y acuerdos', NULL, '/Contratos', 0, '#8b5cf6', 'fa-file-contract', 1),
('Facturas', 'Facturas y documentos fiscales', NULL, '/Facturas', 0, '#ec4899', 'fa-file-invoice', 1),
('General', 'Documentos generales', NULL, '/General', 0, '#94a3b8', 'fa-folder', 1);

-- Insertar tags por defecto
INSERT IGNORE INTO `document_tags` (`name`, `slug`, `color`, `description`) VALUES
('Urgente', 'urgente', '#ef4444', 'Documentos que requieren atención inmediata'),
('Importante', 'importante', '#f59e0b', 'Documentos de alta prioridad'),
('Revisión', 'revision', '#3b82f6', 'Documentos pendientes de revisión'),
('Aprobado', 'aprobado', '#10b981', 'Documentos aprobados'),
('Borrador', 'borrador', '#6b7280', 'Documentos en borrador'),
('Confidencial', 'confidencial', '#7c3aed', 'Documentos confidenciales'),
('Público', 'publico', '#06b6d4', 'Documentos de acceso público'),
('Archivo', 'archivo', '#64748b', 'Documentos archivados');

-- ============================================================================
-- VISTAS, TRIGGERS Y PROCEDIMIENTOS ALMACENADOS
-- ============================================================================

-- Vista de documentos activos (no eliminados)
CREATE OR REPLACE VIEW `v_documents_active` AS
SELECT 
    d.*,
    u.name as uploaded_by_name,
    u.email as uploaded_by_email,
    f.name as folder_name,
    f.path as folder_path,
    GROUP_CONCAT(DISTINCT dc.name SEPARATOR ', ') as category_names,
    GROUP_CONCAT(DISTINCT dt.name SEPARATOR ', ') as tag_names,
    COUNT(DISTINCT comm.id) as comment_count,
    COUNT(DISTINCT dv.id) as version_count
FROM documents d
LEFT JOIN users u ON d.uploaded_by = u.id
LEFT JOIN document_folders f ON d.folder_id = f.id
LEFT JOIN document_category_rel dcr ON d.id = dcr.document_id
LEFT JOIN document_categories dc ON dcr.category_id = dc.id
LEFT JOIN document_tag_rel dtr ON d.id = dtr.document_id
LEFT JOIN document_tags dt ON dtr.tag_id = dt.id
LEFT JOIN document_comments comm ON d.id = comm.document_id
LEFT JOIN document_versions dv ON d.id = dv.document_id
WHERE d.deleted_at IS NULL
GROUP BY d.id;

-- Vista de documentos más descargados
CREATE OR REPLACE VIEW `v_documents_most_downloaded` AS
SELECT 
    d.id,
    d.title,
    d.downloads,
    d.file_name,
    d.created_at,
    u.name as uploaded_by,
    GROUP_CONCAT(DISTINCT dc.name SEPARATOR ', ') as categories
FROM documents d
LEFT JOIN users u ON d.uploaded_by = u.id
LEFT JOIN document_category_rel dcr ON d.id = dcr.document_id
LEFT JOIN document_categories dc ON dcr.category_id = dc.id
WHERE d.deleted_at IS NULL
GROUP BY d.id
ORDER BY d.downloads DESC
LIMIT 20;

-- Vista de actividad reciente
CREATE OR REPLACE VIEW `v_document_recent_activity` AS
SELECT 
    dal.id,
    dal.document_id,
    d.title as document_title,
    dal.user_id,
    u.name as username,
    dal.action,
    dal.created_at,
    dal.details
FROM document_activity_log dal
LEFT JOIN documents d ON dal.document_id = d.id
LEFT JOIN users u ON dal.user_id = u.id
ORDER BY dal.created_at DESC
LIMIT 100;

-- Vista de documentos públicos activos
CREATE OR REPLACE VIEW `v_public_documents_active` AS
SELECT 
    d.id,
    d.title,
    d.file_name,
    d.file_size,
    d.public_token,
    d.token_expires_at,
    d.public_download_limit,
    d.public_downloads,
    d.public_created_at,
    d.public_last_access,
    u.name as created_by_name,
    CASE 
        WHEN d.public_download_limit IS NOT NULL AND d.public_downloads >= d.public_download_limit THEN 'limit_reached'
        WHEN d.token_expires_at IS NOT NULL AND d.token_expires_at < NOW() THEN 'expired'
        ELSE 'active'
    END as status,
    CASE 
        WHEN d.public_download_limit IS NOT NULL 
        THEN CONCAT(d.public_downloads, '/', d.public_download_limit)
        ELSE CONCAT(d.public_downloads, '/∞')
    END as download_stats
FROM documents d
LEFT JOIN users u ON d.public_created_by = u.id
WHERE d.public_enabled = TRUE
    AND d.deleted_at IS NULL;

-- Triggers para mantener contadores actualizados
DELIMITER //

DROP TRIGGER IF EXISTS `after_document_tag_insert`//
CREATE TRIGGER `after_document_tag_insert` AFTER INSERT ON `document_tag_rel`
FOR EACH ROW
BEGIN
    UPDATE document_tags SET usage_count = usage_count + 1 WHERE id = NEW.tag_id;
END//

DROP TRIGGER IF EXISTS `after_document_tag_delete`//
CREATE TRIGGER `after_document_tag_delete` AFTER DELETE ON `document_tag_rel`
FOR EACH ROW
BEGIN
    UPDATE document_tags SET usage_count = usage_count - 1 WHERE id = OLD.tag_id;
END//

-- Procedimiento para limpiar enlaces compartidos expirados
DROP PROCEDURE IF EXISTS `sp_cleanup_expired_shares`//
CREATE PROCEDURE `sp_cleanup_expired_shares`()
BEGIN
    UPDATE document_shares 
    SET is_active = FALSE 
    WHERE expires_at IS NOT NULL 
    AND expires_at < NOW() 
    AND is_active = TRUE;
    
    -- También desactivar los tokens públicos expirados
    UPDATE documents 
    SET public_enabled = FALSE
    WHERE public_enabled = TRUE
        AND token_expires_at IS NOT NULL
        AND token_expires_at < NOW();
    
    SELECT ROW_COUNT() as cleaned_shares;
END//

-- Procedimiento para mover documento a papelera
DROP PROCEDURE IF EXISTS `sp_trash_document`//
CREATE PROCEDURE `sp_trash_document`(IN doc_id INT, IN user_id INT)
BEGIN
    UPDATE documents 
    SET deleted_at = NOW(), deleted_by = user_id 
    WHERE id = doc_id AND deleted_at IS NULL;
    
    INSERT INTO document_activity_log (document_id, user_id, action, details)
    VALUES (doc_id, user_id, 'trashed', 'Documento movido a papelera');
    
    SELECT ROW_COUNT() > 0 as success;
END//

-- Procedimiento para restaurar documento de papelera
DROP PROCEDURE IF EXISTS `sp_restore_document`//
CREATE PROCEDURE `sp_restore_document`(IN doc_id INT, IN user_id INT)
BEGIN
    UPDATE documents 
    SET deleted_at = NULL, deleted_by = NULL 
    WHERE id = doc_id AND deleted_at IS NOT NULL;
    
    INSERT INTO document_activity_log (document_id, user_id, action, details)
    VALUES (doc_id, user_id, 'restore', 'Documento restaurado de papelera');
    
    SELECT ROW_COUNT() > 0 as success;
END//

-- Procedimiento para generar token público
DROP PROCEDURE IF EXISTS `sp_generate_public_token`//
CREATE PROCEDURE `sp_generate_public_token`(
    IN p_document_id INT,
    IN p_user_id INT,
    IN p_expires_at DATETIME,
    IN p_download_limit INT,
    OUT p_token VARCHAR(64)
)
BEGIN
    DECLARE v_token VARCHAR(64);
    DECLARE v_exists INT;
    
    -- Generar token único
    REPEAT
        SET v_token = SHA2(CONCAT(p_document_id, NOW(), RAND(), UUID()), 256);
        SELECT COUNT(*) INTO v_exists FROM documents WHERE public_token = v_token;
    UNTIL v_exists = 0 END REPEAT;
    
    -- Actualizar documento con el token
    UPDATE documents 
    SET public_token = v_token,
        token_expires_at = p_expires_at,
        public_download_limit = p_download_limit,
        public_downloads = 0,
        public_enabled = TRUE,
        public_created_at = NOW(),
        public_created_by = p_user_id,
        public_last_access = NULL
    WHERE id = p_document_id;
    
    INSERT INTO document_activity_log (document_id, action, user_id, details)
    VALUES (p_document_id, 'public_link_created', p_user_id, 
            JSON_OBJECT('expires_at', p_expires_at, 'download_limit', p_download_limit));
    
    SET p_token = v_token;
END//

-- Procedimiento para revocar enlace público
DROP PROCEDURE IF EXISTS `sp_revoke_public_token`//
CREATE PROCEDURE `sp_revoke_public_token`(
    IN p_document_id INT,
    IN p_user_id INT
)
BEGIN
    UPDATE documents 
    SET public_enabled = FALSE
    WHERE id = p_document_id;
    
    INSERT INTO document_activity_log (document_id, action, user_id, details)
    VALUES (p_document_id, 'public_link_revoked', p_user_id, NULL);
END//

-- Función para verificar si un token público es válido
DROP FUNCTION IF EXISTS `fn_is_public_token_valid`//
CREATE FUNCTION `fn_is_public_token_valid`(
    p_token VARCHAR(64)
) RETURNS BOOLEAN
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_valid BOOLEAN DEFAULT FALSE;
    
    SELECT 
        (public_enabled = TRUE
        AND deleted_at IS NULL
        AND (token_expires_at IS NULL OR token_expires_at > NOW())
        AND (public_download_limit IS NULL OR public_downloads < public_download_limit))
    INTO v_valid
    FROM documents
    WHERE public_token = p_token;
    
    RETURN COALESCE(v_valid, FALSE);
END//

DELIMITER ;
