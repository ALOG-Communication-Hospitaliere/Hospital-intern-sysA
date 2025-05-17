-- Database creation script
CREATE DATABASE IF NOT EXISTS hospital_sys;
USE hospital_sys;

-- Patients table
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_securite_sociale VARCHAR(15) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(100),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patient documents table
CREATE TABLE IF NOT EXISTS patient_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    numero_securite_sociale VARCHAR(15) NOT NULL,
    type_document VARCHAR(50) NOT NULL,
    description TEXT,
    date_document DATE NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (numero_securite_sociale) REFERENCES patients(numero_securite_sociale) ON DELETE CASCADE,
    INDEX idx_nss_type_date (numero_securite_sociale, type_document, date_document)
);

-- Sample data insertion
INSERT INTO patients (numero_securite_sociale, nom, prenom, date_naissance, adresse, telephone, email)
VALUES 
('1234567890123', 'Dupont', 'Jean', '1980-05-15', '123 Rue de Paris, 75001 Paris', '0612345678', 'jean.dupont@email.com'),
('2345678901234', 'Martin', 'Sophie', '1975-10-22', '456 Avenue Victor Hugo, 69002 Lyon', '0723456789', 'sophie.martin@email.com'),
('3456789012345', 'Bernard', 'Pierre', '1990-02-08', '789 Boulevard Voltaire, 13001 Marseille', '0634567890', 'pierre.bernard@email.com');

-- Sample document data insertion
INSERT INTO patient_documents (patient_id, numero_securite_sociale, type_document, description, date_document)
VALUES
(1, '1234567890123', 'analyse_sang', 'Analyse de sang complète', '2024-05-10'),
(1, '1234567890123', 'radiographie', 'Radio thorax', '2024-05-12'),
(2, '2345678901234', 'echographie', 'Échographie abdominale',  '2024-05-05');