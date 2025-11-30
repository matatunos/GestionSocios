-- ============================================
-- Datos de Ejemplo para Sistema de Gestión de Socios
-- ============================================
-- IMPORTANTE: Este archivo debe ejecutarse DESPUÉS de schema.sql

-- Deshabilitar verificación de foreign keys temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar tablas existentes (en orden inverso a las dependencias)
TRUNCATE TABLE event_attendance;
TRUNCATE TABLE payments;
TRUNCATE TABLE donations;
TRUNCATE TABLE book_ads;
TRUNCATE TABLE expenses;
TRUNCATE TABLE tasks;
TRUNCATE TABLE notifications;
TRUNCATE TABLE messages;
TRUNCATE TABLE conversation_participants;
TRUNCATE TABLE conversations;
TRUNCATE TABLE document_permissions;
TRUNCATE TABLE documents;
TRUNCATE TABLE members;
TRUNCATE TABLE events;
TRUNCATE TABLE donors;
TRUNCATE TABLE category_fee_history;
TRUNCATE TABLE member_categories;
TRUNCATE TABLE expense_categories;
TRUNCATE TABLE task_categories;
TRUNCATE TABLE ad_prices;
TRUNCATE TABLE annual_fees;
TRUNCATE TABLE organization_settings;

-- Reactivar verificación de foreign keys
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- CONFIGURACIÓN DE LA ORGANIZACIÓN
-- ============================================

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

-- ============================================
-- CUOTAS Y PRECIOS
-- ============================================

-- Cuotas anuales
INSERT INTO annual_fees (year, amount) VALUES
(2023, 18.00),
(2024, 19.00),
(2025, 20.00),
(2026, 22.00);

-- Precios de anuncios del libro de fiestas
INSERT INTO ad_prices (year, type, amount) VALUES
(2024, 'media', 45.00),
(2024, 'full', 85.00),
(2024, 'cover', 140.00),
(2024, 'back_cover', 110.00),
(2025, 'media', 50.00),
(2025, 'full', 90.00),
(2025, 'cover', 150.00),
(2025, 'back_cover', 120.00),
(2026, 'media', 55.00),
(2026, 'full', 95.00),
(2026, 'cover', 160.00),
(2026, 'back_cover', 130.00);

-- ============================================
-- CATEGORÍAS
-- ============================================

-- Categorías de socios
INSERT INTO member_categories (name, description, color, is_active, display_order, default_fee) VALUES
('General', 'Socios generales', '#3498db', 1, 1, 20.00),
('Joven', 'Socios jóvenes (menores de 25)', '#2ecc71', 1, 2, 10.00),
('Juvenil', 'Socios juveniles (menores de 14)', '#f1c40f', 1, 3, 5.00),
('Honorífico', 'Socios honoríficos', '#9b59b6', 1, 4, 0.00),
('Senior', 'Socios mayores de 65', '#e67e22', 1, 5, 15.00),
('Familiar', 'Unidad familiar', '#e74c3c', 1, 6, 30.00),
('Simpatizante', 'Colaboradores sin voto', '#95a5a6', 1, 7, 10.00);

-- Categorías de gastos
INSERT INTO expense_categories (name, description, color, is_active) VALUES
('Material Oficina', 'Gastos de material de oficina', '#e67e22', 1),
('Alquiler', 'Pago de alquiler del local', '#9b59b6', 1),
('Servicios', 'Luz, agua, internet, etc.', '#3498db', 1),
('Eventos', 'Gastos de organización de eventos', '#2ecc71', 1),
('Mantenimiento', 'Reparaciones y mantenimiento', '#e74c3c', 1);

-- Categorías de tareas
INSERT INTO task_categories (name, color, icon, description) VALUES
('Administrativo', '#e67e22', 'fa-briefcase', 'Tareas administrativas'),
('Gestión', '#9b59b6', 'fa-tasks', 'Tareas de gestión'),
('Eventos', '#2ecc71', 'fa-calendar', 'Organización de eventos'),
('Comunicación', '#3498db', 'fa-bullhorn', 'Comunicación con socios');

-- ============================================
-- SOCIOS
-- ============================================

INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, deactivated_at) VALUES
('Juan', 'Pérez García', '12345678A', 'juan.perez@example.com', '600123456', 'Calle Mayor 1', 1, 'active', '2020-01-15', NULL),
('Ana', 'García López', '87654321B', 'ana.garcia@example.com', '600654321', 'Calle Menor 2', 2, 'active', '2021-03-10', NULL),
('Luis', 'Martín Ruiz', '11223344C', 'luis.martin@example.com', '600987654', 'Avenida Central 3', 1, 'inactive', '2019-07-20', '2023-12-31'),
('María', 'Rodríguez Sanz', '22334455D', 'maria.rodriguez@example.com', '600111222', 'Plaza España 4', 5, 'active', '2022-02-14', NULL),
('Carlos', 'Fernández Díaz', '33445566E', 'carlos.fernandez@example.com', '600333444', 'Calle Luna 5', 1, 'active', '2020-05-20', NULL),
('Laura', 'González Moreno', '44556677F', 'laura.gonzalez@example.com', '600555666', 'Avenida Sol 6', 3, 'active', '2023-01-10', NULL),
('Pedro', 'Sánchez Torres', '55667788G', 'pedro.sanchez@example.com', '600777888', 'Calle Estrella 7', 6, 'active', '2021-06-15', NULL),
('Carmen', 'López Jiménez', '66778899H', 'carmen.lopez@example.com', '600999000', 'Plaza Mayor 8', 1, 'active', '2022-08-22', NULL),
('Javier', 'Martínez Romero', '77889900I', 'javier.martinez@example.com', '600222333', 'Calle Paz 9', 4, 'active', '2018-04-10', NULL),
('Isabel', 'Hernández Castro', '88990011J', 'isabel.hernandez@example.com', '600444555', 'Avenida Libertad 10', 1, 'active', '2023-09-05', NULL),
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
('Socio25', 'Apellido25', '10000025Y', 'socio25@demo.com', '600000025', 'Calle 25', 1, 'inactive', '2018-01-30', '2021-11-30');

-- ============================================
-- EVENTOS
-- ============================================

INSERT INTO events (title, description, event_date, location) VALUES
('Fiesta de Primavera', 'Celebración anual de la primavera con música y comida.', '2025-04-12', 'Parque Central'),
('Taller de Fotografía', 'Aprende técnicas básicas de fotografía con expertos.', '2025-05-05', 'Centro Cultural'),
('Excursión a la Montaña', 'Ruta de senderismo y picnic en la montaña.', '2025-06-20', 'Sierra Local'),
('Concierto Solidario', 'Concierto benéfico para recaudar fondos.', '2025-07-18', 'Auditorio Municipal'),
('Charla sobre Salud', 'Charla informativa sobre hábitos saludables.', '2025-03-10', 'Salón de Actos'),
('Torneo de Ajedrez', 'Competición abierta de ajedrez para socios.', '2025-09-05', 'Sala Polivalente'),
('Cena de Navidad', 'Cena de fin de año para todos los socios.', '2025-12-19', 'Restaurante El Encuentro'),
('Mercadillo Solidario', 'Venta de productos donados para recaudar fondos.', '2025-11-22', 'Plaza Mayor'),
('Curso de Primeros Auxilios', 'Formación básica en primeros auxilios.', '2025-10-10', 'Centro de Salud'),
('Fiesta de Verano', 'Fiesta temática para celebrar el verano.', '2025-08-15', 'Piscina Municipal');

-- ============================================
-- ASISTENCIAS A EVENTOS
-- ============================================

INSERT INTO event_attendance (event_id, member_id, status, attended, attended_at, registered_at, registration_date) VALUES
(1, 1, 'confirmed', 1, '2025-04-12 18:05:00', '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(1, 2, 'registered', 0, NULL, '2025-04-02 11:00:00', '2025-04-02 11:00:00'),
(1, 3, 'attended', 1, '2025-04-12 18:10:00', '2025-04-03 12:00:00', '2025-04-03 12:00:00'),
(1, 5, 'confirmed', 1, '2025-04-12 18:15:00', '2025-04-04 13:00:00', '2025-04-04 13:00:00'),
(1, 8, 'attended', 1, '2025-04-12 18:20:00', '2025-04-05 14:00:00', '2025-04-05 14:00:00'),
(2, 2, 'confirmed', 1, '2025-05-05 10:05:00', '2025-04-20 09:00:00', '2025-04-20 09:00:00'),
(2, 5, 'attended', 1, '2025-05-05 10:10:00', '2025-04-21 10:00:00', '2025-04-21 10:00:00'),
(2, 10, 'registered', 0, NULL, '2025-04-22 11:00:00', '2025-04-22 11:00:00'),
(3, 1, 'confirmed', 0, NULL, '2025-06-01 12:00:00', '2025-06-01 12:00:00'),
(3, 4, 'attended', 1, '2025-06-20 08:05:00', '2025-06-02 13:00:00', '2025-06-02 13:00:00'),
(3, 7, 'confirmed', 1, '2025-06-20 08:10:00', '2025-06-03 14:00:00', '2025-06-03 14:00:00'),
(4, 1, 'registered', 0, NULL, '2025-07-01 15:00:00', '2025-07-01 15:00:00'),
(4, 5, 'confirmed', 1, '2025-07-18 20:05:00', '2025-07-02 16:00:00', '2025-07-02 16:00:00'),
(4, 8, 'attended', 1, '2025-07-18 20:10:00', '2025-07-03 17:00:00', '2025-07-03 17:00:00'),
(5, 2, 'attended', 1, '2025-03-10 19:05:00', '2025-02-20 18:00:00', '2025-02-20 18:00:00'),
(5, 10, 'confirmed', 1, '2025-03-10 19:10:00', '2025-02-21 19:00:00', '2025-02-21 19:00:00');

-- ============================================
-- PAGOS
-- ============================================

-- Pagos de cuotas anuales
INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type) VALUES
(1, 20.00, '2023-01-15', 'Cuota anual 2023', 'paid', 2023, 'fee'),
(1, 20.00, '2024-01-10', 'Cuota anual 2024', 'paid', 2024, 'fee'),
(1, 20.00, '2025-01-08', 'Cuota anual 2025', 'paid', 2025, 'fee'),
(2, 10.00, '2023-03-10', 'Cuota anual 2023', 'paid', 2023, 'fee'),
(2, 10.00, '2024-03-15', 'Cuota anual 2024', 'paid', 2024, 'fee'),
(4, 15.00, '2023-02-14', 'Cuota anual 2023', 'paid', 2023, 'fee'),
(4, 15.00, '2024-02-20', 'Cuota anual 2024', 'paid', 2024, 'fee'),
(5, 20.00, '2023-05-20', 'Cuota anual 2023', 'paid', 2023, 'fee'),
(5, 20.00, '2024-05-25', 'Cuota anual 2024', 'paid', 2024, 'fee'),
(8, 20.00, '2023-08-22', 'Cuota anual 2023', 'paid', 2023, 'fee'),
(8, 20.00, '2024-08-28', 'Cuota anual 2024', 'paid', 2024, 'fee'),
(10, 20.00, '2024-09-05', 'Cuota anual 2024', 'paid', 2024, 'fee');

-- Pagos de eventos
INSERT INTO payments (member_id, amount, payment_date, concept, status, payment_type, event_id) VALUES
(1, 5.00, '2025-04-01', 'Fiesta de Primavera', 'paid', 'event', 1),
(2, 5.00, '2025-04-02', 'Fiesta de Primavera', 'pending', 'event', 1),
(3, 5.00, '2025-04-03', 'Fiesta de Primavera', 'paid', 'event', 1),
(5, 5.00, '2025-04-04', 'Fiesta de Primavera', 'paid', 'event', 1),
(8, 5.00, '2025-04-05', 'Fiesta de Primavera', 'paid', 'event', 1),
(4, 10.00, '2025-06-02', 'Excursión a la Montaña', 'paid', 'event', 3),
(7, 10.00, '2025-06-03', 'Excursión a la Montaña', 'paid', 'event', 3),
(5, 15.00, '2025-07-02', 'Concierto Solidario', 'paid', 'event', 4),
(8, 15.00, '2025-07-03', 'Concierto Solidario', 'paid', 'event', 4);

-- ============================================
-- DONANTES
-- ============================================

INSERT INTO donors (name, contact_person, phone, email, address) VALUES
('Comercio Local S.L.', 'Pedro Martínez', '600111222', 'contacto@comerciolocal.es', 'Plaza Mayor 5'),
('Bar El Encuentro', 'María López', '600333444', 'bar.encuentro@email.com', 'Calle Ancha 12'),
('Ferretería García', 'Antonio García', '600555666', 'ferreteria.garcia@email.com', 'Avda. Constitución 45'),
('Panadería La Espiga', 'Carmen Ruiz', '600777888', 'panaderia.espiga@email.com', 'Calle del Pan 3'),
('Talleres Mecánicos Sanz', 'Roberto Sanz', '600999000', 'talleres.sanz@email.com', 'Polígono Industrial Nave 7'),
('Librería El Saber', 'Laura Gómez', '600222333', 'libreria.saber@email.com', 'Calle Cultura 8'),
('Farmacia Central', 'Carlos Ruiz', '600444555', 'farmacia.central@email.com', 'Plaza España 2'),
('Restaurante La Huerta', 'Elena Díaz', '600666777', 'restaurante.huerta@email.com', 'Camino del Río 15'),
('Construcciones López', 'Miguel López', '600888999', 'construcciones.lopez@email.com', 'Calle Industria 22'),
('Peluquería Estilo', 'Sara Martín', '600101010', 'peluqueria.estilo@email.com', 'Calle Mayor 45');

-- ============================================
-- ANUNCIOS DEL LIBRO DE FIESTAS
-- ============================================

INSERT INTO book_ads (donor_id, year, ad_type, amount, status) VALUES
(1, 2024, 'full', 85.00, 'paid'),
(2, 2024, 'media', 45.00, 'paid'),
(3, 2024, 'media', 45.00, 'paid'),
(4, 2025, 'full', 90.00, 'paid'),
(5, 2025, 'cover', 150.00, 'paid'),
(6, 2025, 'media', 50.00, 'pending'),
(7, 2025, 'media', 50.00, 'paid'),
(8, 2025, 'back_cover', 120.00, 'paid');

-- ============================================
-- DONACIONES
-- ============================================

INSERT INTO donations (donor_id, amount, type, year, donation_date) VALUES
(1, 100.00, 'full', 2024, '2024-01-15'),
(2, 50.00, 'media', 2024, '2024-02-20'),
(4, 150.00, 'cover', 2025, '2025-01-10'),
(8, 75.00, 'media', 2025, '2025-02-14');

-- ============================================
-- GASTOS
-- ============================================

INSERT INTO expenses (category_id, description, amount, expense_date, payment_method, invoice_number, provider, created_by) VALUES
(1, 'Compra de material de oficina', 120.50, '2024-02-10', 'transferencia', 'FAC001', 'Papelería S.A.', 1),
(2, 'Pago alquiler local enero', 800.00, '2024-01-05', 'domiciliación', 'ALQ-2024-01', 'Inmobiliaria SL', 1),
(2, 'Pago alquiler local febrero', 800.00, '2024-02-05', 'domiciliación', 'ALQ-2024-02', 'Inmobiliaria SL', 1),
(3, 'Factura electricidad', 145.30, '2024-02-15', 'domiciliación', 'ELEC-2024-02', 'Iberdrola', 1),
(3, 'Factura internet', 45.00, '2024-02-20', 'domiciliación', 'INT-2024-02', 'Movistar', 1),
(4, 'Catering Fiesta Primavera', 350.00, '2024-04-12', 'transferencia', 'CAT-001', 'Catering Delicias', 1),
(5, 'Reparación aire acondicionado', 280.00, '2024-03-15', 'tarjeta', 'REP-001', 'Climatización Pro', 1);

-- ============================================
-- TAREAS
-- ============================================

INSERT INTO tasks (title, description, assigned_to, status, due_date, category_id, created_by, priority) VALUES
('Preparar reunión anual', 'Organizar la asamblea general de socios', 1, 'in_progress', '2025-12-01', 1, 1, 2),
('Actualizar base de datos', 'Revisar y actualizar datos de socios', 1, 'pending', '2025-11-20', 1, 1, 1),
('Cierre contable 2025', 'Preparar balance y cuenta de resultados del año', 1, 'pending', '2025-12-31', 1, 1, 3),
('Planificación eventos 2026', 'Definir calendario de actividades para el próximo año', 1, 'in_progress', '2025-12-15', 3, 1, 2),
('Renovación seguros', 'Revisar y renovar pólizas de seguro del local', 1, 'pending', '2026-01-20', 1, 1, 3),
('Asamblea General Ordinaria', 'Preparar documentación y convocatoria', 1, 'pending', '2026-03-15', 1, 1, 3),
('Campaña de captación', 'Diseñar campaña de primavera para nuevos socios', 1, 'pending', '2026-04-01', 4, 1, 2),
('Revisión inventario', 'Actualizar inventario de material y equipamiento', 1, 'pending', '2026-06-30', 1, 1, 1);

-- ============================================
-- HISTORIAL DE CUOTAS POR CATEGORÍA
-- ============================================

INSERT INTO category_fee_history (category_id, year, fee_amount) VALUES
(1, 2023, 18.00),
(1, 2024, 19.00),
(1, 2025, 20.00),
(2, 2023, 9.00),
(2, 2024, 9.50),
(2, 2025, 10.00),
(3, 2023, 5.00),
(3, 2024, 5.00),
(3, 2025, 5.00),
(5, 2023, 14.00),
(5, 2024, 14.50),
(5, 2025, 15.00),
(6, 2023, 28.00),
(6, 2024, 29.00),
(6, 2025, 30.00);

-- ============================================
-- FIN DE DATOS DE EJEMPLO
-- ============================================
