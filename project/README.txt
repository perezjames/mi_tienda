===========================================
   MI TIENDA - SISTEMA DE GESTIÓN
===========================================

INSTALACIÓN
-----------
1. Asegúrate de tener XAMPP instalado y corriendo (Apache + MySQL)
2. Copia la carpeta 'project' en: c:\xampp\htdocs\mi_tienda\
3. Abre phpMyAdmin: http://localhost/phpmyadmin
4. Ejecuta el archivo: sql/schema.sql (crea la base de datos y tablas)
5. Ejecuta el archivo: sql/datos_prueba.sql (inserta datos de ejemplo)

ACCESO AL SISTEMA
-----------------
URL: http://localhost/mi_tienda/project/public/login.php

CREDENCIALES DE PRUEBA:
-----------------------
Administrador:
  - Tipo documento: DNI
  - Número: 12345678
  - Fecha nacimiento: Día 15, Mes Enero, Año 1990

Trabajador:
  - Tipo documento: DNI
  - Número: 87654321
  - Fecha nacimiento: Día 20, Mes Junio, Año 1995

ESTRUCTURA DEL SISTEMA
----------------------
✓ LOGIN: Validación por documento y fecha de nacimiento
✓ DASHBOARD: Tablas de movimientos, productos vendidos, ventas recientes
✓ NAVBAR: Menú lateral colapsable con icono de tres rayitas
✓ FOOTER: Estático y simple
✓ DISEÑO: 100% Bootstrap 5, sin CSS personalizado

CARACTERÍSTICAS
---------------
- Sin animaciones ni efectos visuales adicionales
- Solo clases Bootstrap 5
- Código simple y organizado
- Compatible con PHP + MySQL

ESTRUCTURA DE ARCHIVOS
-----------------------
project/
  public/     Archivos accesibles (login, dashboard, etc.)
  inc/        Archivos de inclusión (auth, db, header, footer, navbar)
  assets/     CSS y JavaScript
  sql/        Scripts de base de datos

NOTAS IMPORTANTES
-----------------
- El sistema NO usa contraseñas encriptadas en los datos de prueba
- Para producción, debes implementar hash de contraseñas
- Asegúrate de que MySQL esté corriendo en XAMPP
- La base de datos se llama 'tienda'

SOPORTE
-------
Repositorio: https://github.com/perezjames/mi_tienda