# Sistema de Control de Requerimientos - RF Agencia de Marketing SAC

Sistema web para la gestión centralizada de pedidos y requerimientos de RF Agencia de Marketing SAC. Permite la trazabilidad completa, asignación de tareas por roles y seguimiento en tiempo real.

## Stack Tecnológico

- **Backend:** PHP 8.2, CodeIgniter 4, PostgreSQL 15+
- **Frontend:** Bootstrap 5, JavaScript (Fetch API)
- **Librerías:** SweetAlert2, DataTables, SortableJS
- **Arquitectura:** MVC con seguridad CSRF, filtros XSS y Query Builder

## Estructura del Proyecto

```
RFMarketing-Gestion-Pedidos/
├── app/
│   ├── Controllers/          # Controladores por rol
│   ├── Models/               # Modelos de datos
│   ├── Views/                # Vistas por rol
│   ├── Database/             # Migraciones y Seeds
│   └── Libraries/            # Librerías personalizadas
├── public/                   # Archivos públicos
├── writable/                 # Archivos adjuntos (no público)
├── env                       # Plantilla de configuración
├── composer.json             # Dependencias PHP
└── README.md                 # Este archivo
```

## Instalación

### 1. Clonar el proyecto

Abre una terminal en la carpeta donde desees guardar el proyecto y ejecuta:

```bash
git clone https://github.com/tasaycodiego76-hue/RFMarketing-Gestion-Pedidos.git
```

### 2. Instalar dependencias

```bash
composer install
```

```bash
composer require spipu/html2pdf
```

```bash
composer require pusher/pusher-php-server
```


### 3. Configurar entorno

Copia el archivo `env` a `.env` y configura las credenciales de PostgreSQL:

```ini
database.default.hostname = localhost
database.default.database = NOMBRE_DE_TU_BASE_DE_DATOS
database.default.username = postgres
database.default.password = TU_CONTRASEÑA
database.default.DBDriver = Postgre
database.default.port = 5432
```

### 4. Preparar base de datos

```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

### 5. Ejecutar servidor

```bash
php spark serve
```

## Credenciales de Acceso

Accede a http://localhost:8080/auth/login

El sistema incluye usuarios de prueba para cada rol (administrador, responsables de área, empleados y clientes) que se cargan automáticamente al ejecutar el seed. Consulta el archivo `app/Database/Seeds/UsuariosSeeder.php` para ver las credenciales específicas.

## Modelo de Base de Datos

https://drawsql.app/teams/sandro-10/diagrams/modelo-relacional-bd-rf-agencia-marketing

---

**Nota:** Requiere extensiones PHP `intl` y `mbstring` habilitadas.
