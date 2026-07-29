# CEAN — Control Escolar y Administración Normalista

Sistema web para la **Escuela Normal Superior de Querétaro (ENSQ)**. CEAN significa *Control Escolar y Administración Normalista*.

Fase 1:

- **Alumnos:** consulta de boletas con matrícula + fecha de nacimiento
- **Control escolar:** carga masiva de calificaciones vía CSV

## Stack

- PHP 8.3+
- Laravel 13
- Tailwind CSS (Laravel Breeze)
- SQLite (desarrollo) / MySQL (producción en hosting compartido)

## Desarrollo local (Laravel Herd)

El proyecto está en `~/Herd/cean`, accesible como **http://cean.test** si Herd detecta la carpeta.

```bash
composer install
cp .env.example .env   # si aún no existe
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

### Credenciales de prueba (después del seed)

| Rol | Email | Contraseña |
|-----|-------|------------|
| Control escolar | `control@escuela.test` | `password` |
| Docente | `docente@escuela.test` | `password` |

### Alumnos de prueba

| Matrícula | Fecha nacimiento | Notas |
|-----------|------------------|-------|
| `201559590000` | 2000-01-15 | Ejemplo del PDF oficial (8° semestre par) |
| `2025001` | 2005-01-01 | Demo con 3 materias |

## Inicio de sesión con Google (personal)

En `/login`, el botón **Continuar con Google** usa Laravel Socialite. Solo entran usuarios ya registrados en CEAN con correo del dominio institucional.

### 1. Credenciales en Google Cloud

1. [Google Cloud Console](https://console.cloud.google.com/) → APIs y servicios → **Credenciales** → Crear **ID de cliente OAuth** (aplicación web).
2. **URI de redirección autorizada:** debe coincidir con `GOOGLE_REDIRECT_URI` (por defecto `{APP_URL}/auth/google/callback`, p. ej. `http://cean.test/auth/google/callback` en Herd).
3. Copia **Client ID** y **Client secret** al `.env`:

```env
GOOGLE_CLIENT_ID=tu-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
GOOGLE_ALLOWED_DOMAIN=ensq.edu.mx
```

4. El correo del usuario en la tabla `users` debe existir y coincidir con su cuenta de Google (p. ej. `control@escuela.test` en desarrollo; en producción, correos `@ensq.edu.mx` dados de alta por control escolar).

Tras cambiar `.env`, ejecuta `php artisan config:clear` si usas caché de configuración.

## Rutas principales

| Ruta | Descripción |
|------|-------------|
| `/` | Página de inicio |
| `/boleta` | Consulta de boleta (público) |
| `/login` | Acceso personal escolar |
| `/admin/dashboard` | Panel de control escolar |
| `/admin/materias` | Catálogo de licenciaturas y materias del plan |
| `/admin/calificaciones` | Importar calificaciones CSV |

## Formato CSV para calificaciones

```csv
matricula,materia,calificacion,asistencia
201559590000,APRENDIZAJE EN EL SERVICIO,9,95
```

- `asistencia` es el porcentaje (0-100), opcional (default 100)
- La primera fila puede ser encabezado
- Selecciona el semestre antes de importar

## Configuración de la boleta (`.env`)

```env
BOLETA_ESCUELA="ESCUELA NORMAL SUPERIOR DE QUERÉTARO"
BOLETA_DIRECTOR="MTRO. ROBERTO COMPEÁN MARTÍNEZ"
BOLETA_CIUDAD="SANTIAGO DE QUERÉTARO, QRO."
BOLETA_LICENCIATURA="LICENCIATURA EN ENSEÑANZA Y APRENDIZAJE EN"
BOLETA_CODIGO="FM.ENCE.14"
BOLETA_VERSION="00"
```

## Despliegue en hosting compartido

Requisitos típicos: PHP 8.3, extensiones `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, MySQL.

### 1. Subir el proyecto

Sube todo el repositorio **excepto** `node_modules/` y `.env` (crea `.env` en el servidor).

### 2. Document root

Apunta el dominio a la carpeta **`public/`** del proyecto.  
Si el hosting no permite cambiar el document root:

1. Mueve el contenido de `public/` a `public_html/`
2. Edita `public_html/index.php` y ajusta las rutas:

```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

### 3. Configurar `.env` en producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=tu_base
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 4. Comandos en el servidor

Ejecuta vía SSH o el terminal del panel (cPanel/Plesk):

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Compila assets **en tu máquina** antes de subir (el hosting compartido rara vez tiene Node):

```bash
npm ci && npm run build
```

Sube la carpeta `public/build/` generada.

### 5. Permisos

```bash
chmod -R 775 storage bootstrap/cache
```

## GitHub

```bash
git init
git add .
git commit -m "Fase 1: consulta de boletas y carga de calificaciones"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/cean.git
git push -u origin main
```

> No subas `.env` ni credenciales. El archivo ya está en `.gitignore`.

## Próximas fases (sugeridas)

- Alta/edición de alumnos desde el panel
- Gestión de ciclos escolares
- Exportar boletas en PDF
- Roles adicionales (director, maestro)
