-- Vaciado automático de tablas antes de insertar datos de prueba
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE payments;
TRUNCATE TABLE expenses;
TRUNCATE TABLE expense_categories;
TRUNCATE TABLE members;
TRUNCATE TABLE member_categories;
TRUNCATE TABLE tasks;
TRUNCATE TABLE task_categories;
TRUNCATE TABLE users;
TRUNCATE TABLE notifications;
TRUNCATE TABLE organization_settings;
TRUNCATE TABLE roles;
TRUNCATE TABLE polls;
TRUNCATE TABLE conversations;
TRUNCATE TABLE messages;
TRUNCATE TABLE conversation_participants;
TRUNCATE TABLE documents;
TRUNCATE TABLE annual_fees;
TRUNCATE TABLE events;
TRUNCATE TABLE donors;
TRUNCATE TABLE book_ads;
TRUNCATE TABLE donations;
TRUNCATE TABLE ad_prices;
TRUNCATE TABLE settings;
SET FOREIGN_KEY_CHECKS = 1;


INSERT INTO member_categories (name, description, color, is_active, display_order, default_fee) VALUES
('General', 'Socios generales', '#3498db', 1, 1, 20.00),
('Joven', 'Socios jóvenes', '#2ecc71', 1, 2, 10.00);

-- 50 socios de ejemplo
INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, deactivated_at) VALUES
('Socio1', 'Apellido1', '10000001A', 'socio1@demo.com', '600000001', 'Calle 1', 1, 'active', '2020-01-10', NULL),
('Socio2', 'Apellido2', '10000002B', 'socio2@demo.com', '600000002', 'Calle 2', 2, 'active', '2021-02-15', NULL),
('Socio3', 'Apellido3', '10000003C', 'socio3@demo.com', '600000003', 'Calle 3', 1, 'inactive', '2019-03-20', '2022-05-01'),
('Socio4', 'Apellido4', '10000004D', 'socio4@demo.com', '600000004', 'Calle 4', 2, 'active', '2022-04-25', NULL),
('Socio5', 'Apellido5', '10000005E', 'socio5@demo.com', '600000005', 'Calle 5', 1, 'inactive', '2018-05-30', '2021-12-31'),
('Socio6', 'Apellido6', '10000006F', 'socio6@demo.com', '600000006', 'Calle 6', 2, 'active', '2023-06-10', NULL),
('Socio7', 'Apellido7', '10000007G', 'socio7@demo.com', '600000007', 'Calle 7', 1, 'active', '2020-07-15', NULL),
('Socio8', 'Apellido8', '10000008H', 'socio8@demo.com', '600000008', 'Calle 8', 2, 'inactive', '2019-08-20', '2023-01-01'),
('Socio9', 'Apellido9', '10000009I', 'socio9@demo.com', '600000009', 'Calle 9', 1, 'active', '2021-09-25', NULL),
('Socio10', 'Apellido10', '10000010J', 'socio10@demo.com', '600000010', 'Calle 10', 2, 'active', '2022-10-30', NULL),
('Socio11', 'Apellido11', '10000011K', 'socio11@demo.com', '600000011', 'Calle 11', 1, 'active', '2020-11-10', NULL),
('Socio12', 'Apellido12', '10000012L', 'socio12@demo.com', '600000012', 'Calle 12', 2, 'inactive', '2019-12-15', '2022-06-01'),
('Socio13', 'Apellido13', '10000013M', 'socio13@demo.com', '600000013', 'Calle 13', 1, 'active', '2021-01-20', NULL),
('Socio14', 'Apellido14', '10000014N', 'socio14@demo.com', '600000014', 'Calle 14', 2, 'active', '2022-02-25', NULL),
('Socio15', 'Apellido15', '10000015O', 'socio15@demo.com', '600000015', 'Calle 15', 1, 'inactive', '2018-03-30', '2020-12-31'),
('Socio16', 'Apellido16', '10000016P', 'socio16@demo.com', '600000016', 'Calle 16', 2, 'active', '2023-04-10', NULL),
('Socio17', 'Apellido17', '10000017Q', 'socio17@demo.com', '600000017', 'Calle 17', 1, 'active', '2020-05-15', NULL),
('Socio18', 'Apellido18', '10000018R', 'socio18@demo.com', '600000018', 'Calle 18', 2, 'inactive', '2019-06-20', '2021-01-01'),
('Socio19', 'Apellido19', '10000019S', 'socio19@demo.com', '600000019', 'Calle 19', 1, 'active', '2021-07-25', NULL),
('Socio20', 'Apellido20', '10000020T', 'socio20@demo.com', '600000020', 'Calle 20', 2, 'active', '2022-08-30', NULL),
('Socio21', 'Apellido21', '10000021U', 'socio21@demo.com', '600000021', 'Calle 21', 1, 'active', '2020-09-10', NULL),
('Socio22', 'Apellido22', '10000022V', 'socio22@demo.com', '600000022', 'Calle 22', 2, 'inactive', '2019-10-15', '2022-07-01'),
('Socio23', 'Apellido23', '10000023W', 'socio23@demo.com', '600000023', 'Calle 23', 1, 'active', '2021-11-20', NULL),
('Socio24', 'Apellido24', '10000024X', 'socio24@demo.com', '600000024', 'Calle 24', 2, 'active', '2022-12-25', NULL),
('Socio25', 'Apellido25', '10000025Y', 'socio25@demo.com', '600000025', 'Calle 25', 1, 'inactive', '2018-01-30', '2021-11-30'),
('Socio26', 'Apellido26', '10000026Z', 'socio26@demo.com', '600000026', 'Calle 26', 2, 'active', '2023-02-10', NULL),
('Socio27', 'Apellido27', '10000027A', 'socio27@demo.com', '600000027', 'Calle 27', 1, 'active', '2020-03-15', NULL),
('Socio28', 'Apellido28', '10000028B', 'socio28@demo.com', '600000028', 'Calle 28', 2, 'inactive', '2019-04-20', '2022-02-01'),
('Socio29', 'Apellido29', '10000029C', 'socio29@demo.com', '600000029', 'Calle 29', 1, 'active', '2021-05-25', NULL),
('Socio30', 'Apellido30', '10000030D', 'socio30@demo.com', '600000030', 'Calle 30', 2, 'active', '2022-06-30', NULL),
('Socio31', 'Apellido31', '10000031E', 'socio31@demo.com', '600000031', 'Calle 31', 1, 'active', '2020-08-10', NULL),
('Socio32', 'Apellido32', '10000032F', 'socio32@demo.com', '600000032', 'Calle 32', 2, 'inactive', '2019-09-15', '2022-03-01'),
('Socio33', 'Apellido33', '10000033G', 'socio33@demo.com', '600000033', 'Calle 33', 1, 'active', '2021-10-20', NULL),
('Socio34', 'Apellido34', '10000034H', 'socio34@demo.com', '600000034', 'Calle 34', 2, 'active', '2022-11-25', NULL),
('Socio35', 'Apellido35', '10000035I', 'socio35@demo.com', '600000035', 'Calle 35', 1, 'inactive', '2018-12-30', '2021-10-31'),
('Socio36', 'Apellido36', '10000036J', 'socio36@demo.com', '600000036', 'Calle 36', 2, 'active', '2023-01-10', NULL),
('Socio37', 'Apellido37', '10000037K', 'socio37@demo.com', '600000037', 'Calle 37', 1, 'active', '2020-02-15', NULL),
('Socio38', 'Apellido38', '10000038L', 'socio38@demo.com', '600000038', 'Calle 38', 2, 'inactive', '2019-03-20', '2022-01-01'),
('Socio39', 'Apellido39', '10000039M', 'socio39@demo.com', '600000039', 'Calle 39', 1, 'active', '2021-04-25', NULL),
('Socio40', 'Apellido40', '10000040N', 'socio40@demo.com', '600000040', 'Calle 40', 2, 'active', '2022-05-30', NULL),
('Socio41', 'Apellido41', '10000041O', 'socio41@demo.com', '600000041', 'Calle 41', 1, 'active', '2020-06-10', NULL),
('Socio42', 'Apellido42', '10000042P', 'socio42@demo.com', '600000042', 'Calle 42', 2, 'inactive', '2019-07-15', '2022-04-01'),
('Socio43', 'Apellido43', '10000043Q', 'socio43@demo.com', '600000043', 'Calle 43', 1, 'active', '2021-08-20', NULL),
('Socio44', 'Apellido44', '10000044R', 'socio44@demo.com', '600000044', 'Calle 44', 2, 'active', '2022-09-25', NULL),
('Socio45', 'Apellido45', '10000045S', 'socio45@demo.com', '600000045', 'Calle 45', 1, 'inactive', '2018-10-30', '2021-09-30'),
('Socio46', 'Apellido46', '10000046T', 'socio46@demo.com', '600000046', 'Calle 46', 2, 'active', '2023-03-10', NULL),
('Socio47', 'Apellido47', '10000047U', 'socio47@demo.com', '600000047', 'Calle 47', 1, 'active', '2020-04-15', NULL),
('Socio48', 'Apellido48', '10000048V', 'socio48@demo.com', '600000048', 'Calle 48', 2, 'inactive', '2019-05-20', '2022-05-01'),
('Socio49', 'Apellido49', '10000049W', 'socio49@demo.com', '600000049', 'Calle 49', 1, 'active', '2021-06-25', NULL),
('Socio50', 'Apellido50', '10000050X', 'socio50@demo.com', '600000050', 'Calle 50', 2, 'active', '2022-07-30', NULL);

-- Pagos de ejemplo para los socios
INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type) VALUES
(1, 20.00, '2022-02-01', 'Cuota anual', 'paid', 2022, 'fee'),
(2, 10.00, '2023-03-15', 'Cuota anual', 'paid', 2023, 'fee'),
(3, 20.00, '2021-04-01', 'Cuota anual', 'paid', 2021, 'fee'),
(4, 10.00, '2022-05-10', 'Cuota anual', 'paid', 2022, 'fee'),
(5, 20.00, '2019-06-01', 'Cuota anual', 'paid', 2019, 'fee'),
(6, 10.00, '2023-07-01', 'Cuota anual', 'paid', 2023, 'fee'),
(7, 20.00, '2020-08-01', 'Cuota anual', 'paid', 2020, 'fee'),
(8, 10.00, '2020-09-01', 'Cuota anual', 'paid', 2020, 'fee'),
(9, 20.00, '2021-10-01', 'Cuota anual', 'paid', 2021, 'fee'),
(10, 10.00, '2022-11-01', 'Cuota anual', 'paid', 2022, 'fee');
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


-- Categorías de gastos
INSERT INTO expense_categories (conversation_id, name, description, color, is_active) VALUES
(1, 'Material Oficina', 'Gastos de material de oficina', '#e67e22', 1),
(2, 'Alquiler', 'Pago de alquiler del local', '#9b59b6', 1);

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
