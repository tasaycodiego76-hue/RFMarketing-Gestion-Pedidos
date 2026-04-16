# Guía de Despliegue - Proyecto RFMarketing-Gestion-Pedidos

Este documento detalla los pasos para clonar, configurar y ejecutar el proyecto localmente.

## 1. Clonar el proyecto

Abre una terminal en la carpeta donde desees guardar el proyecto y ejecuta:

```bash
git clone https://github.com/tasaycodiego76-hue/RFMarketing-Gestion-Pedidos.git
```

## 2. Instalación de Dependencias

Entra a la carpeta clonada y abre tu editor (VS Code). En la terminal, ejecuta:

```bash
composer install
```

## 3. Configuración del Entorno (.env)

1. Busca el archivo llamado `env` en la raíz del proyecto.
2. Haz una copia y cámbiale el nombre a `.env`.
3. Abre el archivo `.env` y en la sección Database configura tus credenciales de PostgreSQL:

```ini
database.default.hostname = localhost
database.default.database = NOMBRE_DE_TU_BASE_DE_DATOS
database.default.username = postgres
database.default.password = TU_CONTRASEÑA
database.default.DBDriver = Postgre
database.default.port = 5432
```

## 4. Preparar la Base de Datos

Ejecuta los siguientes comandos para crear las tablas y cargar los datos de prueba:

**Crear tablas:**

```bash
php spark migrate
```

**Cargar registros (Semillas):**

```bash
php spark db:seed DatabaseSeeder
```

## 5. Ejecutar el Proyecto

Inicia el servidor local con el siguiente comando:

```bash
php spark serve
```

## 6. Enlaces de Verificación de Avance

Con el servidor activo, puedes acceder a las siguientes rutas para validar el funcionamiento:

- **Vista Cliente:** http://localhost:8080/cliente/mis_solicitudes?test_user=9
- **Vista Administrador:** http://localhost:8080/index.php/admin/dashboard?test_user=1

---

# Modelo Relacional DB

Aqui nuestro Modelo Relacional el cual es la Estructura de nuestra DB:

- **MODELO RELACIONAL DB:** https://drawsql.app/teams/sandro-10/diagrams/modelo-relacional-bd-rf-agencia-marketing

---

**Nota:** Asegúrate de tener las extensiones `intl` y `mbstring` habilitadas en tu PHP local.