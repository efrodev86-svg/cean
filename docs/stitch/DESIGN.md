# CEAN — Sistema de diseño para Google Stitch

> Pega este archivo en tu proyecto Stitch **antes** de generar cualquier pantalla.
> **Sube también el logo CEAN** como imagen de referencia en cada generación importante.
> Diagramas de flujo: [flujo-proceso.md](./flujo-proceso.md)

## Producto

- **Nombre completo:** CEAN — Control Escolar y Administración Normalista
- **Institución:** Escuela Normal Superior de Querétaro (ENSQ), Querétaro, México
- **Idioma UI:** Español (México)
- **Stack destino:** Laravel 13 + Blade + Tailwind CSS + Laravel Breeze

## Logo e identidad visual

Usar el logo oficial CEAN en headers, login, landing y sidebar admin.

**Composición del logo:**
- Icono documento/carpeta: contorno azul marino con esquina doblada cyan
- Texto **"CEAN"** y **"NORMALISTA"** en cyan claro, bold, mayúsculas
- Texto **"CONTROL ESCOLAR Y ADMINISTRACIÓN"** en azul marino, bold, mayúsculas, tamaño menor
- Cuatro puntos decorativos encima: cyan · naranja · rojo · azul marino (usar como acentos UI)

**Referencia de archivo:** `public/images/cean-logo.png` (logo institucional)

## Perfiles de usuario

CEAN tiene tres perfiles con shells, navegación y permisos distintos. Indica siempre el perfil al generar en Stitch.

| Perfil | Badge UI | Color acento | Layout | Autenticación |
|--------|----------|--------------|--------|---------------|
| **Administración** | pill cyan "Administración" | Modo oscuro · navy/cyan | **Sidebar izquierda** fija (no navbar) | Login único `/login` |
| **Docente** | pill cyan "Docente" | Sidebar navy, acentos cyan | Sidebar izquierda fija | Login único `/login` (mismo) |
| **Alumno** | pill cyan "Consulta pública" | Cyan `#59C1E3` en CTAs | Guest layout | **Sin login** — `/boleta` |

### Autenticación

| Tipo de acceso | Ruta | Métodos |
|----------------|------|---------|
| **Boleta pública** | `/boleta` | Matrícula + fecha de nacimiento (siempre sin sesión) |
| **Personal CEAN** | `/login` | Correo + contraseña **o** Google OAuth (Workspace) |
| **Post-login** | — | Redirige por `users.role`: admin → `/admin/*`, docente → `/docente/*` |

**Google OAuth:** Laravel Socialite. Restringir a dominio institucional Google Workspace (ej. `@ensq.edu.mx` o dominio configurado en `.env`).

### Navegación por perfil

**Administración:** Inicio · Alumnos · **Grupos** · Materias y Carreras · Docentes · Calificaciones · Ciclos

**Docente:** Inicio · Mis materias · Captura de calificaciones · Mis alumnos · Mi perfil

**Alumno:** Mi perfil · Boleta del periodo · Historial (Kardex)

### Reglas de permisos (UI)

- **Administración:** acceso total; único perfil que publica calificaciones, gestiona ciclos y CRUD global.
- **Docente:** solo sus materias y grupos; captura borrador; envía a revisión; no elimina alumnos.
- **Alumno:** solo consulta boleta/kardex; datos académicos solo lectura.

### Usuarios mock

| Perfil | Nombre | Credencial |
|--------|--------|------------|
| Administración | Control Escolar | `control@escuela.test` |
| Docente | Mtro. Carlos Hernández Ruiz | `docente@escuela.test` |
| Alumno | Ana García López | Matrícula `2025001` · Grupo A · 2° |

## Plataforma

- **Plataforma Stitch:** siempre elegir **Web** (no App móvil nativa) — Stitch genera layouts responsivos automáticamente
- **Admin (control escolar):** desktop-first, **modo oscuro**, **sidebar** fija desde `lg`; móvil = drawer + topbar compacta
- **Público / Alumno / Login:** **mobile-first** (diseñar primero 375px), escala a tablet y desktop
- **Docente:** sidebar drawer en móvil, sidebar fija desde `lg` (1024px)
- **Boleta oficial:** A4 fijo; toolbar web sí es responsive

## Diseño responsive

Stitch crea interfaces responsivas al pegar los prompts. **Especifica breakpoints** para controlar el resultado y mantener coherencia entre pantallas.

### Breakpoints (Tailwind)

| Breakpoint | Ancho | Uso |
|------------|-------|-----|
| **Móvil** (default) | 320–639px | Diseño base; columna única; botones ancho completo |
| **sm** | ≥640px | Formularios un poco más anchos; grids 2 cols en KPIs |
| **md** | ≥768px | Landing: tarjetas perfil en 2 columnas; tablas visibles |
| **lg** | ≥1024px | Sidebar admin y docente fijas 260px; tablas completas |
| **xl** | ≥1280px | Contenedor `max-w-7xl` centrado |

### Patrón landing / guest (referencia móvil CEAN)

Usar en prompts 1A, 1C, 6A (boleta pública):

- **Fondo exterior:** `#1f2937` (gray-800) con **patrón de puntos** blancos sutiles (dot grid)
- **Contenedor:** tarjeta blanca centrada `rounded-2xl`, `shadow-lg`, padding generoso, `max-w-sm` móvil → `max-w-md` desktop
- **Logo:** cuadrado blanco `rounded-xl` con logo CEAN centrado arriba
- **Título CEAN:** navy `#2C5EAB`, bold, centrado
- **Subtítulo:** gray-600, centrado, 2 líneas máx.
- **Tarjetas de perfil:** white, borde `gray-200`, `rounded-xl`, apiladas en móvil (`flex-col gap-4`)
  - Icono en círculo `#E8F6FB` (cyan suave) centrado
  - Título perfil navy bold
  - Subtexto gray-500 text-sm
  - Botón **ancho completo** al pie: primario sólido navy **o** secundario outline navy
- **Touch targets:** mínimo 44px alto en botones e inputs

### Patrón admin / tablas (modo oscuro + sidebar)

- **Shell:** sidebar izquierda navy oscuro `#0f2744` → `#1e3a5f` (no navbar superior)
- **Móvil:** sidebar en **drawer** (hamburger); topbar con logo + notificaciones + avatar; filtros apilados; tabla → **cards** oscuras
- **Tablet:** filtros en 2 cols; KPIs 2–3 columnas
- **Desktop:** sidebar fija 260px; área main fondo `#111827`; toolbar horizontal; tabla en superficie `#1f2937`; modales y slide-overs oscuros

### Patrón docente / alumno logueado

- **Móvil:** sidebar oculta → **drawer** desde izquierda (hamburger) o **bottom nav** 4 ítems
- **Desktop:** sidebar fija 260px + área main fluida

### Qué pedir en Stitch

Al final de cada prompt añadir (o usar el bloque del prompt):

```
Genera diseño web responsive. Prioriza vista móvil 375px. Indica comportamiento en tablet (768px) y desktop (1280px). Botones e inputs touch-friendly (min-h 44px).
```

## Tipografía

- **App web:** Montserrat o similar sans-serif bold para títulos; pesos 400, 500, 600, 700
- **Coincidir con logo:** títulos en bold/all-caps cuando sea encabezado de marca
- **Boleta impresa:** Times New Roman o serif equivalente

## Paleta de colores (extraída del logo)

| Token | Hex | Uso |
|-------|-----|-----|
| **CEAN Cyan** | `#59C1E3` | Marca "CEAN", títulos destacados, pestañas activas, iconos secundarios, focus ring, acentos |
| **CEAN Navy** | `#2C5EAB` | Botones primarios, navbar, links activos, bordes estructurales, texto institucional |
| **CEAN Navy hover** | `#245099` | Hover de botones primarios |
| **CEAN Orange** | `#F18F35` | Advertencias, badges pendiente, alertas amber sustituidas por naranja logo |
| **CEAN Red** | `#C82D31` | Errores, reprobatoria, calificación < 6, asistencia crítica |
| Fondo app | `#f3f4f6` / `gray-100` | Guest / landing (claro) |
| Fondo app admin | `#111827` / `gray-900` | Área principal modo oscuro |
| Sidebar admin | `#0f2744` → `#1e3a5f` | Nav vertical administración |
| Superficie clara | `#ffffff` | Guest, boleta consulta |
| Superficie oscura | `#1f2937` / `gray-800` | Tarjetas, tablas, modales admin |
| Borde oscuro | `#374151` / `gray-700` | Separadores en tema oscuro |
| Texto en oscuro | `#f9fafb` / `gray-100` | Títulos en admin |
| Texto secundario oscuro | `#9ca3af` / `gray-400` | Descripciones en admin |
| Fondo marca suave | `#E8F6FB` | Guest, iconos en círculo |
| Texto principal (claro) | `#1a1a2e` | Títulos en guest |
| Texto secundario | `#4b5563` / `gray-600` | Descripciones guest |
| Borde (claro) | `#e5e7eb` / `gray-200` | Tarjetas guest |
| Éxito | `#15803d` sobre `#f0fdf4` | Badge Activo, alertas éxito |
| Reprobatoria suave | `#FEF2F2` con texto `#C82D31` | Celda calificación < 6 |

**Regla de acentos:** los cuatro puntos del logo (cyan, naranja, rojo, navy) pueden usarse como indicadores de estado o decoración sutil en headers — nunca los cuatro juntos en exceso.

## Componentes (Tailwind + marca CEAN)

- **Botón primario:** `bg-[#2C5EAB] hover:bg-[#245099]`, texto blanco, `rounded-lg`, `font-semibold`
- **Botón secundario:** borde `#2C5EAB`, texto `#2C5EAB`, fondo transparente
- **Botón acento / CTA alumno:** `bg-[#59C1E3] hover:bg-[#4ab0d4]`, texto `#1a1a2e` o blanco según contraste
- **Pestaña activa:** fondo `#59C1E3` o borde inferior `#2C5EAB` 3px
- **Tarjetas:** `bg-white rounded-lg shadow-sm`, borde opcional `border-gray-200`, padding `p-6`
- **Inputs:** `rounded-md border-gray-300`, focus `ring-2 ring-[#59C1E3] border-[#59C1E3]`
- **Sidebar admin (modo oscuro):** fondo `#0f2744`, logo CEAN blanco/cyan, links `#e5e7eb`, activo barra lateral `#59C1E3` 4px + fondo `#1e3a5f`, badge pill "Administración" cyan
- **Topbar admin (móvil):** `#1f2937`, borde inferior `gray-700`, hamburger + logo + campana + avatar
- **Badges:** pill `rounded-full`; activo verde; inactivo gray; advertencia `#F18F35`; error `#C82D31`
- **Barra progreso KPI:** relleno `#59C1E3`, track `#E8F6FB`

## Layouts de la app

### Guest layout (público / alumno Fase 1)
Centrado vertical, **logo CEAN completo** arriba, tarjeta blanca `max-w-md` sobre fondo `#E8F6FB` → `gray-100`. Usado por perfil **Alumno** (consulta boleta) y **Público** (landing).

### App layout — Administración (modo oscuro)
- **Sidebar izquierda fija** 260px: logo CEAN, badge "Administración", nav vertical (Inicio · Alumnos · Grupos · Materias y Carreras · Docentes · Calificaciones · Ciclos), pie "Control Escolar"
- **NO usar navbar horizontal** como navegación principal
- **Main:** fondo `#111827`, header de página título cyan/blanco, breadcrumb `gray-400`
- **Contenido:** `py-8 px-6`, `max-w-7xl`; tarjetas `#1f2937` borde `gray-700`
- **Móvil:** sidebar → drawer; topbar compacta

### App layout — Docente
- Sidebar fija `#2C5EAB`, badge "Docente", nav vertical, activo barra cyan 4px
- Área principal: header white + breadcrumb, fondo `gray-100`

### Sidebar portal — Alumno (Fase 2)
Fondo `#2C5EAB`, badge "Alumno", texto blanco, item activo barra lateral `#59C1E3` 4px, logo CEAN arriba.

## Datos de ejemplo (usar en mocks)

- Ciclo activo: `2023-2024`
- Matrícula: `201559590000`
- **Grupo escolar:** unidad Semestre + Salón + Licenciatura + Ciclo (ej. `2°-A TELESECUNDARIA · 2023-2024`)
- **Asignación:** Materia + Docente titular **por grupo** (Grupo 2°-B puede tener otro docente que 2°-A)
- **Boleta semestre-grupo:** letra del salón → `2- A`
- **Orden config:** Materias (3) → Docentes (4) → **Grupos (4C)** → Alumnos (2)
- Materias: APRENDIZAJE EN EL SERVICIO, DIDÁCTICA DE LAS MATEMÁTICAS
- Licenciatura: TELESECUNDARIA / ENSEÑANZA Y APRENDIZAJE DEL ESPAÑOL EN EDUCACIÓN SECUNDARIA
- Calificación mínima aprobatoria: 6 · Escala: 0–10
- **Administración:** Control Escolar · `control@escuela.test`
- **Docente:** Mtro. Carlos Hernández Ruiz · `docente@escuela.test` · titular en 2°-A y 2°-B Didáctica Matemáticas, 8°-A Aprendizaje
- **Alumno:** Ana García López · `2025001` · inscrita en **Grupo 2°-A TELESECUNDARIA**

## Accesibilidad

- Contraste WCAG 2.1 AA (verificar cyan `#59C1E3` sobre blanco — usar navy para texto pequeño)
- Focus rings visibles (`focus:ring-2 focus:ring-[#59C1E3]`)
- Labels asociados a todos los campos de formulario

## Prompt base para Stitch (copiar al inicio de cada generación)

```
Identidad CEAN: logo documento cyan/navy, colores #59C1E3, #2C5EAB, #F18F35, #C82D31. Montserrat bold.
Perfil usuario: [Administración | Docente | Alumno | Público] — shell según DESIGN.md.
Plataforma: Web responsive. Prioriza móvil 375px; escala a tablet 768px y desktop 1280px. Touch targets min 44px.
Sube logo CEAN como referencia visual.
```
