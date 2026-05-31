# CEAN — Control Escolar

Sistema web para administración escolar (Fase 1):

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

### Alumnos de prueba

| Matrícula | Fecha nacimiento |
|-----------|------------------|
| `2025001` | 2005-01-01 |
| `2025002` | 2005-02-15 |

## Rutas principales

| Ruta | Descripción |
|------|-------------|
| `/` | Página de inicio |
| `/boleta` | Consulta de boleta (público) |
| `/login` | Acceso personal escolar |
| `/admin/dashboard` | Panel de control escolar |
| `/admin/calificaciones` | Importar calificaciones CSV |

## Formato CSV para calificaciones

```csv
matricula,materia,calificacion,faltas
2025001,Matemáticas,9.0,2
2025001,Español,8.5,0
2025002,Matemáticas,7.8,1
```

- `faltas` es opcional (default 0)
- La primera fila puede ser encabezado
- Selecciona el bimestre antes de importar

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
