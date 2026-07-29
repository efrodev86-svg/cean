# CEAN — Diagramas de flujo del proceso

Documentación del flujo operativo del sistema, alineada con `DESIGN.md`, `prompts.md` y la arquitectura Laravel objetivo.

**Orden académico clave:** Materias (3) → Docentes (4) → **Grupos (4C)** → Alumnos (2) → Calificaciones (5 / 7)

---

## 1. Entrada al sistema (público)

```mermaid
flowchart TD
    START([Usuario entra a CEAN /]) --> LANDING[Landing · Módulo 1A]

    LANDING --> ALUMNO{Soy alumno}
    LANDING --> PERSONAL{Personal escolar}

    ALUMNO --> BOLETA_FORM[Consulta boleta pública · 6A<br/>/boleta — sin login]
    BOLETA_FORM --> BOLETA_OK{¿Datos correctos?}
    BOLETA_OK -->|No| BOLETA_FORM
    BOLETA_OK -->|Sí| BOLETA_DOC[Boleta oficial · 6B<br/>Imprimir / PDF]

    PERSONAL --> LOGIN[Login único · 1C<br/>Correo/contraseña o Google OAuth]
    LOGIN --> AUTH{¿Autenticado?}
    AUTH -->|No| LOGIN
    AUTH -->|Sí| ROL{Rol en sistema}
    ROL -->|admin| PORTAL_A[Panel admin · Módulo 5]
    ROL -->|docente| PORTAL_D[Portal docente · Módulo 7]
```

| Perfil | Autenticación | Destino |
|--------|---------------|---------|
| Alumno / público | **Sin login** — matrícula + fecha nacimiento | `/boleta` |
| Personal (admin o docente) | **Login único** — correo/contraseña o Google OAuth | `/login` → redirección por rol |

---

## 2. Configuración académica (administración)

Prerequisito: **ciclo escolar activo** (Módulo 5C).

```mermaid
flowchart TD
    subgraph PRE["Prerequisitos"]
        CICLO[Ciclo escolar activo · 5C]
    end

    CICLO --> M3[Módulo 3 · Licenciaturas y materias<br/>Catálogo de asignaturas por plan]

    M3 --> M4[Módulo 4 · Docentes<br/>Registrar plantilla docente]

    M4 --> M4C[Módulo 4C · Grupos escolares]

    subgraph GRUPO["Crear grupo escolar"]
        M4C --> G1[4C-B · Crear grupo<br/>Semestre + Salón A/B + Licenciatura + Ciclo]
        G1 --> G2[4C-C · Asignar por grupo<br/>Materia → Docente titular]
        G2 --> G3{¿Todas las materias<br/>con docente?}
        G3 -->|No| G2
        G3 -->|Sí| G4[Grupo activo · 4C-A / 4C-D]
    end

    G4 --> M2[Módulo 2 · Alumnos<br/>Inscribir alumno en grupo existente]

    M2 --> LISTO[Listo para calificaciones y boletas]
```

### Ejemplo concreto

| Paso | Acción | Resultado |
|------|--------|-----------|
| 1 | Crear materias del 2° semestre | Didáctica Matemáticas, Lengua… |
| 2 | Registrar Mtro. Hernández | Docente en plantilla |
| 3 | Crear **2°-A TELESECUNDARIA** | Grupo escolar |
| 4 | Asignar Matemáticas → Hernández en **2°-A** | Asignación grupo + materia + docente |
| 5 | Crear **2°-B** y asignar Matemáticas → **otro docente** | Mismo semestre, distinto salón |
| 6 | Inscribir a Ana García en **2°-A** | Alumno ligado al grupo |

### Concepto: Grupo escolar

| Elemento | Ejemplo | Dónde se define |
|----------|---------|-----------------|
| Grupo escolar | `2°-A · TELESECUNDARIA · 2023-2024` | Módulo 4C |
| Asignación | Didáctica Matemáticas → Mtro. Hernández · Grupo 2°-A | Módulo 4C-C |
| Alumno inscrito | Ana García → Grupo 2°-A | Módulo 2 |
| Boleta semestre-grupo | `2- A` (semestre + letra salón) | Derivado del grupo |

**Regla:** no se crean grupos al registrar alumnos. Primero **4C**, luego **2**.

---

## 3. Calificaciones (docente + administración)

```mermaid
flowchart TD
    subgraph DOC["Docente · Módulo 7"]
        D1[7A · Elige Grupo + Materia<br/>solo sus asignaciones 4C]
        D1 --> D2[7C · Captura calificaciones y asistencia<br/>solo alumnos de ese grupo]
        D2 --> D3[Enviar borrador a control escolar]
    end

    subgraph ADM["Administración · Módulo 5"]
        A1[5B · Importar CSV<br/>opcional, por grupo/semestre]
        A2[5D · Acta editable<br/>Grupo + Materia + alumnos del grupo]
        A3{Publicar calificaciones<br/>oficiales}
    end

    D3 --> A2
    A1 --> A3
    A2 --> A3

    A3 --> BOLETA[Boleta disponible para alumno · 6A/6B]
    A3 --> KARDEX[Kardex / historial · 6C Fase 2]
```

### Permisos por perfil

| Acción | Docente | Administración |
|--------|---------|----------------|
| Capturar borrador | Sí (su grupo + materia) | Sí (cualquier grupo) |
| Publicar oficial | No | Sí |
| Crear grupos / asignar docentes | No | Sí |
| Importar CSV global | No | Sí |
| CRUD alumnos | No | Sí |

---

## 4. Vista del alumno (consulta de boleta)

```mermaid
flowchart LR
    A[Alumno inscrito<br/>Grupo 2°-A] --> B[Grupo tiene materias<br/>y docentes · 4C]
    B --> C[Calificaciones publicadas · 5]
    C --> D[Alumno consulta · 6A]
    D --> E[Boleta muestra:<br/>Semestre-grupo 2- A<br/>Materia · Docente · Calif. · Asistencia]
```

**En la boleta impresa (6B):**

- **Semestre-grupo:** `2- A` — semestre + letra del salón
- **Licenciatura:** párrafo académico aparte
- **Docente titular:** por materia (desde asignación 4C)
- **Reprobatoria:** calificación &lt; 6 · **Asistencia baja:** &lt; 80%

---

## 5. Mapa de módulos por perfil

```mermaid
flowchart TB
    subgraph PUBLICO["Público"]
        M1[1 · Acceso]
    end

    subgraph ADMIN["Administración"]
        M3[3 Materias]
        M4[4 Docentes]
        M4C[4C Grupos]
        M2[2 Alumnos]
        M5[5 Ciclos y calificaciones]
    end

    subgraph DOCENTE["Docente"]
        M7[7 Portal docente]
    end

    subgraph ALUMNO["Alumno"]
        M6[6 Boleta / kardex]
    end

    M1 --> M6
    M1 --> M7
    M1 --> ADMIN

    M3 --> M4 --> M4C --> M2 --> M5
    M4C --> M7
    M5 --> M6
```

---

## 6. Modelo de datos objetivo (Laravel)

```mermaid
erDiagram
    CICLO_ESCOLAR ||--o{ GRUPO : tiene
    LICENCIATURA ||--o{ GRUPO : pertenece
    GRUPO ||--o{ ALUMNO : inscribe
    GRUPO ||--o{ GRUPO_MATERIA_DOCENTE : asigna
    MATERIA ||--o{ GRUPO_MATERIA_DOCENTE : imparte
    DOCENTE ||--o{ GRUPO_MATERIA_DOCENTE : titular
    ALUMNO ||--o{ CALIFICACION : recibe
    MATERIA ||--o{ CALIFICACION : evalua

    GRUPO {
        int semestre
        string letra_salon
        int ciclo_escolar_id
        int licenciatura_id
    }

    GRUPO_MATERIA_DOCENTE {
        int grupo_id
        int materia_id
        int docente_id
    }
```

**Estado actual en código:** parcial — `alumnos.grupo` es texto (letra); faltan tablas `grupos` y `grupo_materia_docente`. Ver `prompts.md` Módulo 4C.

---

## 7. Secuencia para diseño en Stitch

```
1. DESIGN.md + logo CEAN
2. Shells 0A (admin), 0B (docente), 0C (alumno)
3. Módulo 1 — Acceso
4. Módulo 3 → 4 → 4C → 2 → 5  (orden académico)
5. Módulo 7 — Portal docente
6. Módulo 6 — Portal / boleta alumno
```

---

## Referencias

| Archivo | Contenido |
|---------|-----------|
| [DESIGN.md](./DESIGN.md) | Identidad visual, perfiles, responsive |
| [prompts.md](./prompts.md) | Prompts Stitch por módulo |
| [README.md](../../README.md) | Estado implementado en Laravel (Fase 1) |
