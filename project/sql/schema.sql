CREATE DATABASE IF NOT EXISTS tienda; 
USE tienda;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','trabajador','proveedor','cliente') DEFAULT 'trabajador',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  categoria_id INT,
  codigo VARCHAR(50),
  precio DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

CREATE TABLE clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  contacto VARCHAR(150),
  telefono VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE proveedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  contacto VARCHAR(150),
  telefono VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT,
  usuario_id INT,
  total DECIMAL(12,2),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
  FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE venta_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venta_id INT,
  producto_id INT,
  cantidad INT,
  precio_unit DECIMAL(10,2),
  FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

CREATE TABLE historial (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT,
  accion VARCHAR(100),
  tabla_afectada VARCHAR(100),
  registro_id INT,
  detalles TEXT,
  fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Datos de ejemplo
-- Password: admin123
INSERT INTO users (nombre,email,password,role) VALUES
('Admin','admin@local', '$2y$10$hTG4.O6wyTGkwtTsHZvX5eAUiPyppONUd46/.GRuIwrZEekSOprhy','admin');

INSERT INTO categorias (nombre) VALUES ('General'), ('Electrónica'), ('Ropa');

INSERT INTO productos (nombre, categoria_id, codigo, precio, stock) VALUES 
('Laptop Basic', 2, 'LAP001', 500.00, 10),
('Camiseta Negra', 3, 'CAM001', 15.00, 50),
('Mouse USB', 2, 'MOU001', 10.00, 100);

INSERT INTO clientes (nombre, contacto, telefono) VALUES 
('Cliente Mostrador', 'N/A', '000000000');
