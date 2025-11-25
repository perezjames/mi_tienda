Sistema de gestión de inventario y ventas
Proyecto básico desarrollado en PHP y MySQL para ejecutarse en XAMPP

INSTALACIÓN
   1. Copiar la carpeta "project" en: C:\xampp\htdocs\mi_tienda
   2. Activar Apache y MySQL en XAMPP.
   3. Crear la base de datos desde phpMyAdmin importando el archivo: sql/schema.sql
      Esto crea la base de datos "tienda" y todas las tablas.
   4. Ajustar la conexión en inc/db.php si es necesario (por defecto: root sin contraseña).

ACCESO
   Abrir en el navegador: http://localhost/project/mi_tienda/public/login.php

   Credenciales por defecto:
      Rol         administrador  trabajador  proveedor   cliente
      Email       administrador  trabajador  proveedor   cliente
      Contraseña  123            123         133         123

FUNCIONALIDADES
   Dashboard con resumen general
   Gestión de productos con control de stock
   Registro de ventas
   Gestión de clientes y proveedores
   Usuarios y roles (solo administrador)
   Reportes filtrados y exportación
   Historial de acciones

TECNOLOGÍAS
   PHP con PDO
   MySQL
   Bootstrap 5
   Seguridad básica (hash de contraseñas y prepared statements)

ESTRUCTURA DE ARCHIVOS
   project/
      public/     vista pública
      inc/        lógica
      assets/     recursos
      sql/        esquema database