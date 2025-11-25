-- ============================================
-- MIGRACIÓN DE BASE DE DATOS EXISTENTE
-- Ejecutar solo si ya tienes la BD creada
-- ============================================

USE tienda;

-- Agregar nuevas columnas a tabla ventas
ALTER TABLE ventas ADD COLUMN subtotal DECIMAL(12,2) AFTER usuario_id;
ALTER TABLE ventas ADD COLUMN iva DECIMAL(12,2) AFTER subtotal;

-- Actualizar ventas existentes (calcular subtotal e IVA del total actual)
-- Asumiendo que el total actual ya incluía IVA del 19%
UPDATE ventas 
SET subtotal = total / 1.19, 
    iva = total - (total / 1.19) 
WHERE subtotal IS NULL;

-- Verificar que todo está correcto
SELECT id, cliente_id, subtotal, iva, total, fecha FROM ventas LIMIT 10;
