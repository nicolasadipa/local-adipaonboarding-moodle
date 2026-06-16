# local_adipaonboarding

**Tours guiados para estudiantes del aula ADIPA.** Plugin Moodle 4.x type `local` que muestra recorridos interactivos (Driver.js) la primera vez que un estudiante entra a un curso o al viewer de cápsulas de video. Pensado para que el alumno descubra las features del aula sin necesidad de que alguien le explique a mano.

> **Estado**: v1.0.5 — **ALPHA estable**. Validado en staging + producción acotada. Apto para rollout gradual.

---

## ¿Para qué sirve?

Imaginá que un estudiante entra por primera vez a su curso en aula.adipa.cl. No sabe dónde están las cápsulas, no entiende qué es el banner del programa, no sabe que puede tomar apuntes con timestamp dentro del video viewer.

Este plugin le muestra, paso a paso, los elementos clave del aula con popovers. Una sola vez (o cuando el alumno haga click en "¿Cómo usar el aula?" para repasar). Texto pensado para acompañar, no para abrumar.

**Scopes soportados:**

| Scope | URL | Cuándo aparece |
|-------|-----|----------------|
| `course_view` | `/course/view.php?id=N` | Al entrar a la página de un curso (variante según `course_type`). |
| `mod_adipavideo` | `/mod/adipavideo/view.php?id=N` | Al abrir el viewer de una cápsula de video. |

---

## Instalación

1. Descargar el ZIP de [releases](../../releases) o desde `~/Downloads/local_adipaonboarding-X.Y.Z.zip` (build local).
2. Site admin → Plugins → Install plugins → subir ZIP.
3. Confirmar upgrade (puede pedir `--allow-unstable` por estar en MATURITY_ALPHA).
4. Después del install, el plugin queda **enabled por defecto**.

**Compatibilidad:**

- Moodle 4.0+ (probado en 4.1.9)
- PHP 7.4 (la producción ADIPA corre en 7.4 — sin sintaxis 8.x en el código)
- MariaDB/MySQL con `utf8mb4` (emojis funcionan)
- Themes: testeado en `theme_adipa` (local) y `newlearning` (prod)

---

## Configuración inicial (5 min)

**Site admin → Plugins → Local plugins → ADIPA Onboarding → Configuración**

1. **Activar onboarding** ☑ (default: on). Si está off, ningún estudiante ve tour.
2. **Retraso por defecto (ms)**: cuántos milisegundos espera el tour para auto-lanzarse después que carga la página. Default 7000 (7 segundos). Ajustá si el aula tiene assets pesados que tardan más.
3. **Rollout: mostrar tour solo en estos cursos (IDs)**: comma-separated, ej: `2203, 2204`. Si está vacío, el tour aparece en **todos los cursos elegibles**. **Úsalo para rollout gradual** — empezás con uno o dos cursos piloto, observás en Reportabilidad, y después vacías el campo para liberar a todos.
4. **Resetear "ya visto"**: botón que limpia la tabla `seen` (todos los estudiantes vuelven a ver el tour la próxima vez). Útil tras un upgrade material del tour.

---

## Cómo funciona (arquitectura)

### Modelo de datos

3 tablas core + 3 de soporte:

```
local_adipaonboarding_steps        (Step Library — ladrillos reusables)
  ├ id, stepkey, selector, placement
  ├ titlelangkey | titletext     (text gana sobre langkey)
  ├ bodylangkey  | bodytext
  ├ actionsjson  (pre-actions: click/wait, opcional)
  ├ responsivejson, shipped, dirty, createdat, updatedat

local_adipaonboarding_tours        (Tour = secuencia por scope + course_type)
  ├ id, scope, coursetype, version
  ├ enabled, visibilityjson (delay_ms, min_viewport, frequency)
  └ shipped, dirty, createdat, updatedat

local_adipaonboarding_tsteps       (junction tour→steps con override opcional)
  ├ id, tourid, stepid, sortorder, overridejson
  └ FKs: tourid→tours.id, stepid→steps.id

local_adipaonboarding_seen         (recorridos ya vistos por usuario+version)
local_adipaonboarding_events       (telemetría: viewed/completed/dismissed)
local_adipaonboarding_optout       (opt-out per-course)
```

**Shipped vs dirty**: un step/tour `shipped=1` viene del seed (código). Cuando el admin lo edita desde la UI, queda `dirty=1` y el upgrade **no lo sobrescribe** en el próximo seeder run. Eso permite combinar seeds versionados con edición manual sin pisarlas.

### Lifecycle de un tour (request lifecycle)

```
Browser request → page load
  ↓
local_adipaonboarding_before_footer() [lib.php]
  ├ Filtros: logged-in, no-guest, no-admin, plugin enabled
  ├ resolver::for_page($PAGE)
  │    └ matchea pagetype → tour_repository::build_manifest_for(scope, coursetype)
  ├ Filtros de curso: restricted_courses, opt-out, enrolled, no editing rights
  ├ Emite <script type="application/json" id="adipa-onboarding-manifest">PAYLOAD</script>
  └ js_call_amd('runner', 'init', []) + js_call_amd('trigger', 'init', [])
  ↓
Browser
  ├ trigger.js init  → lee payload del DOM → inyecta "?" en `.adipa-s0-banner`
  │                                       o en wrap-tip después de `.adv-tabs`
  └ runner.js  init  → lee payload del DOM → si rules::should_show, auto-launch
                                          ↓
                     Driver.js (load on demand) → drive(steps)
                                          ↓
                     onPopoverRender → logEvent('viewed')
                     onDestroyed     → markSeen + logEvent('completed'|'dismissed')
```

### Por qué DOM handoff (script JSON) y no `js_call_amd` con args

Moodle warna cuando los args de `js_call_amd` superan 1024 chars (`debugging('Too much data passed as arguments...')`). Con `debug=DEVELOPER`, ese warning se imprime inline en el HTML y rompe el bootstrap de RequireJS → cascadea errores `define is not a function` en `core/network`, `core/toast`, etc.

Solución: `lib.php` retorna `<script type="application/json" id="adipa-onboarding-manifest">{...}</script>` en el footer. Los AMD modules leen via `document.getElementById(...).textContent` + `JSON.parse`. Cero contaminación del payload por args.

### Pre-actions (`step.actions`)

JSON array opcional en cada step. Permite **interactuar con el DOM antes del popover**:

```json
[
  {"type": "click", "selector": ".adv-tab-btn[data-tab=\"apuntes\"]"},
  {"type": "wait",  "ms": 200}
]
```

- `click` — dispara `.click()` programático en el elemento. Sirve para abrir una tab antes de highlightear su panel.
- `wait` — pausa best-effort para encadenar acciones.

El runner ejecuta las actions en `onHighlightStarted` de Driver.js. **Steps con `click` action saltean la validación de visibilidad** del target (el click puede hacer aparecer el target). Steps sin `click` requieren target visible YA en el DOM (sino el popover quedaría mal anclado y se filtra silenciosamente).

**Uso real:** `video_notes` y `video_transcript` en `mod_adipavideo` — abren la tab antes de mostrar el popover sobre el panel correspondiente.

### Trigger UI ("¿Cómo usar el aula?")

**Un host fijo por scope, sin cascada ni fallback:**

- **`course_view`** → ícono `?` circular 36px dentro de `.adipa-s0-banner` (banner del programa). Tooltip CSS al hover.
- **`mod_adipavideo`** → wrap "tip" después de `.adv-tabs` (ícono `?` + texto "¿Cómo usar el aula?" al lado).

Si el host no matchea (theme o plugin distinto), el botón no se renderiza. No hay fallback floating ni inline.

---

## Cómo agregar un step nuevo

### Opción A — Step shipped (parte del seed por defecto)

1. Editar `tours/<scope>.php` (ej: `tours/course_view.php`).
2. Agregar al step library: definir el step con `step_key`, `selector`, `title_lang_key`, `body_lang_key`, `placement`, etc.
3. Agregar el `step_key` al array `step_keys` del tour donde querés mostrarlo.
4. Bumpear el `version` del tour (para que se relance a estudiantes que ya vieron la versión anterior).
5. Agregar las lang strings en `lang/es/`, `lang/es_mx/` y `lang/en/`.
6. Bumpear `version.php`, agregar migration en `db/upgrade.php` si toca schema.
7. Run upgrade.

### Opción B — Step custom (solo este sitio, desde admin UI)

1. Site admin → ADIPA Onboarding → **Step Library** → "Nuevo step".
2. Definir `stepkey` único + `selector` + `title_text` + `body_text` + placement + actions JSON opcional.
3. Site admin → ADIPA Onboarding → **Tours** → editar el tour → arrastrar el step custom desde la library al tour.
4. Cambios autosave. El tour queda `dirty=1` (protegido del próximo seeder run).

---

## Cómo agregar un scope nuevo

1. Crear `tours/<nuevo_scope>.php` con función `local_adipaonboarding_<scope>_seeds()` (devuelve `['steps' => [...], 'tours' => [...]]`).
2. Agregar el archivo + función al `classes/local/tour/seeder.php` (en `seed_files()` y `seed_functions()`).
3. Agregar el match de pagetype en `classes/local/tour/resolver.php::for_page()`.
4. (Opcional) Adaptar `amd/src/trigger.js` si querés un host distinto para ese scope.
5. Bump version + migration que llame al seeder.

---

## Reportabilidad

**Site admin → ADIPA Onboarding → Reportabilidad** muestra:

- **Totales globales**: viewed, completed, dismissed.
- **Drop-off por tour**: dónde abandonan los alumnos (bar chart Chart.js).
- **Botón "Resetear eventos"**: limpia la tabla de events (útil al cerrar un sprint para no inflar el reporte con datos viejos).

Los eventos se registran via WS `local_adipaonboarding_log_event`. Acciones válidas: `viewed`, `skipped`, `completed`, `dismissed`. Cualquier otra acción rechaza con `invalid_parameter_exception`.

---

## Opt-out per-course

Profesores/managers de un curso pueden desactivar el tour solo para su curso:

**Course → Onboarding (en el menú de admin del curso) → "Desactivar tour en este curso"**

Requiere capability `moodle/course:update` sobre el curso (no es global). Útil cuando un curso tiene una UI custom y el tour no aplica.

---

## Capabilities

| Capability | Para qué | Default role |
|------------|----------|--------------|
| `local/adipaonboarding:manage_tours` | Editar tours desde admin UI | Site admin |
| `local/adipaonboarding:manage_steps` | Editar step library + selector tester | Site admin |
| `local/adipaonboarding:view_telemetry` | Ver reportabilidad | Site admin |
| `local/adipaonboarding:reset_all_seen` | Resetear el "ya visto" de todos los estudiantes | Site admin |

---

## Estructura de archivos

```
local_adipaonboarding/
├── admin/                      # Páginas admin (tours, steps, telemetry, edit)
├── amd/src/                    # JS modules
│   ├── runner.js               # Orquestador del tour (Driver.js)
│   ├── trigger.js              # Botón "?" en banner / video viewer
│   ├── selector_tester.js      # Highlight para testear selectores
│   └── admin/                  # JS de las páginas admin
├── amd/build/                  # Copias .min.js (mismo contenido — convención ADIPA)
├── classes/local/
│   ├── tour/                   # Repos: step, tour, manifest, resolver, seeder
│   └── external/               # WS endpoints
├── db/
│   ├── install.xml             # Schema fresh install
│   ├── upgrade.php             # Migrations
│   ├── services.php            # WS routing
│   └── access.php              # Capabilities
├── docs/
│   └── ARCHITECTURE.md         # Doc técnico
├── lang/                       # i18n (es, es_mx, en)
├── lib/                        # Bundles vendoreados (Driver.js, Sortable, Chart)
├── templates/                  # Mustache (admin UI + step/tour editors)
├── tours/                      # Seeds por scope
│   ├── course_view.php
│   └── mod_adipavideo.php
├── lib.php                     # Hooks Moodle (before_footer, extend_nav_course)
├── settings.php                # Admin settings
└── version.php
```

---

## Bundles vendoreados (`lib/`)

| Archivo | Lib | Notas |
|---------|-----|-------|
| `driver.js.iife.js` | Driver.js v1.3.1 | IIFE puro, setea `window.driver`. |
| `driver.css` | Driver.js styles | Cargado on-demand. |
| `Sortable.min.js` | SortableJS 1.15.2 | **PATCHED**: AMD detection deshabilitada. |
| `chart.umd.min.js` | Chart.js 4.4.1 | **PATCHED**: AMD detection deshabilitada. |

### ⚠️ Si actualizás SortableJS o Chart.js

Re-aplicar el patch del UMD wrapper. El bundle original tiene:

```js
"function"==typeof define&&define.amd?define(e):...
```

Cambiar a:

```js
false?define(e):...
```

**Razón:** Moodle usa RequireJS con `define.amd` truthy. Si el bundle UMD detecta esto, llama `define()` anónimo y contamina RequireJS → cascadea errores `Mismatched anonymous define() module` en `core/network`, `core/toast`, `core_course/events`, etc. Estos errores son **fatales** para mod_adipavideo y otras features de Moodle.

El patch fuerza al bundle a tomar el branch global (`window.Sortable = e()`) en vez del AMD branch.

---

## Limitaciones / errores conocidos

- **v1.0.1 todavía tiene issues en producción** que la usuaria reportó pero no se cerraron antes del freeze. Pendiente para próxima iteración.
- El admin no puede subir media a través del Moodle File Picker (campo "Media URL" usa URL absoluta solamente). Sprint C removió el feature, queda pendiente reintroducirlo limpio si se necesita.
- El campo `responsivejson` en steps soporta `mobile.placement` override pero la admin UI no tiene form field para editarlo — solo se setea desde seed.
- No hay tests phpunit propios (solo lint + smoke testing manual).

---

## Versionado / changelog

| Versión | Fecha | Highlights |
|---------|-------|------------|
| **1.0.5** | 2026-06-16 | Nuevo step: barra de progreso del curso. Dos variantes según course_type: `progress_bar_date` (sync: course/diploma/postitulo/acreditacion — barra avanza por fechas) y `progress_bar_completion` (async: especializacion/magistral/asincronico — barra avanza por % de actividades). Bump version en todos los tours → re-show a usuarios que ya vieron el recorrido. |
| **1.0.4** | 2026-06-16 | CSS-only: breakpoint del trigger (?) banner alineado al de format_adipa (700px en lugar de 575px) — entre 576–700px el banner colapsa pero el icono quedaba con tamaño desktop. |
| **1.0.3** | 2026-06-16 | Trigger banner: 22×22 top-right (más sutil, ya no choca con caja sesiones). Telemetría: fix discrepancia cards vs tabla (era `get_records_sql` con keys colisionando — ahora `get_recordset_sql`). Runner: nueva señal de "completed" via `onPopoverRender` del último step (más confiable que `hasNextStep()`). +1 test PHPUnit corregido para multi-coursetype. |
| **1.0.2** | 2026-06-16 | Responsive: skip steps con target oculto en mobile (video_sidebar off-canvas, video_progress display:none) + placement por breakpoint en todos los steps. Trigger video: link sutil debajo del Tip de apuntes (no encima). Trigger banner: icono compacto top-right con contraste por programa (blanco/navy). Scroll-into-view antes de cada highlight (fix nid_row). Charts telemetría con wrap altura fija. |
| **1.0.1** | 2026-06-15 | Cleanup batch: helper admin_page_setup, cache buster en bundles UMD, drop `default_test_course_id`. |
| **1.0.0** | 2026-06-15 | **Sprint Cleanup**: drop dashboard scope, drop hero/popup mode (Sprint C rollback), drop video_capsule, simplify trigger (1 host por scope), runner refactor lineal, patch UMD bundles (Sortable + Chart) para no contaminar RequireJS, ARCHITECTURE.md. |
| 0.9.x | 2026-06-15 | Sprint D: pre_action engine (click/wait/scroll_to), runner visibility check, Driver.js doneBtnText override hack, DOM handoff payload, lint fixes. |
| 0.8.0 | 2026-06-14 | Sprint C: hero/popup mode con media slot. Removido en v1.0.0. |
| 0.7.x | 2026-06-14 | Sprint B: 6 course_types + dashboard scope (removido en v1.0.0). |
| 0.6.x | 2026-06-14 | Sprint A: Step Library + Tour Composition + admin drag-drop + selector tester + preview tour. |
| 0.5.x | 2026-06-14 | mod_adipavideo scope. |
| 0.2.0 | 2026-06-14 | Step Library + Tour Composition primera versión. |

---

## Decisiones de diseño que vale la pena conocer

1. **Step Library + Tour Composition**: ladrillos reusables. Aunque hoy no hay reúso entre tours (cada tour tiene su secuencia única), la abstracción permite que admin componga tours custom desde steps reusables. Eliminarla sería YAGNI inverso.

2. **DOM handoff (`<script type="application/json">`)** en vez de `js_call_amd` con payload: evita el warning "Too much data" de Moodle que rompe RequireJS en debug=DEVELOPER. Ver sección "Por qué DOM handoff" arriba.

3. **Linear flow en runner**: el runner v1.0+ no tiene branching por kind/hero/popup. Una función `runTour(payload, isPreview)`. Si hay que sumar features tipo "hero modal" otra vez, considerar feature flag separado, no rama en el runner.

4. **Trigger sin fallback floating**: si el host CSS no matchea, no se muestra el botón. Mejor que un floating feo en un theme que no soporta el host esperado.

5. **PHP 7.4 compatibility**: producción ADIPA corre 7.4. Lint con `php:7.4-cli` antes de cada bump. Sin `readonly`, `match (`, `?->`, named args.

---

## Cómo armar un ZIP local para deploy

```bash
cd "/ruta/al/proyecto"
rm -f ~/Downloads/local_adipaonboarding-X.Y.Z.zip
COPYFILE_DISABLE=1 tar --no-xattrs --exclude='.DS_Store' --exclude='._*' \
  -czf /tmp/onboarding.tgz local_adipaonboarding
cd /tmp && tar xzf onboarding.tgz
zip -rq ~/Downloads/local_adipaonboarding-X.Y.Z.zip local_adipaonboarding \
  -x '*.DS_Store' -x '._*'
```

**Crítico:** `COPYFILE_DISABLE=1` y `--no-xattrs` previenen archivos AppleDouble (`._*`) que macOS embebe en el tar y rompen el theme cuando se extrae en Linux.

---

## Smoke test pre-deploy

```bash
# PHP 7.4 lint (vía docker)
docker run --rm -v "$PWD":/app -w /app php:7.4-cli sh -c \
  'find . -type f -name "*.php" -not -path "./lib/*" | xargs -I{} php -l {}'

# JS syntax check
docker run --rm -v "$PWD":/app -w /app node:20-alpine sh -c \
  'find amd/src -name "*.js" -exec node --check {} \;'
```

---

## Contacto

Maintainer: nicolas@adipa.cl
Repo: https://github.com/nicolasadipa/local-adipaonboarding-moodle

---

## Licencia

GPL v3 (los plugins Moodle deben ser GPL).
