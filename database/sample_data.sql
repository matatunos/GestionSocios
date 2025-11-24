-- Datos de ejemplo para GestionSocios

-- Categorías de socios
INSERT INTO member_categories (name, description, color, is_active, display_order, default_fee) VALUES
('General', 'Socios generales', '#3498db', 1, 1, 20.00),
('Joven', 'Socios jóvenes', '#2ecc71', 1, 2, 10.00);

-- Socios
INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date) VALUES
('Juan', 'Pérez', '12345678A', 'juan.perez@example.com', '600123456', 'Calle Mayor 1', 1, 'active', '2022-01-15'),
('Ana', 'García', '87654321B', 'ana.garcia@example.com', '600654321', 'Calle Menor 2', 2, 'active', '2023-03-10'),
('Luis', 'Martín', '11223344C', 'luis.martin@example.com', '600987654', 'Avenida Central 3', 1, 'inactive', '2021-07-20');

-- Gastos
INSERT INTO expenses (category_id, description, amount, expense_date, payment_method, invoice_number, provider, created_by) VALUES
(1, 'Compra de material oficina', 120.50, '2023-02-10', 'transferencia', 'FAC001', 'Papelería S.A.', 1),
(2, 'Pago alquiler local', 800.00, '2023-02-01', 'domiciliación', 'FAC002', 'Inmobiliaria SL', 2);

-- Tareas
INSERT INTO tasks (title, description, assigned_to, status, due_date, category_id, created_by, priority) VALUES
('Preparar reunión anual', 'Organizar la asamblea de socios', 1, 'in_progress', '2023-12-01', 1, 1, 2),
('Actualizar base de datos', 'Revisar y actualizar datos de socios', 2, 'pending', '2023-11-20', 2, 2, 1);

-- Categorías de socios
INSERT INTO member_categories (name, description, color, is_active, display_order, default_fee) VALUES
('General', 'Socios generales', '#3498db', 1, 1, 20.00),
('Joven', 'Socios jóvenes', '#2ecc71', 1, 2, 10.00);

-- Categorías de tareas
INSERT INTO task_categories (name, color, icon, description) VALUES
('Administrativo', '#e67e22', 'fa-briefcase', 'Tareas administrativas'),
('Gestión', '#9b59b6', 'fa-tasks', 'Tareas de gestión');

-- Usuarios
INSERT INTO users (email, name, password, role, active, status) VALUES
('admin@admin.com', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 'active'),
('usuario@demo.com', 'Usuario Demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1, 'active');

-- Ejemplo de organización
INSERT INTO organization_settings (category, setting_key, setting_value, setting_type, description) VALUES
('General', 'nombre', 'Asociación Demo', 'string', 'Nombre de la organización'),
('General', 'email', 'info@demo.org', 'string', 'Email de contacto');
