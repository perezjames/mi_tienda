# Mi Tienda - Sistema de Gestión con Login por Roles

¡Hola! 👋 Este es un proyecto de gestión de inventario y ventas con un sistema de autenticación por roles moderno y seguro usando **PHP** y **MySQL**.

## 🆕 NUEVO SISTEMA DE LOGIN

El sistema ahora cuenta con un **login dinámico por roles** con las siguientes características:

- ✅ **4 Roles diferentes**: Administrador, Trabajador/Colaborador, Proveedor, Cliente
- ✅ **Autenticación por documento**: Tipo de documento + número + fecha de nacimiento
- ✅ **Formularios dinámicos**: Se despliegan campos según el rol seleccionado
- ✅ **Validaciones completas**: Alertas personalizadas para cada error
- ✅ **Diseño moderno**: Interfaz con gradientes y animaciones
- ✅ **Seguridad**: Contraseñas hasheadas con bcrypt
- ✅ **Sistema de registro**: Los usuarios pueden crear sus propias cuentas

## ¿Qué hace?
Básicamente te ayuda a controlar:
- 📦 Productos y stock
- 💰 Ventas
- 👥 Clientes y Proveedores
- 📊 Reportes simples
- 🔐 Usuarios con diferentes roles

## 🚀 Instalación Rápida

### Opción 1: Automática (Windows)
1. Asegúrate de que XAMPP esté corriendo (MySQL + Apache)
2. Ejecuta: `project\sql\INSTALAR.bat`
3. ¡Listo! Accede a: `http://localhost/mi_tienda/project/public/login.php`

### Opción 2: Manual
1. Clona el repo o descarga los archivos en tu carpeta `htdocs`
2. Importa `project/sql/schema.sql` en MySQL/phpMyAdmin
3. Ejecuta desde terminal: `php project/sql/generar_datos.php`
4. Configura la conexión en `project/inc/db.php` si es necesario
5. Abre: `http://localhost/mi_tienda/project/public/login.php`

### Verificar Instalación
Ejecuta desde PowerShell:
```powershell
.\verificar.ps1
```

## 👤 Usuarios de Prueba

Después de la instalación tendrás estos usuarios listos para usar:

### 👑 Administrador
- **Documento**: CC 1234567890
- **Fecha**: 15/Mayo/1990
- **Contraseña**: `admin123`

### 👷 Trabajador
- **Documento**: CC 9876543210
- **Fecha**: 20/Agosto/1995
- **Contraseña**: `trabajador123`

### 📦 Proveedor
- **Documento**: NIT 900123456
- **Fecha**: 10/Marzo/1985
- **Contraseña**: `proveedor123`

### 🛍️ Cliente
- **Documento**: CC 5555555555
- **Fecha**: 25/Diciembre/2000
- **Contraseña**: `cliente123`

## 📁 Estructura del Proyecto

```
mi_tienda/
├── project/
│   ├── assets/
│   │   ├── css/style.css
│   │   └── js/scripts.js
│   ├── inc/
│   │   ├── auth.php          (Nueva función login_por_rol)
│   │   ├── db.php
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── ...
│   ├── public/
│   │   ├── login.php         (Rediseñado - Login dinámico)
│   │   ├── registro.php      (NUEVO - Registro de usuarios)
│   │   ├── guia.php          (NUEVO - Guía visual)
│   │   ├── dashboard.php
│   │   └── ...
│   ├── sql/
│   │   ├── schema.sql        (Actualizado - Nuevas tablas)
│   │   ├── generar_datos.php (NUEVO - Script de datos)
│   │   ├── INSTALAR.bat      (NUEVO - Instalador automático)
│   │   └── datos_ejemplo.sql
│   ├── INSTRUCCIONES_LOGIN.md (NUEVO - Documentación completa)
│   └── RESUMEN_CAMBIOS.txt    (NUEVO - Resumen de cambios)
└── verificar.ps1              (NUEVO - Script de verificación)
```

## 🔐 Características de Seguridad

- Contraseñas hasheadas con **bcrypt** (PASSWORD_DEFAULT)
- Prepared statements para prevenir **SQL Injection**
- Sanitización de todos los inputs
- Validación de sesiones
- Verificación de roles en cada acción
- Campos obligatorios con validación frontend y backend

## 📚 Documentación

- **Guía Visual**: `http://localhost/mi_tienda/project/public/guia.php`
- **Instrucciones Completas**: `project/INSTRUCCIONES_LOGIN.md`
- **Resumen de Cambios**: `project/RESUMEN_CAMBIOS.txt`

## 🎨 Diseño del Login

- Fondo con gradiente morado/azul (#667eea - #764ba2)
- Logo "Mi Tienda" con ícono 🛒
- Formulario responsive con Bootstrap 5
- Campos tipo "floating labels"
- Animaciones suaves en botones
- Alertas personalizadas con iconos
- Campo NIT solo visible para proveedores

## 🛠️ Tecnologías

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3
- **Servidor**: Apache (XAMPP)

## 📞 Soporte

Si tienes problemas:
1. Verifica que MySQL y Apache estén corriendo
2. Revisa la configuración en `project/inc/db.php`
3. Consulta `project/INSTRUCCIONES_LOGIN.md`
4. Ejecuta `verificar.ps1` para diagnosticar problemas

## 🔄 Cambios Recientes

### v2.0 - Sistema de Login por Roles
- ✨ Nuevo sistema de autenticación por documento y fecha
- ✨ Tablas separadas por rol (administradores, trabajadores, proveedores, clientes)
- ✨ Formulario dinámico que cambia según el rol
- ✨ Página de registro de usuarios
- ✨ Scripts de instalación automática
- ✨ Documentación completa

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.