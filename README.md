# 🚀 Sistema de Gestión de Proyectos de Software

## 📋 Descripción
Sistema web completo para la gestión de proyectos de software con roles diferenciados (Admin, Desarrollador, Gestor de Proyecto), control de asistencia y gestión de tareas.

## 🏗️ Arquitectura
- **Patrón**: MVC (Modelo-Vista-Controlador)
- **Lenguaje**: PHP 7.4+
- **Base de Datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Servidor**: Apache (XAMPP)

## 📁 Estructura del Proyecto
```
sistema-de-gestion-de-proyectos-de-software/
├── index.php                       # Punto de entrada
├── core/                           # Núcleo del sistema
│   ├── Router.php                 # Enrutador
│   └── Controller.php             # Controlador base
├── app/
│   ├── config/                    # Configuración
│   │   ├── config.php             # Configuración general
│   │   └── database.php           # Conexión BD
│   ├── controllers/               # Controladores
│   │   ├── AuthController.php     # Autenticación
│   │   ├── UsuarioController.php
│   │   ├── AsistenciaController.php
│   │   ├── ProyectoController.php
│   │   └── TareaController.php
│   ├── models/                    # Modelos
│   │   ├── Usuario.php
│   │   ├── Asistencia.php
│   │   ├── Proyecto.php
│   │   └── Tarea.php
│   ├── middleware/                # Middleware
│   │   └── AuthMiddleware.php     # Protección rutas
│   └── views/                     # Vistas
│       ├── auth/
│       ├── admin/
│       └── desarrollador/
├── public/                        # Recursos públicos
│   ├── css/
│   ├── js/
│   └── img/
├── bd.sql                         # Script base de datos
├── verificar_proyecto.php         # Script verificación
└── README.md                      # Este archivo
```

## ⚙️ Instalación

### 1. Requisitos Previos
- XAMPP instalado
- PHP 7.4+
- MySQL/MariaDB
- Navegador web moderno

### 2. Configuración del Servidor
1. Copiar el proyecto a `C:\xampp\htdocs\`
2. Iniciar Apache y MySQL desde XAMPP Control Panel

### 3. Base de Datos
1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Crear nueva base de datos o usar el script:
   - Abrir pestaña "SQL"
   - Pegar contenido de `bd.sql`
   - Ejecutar script

### 4. Configuración del Proyecto
1. Editar `app/config/database.php` si es necesario:
   ```php
   private static $host = "localhost";
   private static $db_name = "prueba_proyectos";
   private static $username = "root";
   private static $password = "";
   ```

### 5. Verificación
1. Ejecutar: `http://localhost/sistema-de-gestion-de-proyectos-de-software/verificar_proyecto.php`
2. Corregir errores mostrados

## 🚪 Acceso al Sistema

### URL Principal
```
http://localhost/sistema-de-gestion-de-proyectos-de-software/
```

### Usuarios de Prueba
| Rol | Email | Contraseña |
|-----|--------|------------|
| Admin | admin@empresa.com | 123456 |
| Desarrollador | juan.perez@empresa.com | 123456 |
| Gestor | maria.garcia@empresa.com | 123456 |

## 🎯 Funcionalidades

### 👑 Administrador
- ✅ Dashboard con resumen de proyectos
- ✅ Gestión completa de usuarios
- ✅ Creación y asignación de proyectos
- ✅ Reportes y exportación
- ✅ Control total del sistema

### 👨‍💻 Desarrollador
- ✅ Dashboard personalizado
- ✅ Registro de asistencia diario
- ✅ Vista de proyectos asignados
- ✅ Gestión de tareas
- ✅ Actualización de progreso

### 👔 Gestor de Proyecto
- ✅ Dashboard de gestión
- ✅ Supervisión de proyectos
- ✅ Asignación de tareas
- ✅ Control de avances
- ✅ Reportes de equipo

## 🔐 Sistema de Seguridad

### Autenticación
- Hash de contraseñas con `password_hash()`
- Sesiones PHP seguras
- Middleware de protección de rutas

### Autorización
- Control de acceso por roles
- Validación de permisos en cada acción
- Redirección automática según rol

## 📊 Base de Datos

### Tablas Principales
- `usuarios` - Información de usuarios
- `roles` - Roles del sistema
- `proyectos` - Proyectos de software
- `tareas` - Tareas asignadas
- `asistencias` - Control de asistencia
- `notificaciones` - Sistema de notificaciones

### Relaciones
- Usuario → Rol (N:1)
- Usuario → Asistencias (1:N)
- Proyecto → Tareas (1:N)
- Usuario → Tareas (1:N)

## 🛠️ Desarrollo

### Agregar Nuevo Controlador
1. Crear archivo en `app/controllers/`
2. Extender de `Controller`
3. Implementar métodos públicos
4. Crear vistas correspondientes

### Agregar Nuevo Modelo
1. Crear archivo en `app/models/`
2. Configurar conexión a BD
3. Implementar métodos CRUD
4. Usar en controladores

### Agregar Nueva Vista
1. Crear archivo en `app/views/`
2. Usar HTML + PHP
3. Incluir CSS/JS necesarios
4. Llamar desde controlador

## 🔧 Solución de Problemas

### Error: "Página no encontrada"
- Verificar que Apache esté ejecutándose
- Verificar que mod_rewrite esté habilitado
- Comprobar la URL de acceso

### Error: "Error de conexión"
- Verificar que MySQL esté ejecutándose
- Comprobar credenciales en `database.php`
- Verificar que la base de datos exista

### CSS/JS no cargan
- Verificar rutas en archivos de vista
- Comprobar que los archivos existan en `public/`
- Verificar permisos de carpetas

### Problemas de sesión
- Verificar que `session_start()` se ejecute
- Comprobar permisos de carpeta temporal
- Verificar configuración PHP

## 🚀 Despliegue en Producción

### Preparación
1. Cambiar credenciales de BD
2. Activar modo producción en config
3. Optimizar archivos CSS/JS
4. Configurar SSL
5. Configurar copias de seguridad

### Seguridad Adicional
- Cambiar contraseñas por defecto
- Configurar firewall
- Activar logs de acceso
- Implementar rate limiting
- Configurar HTTPS

## 📝 Changelog

### Versión 1.0 (Actual)
- ✅ Sistema de autenticación completo
- ✅ Gestión de usuarios por roles
- ✅ Dashboard diferenciado por rol
- ✅ Control de asistencia básico
- ✅ Gestión de proyectos y tareas
- ✅ Interface responsive
- ✅ Configuración dinámica de rutas

### Próximas Versiones
- 🔄 Sistema de notificaciones en tiempo real
- 🔄 Reportes avanzados con gráficos
- 🔄 API REST para integraciones
- 🔄 Módulo de facturación
- 🔄 Chat interno del equipo

## 👥 Contribución
Para contribuir al proyecto:
1. Fork del repositorio
2. Crear rama feature
3. Realizar cambios
4. Crear pull request
5. Revisión y merge

## 📞 Soporte
Para soporte técnico:
- Email: soporte@empresa.com
- Documentación: [Wiki del proyecto]
- Issues: [GitHub Issues]

## 📄 Licencia
Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

---

**Desarrollado por:** Equipo de Desarrollo
**Última actualización:** Octubre 2025
**Versión:** 1.0.0