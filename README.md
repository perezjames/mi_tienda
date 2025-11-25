# Mi Tienda

¡Hola! 👋 Este es un proyecto sencillo de gestión de inventario y ventas que armé usando **PHP** y **MySQL**. Es ideal para aprender cómo funciona un sistema básico de tienda por dentro

## ¿Qué hace?
Básicamente te ayuda a controlar:
- 📦 Productos y stock
- 💰 Ventas
- 👥 Clientes y Proveedores
- 📊 Reportes simples

## ¿Cómo lo pruebo?
1. Clona el repo o descarga los archivos en tu carpeta `htdocs` (si usas XAMPP)
2. Importa el archivo `project/sql/schema.sql` en tu base de datos (creará una DB llamada `tienda`)
3. Configura la conexión en `project/inc/db.php` si tu usuario de MySQL no es `root` sin contraseña
4. Abre el navegador y ve a `http://localhost/mi_tienda/project/public/login.php`

## Credenciales de prueba
Para entrar rápido y romper cosas:
- **Admin:** `administrador` / `123`
- **Trabajador:** `trabajador` / `123`