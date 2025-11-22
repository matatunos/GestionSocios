-- Migration: Add expenses management system
-- Date: 2025-11-22

-- Create expense_categories table
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#ef4444',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_expense_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default expense categories
INSERT INTO expense_categories (name, description, color) VALUES
('Suministros', 'Agua, luz, gas, teléfono, internet', '#f59e0b'),
('Local', 'Alquiler, mantenimiento, reparaciones del local', '#3b82f6'),
('Eventos', 'Gastos relacionados con la organización de eventos', '#8b5cf6'),
('Material', 'Material de oficina, equipamiento', '#10b981'),
('Servicios Profesionales', 'Asesoría, gestoría, abogados', '#ef4444'),
('Marketing', 'Publicidad, imprenta, diseño', '#ec4899'),
('Seguros', 'Seguros de responsabilidad civil, local', '#6366f1'),
('Otros', 'Gastos diversos no categorizados', '#64748b');

-- Create expenses table
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    payment_method ENUM('cash', 'transfer', 'card', 'check') DEFAULT 'transfer',
    invoice_number VARCHAR(50),
    provider VARCHAR(150),
    notes TEXT,
    receipt_file VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_expense_date (expense_date),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add expense permissions
INSERT INTO permissions (name, display_name, description, module) VALUES
('expenses.view', 'Ver Gastos', 'Ver listado de gastos', 'expenses'),
('expenses.create', 'Registrar Gastos', 'Añadir nuevos gastos', 'expenses'),
('expenses.edit', 'Editar Gastos', 'Modificar gastos existentes', 'expenses'),
('expenses.delete', 'Eliminar Gastos', 'Eliminar registros de gastos', 'expenses'),
('expenses.export', 'Exportar Gastos', 'Exportar gastos a PDF/Excel', 'expenses');

-- Assign expense permissions to admin and treasurer
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name IN ('admin', 'treasurer') AND p.module = 'expenses';
