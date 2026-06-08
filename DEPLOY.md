# Guía de Despliegue con Docker (VPS & Render)

Esta guía te explica de forma clara y paso a paso cómo desplegar este proyecto utilizando Docker, tanto en tu propio Servidor Privado Virtual (VPS) como en Render.

---

## Opción A: Despliegue en un VPS (Usando Docker Compose)

Esta opción es ideal si tienes un VPS (Ubuntu, Debian, etc.) donde deseas tener control total y ejecutar tanto la aplicación web como la base de datos PostgreSQL en el mismo servidor de forma optimizada y segura.

### Requisitos previos en el VPS
Asegúrate de tener instalados **Docker** y **Docker Compose** en tu VPS. Si no los tienes, puedes instalarlos con:
```bash
sudo apt update
sudo apt install -y docker.io docker-compose
sudo systemctl enable --now docker
```

### Paso 1: Clonar o subir tu proyecto al VPS
Puedes subir tus archivos usando Git (recomendado) o mediante SFTP (FileZilla):
```bash
git clone <URL_DE_TU_REPOSITORIO>
cd RFMarketing-Gestion-Pedidos
```

### Paso 2: Iniciar la aplicación con Docker Compose
Ejecuta el siguiente comando en la raíz del proyecto para descargar las imágenes, construir el contenedor del servidor Apache y levantar la base de datos de manera automatizada:
```bash
docker-compose up -d --build
```
> **¿Qué hace este comando?**
> - `--build`: Compila tu imagen personalizada a partir del `Dockerfile` (instala PHP 8.2, las extensiones de PostgreSQL y las dependencias de Composer).
> - `-d`: Ejecuta los contenedores en segundo plano (modo detached), lo que te permite cerrar la terminal del VPS sin detener la aplicación.

### Paso 3: Migrar y poblar la Base de Datos (Primera vez)
Una vez que los contenedores estén corriendo, debes ejecutar las migraciones y seeders de tu CodeIgniter 4 para crear las tablas en la base de datos de Docker:
```bash
# Ejecutar las migraciones
docker exec -it rfmarketing_app php spark migrate

# Ejecutar los seeders (si tienes datos iniciales)
docker exec -it rfmarketing_app php spark db:seed
```

### Comandos útiles en el VPS
*   **Ver el estado de los contenedores:**
    ```bash
    docker-compose ps
    ```
*   **Ver los logs en tiempo real (útil para depurar errores):**
    ```bash
    docker-compose logs -f app
    ```
*   **Detener la aplicación:**
    ```bash
    docker-compose down
    ```

---

## Opción B: Despliegue en Render (PaaS)

Render es una plataforma en la nube excelente y fácil de usar. Al ser un entorno administrado, es altamente recomendable separar la base de datos y la aplicación en dos servicios individuales.

### Paso 1: Crear la Base de Datos PostgreSQL en Render
1. Inicia sesión en [Render](https://render.com/).
2. Haz clic en **New +** y selecciona **PostgreSQL**.
3. Configura los campos básicos:
   - **Name**: `rfmarketing-db`
   - **Database**: `db_rfmarketing`
   - **User**: `postgres`
4. Selecciona el plan gratuito o el de tu preferencia y haz clic en **Create Database**.
5. Espera a que se cree. Una vez completado, copia el **Internal Database URL** (para conectar la app con la BD de forma privada) o las credenciales individuales.

### Paso 2: Crear el Servicio Web (Web Service) en Render
1. Haz clic en **New +** y selecciona **Web Service**.
2. Conecta tu repositorio de GitHub o GitLab.
3. Configura el servicio web con los siguientes parámetros:
   - **Name**: `rfmarketing-app`
   - **Language**: `Docker` *(Render detectará automáticamente tu `Dockerfile`)*
   - **Branch**: `main` (o la rama que uses)
4. Desplázate hacia abajo hasta la sección **Advanced** y añade las siguientes **Variables de Entorno (Environment Variables)**:
   - `CI_ENVIRONMENT` = `production`
   - `database.default.hostname` = *(Pega el Hostname interno de tu PostgreSQL de Render, ej: `dpg-xxxxxx-a.oregon-postgres.render.com`)*
   - `database.default.database` = `db_rfmarketing`
   - `database.default.username` = `postgres`
   - `database.default.password` = *(La contraseña autogenerada por Render para tu BD)*
   - `database.default.DBDriver` = `Postgre`
   - `database.default.port` = `5432`
   - `PUSHER_APP_ID` = `2156424`
   - `PUSHER_KEY` = `1b4cf26bb870153e30b6`
   - `PUSHER_SECRET` = `5210e876a0d7b2a35620`
   - `PUSHER_CLUSTER` = `us2`

### Paso 2.5: Migraciones y Seeds Automáticos
**IMPORTANTE**: El `Dockerfile` de este proyecto está configurado para ejecutar automáticamente las migraciones y seeds cada vez que se inicia el contenedor. Esto significa que:
- Al crear una nueva base de datos en Render, las tablas se crearán automáticamente al desplegar
- Si recreas la base de datos, solo necesitas hacer un nuevo deploy para que se vuelvan a ejecutar las migraciones y seeds
- No necesitas ejecutar comandos manuales como en el VPS

Si necesitas forzar la ejecución de migraciones/seeds manualmente (por ejemplo, después de recrear la BD), simplemente haz un nuevo deploy vacío (commit sin cambios) o modifica cualquier archivo para forzar el rebuild.

### Paso 3: Persistencia de Imágenes en Render (Muy Importante)
Por defecto, los servicios web de Render tienen sistemas de archivos efímeros. Para que las imágenes que subas a la carpeta `public/uploads` **no se borren** al reiniciar o desplegar código nuevo:
1. En la configuración de tu Web Service en Render, ve a la sección **Disks**.
2. Haz clic en **Add Disk**.
3. Configura el disco:
   - **Name**: `uploads-disk`
   - **Mount Path**: `/var/www/html/public/uploads`
   - **Size**: `1 GiB` (o el tamaño que necesites)
4. Haz clic en **Save**. ¡Listo! Tus imágenes estarán a salvo de forma permanente.

---

## 🎨 Notas sobre la persistencia y almacenamiento local de imágenes
Tanto en tu VPS como en Render, hemos configurado los volúmenes para apuntar a:
- `/var/www/html/public/uploads` (Imágenes subidas por usuarios)
- `/var/www/html/writable` (Logs, sesiones y archivos temporales)

Esto asegura que no pierdas ningún dato cuando actualices el contenedor o subas una nueva versión de tu aplicación.
