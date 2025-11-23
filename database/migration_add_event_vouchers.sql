-- Tabla para vales QR de eventos
CREATE TABLE IF NOT EXISTS event_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    member_id INT NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    vendido TINYINT(1) DEFAULT 0,
    recogido TINYINT(1) DEFAULT 0,
    fecha_venta DATETIME DEFAULT NULL,
    fecha_recogida DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);
