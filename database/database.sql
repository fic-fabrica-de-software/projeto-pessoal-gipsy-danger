CREATE DATABASE IF NOT EXISTS medset;
USE medset;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    user_password_hash VARCHAR(255) NOT NULL,
    user_email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_name VARCHAR(255) NOT NULL,
    doctor_specialty VARCHAR(255),
    doctor_phone VARCHAR(20),
    doctor_email VARCHAR(100),
    doctor_address TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_doctor_user (doctor_name, user_id)
);

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    appointment_type VARCHAR(100),
    appointment_location VARCHAR(255),
    appointment_notes TEXT,
    doctor_id INT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, appointment_date)
);

CREATE TABLE IF NOT EXISTS medicaments (
    med_id INT AUTO_INCREMENT PRIMARY KEY,
    med_name VARCHAR(255) NOT NULL,
    med_brand VARCHAR(100),
    med_expirydate DATE,
    med_begindate DATE,
    med_enddate DATE,
    med_dosage DECIMAL(10,2) NOT NULL,
    med_type VARCHAR(100) NOT NULL,
    med_milligram DECIMAL(10,2) NOT NULL,
    med_milligram_unit VARCHAR(50) NOT NULL,
    med_time TIME NOT NULL,
    med_weekdays VARCHAR(20) NOT NULL COMMENT 'Dias da semana como string separada por vírgulas: 0,1,2,3,4,5,6',
    med_frequency VARCHAR(50) DEFAULT 'diario',
    med_acquisition_type ENUM('comprado', 'manipulado') DEFAULT 'comprado',
    med_remaining INT DEFAULT 0,
    med_alert_days INT DEFAULT 7,
    med_price DECIMAL(10,2),
    med_place_purchase VARCHAR(255),
    med_notes TEXT,
    med_is_active BOOLEAN DEFAULT TRUE,
    med_last_alert_date DATE,
    doctor_id INT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_med (user_id, med_time),
    INDEX idx_med_weekdays (med_weekdays),
    INDEX idx_user_active (user_id, med_is_active)
);

CREATE TABLE IF NOT EXISTS medication_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    med_id INT NOT NULL,
    user_id INT NOT NULL,
    taken_at DATETIME NOT NULL,
    taken_date DATE NOT NULL,
    status ENUM('taken', 'missed', 'skipped') DEFAULT 'taken',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (med_id) REFERENCES medicaments(med_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, taken_date),
    INDEX idx_med_date (med_id, taken_date),
    UNIQUE KEY unique_med_date (med_id, taken_date, user_id)
);

CREATE TABLE IF NOT EXISTS stock_alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    med_id INT NOT NULL,
    user_id INT NOT NULL,
    alert_type ENUM('low_stock', 'expiring', 'out_of_stock') DEFAULT 'low_stock',
    alert_message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (med_id) REFERENCES medicaments(med_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_alert (user_id, is_read),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    related_type ENUM('medication', 'appointment', 'system') DEFAULT 'system',
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);
-- Inserir médicos de exemplo
INSERT INTO doctors (doctor_name, doctor_specialty, doctor_phone, user_id) VALUES 
('Dr. Carlos Silva', 'Cardiologia', '(11) 9999-8888', 1),
('Dra. Maria Santos', 'Dermatologia', '(11) 9777-6666', 1);

-- Inserir medicamentos de exemplo
INSERT INTO medicaments (
    med_name, med_brand, med_dosage, med_type, med_milligram, med_milligram_unit, 
    med_time, med_weekdays, med_frequency, med_acquisition_type, med_remaining, 
    med_alert_days, doctor_id, user_id
) VALUES 
('Losartana', 'Genérico', 1.0, 'comprimido', 50, 'mg', '08:00:00', '1,2,3,4,5', 'diario', 'comprado', 30, 7, 1, 1),
('Sinvastatina', 'Zocor', 1.0, 'comprimido', 20, 'mg', '20:00:00', '1,2,3,4,5', 'diario', 'comprado', 15, 7, 1, 1),
('Vitamina D', 'Manipulado', 2.0, 'gotas', 1000, 'UI', '12:00:00', '1,3,5', 'diario', 'manipulado', 60, 14, 2, 1);

-- Inserir consultas de exemplo
INSERT INTO appointments (
    appointment_date, appointment_time, appointment_type, appointment_location, 
    doctor_id, user_id
) VALUES 
(CURDATE() + INTERVAL 7 DAY, '14:00:00', 'consulta-rotina', 'Hospital São Paulo', 1, 1),
(CURDATE() + INTERVAL 14 DAY, '10:30:00', 'retorno', 'Clínica Dermatológica', 2, 1);

-- Inserir histórico de exemplo
INSERT INTO medication_history (med_id, user_id, taken_at, taken_date, status) VALUES 
(1, 1, NOW(), CURDATE(), 'taken'),
(2, 1, NOW() - INTERVAL 1 DAY, CURDATE() - INTERVAL 1 DAY, 'taken');