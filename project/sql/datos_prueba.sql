-- Datos de prueba para el sistema
USE tienda;

-- Usuario administrador de prueba
-- Credenciales: DNI 12345678, Fecha: 1990-01-15
INSERT INTO users (tipo_documento, numero_documento, fecha_nacimiento, nombre, correo, password, rol, estado) 
VALUES ('DNI', '12345678', '1990-01-15', 'Administrador del Sistema', 'admin@mitienda.com', 'sin_password', 'administrador', 'activo');

-- Usuario trabajador de prueba
-- Credenciales: DNI 87654321, Fecha: 1995-06-20
INSERT INTO users (tipo_documento, numero_documento, fecha_nacimiento, nombre, correo, password, rol, estado) 
VALUES ('DNI', '87654321', '1995-06-20', 'Carlos Trabajador', 'carlos@mitienda.com', 'sin_password', 'trabajador', 'activo');

-- Categorías de ejemplo
INSERT INTO categorias (nombre) VALUES 
('Electrónica'),
('Ropa'),
('Alimentos'),
('Hogar');

-- Productos de ejemplo
INSERT INTO productos (nombre, categoria_id, codigo, precio, stock) VALUES
('Laptop Dell', 1, 'ELEC001', 899.99, 15),
('Mouse Logitech', 1, 'ELEC002', 25.50, 50),
('Camisa Polo', 2, 'ROPA001', 35.00, 30),
('Pantalón Jean', 2, 'ROPA002', 45.00, 25),
('Arroz 1kg', 3, 'ALIM001', 2.50, 100);

-- Clientes de ejemplo
INSERT INTO clientes (nombre, contacto, telefono) VALUES
('Juan Pérez', 'juan@email.com', '987654321'),
('María García', 'maria@email.com', '987654322'),
('Pedro López', 'pedro@email.com', '987654323');

-- Proveedores de ejemplo
INSERT INTO proveedores (nombre, contacto, telefono) VALUES
('Distribuidora Tech SAC', 'ventas@tech.com', '012345678'),
('Textiles del Norte', 'info@textiles.com', '012345679'),
('Alimentos Frescos', 'pedidos@frescos.com', '012345680');
