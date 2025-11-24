-- Tabla expense_categories
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(30),
    is_active TINYINT(1) DEFAULT 1
)
ENGINE=InnoDB;
-- Tabla expenses
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
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL
)
ENGINE=InnoDB;
-- Tabla roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    display_name VARCHAR(100),
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_documents_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
)
ENGINE=InnoDB;
-- Tabla polls
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS member_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(30) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
ENGINE=InnoDB;
-- ...existing code...
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
    deactivated_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES member_categories(id) ON DELETE SET NULL,
    UNIQUE KEY unique_email (email)
)
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
)
ENGINE=InnoDB;
-- payments table moved to after members and events

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    active TINYINT(1) DEFAULT 1,
    status ENUM('active', 'inactive') DEFAULT 'active'
)
ENGINE=InnoDB;
-- Usuario admin por defecto (clave: admin)
INSERT INTO users (email, name, password, role, active, status) VALUES ('admin@admin.com', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 'active') ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    file_size INT DEFAULT 0,
    file_type VARCHAR(50),
    category VARCHAR(50),
    uploaded_user_id INT DEFAULT NULL,
    is_public TINYINT(1) DEFAULT 1,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_documents_uploaded_user FOREIGN KEY (uploaded_user_id) REFERENCES users(id) ON DELETE SET NULL
)
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS annual_fees (
    year INT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    event_type VARCHAR(50),
    color VARCHAR(30),
    description TEXT,
    location VARCHAR(255),
    date DATE NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    price DECIMAL(10, 2) DEFAULT 0.00,
    max_attendees INT DEFAULT NULL,
    requires_registration TINYINT(1) DEFAULT 0,
    registration_deadline DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla book_ads

CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    latitude DECIMAL(9,6) DEFAULT NULL,
    longitude DECIMAL(9,6) DEFAULT NULL,
    logo_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS book_ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    year YEAR NOT NULL,
    ad_type ENUM('media', 'full', 'cover', 'back_cover') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('paid', 'pending') DEFAULT 'pending',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_book_ads_donor FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    concept VARCHAR(255) NOT NULL,
    status ENUM('paid', 'pending') DEFAULT 'paid',
    fee_year INT DEFAULT NULL,
    payment_type ENUM('fee', 'event', 'donation') DEFAULT 'fee',
    event_id INT DEFAULT NULL,
    book_ad_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL,
    CONSTRAINT fk_payments_book_ad FOREIGN KEY (book_ad_id) REFERENCES book_ads(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('media','full','cover','back_cover') NOT NULL,
    year YEAR NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS ad_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year YEAR NOT NULL,
    type ENUM('media', 'full', 'cover', 'back_cover') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_year_type (year, type)
);

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES ('association_name', 'Mi Asociación') ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- Insert default admin user (password: admin123)
INSERT INTO users (email, name, password, role, active) VALUES 
('admin@admin.com', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1)
ON DUPLICATE KEY UPDATE id=id;
