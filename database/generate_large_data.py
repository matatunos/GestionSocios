#!/usr/bin/env python3
"""
Generador de datos masivos para GestionSocios
Genera sample_data_large.sql con:
- 2000 socios
- 500 donantes
- 50 eventos (5 años)
- Miles de asistencias, pagos, donaciones, etc.
"""

import random
from datetime import datetime, timedelta

# Configuración
NUM_MEMBERS = 2000
NUM_DONORS = 500
NUM_EVENTS = 50
YEARS = 5
START_YEAR = 2020

# Nombres y apellidos españoles comunes
FIRST_NAMES = [
    'Juan', 'María', 'José', 'Ana', 'Antonio', 'Carmen', 'Francisco', 'Isabel',
    'Manuel', 'Dolores', 'David', 'Pilar', 'Daniel', 'Teresa', 'Carlos', 'Rosa',
    'Miguel', 'Josefa', 'Rafael', 'Antonia', 'Pedro', 'Francisca', 'Ángel', 'Laura',
    'Alejandro', 'Mercedes', 'Fernando', 'Cristina', 'Pablo', 'Lucía', 'Jorge', 'Elena',
    'Luis', 'Marta', 'Sergio', 'Sara', 'Alberto', 'Patricia', 'Javier', 'Raquel',
    'Adrián', 'Beatriz', 'Rubén', 'Silvia', 'Óscar', 'Natalia', 'Iván', 'Rocío',
    'Diego', 'Andrea', 'Víctor', 'Paula', 'Raúl', 'Irene', 'Álvaro', 'Clara',
    'Marcos', 'Alicia', 'Gonzalo', 'Nuria', 'Martín', 'Eva', 'Hugo', 'Sofía'
]

LAST_NAMES = [
    'García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez',
    'Pérez', 'Gómez', 'Martín', 'Jiménez', 'Ruiz', 'Hernández', 'Díaz', 'Moreno',
    'Muñoz', 'Álvarez', 'Romero', 'Alonso', 'Gutiérrez', 'Navarro', 'Torres',
    'Domínguez', 'Vázquez', 'Ramos', 'Gil', 'Ramírez', 'Serrano', 'Blanco', 'Molina',
    'Castro', 'Ortega', 'Rubio', 'Marín', 'Sanz', 'Iglesias', 'Medina', 'Garrido',
    'Santos', 'Cortés', 'Guerrero', 'Lozano', 'Cano', 'Cruz', 'Prieto', 'Méndez',
    'Flores', 'Herrera', 'Peña', 'León', 'Márquez', 'Cabrera', 'Gallego', 'Calvo'
]

BUSINESS_TYPES = [
    'Bar', 'Restaurante', 'Cafetería', 'Panadería', 'Carnicería', 'Pescadería',
    'Frutería', 'Supermercado', 'Ferretería', 'Farmacia', 'Librería', 'Papelería',
    'Peluquería', 'Gimnasio', 'Floristería', 'Joyería', 'Óptica', 'Zapatería',
    'Tienda de Ropa', 'Electrodomésticos', 'Muebles', 'Juguetería', 'Autoescuela',
    'Taller Mecánico', 'Construcciones', 'Inmobiliaria', 'Asesoría', 'Gestoría',
    'Despacho de Abogados', 'Clínica Dental', 'Centro Médico', 'Agencia de Seguros'
]

BUSINESS_NAMES = [
    'El Rincón', 'La Esquina', 'Los Amigos', 'El Encuentro', 'La Plaza', 'El Centro',
    'La Estrella', 'El Sol', 'La Luna', 'El Paraíso', 'La Perla', 'El Jardín',
    'La Fuente', 'El Bosque', 'La Montaña', 'El Valle', 'La Costa', 'El Puerto',
    'Central', 'Principal', 'Real', 'Imperial', 'Royal', 'Premium', 'Elite', 'Top',
    'Nuevo', 'Moderno', 'Clásico', 'Tradicional', 'Familiar', 'Popular'
]

EVENT_TYPES = [
    ('social', '#4caf50', 'Fiesta'),
    ('formativo', '#2196f3', 'Taller'),
    ('formativo', '#2196f3', 'Curso'),
    ('formativo', '#2196f3', 'Charla'),
    ('ocio', '#ff9800', 'Excursión'),
    ('ocio', '#ff9800', 'Visita'),
    ('deportivo', '#795548', 'Torneo'),
    ('deportivo', '#795548', 'Carrera'),
    ('solidario', '#e91e63', 'Concierto Solidario'),
    ('solidario', '#009688', 'Mercadillo'),
    ('social', '#f44336', 'Cena'),
    ('social', '#ffeb3b', 'Comida'),
]

EVENT_THEMES = [
    'de Primavera', 'de Verano', 'de Otoño', 'de Invierno', 'de Navidad',
    'de Fin de Año', 'Benéfico', 'Cultural', 'Deportivo', 'Gastronómico',
    'Musical', 'Infantil', 'Familiar', 'Juvenil', 'Senior', 'Anual',
    'de Fotografía', 'de Pintura', 'de Teatro', 'de Danza', 'de Ajedrez',
    'de Pádel', 'de Senderismo', 'de Ciclismo', 'de Natación', 'Solidario',
    'de Primeros Auxilios', 'de Cocina', 'de Idiomas', 'de Informática'
]

def random_date(start_year, end_year):
    """Genera una fecha aleatoria entre dos años"""
    start = datetime(start_year, 1, 1)
    end = datetime(end_year, 12, 31)
    delta = end - start
    random_days = random.randint(0, delta.days)
    return start + timedelta(days=random_days)

def random_join_date():
    """Genera fecha de alta con distribución realista (más recientes)"""
    # Distribución: 10% en 2020, 15% en 2021, 20% en 2022, 25% en 2023, 30% en 2024-2025
    rand = random.random()
    if rand < 0.10:
        year = 2020
    elif rand < 0.25:
        year = 2021
    elif rand < 0.45:
        year = 2022
    elif rand < 0.70:
        year = 2023
    else:
        year = random.choice([2024, 2025])
    
    return random_date(year, year)

def random_asturias_coords():
    """Genera coordenadas aleatorias en la zona central de Asturias"""
    # Zona central de Asturias (Oviedo, Gijón, Avilés)
    # Latitud: 43.3 - 43.6
    # Longitud: -6.0 - -5.6
    lat = round(random.uniform(43.30, 43.60), 6)
    lng = round(random.uniform(-6.00, -5.60), 6)
    return lat, lng

def random_phone():
    """Genera un número de teléfono español"""
    return f"6{random.randint(0, 9)}{random.randint(10000000, 99999999)}"

def random_dni():
    """Genera un DNI español (formato simplificado)"""
    letters = 'TRWAGMYFPDXBNJZSQVHLCKE'
    num = random.randint(10000000, 99999999)
    return f"{num}{letters[num % 23]}"

def random_email(first_name, last_name, domain='demo.com'):
    """Genera un email"""
    return f"{first_name.lower()}.{last_name.lower()}{random.randint(1, 999)}@{domain}"

def generate_sql():
    """Genera el archivo SQL completo"""
    
    output = []
    
    # Cabecera
    output.append("""-- ============================================
-- Datos Masivos de Ejemplo para Sistema de Gestión de Socios
-- ============================================
-- 2000 socios, 500 donantes, 50 eventos, 5 años de actividad
-- IMPORTANTE: Este archivo debe ejecutarse DESPUÉS de schema.sql

SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar tablas existentes
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

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- CONFIGURACIÓN
-- ============================================

INSERT INTO organization_settings (category, setting_key, setting_value, setting_type, description) VALUES
('general', 'org_name', 'Asociación Cultural y Deportiva Gran Ciudad', 'string', 'Nombre completo de la organización'),
('general', 'org_short_name', 'ACDGC', 'string', 'Siglas'),
('general', 'org_founded_year', '2020', 'int', 'Año de fundación'),
('general', 'org_cif', 'G12345678', 'string', 'CIF/NIF'),
('contact', 'org_email', 'info@acdgc.org', 'string', 'Email'),
('contact', 'org_phone', '912345678', 'string', 'Teléfono');

-- Cuotas anuales
INSERT INTO annual_fees (year, amount) VALUES
(2020, 15.00),
(2021, 16.00),
(2022, 17.00),
(2023, 18.00),
(2024, 19.00),
(2025, 20.00);

-- Precios de anuncios
INSERT INTO ad_prices (year, type, amount) VALUES
""")
    
    # Precios de anuncios (5 años)
    ad_prices = []
    for year in range(START_YEAR, START_YEAR + YEARS + 1):
        base_media = 40 + (year - START_YEAR) * 5
        ad_prices.append(f"({year}, 'media', {base_media}.00)")
        ad_prices.append(f"({year}, 'full', {base_media * 2}.00)")
        ad_prices.append(f"({year}, 'cover', {base_media * 3}.00)")
        ad_prices.append(f"({year}, 'back_cover', {base_media * 2.5}.00)")
    
    output.append(",\n".join(ad_prices) + ";\n")
    
    # Categorías de socios
    output.append("""
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
('Mantenimiento', 'Reparaciones y mantenimiento', '#e74c3c', 1),
('Marketing', 'Publicidad y promoción', '#f39c12', 1),
('Seguros', 'Pólizas de seguro', '#8e44ad', 1);

-- Categorías de tareas
INSERT INTO task_categories (name, color, icon, description) VALUES
('Administrativo', '#e67e22', 'fa-briefcase', 'Tareas administrativas'),
('Gestión', '#9b59b6', 'fa-tasks', 'Tareas de gestión'),
('Eventos', '#2ecc71', 'fa-calendar', 'Organización de eventos'),
('Comunicación', '#3498db', 'fa-bullhorn', 'Comunicación con socios');

""")
    
    # Generar 2000 socios
    output.append("-- ============================================\n")
    output.append(f"-- SOCIOS ({NUM_MEMBERS} registros)\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO members (first_name, last_name, dni, email, phone, address, category_id, status, join_date, deactivated_at, latitude, longitude) VALUES\n")
    
    members = []
    for i in range(NUM_MEMBERS):
        first_name = random.choice(FIRST_NAMES)
        last_name = f"{random.choice(LAST_NAMES)} {random.choice(LAST_NAMES)}"
        dni = random_dni()
        email = random_email(first_name, last_name.split()[0])
        phone = random_phone()
        address = f"Calle {random.choice(['Mayor', 'Real', 'Principal', 'Central', 'Nueva'])} {random.randint(1, 200)}"
        category_id = random.randint(1, 7)
        
        # 85% activos, 15% inactivos
        if random.random() < 0.85:
            status = 'active'
            deactivated_at = 'NULL'
        else:
            status = 'inactive'
            deact_date = random_join_date()
            deactivated_at = f"'{deact_date.strftime('%Y-%m-%d')}'"
        
        join_date = random_join_date()
        lat, lng = random_asturias_coords()
        
        members.append(
            f"('{first_name}', '{last_name}', '{dni}', '{email}', '{phone}', '{address}', "
            f"{category_id}, '{status}', '{join_date.strftime('%Y-%m-%d')}', {deactivated_at}, {lat}, {lng})"
        )
    
    output.append(",\n".join(members) + ";\n\n")
    
    # Generar 500 donantes
    output.append("-- ============================================\n")
    output.append(f"-- DONANTES ({NUM_DONORS} registros)\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO donors (name, contact_person, phone, email, address, latitude, longitude) VALUES\n")
    
    donors = []
    for i in range(NUM_DONORS):
        business_type = random.choice(BUSINESS_TYPES)
        business_name = random.choice(BUSINESS_NAMES)
        name = f"{business_type} {business_name}"
        contact = f"{random.choice(FIRST_NAMES)} {random.choice(LAST_NAMES)}"
        phone = random_phone()
        email = f"contacto{i+1}@{business_name.lower().replace(' ', '')}.com"
        address = f"{random.choice(['Calle', 'Avenida', 'Plaza'])} {random.choice(LAST_NAMES)} {random.randint(1, 100)}"
        lat, lng = random_asturias_coords()
        
        donors.append(
            f"('{name}', '{contact}', '{phone}', '{email}', '{address}', {lat}, {lng})"
        )
    
    output.append(",\n".join(donors) + ";\n\n")
    
    # Generar 50 eventos
    output.append("-- ============================================\n")
    output.append(f"-- EVENTOS ({NUM_EVENTS} registros)\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO events (name, event_type, color, description, location, date, start_time, end_time, price, max_attendees, requires_registration, registration_deadline, is_active) VALUES\n")
    
    events = []
    for i in range(NUM_EVENTS):
        event_type, color, event_prefix = random.choice(EVENT_TYPES)
        theme = random.choice(EVENT_THEMES)
        name = f"{event_prefix} {theme}"
        description = f"Evento {event_type} organizado por la asociación."
        location = random.choice(['Centro Cultural', 'Polideportivo', 'Parque Municipal', 'Salón de Actos', 'Plaza Mayor', 'Auditorio'])
        
        event_date = random_date(START_YEAR, START_YEAR + YEARS)
        start_time = f"{random.randint(9, 20):02d}:00"
        end_hour = random.randint(int(start_time[:2]) + 1, 23)
        end_time = f"{end_hour:02d}:00"
        
        price = random.choice([0, 3, 5, 8, 10, 12, 15, 20, 25])
        max_attendees = random.choice([30, 50, 80, 100, 150, 200, 300])
        requires_registration = 1 if random.random() < 0.8 else 0
        
        if requires_registration:
            reg_deadline = (event_date - timedelta(days=random.randint(2, 7))).strftime('%Y-%m-%d')
        else:
            reg_deadline = 'NULL'
        
        is_active = 1 if event_date.year >= 2024 else 0
        
        reg_deadline_str = f"'{reg_deadline}'" if reg_deadline != 'NULL' else 'NULL'
        
        events.append(
            f"('{name}', '{event_type}', '{color}', '{description}', '{location}', "
            f"'{event_date.strftime('%Y-%m-%d')}', '{start_time}', '{end_time}', {price}.00, "
            f"{max_attendees}, {requires_registration}, {reg_deadline_str}, {is_active})"
        )
    
    output.append(",\n".join(events) + ";\n\n")
    
    # Generar asistencias a eventos (aprox. 60% de capacidad promedio)
    output.append("-- ============================================\n")
    output.append("-- ASISTENCIAS A EVENTOS\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO event_attendance (event_id, member_id, status, attended, attended_at, registered_at, registration_date) VALUES\n")
    
    attendances = []
    attendance_count = 0
    for event_id in range(1, NUM_EVENTS + 1):
        # Cada evento tiene entre 20% y 80% de asistentes
        num_attendees = random.randint(int(NUM_MEMBERS * 0.01), int(NUM_MEMBERS * 0.05))
        attendee_ids = random.sample(range(1, NUM_MEMBERS + 1), num_attendees)
        
        for member_id in attendee_ids:
            status = random.choice(['registered', 'confirmed', 'attended', 'cancelled'])
            attended = 1 if status == 'attended' else (1 if status == 'confirmed' and random.random() < 0.8 else 0)
            
            reg_date = random_date(START_YEAR, START_YEAR + YEARS)
            registered_at = f"'{reg_date.strftime('%Y-%m-%d %H:%M:%S')}'"
            
            if attended:
                att_date = reg_date + timedelta(days=random.randint(1, 30))
                attended_at = f"'{att_date.strftime('%Y-%m-%d %H:%M:%S')}'"
            else:
                attended_at = 'NULL'
            
            attendances.append(
                f"({event_id}, {member_id}, '{status}', {attended}, {attended_at}, {registered_at}, {registered_at})"
            )
            attendance_count += 1
            
            # Limitar a 10000 asistencias para no hacer el archivo demasiado grande
            if attendance_count >= 10000:
                break
        
        if attendance_count >= 10000:
            break
    
    output.append(",\n".join(attendances) + ";\n\n")
    
    # Generar pagos de cuotas (5 años)
    output.append("-- ============================================\n")
    output.append("-- PAGOS DE CUOTAS ANUALES\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO payments (member_id, amount, payment_date, concept, status, fee_year, payment_type) VALUES\n")
    
    fee_payments = []
    for year in range(START_YEAR, START_YEAR + YEARS + 1):
        fee_amount = 15 + (year - START_YEAR)
        # 70% de los socios pagan cada año
        paying_members = random.sample(range(1, NUM_MEMBERS + 1), int(NUM_MEMBERS * 0.7))
        
        for member_id in paying_members:
            payment_date = random_date(year, year)
            status = 'paid' if random.random() < 0.95 else 'pending'
            
            fee_payments.append(
                f"({member_id}, {fee_amount}.00, '{payment_date.strftime('%Y-%m-%d')}', "
                f"'Cuota anual {year}', '{status}', {year}, 'fee')"
            )
    
    output.append(",\n".join(fee_payments) + ";\n\n")
    
    # Generar pagos de eventos
    output.append("-- Pagos de eventos\n")
    output.append("INSERT INTO payments (member_id, amount, payment_date, concept, status, payment_type, event_id) VALUES\n")
    
    event_payments = []
    for event_id in range(1, min(NUM_EVENTS, 30) + 1):  # Solo eventos con precio
        price = random.choice([5, 8, 10, 12, 15, 20, 25])
        num_payers = random.randint(10, 50)
        payer_ids = random.sample(range(1, NUM_MEMBERS + 1), num_payers)
        
        for member_id in payer_ids:
            payment_date = random_date(START_YEAR, START_YEAR + YEARS)
            status = 'paid' if random.random() < 0.9 else 'pending'
            
            event_payments.append(
                f"({member_id}, {price}.00, '{payment_date.strftime('%Y-%m-%d')}', "
                f"'Pago evento', '{status}', 'event', {event_id})"
            )
    
    output.append(",\n".join(event_payments) + ";\n\n")
    
    # Generar anuncios del libro de fiestas
    output.append("-- ============================================\n")
    output.append("-- ANUNCIOS DEL LIBRO DE FIESTAS\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO book_ads (donor_id, year, ad_type, amount, status) VALUES\n")
    
    book_ads = []
    for year in range(START_YEAR, START_YEAR + YEARS + 1):
        # 30-40% de donantes compran anuncios cada año
        num_ads = random.randint(int(NUM_DONORS * 0.3), int(NUM_DONORS * 0.4))
        ad_donors = random.sample(range(1, NUM_DONORS + 1), num_ads)
        
        for donor_id in ad_donors:
            ad_type = random.choice(['media', 'media', 'media', 'full', 'full', 'cover', 'back_cover'])
            base_price = 40 + (year - START_YEAR) * 5
            
            if ad_type == 'media':
                amount = base_price
            elif ad_type == 'full':
                amount = base_price * 2
            elif ad_type == 'cover':
                amount = base_price * 3
            else:  # back_cover
                amount = base_price * 2.5
            
            status = 'paid' if random.random() < 0.85 else 'pending'
            
            book_ads.append(
                f"({donor_id}, {year}, '{ad_type}', {amount:.2f}, '{status}')"
            )
    
    output.append(",\n".join(book_ads) + ";\n\n")
    
    # Generar donaciones
    output.append("-- ============================================\n")
    output.append("-- DONACIONES\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO donations (donor_id, amount, type, year, donation_date) VALUES\n")
    
    donations = []
    for year in range(START_YEAR, START_YEAR + YEARS + 1):
        # 20% de donantes hacen donaciones adicionales
        num_donations = random.randint(int(NUM_DONORS * 0.15), int(NUM_DONORS * 0.25))
        donation_donors = random.sample(range(1, NUM_DONORS + 1), num_donations)
        
        for donor_id in donation_donors:
            amount = random.choice([50, 75, 100, 150, 200, 250, 300, 500])
            donation_type = random.choice(['media', 'full', 'cover', 'back_cover'])
            donation_date = random_date(year, year)
            
            donations.append(
                f"({donor_id}, {amount}.00, '{donation_type}', {year}, '{donation_date.strftime('%Y-%m-%d')}')"
            )
    
    output.append(",\n".join(donations) + ";\n\n")
    
    # Generar gastos
    output.append("-- ============================================\n")
    output.append("-- GASTOS OPERATIVOS\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO expenses (category_id, description, amount, expense_date, payment_method, invoice_number, provider, created_by) VALUES\n")
    
    expenses = []
    expense_types = [
        (1, 'Material de oficina', 50, 200),
        (2, 'Alquiler local', 600, 900),
        (3, 'Factura electricidad', 80, 200),
        (3, 'Factura agua', 30, 80),
        (3, 'Internet y telefonía', 40, 80),
        (4, 'Catering evento', 200, 800),
        (4, 'Alquiler equipo sonido', 100, 300),
        (5, 'Reparación instalaciones', 150, 500),
        (6, 'Publicidad redes sociales', 50, 200),
        (7, 'Seguro responsabilidad civil', 300, 600),
    ]
    
    for year in range(START_YEAR, START_YEAR + YEARS + 1):
        # Generar gastos mensuales
        for month in range(1, 13):
            # 5-10 gastos por mes
            num_expenses = random.randint(5, 10)
            
            for _ in range(num_expenses):
                category_id, desc_base, min_amount, max_amount = random.choice(expense_types)
                amount = random.uniform(min_amount, max_amount)
                expense_date = datetime(year, month, random.randint(1, 28))
                payment_method = random.choice(['transferencia', 'domiciliación', 'tarjeta', 'efectivo'])
                invoice_number = f"FAC-{year}-{month:02d}-{random.randint(1, 999):03d}"
                provider = f"Proveedor {random.randint(1, 50)}"
                
                expenses.append(
                    f"({category_id}, '{desc_base}', {amount:.2f}, '{expense_date.strftime('%Y-%m-%d')}', "
                    f"'{payment_method}', '{invoice_number}', '{provider}', 1)"
                )
    
    output.append(",\n".join(expenses) + ";\n\n")
    
    # Generar tareas
    output.append("-- ============================================\n")
    output.append("-- TAREAS\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO tasks (title, description, assigned_to, status, due_date, category_id, created_by, priority) VALUES\n")
    
    tasks = []
    task_templates = [
        ('Preparar asamblea general', 'Organizar documentación y convocatoria', 1, 3),
        ('Actualizar censo de socios', 'Revisar y actualizar datos', 1, 1),
        ('Renovar seguros', 'Gestionar renovación de pólizas', 1, 3),
        ('Planificar eventos trimestre', 'Definir calendario de actividades', 3, 2),
        ('Campaña captación socios', 'Diseñar campaña de marketing', 4, 2),
        ('Cierre contable anual', 'Preparar balance y cuentas', 1, 3),
        ('Mantenimiento instalaciones', 'Revisar estado del local', 2, 2),
        ('Newsletter mensual', 'Preparar y enviar boletín', 4, 1),
    ]
    
    for year in range(START_YEAR, START_YEAR + YEARS + 1):
        for quarter in range(1, 5):
            for title, desc, category, priority in random.sample(task_templates, random.randint(3, 6)):
                due_date = datetime(year, quarter * 3, random.randint(1, 28))
                status = random.choice(['pending', 'in_progress', 'completed'])
                
                tasks.append(
                    f"('{title}', '{desc}', 1, '{status}', '{due_date.strftime('%Y-%m-%d')}', "
                    f"{category}, 1, {priority})"
                )
    
    output.append(",\n".join(tasks) + ";\n\n")
    
    # Historial de cuotas por categoría
    output.append("-- ============================================\n")
    output.append("-- HISTORIAL DE CUOTAS POR CATEGORÍA\n")
    output.append("-- ============================================\n\n")
    output.append("INSERT INTO category_fee_history (category_id, year, fee_amount) VALUES\n")
    
    fee_history = []
    for year in range(START_YEAR, START_YEAR + YEARS + 1):
        base_fee = 15 + (year - START_YEAR)
        fee_history.append(f"(1, {year}, {base_fee}.00)")  # General
        fee_history.append(f"(2, {year}, {base_fee * 0.5:.2f})")  # Joven
        fee_history.append(f"(3, {year}, {base_fee * 0.25:.2f})")  # Juvenil
        fee_history.append(f"(4, {year}, 0.00)")  # Honorífico
        fee_history.append(f"(5, {year}, {base_fee * 0.75:.2f})")  # Senior
        fee_history.append(f"(6, {year}, {base_fee * 1.5:.2f})")  # Familiar
        fee_history.append(f"(7, {year}, {base_fee * 0.5:.2f})")  # Simpatizante
    
    output.append(",\n".join(fee_history) + ";\n\n")
    
    output.append("-- ============================================\n")
    output.append("-- FIN DE DATOS MASIVOS\n")
    output.append("-- ============================================\n")
    
    return "".join(output)

if __name__ == "__main__":
    print("Generando sample_data_large.sql...")
    sql_content = generate_sql()
    
    # Guardar archivo
    with open("database/sample_data_large.sql", "w", encoding="utf-8") as f:
        f.write(sql_content)
    
    print(f"OK Archivo generado: database/sample_data_large.sql")
    print(f"OK {NUM_MEMBERS} socios")
    print(f"OK {NUM_DONORS} donantes")
    print(f"OK {NUM_EVENTS} eventos")
    print("OK Generacion completada!")
