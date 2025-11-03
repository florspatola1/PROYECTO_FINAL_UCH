

CREATE DATABASE IF NOT EXISTS proyecto_final_uch;
USE proyecto_final_uch;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    edad INT,
    rol ENUM('donante', 'administrador', 'master') DEFAULT 'donante',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de turnos 
CREATE TABLE turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donante_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    motivo TEXT,
    estado ENUM('pendiente', 'confirmado', 'cancelado','rechazado','realizado') DEFAULT 'pendiente',
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de evaluaciones médicas 
CREATE TABLE evaluaciones_medicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donante_id INT NOT NULL,
    turno_id INT,
    fecha_evaluacion DATE NOT NULL,
    peso DECIMAL(5,2),
    altura INT,
    presion_arterial VARCHAR(20),
    frecuencia_cardiaca INT,
    apto_para_donar BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    evaluado_por INT,
    FOREIGN KEY (donante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE SET NULL,
    FOREIGN KEY (evaluado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Insertar usuario master por defecto
INSERT INTO usuarios (nombre, apellido, dni, email, password, rol) VALUES 
('Admin', 'Master', '00000000', 'admin@bloodbank.com', 'admin123', 'master');

