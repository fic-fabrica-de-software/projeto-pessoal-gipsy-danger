CREATE DATABASE IF NOT EXISTS medset;
USE medset;
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    user_password_hash VARCHAR(255) NOT NULL,
    user_email VARCHAR(100) NOT NULL UNIQUE
    user_age INT NOT NULL,
);

CREATE TABLE IF NOT EXISTS medicaments (
    med_id INT AUTO_INCREMENT,
    med_name VARCHAR(255) NOT NULL,
    med_expirydate DATE,
    med_begindate DATE,
    med_enddate DATE,
    med_dosage DECIMAL NOT NULL,
    med_dosageunit VARCHAR(50) NOT NULL,
    med_type VARCHAR(100) NOT NULL,
    med_brand VARCHAR(100),
    med_acquisitiontype VARCHAR(100),
    med_treatmentduration DATE,
    med_remaining INT,
    med_doctor VARCHAR(100),
    med_price DECIMAL,
    med_placeofpurchase VARCHAR(255),
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    PRIMARY KEY (med_id, user_id)
);

