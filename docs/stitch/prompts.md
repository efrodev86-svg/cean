# Prompts Google Stitch — CEAN

> Diagramas de flujo del proceso: [flujo-proceso.md](./flujo-proceso.md)  
> Proyecto ejecutivo (Notion): [proyecto-ejecutivo.md](../proyecto-ejecutivo.md) · [CSV módulos](../cean-modulos-notion.csv)

Orden recomendado de generación:

1. `DESIGN.md` → subir primero al proyecto Stitch
2. **Logo CEAN** → `public/images/cean-logo.png` como referencia visual
3. **Shells por perfil** → Prompts 0A, 0B, 0C (uno por rol)
4. Módulos en **orden académico** (ver flujo de grupos abajo)
5. Iterar con follow-ups puntuales

### Orden académico recomendado (importante para grupos)

```
3 Materias → 4 Docentes → 4C Grupos (maestros + materias por salón) → 2 Alumnos → 5 Calificaciones
```

| Paso | Módulo | Qué configuras |
|------|--------|----------------|
| 1 | **3** Licenciaturas y materias | Catálogo de asignaturas por plan |
| 2 | **4** Docentes | Plantilla docente (sin asignar a salón aún) |
| 3 | **4C** Grupos escolares | Crear 2°-A, 2°-B… y asignar **materia + docente por grupo** |
| 4 | **2** Alumnos | Inscribir alumno en un **grupo ya creado** |
| 5 | **5 / 7** Actas | Calificaciones del grupo + materia + docente |

**Convención:** cada bloque entre comillas es un prompt independiente para pegar en Stitch.
- Plataforma Stitch: **Web** (genera UI responsiva automáticamente)
- Incluye siempre **Perfil usuario** + **breakpoints** según la guía responsive abajo
- Sube logo CEAN como referencia visual

---

## Guía responsive (Stitch crea layouts adaptativos)

Stitch interpreta "Web" como responsive. **Sin breakpoints explícitos, cada prompt produce resultados distintos.** Usa estos bloques según tipo de pantalla:

### Bloque A — Guest / Landing / Login / Alumno consulta
*(Prompts 1A, 1C, 6A)*

```
Responsive: mobile-first 375px. Fondo gray-800 #1f2937 con dot grid sutil. Contenedor white rounded-2xl shadow-lg centrado max-w-sm (móvil) max-w-md (md+). Logo en cuadrado white rounded-xl. Tarjetas perfil apiladas gap-4; iconos en círculo #E8F6FB; botones w-full min-h-[44px]. Tablet md: tarjetas perfil grid-cols-2. Desktop: contenedor max-w-lg centrado.
```

### Bloque B — Admin / tablas / dashboards (modo oscuro + sidebar)
*(Prompts 0A, 2–5, **4C**)*

**Siempre incluir en pantallas admin:** `Shell admin CEAN: modo oscuro + sidebar izquierda (prompt 0A). NO navbar superior.`

```
Tema oscuro CEAN: fondo main #111827, tarjetas #1f2937, bordes gray-700, texto claro, acentos cyan #59C1E3 y navy #2C5EAB.
Shell: sidebar izquierda 260px navy oscuro #0f2744 (NO navbar horizontal). Nav: Inicio, Alumnos, Grupos, Materias y Carreras, Docentes, Calificaciones, Ciclos.
Responsive: desktop-first max-w-7xl. Móvil: sidebar en drawer 280px (hamburger), topbar compacta logo+campana+avatar; KPIs 1 col; filtros apilados; tabla → cards oscuras. Tablet: KPIs 2-3 cols. Desktop lg+: sidebar fija, main fluido, tabla completa scroll-x. Modales fondo #1f2937, full-screen móvil.
```

### Bloque C — Docente / portal alumno con sidebar
*(Prompts 0B, 0C, 6C–D, 7A–D)*

```
Responsive: móvil sidebar oculta, botón hamburguesa abre drawer 280px; bottom nav opcional 4 ítems (Inicio, Materias/Boleta, Alumnos/Kardex, Perfil). Tablet md: drawer. Desktop lg+: sidebar fija 260px navy, main fluido. Cards grid 1 col móvil, 2 cols md, 3 cols xl.
```

### Bloque D — Boleta imprimible
*(Prompt 6B)*

```
Responsive solo en toolbar web (móvil: botones apilados w-full). Documento A4 fijo centrado, ancho max 210mm, sin adaptar tipografía del acta.
```

---

## Perfiles de usuario CEAN

| Perfil | Usuario tipo | Autenticación | Layout Stitch | Rutas Laravel |
|--------|--------------|---------------|---------------|---------------|
| **Administración** | Personal de control escolar | **Login único** `/login` | Shell admin (**sidebar** + **modo oscuro**) | `/admin/*` |
| **Docente** | Profesor titular de materias | **Login único** `/login` (mismo que admin) | Shell docente (sidebar navy) | `/docente/*` 🔜 |
| **Alumno** | Estudiante | **Sin cuenta** — consulta pública | Guest layout | `/boleta` 🔜 portal |
| **Público** | Sin sesión | Ninguna | Landing | `/` |

### Autenticación unificada (personal CEAN)

| Acceso | Ruta | Método | Notas |
|--------|------|--------|-------|
| **Consulta boleta (pública)** | `/boleta` | Matrícula + fecha nacimiento | Sin login. Siempre disponible. |
| **Personal (admin + docente)** | `/login` | Correo + contraseña **o** Google OAuth | Una sola pantalla. Google Workspace ENSQ. |
| **Redirección post-login** | — | Por rol en `users.role` | `admin` → `/admin/dashboard` · `docente` → `/docente/dashboard` |

**Google OAuth:** dominio institucional `@…` (Google Workspace). Laravel Socialite + restricción de dominio permitido.

### Qué puede hacer cada perfil

**Administración**
- CRUD alumnos, docentes, materias, licenciaturas, **grupos escolares**, ciclos
- Crear grupos (semestre + salón + licenciatura) y asignar docente titular por materia
- Carga masiva CSV y captura en acta por grupo/materia
- Publicar boletas y gestionar ciclo activo
- Usuario mock: *Control Escolar* · `control@escuela.test`

**Docente**
- Ver materias y **grupos asignados** (ej. Didáctica Matemáticas · 2°-A)
- Capturar calificaciones **solo de sus materias en sus grupos**
- Consultar lista de alumnos del grupo/materia (solo lectura)
- Editar su perfil (teléfono; no cambia asignaciones — las define administración)
- Usuario mock: *Mtro. Carlos Hernández Ruiz* · `docente@escuela.test`

**Alumno**
- **Consulta pública de boleta** en `/boleta` (matrícula + fecha nacimiento, sin login)
- Portal con sesión y kardex (Fase 2, opcional)
- Usuario mock: *Ana García López* · matrícula `2025001` · **Grupo 2°-A TELESECUNDARIA**

### Prefijo estándar (copiar al inicio de cada prompt en Stitch)

```
Perfil usuario: [Administración | Docente | Alumno | Público].
Producto: CEAN — Control Escolar y Administración Normalista, ENSQ Querétaro.
Identidad: logo CEAN, cyan #59C1E3, navy #2C5EAB, acentos #F18F35 y #C82D31. Montserrat.
Plataforma Web responsive: diseña móvil 375px primero; tablet 768px; desktop 1280px. Touch min 44px.
Sube logo CEAN como referencia.
[Añade al final el Bloque A, B, C o D según tipo de pantalla]
```

---

## Concepto: Grupo escolar CEAN

Un **grupo escolar** no es solo la letra del salón. Es la unidad donde conviven alumnos, materias y docentes titulares.

| Concepto | Ejemplo | Dónde se define |
|----------|---------|-----------------|
| **Grupo escolar** | `2°-A · TELESECUNDARIA · 2023-2024` | Módulo **4C** (admin) |
| **Salón (letra)** | A, B, C | Parte del grupo |
| **Asignación materia–docente–grupo** | Didáctica Matemáticas → Mtro. Hernández · Grupo 2°-A | Módulo **4C** |
| **Alumno inscrito** | Ana García → Grupo 2°-A | Módulo **2** (elige grupo existente) |
| **Boleta semestre-grupo** | `2- A` (semestre + letra del salón) | Se deriva del grupo del alumno |

**Regla:** no se crean grupos al registrar alumnos. Primero **4C Grupos**, luego **2 Alumnos**.

**Modelo Laravel objetivo:** `grupos`, `grupo_materia_docente`, `alumno.grupo_id`.

---

## Prompt 0 — Shells por perfil (generar uno por rol)

### 0A — Shell Administración (modo oscuro + sidebar)

**Perfil:** Administración · **Ruta:** `layouts/navigation.blade.php` (o `layouts/admin.blade.php`) · **Estado:** parcial — regenerar con prompt abajo

**Importante:** NO generar navbar horizontal. Navegación = **sidebar izquierda** + tema **oscuro**.

```
Perfil usuario: Administración (personal de control escolar ENSQ).
Producto CEAN. Logo CEAN como referencia. DESIGN.md.
Plataforma Web responsive. Tema OSCURO obligatorio (no fondo blanco en el shell).

Shell web administración — layout sidebar + main (NO navbar superior):

SIDEBAR izquierda fija 260px, fondo navy oscuro #0f2744 a #1e3a5f:
- Logo CEAN compacto blanco/cyan arriba
- Badge pill cyan: "Administración"
- Nav vertical con iconos + texto blanco/gray-200:
  Inicio | Alumnos | Grupos | Materias y Carreras | Docentes | Calificaciones | Ciclos
- Ítem activo: fondo #1e3a5f + barra lateral cyan #59C1E3 4px + texto cyan
- Pie sidebar: avatar pequeño + "Control Escolar" + enlace Cerrar sesión

MAIN (área derecha), fondo app #111827 gray-900:
- Topbar solo en móvil/tablet: hamburger, logo mini, campana, avatar (NO repetir menú completo arriba)
- Header página: título "Panel de control escolar" texto blanco o cyan #59C1E3, bold
- Breadcrumb gray-400 opcional
- Contenido: tarjeta placeholder #1f2937 gray-800, borde gray-700, rounded-xl, padding generoso:
  icono grid cyan + "Módulo administrativo" + "Contenido principal a integrar."

Colores marca en oscuro: acentos #59C1E3 cyan, #2C5EAB navy en botones, #F18F35 advertencias, #C82D31 errores.
Botón primario: bg #2C5EAB hover #245099, texto blanco.

Responsive: desktop-first. Móvil <640px: sidebar oculta, drawer 280px al abrir hamburger; main full width; topbar compacta. Desktop lg+: sidebar siempre visible, main con max-w-7xl centrado. WCAG 2.1 AA en tema oscuro. Español México.
```

**Bloque B** al final del mensaje en Stitch (copiar desde sección Guía responsive arriba).

### 0B — Shell Docente

**Perfil:** Docente · **Ruta:** `layouts/docente.blade.php` · **Estado:** 🔜

```
Perfil usuario: Docente (profesor titular ENSQ).
Producto CEAN. Logo CEAN. DESIGN.md.

Shell web docente, fondo gray-100.

Sidebar navy #2C5EAB:
- Logo CEAN blanco/cyan, badge "Docente"
- Nav: Inicio | Mis materias | Captura calificaciones | Mis alumnos | Mi perfil
- Activo: barra cyan #59C1E3 4px
- Pie: Mtro. Carlos Hernández Ruiz

Main: header "Portal docente" + breadcrumb. Placeholder contenido.

Responsive: móvil sidebar drawer 280px (hamburger); lg+ sidebar fija 260px. Main padding px-4 móvil, px-8 desktop. Español México.
```

### 0C — Shell Alumno (portal Fase 2)

**Perfil:** Alumno · **Ruta:** `layouts/alumno.blade.php` · **Estado:** 🔜

```
Perfil usuario: Alumno (estudiante de la normal).
Producto CEAN. Logo CEAN. DESIGN.md.

Shell portal alumno, fondo gray-100.

Sidebar navy #2C5EAB: logo, badge "Alumno", nav Mi perfil | Boleta | Kardex.
Pie: Ana García López · 2025001 · Grupo A · 2°

Main: header "Boleta del periodo" + acciones.

Responsive: móvil drawer sidebar + bottom nav 4 íconos; lg+ sidebar fija. Cards 1 col móvil, 2 md. Español México.
```

---

## Módulo 1 — Acceso (landing + login único + boleta pública)

**Flujo:**
- Landing `/` → **Soy alumno** → `/boleta` (público, sin login)
- Landing `/` → **Personal escolar** → `/login` (único: admin y docente)
- Post-login → redirección automática según rol

### 1A — Página de inicio

**Perfil:** Público · **Ruta:** `welcome.blade.php` · **Estado:** ✅

```
Perfil usuario: Público (sin sesión; elige cómo entrar).
Producto CEAN. Logo CEAN. DESIGN.md.

Landing responsive mobile-first:

FONDO: gray-800 #1f2937 dot grid, min-h-screen.

CONTENEDOR white rounded-2xl shadow-xl p-6 mx-4 max-w-sm/md:
- Logo CEAN en cuadrado white rounded-xl
- Título "CEAN" navy #2C5EAB bold
- Subtítulo: "Sistema de control escolar. Consulta boletas y gestión de calificaciones."

DOS tarjetas (grid 2 cols md+ / apiladas móvil):

1. SOY ALUMNO → enlace /boleta (consulta pública, NO es login)
   - Icono birrete círculo #E8F6FB
   - "Consultar mi boleta" · "Matrícula y fecha de nacimiento"
   - Botón w-full navy "Consultar boleta" min-h-[44px]

2. PERSONAL ESCOLAR → enlace /login (login único admin + docente)
   - Icono escudo círculo #E8F6FB
   - "Docentes y control escolar" · "Correo institucional o Google"
   - Botón w-full outline navy "Iniciar sesión" min-h-[44px]

Sin tercera tarjeta separada para docente. Mobile-first 375px. Español México.
```

### 1C — Login único personal (admin + docente)

**Perfil:** Administración y Docente · **Ruta:** `auth/login.blade.php` · **Estado:** ✅ (falta OAuth Google)

```
Perfil usuario: Personal CEAN (control escolar o docente; el sistema detecta el rol tras autenticarse).
Producto CEAN. Guest layout responsive. Logo CEAN. DESIGN.md.

Patrón guest: fondo dot grid gray-800, tarjeta white rounded-2xl max-w-sm p-6.

Encabezado:
- Título navy "Iniciar sesión"
- Subtítulo gray: "Personal de la Escuela Normal Superior de Querétaro"
- Texto pequeño: "Acceso para control escolar y docentes"

OPCIÓN 1 — Google Workspace (destacada arriba):
- Botón w-full white border gray-300 shadow-sm min-h-[48px]
- Icono Google + "Continuar con Google"
- Hint: "Usa tu cuenta institucional @ensq…"

Separador: "— o con correo institucional —"

OPCIÓN 2 — Correo y contraseña:
- Email institucional (placeholder control@escuela.test)
- Contraseña con toggle mostrar/ocultar
- Checkbox Recordarme
- Enlace "¿Olvidaste tu contraseña?"
- Botón w-full navy "Iniciar sesión" min-h-[44px]

Pie enlaces:
- "Consultar boleta (soy alumno)" → /boleta (público)
- "Volver al inicio" → /

Post-login (nota diseño): no mostrar selector de rol; el backend redirige según permisos.
Mobile-first 375px. Español México.
```

### ~~1B — Acceso con pestañas~~ · ~~1D — Login docente~~

**Cancelados.** Sustituidos por: boleta pública en `/boleta` + login único en `/login`.

---

## Módulo 2 — CRUD de Estudiantes

**Perfil:** Administración · **Ruta:** `admin/alumnos` · **Estado:** 🔜

**Prerequisito:** grupos creados en Módulo **4C**. El alumno se **inscribe** en un grupo existente; no se crea el grupo aquí.

**Boleta:** semestre-grupo = `{semestre}- {letra}` (ej. `2- A`), derivado del grupo asignado.

### 2A — Listado de alumnos

**Perfil:** Administración

```
Perfil usuario: Administración (gestiona todos los alumnos del plantel).
Shell admin CEAN: modo oscuro + sidebar izquierda (0A). NO navbar. DESIGN.md.

Pantalla "Administración de alumnos".

Toolbar: búsqueda, filtros **Grupo escolar** (select: 2°-A TELESECUNDARIA, 2°-B…, 8°-A…) | Semestre | Licenciatura, botón "+ Agregar alumno" navy.

Tabla: Matrícula | Nombre | Grupo escolar | Semestre | Licenciatura | Ciclo | Calificaciones | Acciones.

Mock:
- 2025001 | Ana García López | 2°-A TELESECUNDARIA | 2° | TELESECUNDARIA | 2023-2024 | 3
- 2025042 | Pedro Martínez | 2°-B TELESECUNDARIA | 2° | TELESECUNDARIA | 2023-2024 | 3
- 201559590000 | Jorge Luis Benitez | 8°-A TELESECUNDARIA | 8° | TELESECUNDARIA | 2023-2024 | 12

Columna Grupo: badge pill #E8F6FB navy con nombre completo del grupo.

Acciones: Ver boleta, Editar, Eliminar. Paginación.
Responsive Bloque B. Español México.
```

### 2B — Modal crear/editar alumno

**Perfil:** Administración

```
Perfil usuario: Administración.
Panel slide-over "Registrar alumno". DESIGN.md.

Datos personales: nombres, apellidos, CURP, fecha nacimiento.

Datos académicos:
- Matrícula (12 dígitos)
- **Grupo escolar** — select obligatorio con grupos ya creados (Módulo 4C):
  · 2°-A TELESECUNDARIA (2023-2024) — 28 alumnos
  · 2°-B TELESECUNDARIA (2023-2024) — 25 alumnos
- Al elegir grupo, autocompletar readonly: Semestre, Licenciatura, Ciclo, Letra salón
- Hint: "¿No aparece tu grupo? Créalo en Grupos escolares primero." + link "Ir a Grupos"

Pie: Cancelar | Guardar navy.
Responsive: slide-over full-width móvil, 480px desktop. Español México.
```

### 2C — Expediente / kardex admin

**Perfil:** Administración

```
Perfil usuario: Administración.
Modal "Expediente del alumno". DESIGN.md.

Header: JORGE LUIS BENITEZ SALAZAR · 201559590000.
Badges: **Grupo 8°-A TELESECUNDARIA** · 8° semestre · ciclo 2023-2024.

Tab Datos generales — bloque "Grupo escolar":
- Grupo: 8°-A TELESECUNDARIA
- Materias y docentes del grupo (mini-tabla readonly):
  | Materia | Docente titular |
  | APRENDIZAJE EN EL SERVICIO | Mtro. Carlos Hernández |

Tab Calificaciones por semestre. Calif < 6: #FEF2F2 #C82D31.
Responsive modal. Español México.
```

---

## Módulo 3 — Carreras y Materias

**Perfil:** Administración · **Rutas:** `admin/carreras`, `admin/materias` · **Estado:** parcial

### 3A — Configuración académica

**Perfil:** Administración

```
Perfil usuario: Administración (configura planes de estudio; docentes solo consumen catálogo).
Shell admin: modo oscuro + sidebar (0A). Layout 2 columnas en main. DESIGN.md.

Izquierda — Licenciaturas: form alta + lista planes.
Derecha — Materias: buscador, filtros, form inline, tabla Clave | Materia | Semestre | Licenciatura.

Docente no edita catálogo. Responsive: 1 col móvil, 2 cols lg+. Español México.
```

---

## Módulo 4 — CRUD de Docentes (vista administración)

**Perfil:** Administración · **Ruta:** `admin/docentes` · **Estado:** 🔜

### 4A — Directorio de docentes

**Perfil:** Administración

```
Perfil usuario: Administración (gestiona plantilla docente completa).
Shell admin: modo oscuro + sidebar (0A). DESIGN.md.

KPIs: Total docentes 24 | Materias cubiertas 18/22 | Plantilla 82%.
Buscador + "+ Registrar docente" navy.

Grid tarjetas docente:
- Avatar, Mtro. Carlos Hernández Ruiz, badge Mtro.
- Email gray-500
- Resumen: "Asignado a 3 grupos · 4 materias" (chips: 2°-A Matemáticas, 2°-B Matemáticas, 8°-A Aprendizaje…)
- Botón outline "Editar perfil" — no edita asignaciones de grupo (van en 4C)

Solo administración. Responsive Bloque B. Español México.
```

### 4B — Modal registrar docente

**Perfil:** Administración

```
Perfil usuario: Administración.
Modal "Registrar docente" max-w-lg. DESIGN.md.

Campos: nombre completo, grado académico (Lic./Mtro./Mtra.), email institucional, teléfono.

Nota UI: "Las asignaciones a grupos y materias se configuran en Grupos escolares (Módulo 4C)."

Pie: Cancelar | Guardar navy.
Responsive modal. Español México.
```

---

## Módulo 4C — Grupos escolares y asignaciones

**Perfil:** Administración · **Rutas:** `admin/grupos`, `admin/grupos/{id}/asignaciones` · **Estado:** 🔜

**Cuándo usar:** después de Materias (3) y Docentes (4), **antes** de inscribir alumnos (2).

Un grupo = **Semestre + Letra salón + Licenciatura + Ciclo activo** + tabla de **Materia → Docente titular**.

### 4C-A — Listado de grupos

**Perfil:** Administración

```
Perfil usuario: Administración.
Shell admin: modo oscuro + sidebar (0A). Pantalla "Grupos escolares". DESIGN.md.

Header navy: "Grupos escolares" + botón "+ Crear grupo" #2C5EAB.

Toolbar filtros: Ciclo (2023-2024) | Semestre (1°–8°) | Licenciatura | buscar por nombre.

Tabla: Grupo | Semestre | Salón | Licenciatura | Ciclo | Alumnos | Materias asignadas | Docentes | Estatus | Acciones.

Mock:
- 2°-A TELESECUNDARIA | 2° | A | TELESECUNDARIA | 2023-2024 | 28 | 12/12 | 8 | Activo | Editar · Asignaciones · Ver alumnos
- 2°-B TELESECUNDARIA | 2° | B | TELESECUNDARIA | 2023-2024 | 25 | 10/12 | 7 | Activo | …
- 8°-A TELESECUNDARIA | 8° | A | TELESECUNDARIA | 2023-2024 | 32 | 12/12 | 9 | Activo | …

Badge Activo verde. Alerta naranja si materias sin docente asignado.
Columna Acciones: "Asignar materias" (icono principal), Editar, Desactivar.

Responsive Bloque B: cards móvil con nombre grupo grande + chips alumnos/materias. Español México.
```

### 4C-B — Crear / editar grupo

**Perfil:** Administración

```
Perfil usuario: Administración.
Panel slide-over "Crear grupo escolar" o modal centrado. DESIGN.md.

Sección Identificación:
- Ciclo escolar — select (2023-2024 activo)
- Semestre — select 1°–8° (Par/Impar)
- Licenciatura — select (catálogo Módulo 3)
- Letra de salón — select A, B, C, D, E
- Vista previa readonly: "Nombre del grupo: 2°-A TELESECUNDARIA"

Sección Capacidad (opcional):
- Cupo máximo alumnos (number)
- Observaciones (textarea)

Pie: Cancelar | "Crear y asignar materias" navy (lleva a 4C-C) | Guardar borrador outline.

Hint: "Al crear el grupo podrás asignar qué docente imparte cada materia."
Responsive slide-over. Español México.
```

### 4C-C — Asignación materias y docentes por grupo

**Perfil:** Administración

```
Perfil usuario: Administración.
Pantalla "Asignaciones — Grupo 2°-A TELESECUNDARIA". Shell admin: modo oscuro + sidebar (0A). DESIGN.md.

Header contexto (tarjeta cyan border-left 4px):
- Grupo: 2°-A TELESECUNDARIA · Ciclo 2023-2024 · 28 alumnos inscritos
- Breadcrumb: Grupos > 2°-A > Asignaciones

Tabla editable principal — materias del plan del semestre/licenciatura:
| Materia | Clave | Docente titular (select) | Estatus |

Filas mock:
- DIDÁCTICA DE LAS MATEMÁTICAS | MAT-201 | Mtro. Carlos Hernández Ruiz ▼ | ✓ Completo
- DIDÁCTICA DE LA LENGUA ESPAÑOLA | MAT-202 | Mtra. Ana Morales ▼ | ✓ Completo
- TECNOLOGÍAS DE LA INFORMACIÓN | MAT-203 | Sin asignar ▼ | ⚠ Pendiente (naranja #F18F35)

Select docente: solo docentes registrados (Módulo 4); opción "Sin asignar" en rojo.

Barra progreso: "Plantilla del grupo: 10/12 materias con docente" — track #E8F6FB, fill cyan.

Acciones: Guardar asignaciones navy | Duplicar desde otro grupo (outline) | Ver alumnos del grupo.

Nota: Grupo 2°-B puede tener **distintos docentes** para la misma materia.
Responsive: tabla scroll-x móvil; selects full-width en cards móvil. Español México.
```

### 4C-D — Detalle grupo (vista resumen)

**Perfil:** Administración

```
Perfil usuario: Administración.
Pantalla detalle "Grupo 2°-A TELESECUNDARIA". Shell admin: modo oscuro + sidebar (0A). DESIGN.md.

Tabs: Resumen | Materias y docentes | Alumnos inscritos

Tab Resumen — 3 KPI cards:
- 28 alumnos | 12 materias | 10 docentes distintos

Tab Materias y docentes — tabla readonly igual 4C-C.

Tab Alumnos — mini-listado Matrícula | Nombre | link Ver expediente.
Botón "+ Inscribir alumno" → Módulo 2B.

Responsive Bloque B. Español México.
```

---

## Módulo 5 — Ciclos, Calificaciones y Actas

### 5A — Dashboard control escolar

**Perfil:** Administración · **Ruta:** `admin/dashboard` · **Estado:** ✅

```
Perfil usuario: Administración.
Dashboard dentro del shell admin: modo oscuro + sidebar izquierda (0A). NO navbar. DESIGN.md.

Alerta ciclo activo 2023-2024 (cyan sobre fondo #1f2937) o sin ciclo (naranja #F18F35).
KPI cards fondo #1f2937 borde gray-700, números cyan/blancos.
KPIs: Alumnos 156 | **Grupos activos 12** | Materias 42 | Calificaciones 1,248.
Acciones: **Gestionar grupos** (navy) | Cargar calificaciones | Gestionar ciclos | Captura en acta.

No visible para docente/alumno. Responsive Bloque B: KPI 1 col móvil, 3 cols md+. Español México.
```

### 5B — Importación CSV

**Perfil:** Administración · **Ruta:** `admin/calificaciones/index` · **Estado:** ✅

```
Perfil usuario: Administración (carga masiva global).
Shell admin: modo oscuro + sidebar (0A). DESIGN.md.

Tarjeta import CSV: semestre, **grupo escolar** (select), archivo, botón Importar navy.
Tabla alumnos filtrada por grupo: Matrícula | Nombre | Grupo escolar | Calificaciones.

Docente usa captura por materia (módulo 7). Responsive Bloque B: form apilado móvil; tabla → cards móvil. Español México.
```

### 5C — Gestión de ciclos

**Perfil:** Administración · **Ruta:** `admin/ciclos` · **Estado:** 🔜

```
Perfil usuario: Administración (único perfil que activa/cierra ciclos).
Shell admin: modo oscuro + sidebar (0A). DESIGN.md.

Ciclo activo readonly + desactivar. Form nuevo ciclo. Tabla histórico.

Docente/alumno solo ven ciclo como badge. Responsive Bloque B. Español México.
```

### 5D — Matriz de acta (admin)

**Perfil:** Administración · **Ruta:** `admin/calificaciones/acta` · **Estado:** 🔜

```
Perfil usuario: Administración (captura y publicación oficial de todos los grupos).
Shell admin: modo oscuro + sidebar (0A). Spreadsheet. DESIGN.md.

Contexto barra: Ciclo | **Grupo escolar** (2°-A TELESECUNDARIA) | Materia | Badge Borrador naranja.
Al elegir grupo, cargar solo alumnos inscritos y docente titular de esa materia en ese grupo (readonly arriba: "Docente: Mtro. Carlos Hernández").

Tabla: # | Matrícula | Nombre | Calif. | Letra | % Asistencia.
5 filas mock del grupo 2°-A.

Footer sticky: Guardar borrador outline | Publicar calificaciones navy.
Docente captura en 7C (mismo grupo+materia, envía a revisión).
Responsive: scroll-x tabla; footer botones w-full móvil. Español México.
```

---

## Módulo 6 — Portal del Alumno

### 6A — Consulta de boleta (pública, sin login)

**Perfil:** Alumno / Público · **Ruta:** `boleta/consultar.blade.php` · **Estado:** ✅

**Importante:** esta pantalla NO requiere cuenta ni Google OAuth. Acceso directo desde `/boleta` o landing "Soy alumno".

```
Perfil usuario: Alumno o público (consulta personal sin autenticación).
Guest layout responsive. Logo CEAN. DESIGN.md.

Patrón guest dot grid gray-800 + tarjeta white rounded-2xl max-w-sm p-6.
Badge cyan "Consulta pública · Sin contraseña".
Título "Consulta de boleta".
Texto: "Ingresa tu matrícula y fecha de nacimiento registrados en control escolar."
Badge ciclo 2023-2024.

Form w-full: Matrícula, Fecha nacimiento.
Botón w-full navy "Consultar boleta" min-h-[44px].

Pie: "¿Eres personal?" enlace → /login · "Volver al inicio" → /
Sin menús admin/docente. Mobile-first 375px. Español México.
```

### 6B — Boleta oficial imprimible

**Perfil:** Alumno · **Ruta:** `boleta/mostrar.blade.php` · **Estado:** ✅

```
Perfil usuario: Alumno (documento personal de calificaciones).
Vista A4 imprimible serif. NO shell admin/docente. DESIGN.md.

Logos gobierno + ENSQ. Boleta oficial.
Matrícula 201559590000 | Semestre-grupo 8- A (semestre + salón).
Párrafo aparte: plan TELESECUNDARIA.

Tabla calificaciones + promedio. Reprobatoria < 6 en #C82D31.
Toolbar web responsive: móvil botones apilados w-full; desktop fila horizontal.
Documento A4 fijo max-w-[210mm]. Solo lectura. Español México.
```

### 6C — Portal alumno con navegación

**Perfil:** Alumno · **Ruta:** `alumno/*` · **Estado:** 🔜

```
Perfil usuario: Alumno (portal autenticado Fase 2).
Shell alumno (prompt 0C). DESIGN.md.

Sidebar navy: Mi perfil | Boleta | Kardex. Usuario Ana García · 2025001 · **Grupo 2°-A**.

Main: preview boleta. Tabla: Asignatura | Docente titular | Calif. | % Asistencia.
Docentes vienen de asignaciones del grupo (Módulo 4C).
Botón Descargar PDF navy. Tabs periodo actual | semestres anteriores.
Timeline kardex 1°–8°.

Alumno no edita datos. Responsive Bloque C: drawer + bottom nav móvil. Español México.
```

### 6D — Mi perfil alumno (solo lectura)

**Perfil:** Alumno · **Ruta:** `alumno/perfil` · **Estado:** 🔜

```
Perfil usuario: Alumno.
Shell alumno. DESIGN.md.

Tarjeta "Mis datos" solo lectura:
- Nombre, matrícula, **Grupo escolar 2°-A TELESECUNDARIA**, semestre, licenciatura, ciclo
- Texto: "Para correcciones contacta control escolar"

Sin botón guardar. Responsive Bloque C: cards apiladas móvil. Español México.
```

---

## Módulo 7 — Portal Docente

### 7A — Inicio docente / Mis materias

**Perfil:** Docente · **Ruta:** `docente/dashboard` · **Estado:** 🔜

```
Perfil usuario: Docente (Mtro. Carlos Hernández Ruiz).
Shell docente (prompt 0B). DESIGN.md.

Saludo: "Bienvenido, Mtro. Carlos Hernández Ruiz"
Badge ciclo activo 2023-2024.

Grid tarjetas — una por **asignación grupo+materia** (desde Módulo 4C):
- DIDÁCTICA DE LAS MATEMÁTICAS · **Grupo 2°-A** · 28 alumnos · Mtro. Carlos Hernández (titular)
- DIDÁCTICA DE LAS MATEMÁTICAS · **Grupo 2°-B** · 25 alumnos · (mismo docente u otro)
- APRENDIZAJE EN EL SERVICIO · **Grupo 8°-A** · 32 alumnos

Cada tarjeta: botón "Capturar calificaciones" navy | "Ver alumnos".
Solo asignaciones donde este docente es titular. Responsive Bloque C: grid materias 1→2→3 cols. Español México.
```

### 7B — Lista de alumnos por materia

**Perfil:** Docente · **Ruta:** `docente/alumnos` · **Estado:** 🔜

```
Perfil usuario: Docente (consulta alumnos de sus materias únicamente).
Shell docente. DESIGN.md.

Filtros: **Grupo escolar** (2°-A, 2°-B — solo grupos donde imparte) | Materia | readonly docente titular.

Tabla: Matrícula | Nombre | Grupo escolar | Calif. | % Asistencia.
Solo alumnos del grupo seleccionado inscritos en Módulo 2. Responsive Bloque B+C: tabla → cards móvil. Español México.
```

### 7C — Captura de calificaciones docente

**Perfil:** Docente · **Ruta:** `docente/calificaciones` · **Estado:** 🔜

```
Perfil usuario: Docente (captura parcial; admin publica oficialmente).
Shell docente. Spreadsheet simplificado. DESIGN.md.

Barra contexto: **Grupo escolar** (2°-A TELESECUNDARIA) | Materia | Docente readonly (tú) | Badge Borrador naranja.

Tabla editable solo alumnos **inscritos en ese grupo**:
# | Matrícula | Nombre | Calif. | % Asistencia.
Docente titular validado por asignación 4C.

Footer: "Guardar borrador" outline | "Enviar a control escolar" navy.
Texto: "Control escolar revisará y publicará las calificaciones oficiales."

No Publicar oficial. Responsive: tabla scroll-x; footer sticky botones w-full móvil. Español México.
```

### 7D — Mi perfil docente

**Perfil:** Docente · **Ruta:** `docente/perfil` · **Estado:** 🔜

```
Perfil usuario: Docente.
Shell docente. DESIGN.md.

Sección lectura: nombre, grado, email.
Sección "Mis asignaciones" (readonly, desde Grupos 4C):
- 2°-A · Didáctica Matemáticas
- 2°-B · Didáctica Matemáticas
- 8°-A · Aprendizaje en el Servicio
Texto: "Las asignaciones las gestiona control escolar."

Sección editable: teléfono, enlace cambiar contraseña. Botón "Guardar" navy.
Responsive Bloque C. Español México.
```

---

## Follow-ups de iteración

```
Mantén patrón landing: fondo dot grid gray-800, tarjeta white centrada, botones w-full min-h-44px en móvil.
```

```
En admin móvil convierte tablas en cards oscuras; no reduzcas texto below 14px.
```

```
Admin CEAN: usa SIDEBAR izquierda modo oscuro (#0f2744), NO navbar horizontal ni tema claro. Nav: Inicio, Alumnos, Grupos, Materias y Carreras, Docentes, Calificaciones, Ciclos.
```

```
Sidebar docente/alumno/admin: drawer móvil + topbar compacta; nunca ocultes acciones principales.
```

```
Si Stitch muestra solo desktop, pide: "Muestra también frame móvil 375px apilado verticalmente".
```

```
Los grupos se crean en Módulo 4C antes de inscribir alumnos. Cada grupo tiene materias con docente titular distinto por salón.
```

```
En asignaciones 4C-C, Grupo 2°-B debe poder tener docente diferente al 2°-A para la misma materia.
```

---

## Mapa: prompt → perfil → Laravel

| Prompt | Perfil | Vista | Estado |
|--------|--------|-------|--------|
| 0A | Administración | `layouts/navigation` | Parcial |
| 0B | Docente | `layouts/docente` | 🔜 |
| 0C | Alumno | `layouts/alumno` | 🔜 |
| 1A | Público | `welcome` | ✅ |
| 1C | Admin + Docente | `auth/login` (+ OAuth Google) | Parcial |
| ~~1B~~ | — | cancelado | — |
| ~~1D~~ | — | cancelado (unificado en 1C) | — |
| 2A–C | Administración | `admin/alumnos/*` · **requiere 4C** | 🔜 |
| 3A | Administración | `admin/materias/*` | 🔜 |
| 4A–B | Administración | `admin/docentes/*` | 🔜 |
| **4C-A–D** | **Administración** | **`admin/grupos/*`** | **🔜** |
| 5A–D | Administración | `admin/*` | Parcial |
| 6A–D | Alumno | `boleta/*`, `alumno/*` | Parcial |
| 7A–D | Docente | `docente/*` | 🔜 |
