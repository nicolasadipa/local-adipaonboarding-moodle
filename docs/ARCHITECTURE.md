# local_adipaonboarding — Arquitectura (v1.0.0)

## Objetivo

Mostrar tours guiados (Driver.js) a estudiantes en 2 contextos del aula ADIPA:

- **`course_view`**: la página del curso (`/course/view.php?id=N`), variante por `course_type` (course/diploma/postitulo/especializacion/magistral/asincronico/acreditacion).
- **`mod_adipavideo`**: el viewer de cápsulas de video (`/mod/adipavideo/view.php?id=N`).

No hay tour para dashboard ni para otros contextos. Si el alumno entra a otro lado, el plugin queda silencioso.

## Modelo de datos

3 tablas core + 3 de soporte:

```
local_adipaonboarding_steps    (Step Library — ladrillos reusables)
  ├ id, stepkey, selector, placement
  ├ titlelangkey | titletext     (text gana sobre langkey)
  ├ bodylangkey  | bodytext
  ├ actionsjson  (pre-actions: click/wait, opcional)
  ├ responsivejson, shipped, dirty, createdat, updatedat

local_adipaonboarding_tours    (Tour = secuencia de steps por scope+course_type)
  ├ id, scope, coursetype, version
  ├ enabled, visibilityjson (delay_ms, min_viewport, frequency)
  └ shipped, dirty, createdat, updatedat

local_adipaonboarding_tsteps   (junction tour→steps con override opcional)
  ├ id, tourid, stepid, sortorder, overridejson
  └ FKs: tourid→tours.id, stepid→steps.id

local_adipaonboarding_seen     (recorridos ya vistos por usuario+version)
local_adipaonboarding_events   (telemetria: viewed/completed/dismissed)
local_adipaonboarding_optout   (opt-out per-course)
```

**Shipped vs dirty**: un step/tour `shipped=1` viene del seed. Cuando el admin lo edita, `dirty=1` y el upgrade NO lo sobrescribe en el próximo seeder run. Cleanest separation entre código y data editada.

## Lifecycle de un tour

```
Page request
  ↓
local_adipaonboarding_before_footer()           [lib.php]
  ├ filtros: logged-in, no-guest, no-admin, plugin enabled
  ├ resolver::for_page($PAGE)                    [classes/local/tour/resolver.php]
  │    └→ matchea pagetype → tour_repository::build_manifest_for(scope, coursetype)
  ├ filtros de curso: restricted_courses, opt-out, enrolled, no editing rights
  ├ emite <script type="application/json" id="adipa-onboarding-manifest">PAYLOAD</script>
  └ js_call_amd('runner', 'init', []) + js_call_amd('trigger', 'init', [])
  ↓
Browser
  ├ trigger.js init     → lee payload del DOM → inyecta "?" en .adipa-s0-banner
  │                                          o en wrap-tip despues de .adv-tabs
  └ runner.js init     → lee payload del DOM → si rules::should_show, auto-launch
                                            ↓
                       Driver.js (load on demand) → drive(steps)
                                            ↓
                       onPopoverRender → logEvent('viewed')
                       onDestroyed     → markSeen + logEvent('completed'|'dismissed')
```

## Por qué DOM handoff (script JSON) y no js_call_amd args

Moodle warna cuando los args de `js_call_amd` superan 1024 chars (`debugging('Too much data...')`). Con `debug=DEVELOPER`, el warning se echa inline al HTML, lo que rompe el bootstrap de RequireJS y cascadea errores como `define is not a function` en `core/network`, `core/toast`, etc.

Solución: `lib.php` retorna `<script type="application/json" id="adipa-onboarding-manifest">{...}</script>` en el footer. Los AMD modules leen via `document.getElementById(...).textContent` + `JSON.parse`. Cero contaminación del payload por args.

## Pre-actions (`step.actions`)

JSON array opcional en cada step. Tipos soportados:

- `{type: 'click', selector: '...'}` — dispara `.click()` en el elemento. Ej: abrir una tab antes de highlightear su panel.
- `{type: 'wait', ms: 200}` — pausa best-effort (no bloquea Driver.js, sirve para encadenar acciones).

El runner ejecuta las actions en `onHighlightStarted` del Driver.js. **Steps con click action saltean la validación de visibility** del target (el click puede hacer aparecer el target). Steps sin click action requieren target visible AHORA, sino skippean (el popover quedaría mal anclado).

Uso real: `video_notes` y `video_transcript` en `mod_adipavideo` tour — abren la tab antes de mostrar el popover sobre el panel.

## Trigger UI ("¿Cómo usar el aula?")

Un host fijo por scope, sin cascada ni fallback:

- **`course_view`** → ícono "?" circular 36px dentro de `.adipa-s0-banner` (banner del programa). Tooltip CSS al hover.
- **`mod_adipavideo`** → wrap "tip" después de `.adv-tabs` (icono "?" + texto "¿Cómo usar el aula?" al lado).

Si el host no matchea, el botón no se renderiza. Sin fallback floating ni inline.

## Bundles vendoreados

`local_adipaonboarding/lib/`:

| Archivo | Lib | Notes |
|---------|-----|-------|
| `driver.js.iife.js` | Driver.js v1.3.1 | IIFE puro, setea `window.driver`. |
| `driver.css` | Driver.js styles | Importado por `loadDriver()`. |
| `Sortable.min.js` | SortableJS 1.15.2 | **Patched**: `define.amd` → `false?define(e)`. Si no, contamina RequireJS de Moodle. |
| `chart.umd.min.js` | Chart.js 4.4.1 | **Patched**: mismo issue UMD que Sortable. |

Si actualizás cualquiera de estos bundles, RE-APLICAR el patch del UMD wrapper o tendrás `define is not a function` cascading errors en core modules de Moodle.

## Endpoints WS

| Methodname | Caps | Caller |
|------------|------|--------|
| `mark_seen` | logged-in (no admin gate) | runner (al cerrar tour) |
| `log_event` | logged-in | runner (per step + tour close) |
| `save_step` | `manage_steps` | admin step editor |
| `delete_step` | `manage_steps` | admin step editor |
| `reset_step_defaults` | `manage_steps` | admin step editor |
| `reorder_steps` | `manage_tours` | admin tour editor (drag-drop) |
| `set_step_override` | `manage_tours` | admin tour editor (override modal) |
| `save_tour` | `manage_tours` | admin tour editor (metadata) |
| `bump_tour_version` | `manage_tours` | admin tour editor |
| `reset_tour_defaults` | `manage_tours` | admin tour editor |

## Cómo agregar un step nuevo (admin)

1. **Si es shipped** (parte del default): editar `tours/<scope>.php`, agregar al step library + al array `step_keys` del tour. Bump del `version` del tour. Run upgrade.
2. **Si es custom** (solo este sitio): admin → Onboarding → Step Library → "Nuevo step". Stepkey custom + selector + título/cuerpo + actions JSON opcional. Después editar el tour para incluirlo.

## Cómo agregar un scope nuevo

1. Crear `tours/<nuevo_scope>.php` con funcion `local_adipaonboarding_<scope>_seeds()`.
2. Agregar el archivo + función al `seeder.php` (seed_files + seed_functions).
3. Agregar el match de pagetype en `resolver::for_page()`.
4. (Opcional) Adaptar trigger.js si querés un host distinto para ese scope.
5. Bump version + migration.
