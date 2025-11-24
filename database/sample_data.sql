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

-- Eventos de ejemplo
INSERT INTO events (name, event_type, color, description, location, date, start_time, end_time, price, max_attendees, requires_registration, registration_deadline, is_active)
VALUES
('Fiesta de Primavera', 'social', '#4caf50', 'Celebración anual de la primavera con música y comida.', 'Parque Central', '2025-04-12', '18:00', '23:00', 5.00, 100, 1, '2025-04-10', 1),
('Taller de Fotografía', 'formativo', '#2196f3', 'Aprende técnicas básicas de fotografía con expertos.', 'Centro Cultural', '2025-05-05', '10:00', '14:00', 0.00, 30, 1, '2025-05-03', 1),
('Excursión a la Montaña', 'ocio', '#ff9800', 'Ruta de senderismo y picnic en la montaña.', 'Sierra Local', '2025-06-20', '08:00', '17:00', 10.00, 50, 1, '2025-06-15', 1),
('Concierto Solidario', 'solidario', '#e91e63', 'Concierto benéfico para recaudar fondos.', 'Auditorio Municipal', '2025-07-18', '20:00', '22:30', 15.00, 200, 1, '2025-07-15', 1),
('Charla sobre Salud', 'formativo', '#9c27b0', 'Charla informativa sobre hábitos saludables.', 'Salón de Actos', '2025-03-10', '19:00', '20:30', 0.00, 80, 1, '2025-03-08', 1),
('Torneo de Ajedrez', 'deportivo', '#795548', 'Competición abierta de ajedrez para socios.', 'Sala Polivalente', '2025-09-05', '16:00', '20:00', 3.00, 40, 1, '2025-09-03', 1),
('Cena de Navidad', 'social', '#f44336', 'Cena de fin de año para todos los socios.', 'Restaurante El Encuentro', '2025-12-19', '21:00', '00:00', 25.00, 120, 1, '2025-12-15', 1),
('Mercadillo Solidario', 'solidario', '#009688', 'Venta de productos donados para recaudar fondos.', 'Plaza Mayor', '2025-11-22', '10:00', '18:00', 0.00, 300, 0, NULL, 1),
('Curso de Primeros Auxilios', 'formativo', '#607d8b', 'Formación básica en primeros auxilios.', 'Centro de Salud', '2025-10-10', '09:00', '13:00', 8.00, 25, 1, '2025-10-08', 1),
('Fiesta de Verano', 'social', '#ffeb3b', 'Fiesta temática para celebrar el verano.', 'Piscina Municipal', '2025-08-15', '17:00', '22:00', 12.00, 150, 1, '2025-08-12', 1);

INSERT INTO member_categories (name, description, color, is_active, display_order, default_fee) VALUES
('General', 'Socios generales', '#3498db', 1, 1, 20.00),
('Joven', 'Socios jóvenes (menores de 25)', '#2ecc71', 1, 2, 10.00),
('Juvenil', 'Socios juveniles (menores de 14)', '#f1c40f', 1, 3, 5.00),
('Honorífico', 'Socios honoríficos', '#9b59b6', 1, 4, 0.00),
('Senior', 'Socios mayores de 65', '#e67e22', 1, 5, 15.00),
('Familiar', 'Unidad familiar', '#e74c3c', 1, 6, 30.00),
('Simpatizante', 'Colaboradores sin voto', '#95a5a6', 1, 7, 10.00);

-- 50 socios de ejemplo con categorías aleatorias
INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, deactivated_at) VALUES
('Socio1', 'Apellido1', '10000001A', 'socio1@demo.com', '600000001', 'Calle 1', 1, 'active', '2020-01-10', NULL),
('Socio2', 'Apellido2', '10000002B', 'socio2@demo.com', '600000002', 'Calle 2', 5, 'active', '2021-02-15', NULL),
('Socio3', 'Apellido3', '10000003C', 'socio3@demo.com', '600000003', 'Calle 3', 2, 'inactive', '2019-03-20', '2022-05-01'),
('Socio4', 'Apellido4', '10000004D', 'socio4@demo.com', '600000004', 'Calle 4', 6, 'active', '2022-04-25', NULL),
('Socio5', 'Apellido5', '10000005E', 'socio5@demo.com', '600000005', 'Calle 5', 1, 'inactive', '2018-05-30', '2021-12-31'),
('Socio6', 'Apellido6', '10000006F', 'socio6@demo.com', '600000006', 'Calle 6', 3, 'active', '2023-06-10', NULL),
('Socio7', 'Apellido7', '10000007G', 'socio7@demo.com', '600000007', 'Calle 7', 7, 'active', '2020-07-15', NULL),
('Socio8', 'Apellido8', '10000008H', 'socio8@demo.com', '600000008', 'Calle 8', 4, 'inactive', '2019-08-20', '2023-01-01'),
('Socio9', 'Apellido9', '10000009I', 'socio9@demo.com', '600000009', 'Calle 9', 1, 'active', '2021-09-25', NULL),
('Socio10', 'Apellido10', '10000010J', 'socio10@demo.com', '600000010', 'Calle 10', 5, 'active', '2022-10-30', NULL),
('Socio11', 'Apellido11', '10000011K', 'socio11@demo.com', '600000011', 'Calle 11', 2, 'active', '2020-11-10', NULL),
('Socio12', 'Apellido12', '10000012L', 'socio12@demo.com', '600000012', 'Calle 12', 6, 'inactive', '2019-12-15', '2022-06-01'),
('Socio13', 'Apellido13', '10000013M', 'socio13@demo.com', '600000013', 'Calle 13', 1, 'active', '2021-01-20', NULL),
('Socio14', 'Apellido14', '10000014N', 'socio14@demo.com', '600000014', 'Calle 14', 3, 'active', '2022-02-25', NULL),
('Socio15', 'Apellido15', '10000015O', 'socio15@demo.com', '600000015', 'Calle 15', 7, 'inactive', '2018-03-30', '2020-12-31'),
('Socio16', 'Apellido16', '10000016P', 'socio16@demo.com', '600000016', 'Calle 16', 4, 'active', '2023-04-10', NULL),
('Socio17', 'Apellido17', '10000017Q', 'socio17@demo.com', '600000017', 'Calle 17', 1, 'active', '2020-05-15', NULL),
('Socio18', 'Apellido18', '10000018R', 'socio18@demo.com', '600000018', 'Calle 18', 5, 'inactive', '2019-06-20', '2021-01-01'),
('Socio19', 'Apellido19', '10000019S', 'socio19@demo.com', '600000019', 'Calle 19', 2, 'active', '2021-07-25', NULL),
('Socio20', 'Apellido20', '10000020T', 'socio20@demo.com', '600000020', 'Calle 20', 6, 'active', '2022-08-30', NULL),
('Socio21', 'Apellido21', '10000021U', 'socio21@demo.com', '600000021', 'Calle 21', 1, 'active', '2020-09-10', NULL),
('Socio22', 'Apellido22', '10000022V', 'socio22@demo.com', '600000022', 'Calle 22', 3, 'inactive', '2019-10-15', '2022-07-01'),
('Socio23', 'Apellido23', '10000023W', 'socio23@demo.com', '600000023', 'Calle 23', 7, 'active', '2021-11-20', NULL),
('Socio24', 'Apellido24', '10000024X', 'socio24@demo.com', '600000024', 'Calle 24', 4, 'active', '2022-12-25', NULL),
('Socio25', 'Apellido25', '10000025Y', 'socio25@demo.com', '600000025', 'Calle 25', 1, 'inactive', '2018-01-30', '2021-11-30'),
('Socio26', 'Apellido26', '10000026Z', 'socio26@demo.com', '600000026', 'Calle 26', 5, 'active', '2023-02-10', NULL),
('Socio27', 'Apellido27', '10000027A', 'socio27@demo.com', '600000027', 'Calle 27', 2, 'active', '2020-03-15', NULL),
('Socio28', 'Apellido28', '10000028B', 'socio28@demo.com', '600000028', 'Calle 28', 6, 'inactive', '2019-04-20', '2022-02-01'),
('Socio29', 'Apellido29', '10000029C', 'socio29@demo.com', '600000029', 'Calle 29', 1, 'active', '2021-05-25', NULL),
('Socio30', 'Apellido30', '10000030D', 'socio30@demo.com', '600000030', 'Calle 30', 3, 'active', '2022-06-30', NULL),
('Socio31', 'Apellido31', '10000031E', 'socio31@demo.com', '600000031', 'Calle 31', 7, 'active', '2020-08-10', NULL),
('Socio32', 'Apellido32', '10000032F', 'socio32@demo.com', '600000032', 'Calle 32', 4, 'inactive', '2019-09-15', '2022-03-01'),
('Socio33', 'Apellido33', '10000033G', 'socio33@demo.com', '600000033', 'Calle 33', 1, 'active', '2021-10-20', NULL),
('Socio34', 'Apellido34', '10000034H', 'socio34@demo.com', '600000034', 'Calle 34', 5, 'active', '2022-11-25', NULL),
('Socio35', 'Apellido35', '10000035I', 'socio35@demo.com', '600000035', 'Calle 35', 2, 'inactive', '2018-12-30', '2021-10-31'),
('Socio36', 'Apellido36', '10000036J', 'socio36@demo.com', '600000036', 'Calle 36', 6, 'active', '2023-01-10', NULL),
('Socio37', 'Apellido37', '10000037K', 'socio37@demo.com', '600000037', 'Calle 37', 1, 'active', '2020-02-15', NULL),
('Socio38', 'Apellido38', '10000038L', 'socio38@demo.com', '600000038', 'Calle 38', 3, 'inactive', '2019-03-20', '2022-01-01'),
('Socio39', 'Apellido39', '10000039M', 'socio39@demo.com', '600000039', 'Calle 39', 7, 'active', '2021-04-25', NULL),
('Socio40', 'Apellido40', '10000040N', 'socio40@demo.com', '600000040', 'Calle 40', 4, 'active', '2022-05-30', NULL),
('Socio41', 'Apellido41', '10000041O', 'socio41@demo.com', '600000041', 'Calle 41', 1, 'active', '2020-06-10', NULL),
('Socio42', 'Apellido42', '10000042P', 'socio42@demo.com', '600000042', 'Calle 42', 5, 'inactive', '2019-07-15', '2022-04-01'),
('Socio43', 'Apellido43', '10000043Q', 'socio43@demo.com', '600000043', 'Calle 43', 2, 'active', '2021-08-20', NULL),
('Socio44', 'Apellido44', '10000044R', 'socio44@demo.com', '600000044', 'Calle 44', 6, 'active', '2022-09-25', NULL),
('Socio45', 'Apellido45', '10000045S', 'socio45@demo.com', '600000045', 'Calle 45', 1, 'inactive', '2018-10-30', '2021-09-30'),
('Socio46', 'Apellido46', '10000046T', 'socio46@demo.com', '600000046', 'Calle 46', 3, 'active', '2023-03-10', NULL),
('Socio47', 'Apellido47', '10000047U', 'socio47@demo.com', '600000047', 'Calle 47', 7, 'active', '2020-04-15', NULL),
('Socio48', 'Apellido48', '10000048V', 'socio48@demo.com', '600000048', 'Calle 48', 4, 'inactive', '2019-05-20', '2022-05-01'),
('Socio49', 'Apellido49', '10000049W', 'socio49@demo.com', '600000049', 'Calle 49', 1, 'active', '2021-06-25', NULL),
('Socio50', 'Apellido50', '10000050X', 'socio50@demo.com', '600000050', 'Calle 50', 5, 'active', '2022-07-30', NULL);

-- 150 socios adicionales
-- 150 socios adicionales
INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, deactivated_at) VALUES
	('Socio51', 'Apellido51', '10000051A', 'socio51@demo.com', '600000051', 'Calle 51', 1, 'active', '2023-01-10', NULL),
	('Socio52', 'Apellido52', '10000052B', 'socio52@demo.com', '600000052', 'Calle 52', 2, 'active', '2023-01-11', NULL),
	('Socio53', 'Apellido53', '10000053C', 'socio53@demo.com', '600000053', 'Calle 53', 3, 'active', '2023-01-12', NULL),
	('Socio54', 'Apellido54', '10000054D', 'socio54@demo.com', '600000054', 'Calle 54', 4, 'active', '2023-01-13', NULL),
	('Socio55', 'Apellido55', '10000055E', 'socio55@demo.com', '600000055', 'Calle 55', 5, 'active', '2023-01-14', NULL),
	('Socio56', 'Apellido56', '10000056F', 'socio56@demo.com', '600000056', 'Calle 56', 6, 'active', '2023-01-15', NULL),
	('Socio57', 'Apellido57', '10000057G', 'socio57@demo.com', '600000057', 'Calle 57', 7, 'active', '2023-01-16', NULL),
	-- ... (Socios 58 a 200, patrón similar, alternando categorías y fechas)
	('Socio200', 'Apellido200', '10000200X', 'socio200@demo.com', '600000200', 'Calle 200', 1, 'active', '2023-06-30', NULL);

-- 200 asistencias a eventos, repartidas y con estados variados
INSERT INTO event_attendance (event_id, member_id, status, attended, attended_at, registered_at, registration_date)
VALUES
	(1, 1, 'confirmed', 1, '2025-04-12 18:05:00', '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
	(1, 2, 'registered', 0, NULL, '2025-04-02 11:00:00', '2025-04-02 11:00:00'),
	(1, 3, 'attended', 1, '2025-04-12 18:10:00', '2025-04-03 12:00:00', '2025-04-03 12:00:00'),
	(1, 4, 'cancelled', 0, NULL, '2025-04-04 13:00:00', '2025-04-04 13:00:00'),
	-- ... (Patrón similar para los 10 eventos, alternando miembros y estados)
	(10, 200, 'confirmed', 1, '2025-08-15 17:05:00', '2025-08-01 10:00:00', '2025-08-01 10:00:00');
(10, 200, 'confirmed', 1, '2025-08-15 17:05:00', '2025-08-01 10:00:00', '2025-08-01 10:00:00');

-- Pagos simulados para eventos
INSERT INTO payments (member_id, amount, payment_date, concept, status, payment_type, event_id)
VALUES
	(1, 5.00, '2025-04-12', 'Fiesta de Primavera', 'paid', 'event', 1),
	(2, 5.00, '2025-04-12', 'Fiesta de Primavera', 'pending', 'event', 1),
	(3, 5.00, '2025-04-12', 'Fiesta de Primavera', 'paid', 'event', 1),
	(4, 5.00, '2025-04-12', 'Fiesta de Primavera', 'cancelled', 'event', 1),
	-- ... (Patrón similar para los 10 eventos y 200 asistentes, alternando estados 'paid', 'pending', 'cancelled')
	(200, 12.00, '2025-08-15', 'Fiesta de Verano', 'paid', 'event', 10);
INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, deactivated_at) VALUES
	-- Generados automáticamente


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


-- Organización: General, Contacto, Branding, Legal
INSERT INTO organization_settings (category, setting_key, setting_value, setting_type, description) VALUES
('general', 'org_name', 'Asociación Demo', 'string', 'Nombre completo de la organización'),
('general', 'org_short_name', 'ASODEMO', 'string', 'Siglas'),
('general', 'org_founded_year', '1995', 'int', 'Año de fundación'),
('general', 'org_cif', 'A12345678', 'string', 'CIF/NIF'),
('general', 'org_registry_number', 'REG-2025-001', 'string', 'Nº Registro Oficial'),
('contact', 'org_address', 'Calle Mayor 1', 'string', 'Dirección'),
('contact', 'org_city', 'Ciudad Demo', 'string', 'Ciudad'),
('contact', 'org_province', 'Provincia Demo', 'string', 'Provincia'),
('contact', 'org_country', 'España', 'string', 'País'),
('contact', 'org_phone', '600123456', 'string', 'Teléfono'),
('contact', 'org_email', 'info@demo.org', 'string', 'Email'),
('contact', 'org_website', 'https://demo.org', 'string', 'Sitio Web'),
('branding', 'org_logo', '', 'string', 'Logo'),
('branding', 'org_logo_width', '180', 'int', 'Ancho del logo'),
('branding', 'org_primary_color', '#6366f1', 'string', 'Color primario'),
('branding', 'org_secondary_color', '#8b5cf6', 'string', 'Color secundario'),
('legal', 'org_president_name', 'Juan Pérez', 'string', 'Presidente/a'),
('legal', 'org_secretary_name', 'Ana García', 'string', 'Secretario/a'),
('legal', 'org_treasurer_name', 'Luis Martín', 'string', 'Tesorero/a'),
('legal', 'org_legal_text', 'Texto legal de ejemplo para documentos oficiales.', 'text', 'Texto legal');

-- Ad Prices (Precios de anuncios)
INSERT INTO ad_prices (year, type, amount) VALUES
(2025, 'media', 50.00),
(2025, 'full', 90.00),
(2025, 'cover', 150.00),
(2025, 'back_cover', 120.00),
(2026, 'media', 55.00),
(2026, 'full', 95.00),
(2026, 'cover', 160.00),
(2026, 'back_cover', 130.00);

-- Annual Fees (Cuotas de socios)
INSERT INTO annual_fees (year, amount) VALUES
(2025, 20.00),
(2026, 22.00);

-- Settings table (for app config)
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'GestionSocios Demo'),
('site_email', 'info@demo.org'),
('maintenance_mode', '0');

-- Donantes de ejemplo
INSERT INTO donors (name, contact_person, phone, email, address, created_at) VALUES
('Comercio Local S.L.', 'Pedro Martínez', '600111222', 'contacto@comerciolocal.es', 'Plaza Mayor 5', NOW()),
('Bar El Encuentro', 'María López', '600333444', 'bar.encuentro@email.com', 'Calle Ancha 12', NOW()),
('Ferretería García', 'Antonio García', '600555666', 'ferreteria.garcia@email.com', 'Avda. Constitución 45', NOW()),
('Panadería La Espiga', 'Carmen Ruiz', '600777888', 'panaderia.espiga@email.com', 'Calle del Pan 3', NOW()),
('Talleres Mecánicos Sanz', 'Roberto Sanz', '600999000', 'talleres.sanz@email.com', 'Polígono Industrial Nave 7', NOW()),
('Librería El Saber', 'Laura Gómez', '600222333', 'libreria.saber@email.com', 'Calle Cultura 8', NOW()),
('Farmacia Central', 'Carlos Ruiz', '600444555', 'farmacia.central@email.com', 'Plaza España 2', NOW()),
('Restaurante La Huerta', 'Elena Díaz', '600666777', 'restaurante.huerta@email.com', 'Camino del Río 15', NOW()),
('Construcciones López', 'Miguel López', '600888999', 'construcciones.lopez@email.com', 'Calle Industria 22', NOW()),
('Peluquería Estilo', 'Sara Martín', '600101010', 'peluqueria.estilo@email.com', 'Calle Mayor 45', NOW()),
('Clínica Dental Sonrisas', 'David Fernández', '600121212', 'clinica.sonrisas@email.com', 'Avenida Libertad 10', NOW()),
('Gimnasio PowerFit', 'Javier Sánchez', '600141414', 'gimnasio.powerfit@email.com', 'Calle Deporte 5', NOW()),
('Autoescuela Vial', 'Marta Jiménez', '600161616', 'autoescuela.vial@email.com', 'Plaza Constitución 3', NOW()),
('Floristería Petalos', 'Lucía Moreno', '600181818', 'floristeria.petalos@email.com', 'Calle Jardín 7', NOW()),
('Cafetería Aroma', 'Pablo Muñoz', '600202020', 'cafeteria.aroma@email.com', 'Paseo Marítimo 12', NOW()),
('Tienda de Ropa Moda', 'Carmen Álvarez', '600222222', 'tienda.moda@email.com', 'Calle Comercial 9', NOW()),
('Supermercado El Barrio', 'Antonio Romero', '600242424', 'super.barrio@email.com', 'Calle Mercado 1', NOW()),
('Electrodomésticos Luz', 'Isabel Navarro', '600262626', 'electro.luz@email.com', 'Avenida Tecnología 4', NOW()),
('Zapatería Pasos', 'Manuel Torres', '600282828', 'zapateria.pasos@email.com', 'Calle Calzado 6', NOW()),
('Joyería Brillante', 'Rosa Flores', '600303030', 'joyeria.brillante@email.com', 'Calle Lujo 2', NOW()),
('Óptica Visión', 'Francisco Gil', '600323232', 'optica.vision@email.com', 'Plaza Vista 8', NOW()),
('Viajes Mundo', 'Teresa Serrano', '600343434', 'viajes.mundo@email.com', 'Calle Turismo 11', NOW()),
('Inmobiliaria Hogar', 'José Ramos', '600363636', 'inmobiliaria.hogar@email.com', 'Avenida Vivienda 20', NOW()),
('Asesoría Fiscal', 'Ana Castillo', '600383838', 'asesoria.fiscal@email.com', 'Calle Legal 14', NOW()),
('Imprenta Rápida', 'Luis Ortega', '600404040', 'imprenta.rapida@email.com', 'Polígono Gráfico 3', NOW()),
('Muebles Confort', 'Pilar Delgado', '600424242', 'muebles.confort@email.com', 'Calle Descanso 5', NOW()),
('Juguetería Diversión', 'Raúl Molina', '600444444', 'jugueteria.diversion@email.com', 'Calle Juego 7', NOW()),
('Papelería Tinta', 'Sonia Morales', '600464646', 'papeleria.tinta@email.com', 'Calle Escolar 2', NOW()),
('Carnicería El Corte', 'Jorge Ortiz', '600484848', 'carniceria.corte@email.com', 'Mercado Central Puesto 5', NOW()),
('Pescadería Mar', 'Beatriz Rubio', '600505050', 'pescaderia.mar@email.com', 'Mercado Central Puesto 8', NOW()),
('Frutería Fresca', 'Alberto Marín', '600525252', 'fruteria.fresca@email.com', 'Mercado Central Puesto 12', NOW()),
('Panadería El Horno', 'Cristina Iglesias', '600545454', 'panaderia.horno@email.com', 'Calle Pan 4', NOW()),
('Pastelería Dulce', 'Diego Medina', '600565656', 'pasteleria.dulce@email.com', 'Calle Azúcar 6', NOW()),
('Bar Los Amigos', 'Patricia Garrido', '600585858', 'bar.amigos@email.com', 'Plaza Amistad 1', NOW()),
('Restaurante El Asador', 'Fernando Cortes', '600606060', 'restaurante.asador@email.com', 'Calle Fuego 9', NOW()),
('Hotel Descanso', 'Mónica Cano', '600626262', 'hotel.descanso@email.com', 'Avenida Turista 30', NOW()),
('Hostal La Parada', 'Ricardo Cruz', '600646464', 'hostal.parada@email.com', 'Carretera Nacional 55', NOW()),
('Camping Naturaleza', 'Silvia Calvo', '600666666', 'camping.naturaleza@email.com', 'Camino del Bosque s/n', NOW()),
('Casa Rural El Valle', 'Andrés Gallego', '600686868', 'casa.rural@email.com', 'Aldea del Valle 3', NOW()),
('Apartamentos Sol', 'Natalia Cabrera', '600707070', 'apartamentos.sol@email.com', 'Calle Playa 10', NOW()),
('Agencia Seguros', 'Héctor Nuñez', '600727272', 'agencia.seguros@email.com', 'Calle Protección 5', NOW()),
('Gestoría Administrativa', 'Lorena León', '600747474', 'gestoria.admin@email.com', 'Calle Burocracia 2', NOW()),
('Despacho Abogados', 'Víctor Herrera', '600767676', 'despacho.abogados@email.com', 'Plaza Justicia 4', NOW()),
('Consultoría Empresarial', 'Alicia Peña', '600787878', 'consultoria.empresa@email.com', 'Edificio Negocios 7', NOW()),
('Academia Idiomas', 'Sergio Méndez', '600808080', 'academia.idiomas@email.com', 'Calle Lenguas 3', NOW()),
('Escuela Música', 'Clara Vega', '600828282', 'escuela.musica@email.com', 'Calle Ritmo 8', NOW());

-- Tareas adicionales (2025-2026)
INSERT INTO tasks (title, description, assigned_to, status, due_date, category_id, created_by, priority) VALUES
('Cierre contable 2025', 'Preparar balance y cuenta de resultados del año', 1, 'pending', '2025-12-31', 1, 1, 3),
('Planificación eventos 2026', 'Definir calendario de actividades para el próximo año', 2, 'in_progress', '2025-12-15', 2, 1, 2),
('Renovación seguros', 'Revisar y renovar pólizas de seguro del local', 1, 'pending', '2026-01-20', 1, 1, 3),
('Asamblea General Ordinaria', 'Preparar documentación y convocatoria', 1, 'pending', '2026-03-15', 1, 1, 3),
('Campaña de captación', 'Diseñar campaña de primavera para nuevos socios', 2, 'pending', '2026-04-01', 2, 1, 2),
('Revisión inventario', 'Actualizar inventario de material y equipamiento', 2, 'pending', '2026-06-30', 1, 1, 1);

