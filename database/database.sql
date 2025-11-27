-- database.sql (atualizado com histórico)
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
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
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
    med_weekday INT NOT NULL,
    med_frequency VARCHAR(50) DEFAULT 'diario',
    med_remaining INT,
    med_price DECIMAL(10,2),
    med_place_purchase VARCHAR(255),
    med_notes TEXT,
    doctor_id INT,
    user_id INT NOT NULL,
    med_acquisition_type ENUM('comprado', 'manipulado') DEFAULT 'comprado',
    med_alert_days INT DEFAULT 7,
    med_last_alert_date DATE,
    med_is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_med (user_id, med_time),
    INDEX idx_user_weekday (user_id, med_weekday)
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
    INDEX idx_med_date (med_id, taken_date)
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
    INDEX idx_user_alert (user_id, is_read)
);

ALTER TABLE medicaments 
ADD COLUMN IF NOT EXISTS med_weekdays VARCHAR(20) NOT NULL DEFAULT '1',
ADD COLUMN IF NOT EXISTS med_alert_days INT DEFAULT 7,
ADD COLUMN IF NOT EXISTS med_is_active BOOLEAN DEFAULT TRUE;

-- 2. Migrar dados da coluna antiga para a nova
UPDATE medicaments SET med_weekdays = med_weekday WHERE med_weekdays = '1';

-- 3. Remover a coluna antiga (após garantir que a migração funcionou)
-- ALTER TABLE medicaments DROP COLUMN med_weekday;

-- 4. Se preferir manter compatibilidade, pode renomear em vez de remover:
ALTER TABLE medicaments CHANGE med_weekday med_weekday_old INT;

-- 5. Criar índice para melhor performance
CREATE INDEX idx_med_weekdays ON medicaments (med_weekdays);