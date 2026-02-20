# SQLMyAdmin

Consola de administracion tecnica de bases de datos para desarrolladores. Este sistema permite la conexion dinamica, el listado de tablas y la ejecucion de consultas SQL manuales mediante PDO.

## Caracteristicas

- Arquitectura modular basada en servicios y DTOs bajo estandares PSR-4.
- Uso de caracteristicas modernas de PHP 8.2 (strict typing, constructor property promotion, enums, readonly properties).
- Interfaz de usuario inspirada en Shadcn UI (Slate palette) con Tailwind CSS.
- Editor SQL con soporte para atajos de teclado (Ctrl + Enter).
- Sistema de logs tecnicos para auditoria de operaciones y errores.
- Prevencion de XSS en la visualizacion de datos.

## Requisitos

- PHP 8.2 o superior.
- Extension PDO MySQL habilitada.
- Servidor web (WAMP, XAMPP, Nginx, Apache).

## Estructura del Proyecto

- Core: Servicios de base de datos y objetos de respuesta (DatabaseService, QueryResponse).
- Services: Logica de soporte como el sistema de logs (LoggerService).
- UI: Componentes de presentacion y renderizado HTML (ComponentRenderer).
- logs: Directorio para el registro de actividad de la aplicacion.
- index.php: Controlador principal y enrutador.

## Instalacion

1. Clonar el repositorio en el directorio del servidor web.
2. Asegurar que el servidor tiene permisos de escritura en la carpeta logs.
3. Acceder mediante el navegador al directorio del proyecto.

## Uso

1. Ingresar las credenciales de conexion (Host, Base de Datos, Usuario, Contrasena).
2. Explorar las tablas del sidebar para ejecutar consultas rapidas.
3. Utilizar el editor SQL para realizar consultas personalizadas sobre la base de datos conectada.

## Seguridad

Este sistema es una herramienta de administracion tecnica. Las credenciales se almacenan en la sesion actual y se recomienda su uso en entornos de desarrollo o redes privadas controladas.
