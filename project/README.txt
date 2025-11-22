SISTEMA DE GESTIÓN DE INVENTARIO Y VENTAS
===========================================

Este es un sistema simple desarrollado en PHP y MySQL para ejecutarse en XAMPP.

INSTALACIÓN
-----------

1. Copiar la carpeta 'project' a tu directorio 'htdocs' de XAMPP.
   Ruta sugerida: C:\xampp\htdocs\mi_tienda

2. Iniciar Apache y MySQL desde el panel de control de XAMPP.

3. Crear la Base de Datos:
   - Abrir phpMyAdmin (http://localhost/phpmyadmin).
   - Ir a la pestaña "Importar".
   - Seleccionar el archivo 'sql/schema.sql' que está dentro de la carpeta del proyecto.
   - Ejecutar. Esto creará la base de datos 'tienda' y las tablas necesarias.

4. Configuración de Base de Datos:
   - El archivo de conexión está en 'inc/db.php'.
   - Por defecto está configurado para XAMPP (usuario: root, sin contraseña).
   - Si tienes otra configuración, edita este archivo.

USO
---

1. Abrir el navegador y entrar a:
   http://localhost/project/mi_tienda/public/login.php

2. Iniciar sesión con las credenciales de administrador:
   Email: admin@local
   Contraseña: admin123

CARACTERÍSTICAS
---------------

- Dashboard: Resumen de productos, ventas del día y últimos movimientos.
- Ventas: Registrar nuevas ventas seleccionando cliente y productos. Control de stock automático.
- Productos: CRUD completo (Crear, Leer, Actualizar, Eliminar) con control de stock.
- Clientes/Proveedores: Gestión de contactos.
- Usuarios: Gestión de usuarios y roles (Solo Admin).
- Reportes: Filtrado de ventas por fecha, exportación a CSV e impresión.
- Historial: Registro automático de todas las acciones importantes.

NOTAS TÉCNICAS
--------------

- Frontend: Bootstrap 5 (CDN).
- Backend: PHP nativo con PDO.
- Base de Datos: MySQL.
- Seguridad: Hash de contraseñas, Prepared Statements, Sanitización básica.

ESTRUCTURA DE ARCHIVOS
----------------------

project/
├─ public/       # Archivos accesibles vía web
├─ inc/          # Archivos incluidos (lógica, conexión, templates)
├─ assets/       # CSS y JS
└─ sql/          # Script de base de datos
