<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Onboarding ADIPA';
$string['errornotfound'] = 'No se encontró el recurso solicitado.';
$string['on'] = 'Activado';
$string['off'] = 'Desactivado';

$string['welcome_title'] = '✨ Una experiencia renovada';
$string['welcome_body'] = '¡Mejoramos cada detalle del aula para que tu experiencia sea la mejor! Crece profesionalmente con el contenido y la tecnología que te mereces. Te acompañamos en este recorrido 🎉';
$string['closing_title'] = '¡Listo! Ya conoces lo nuevo';
$string['closing_body'] = 'Cuando quieras volver a recorrer el aula, busca el botón "¿Cómo usar el aula?" en la parte inferior. Estamos aquí para acompañarte en cada paso de tu aprendizaje.';

$string['step_header_title'] = 'Tu programa, siempre visible';
$string['step_header_body'] = 'Aquí encontrarás el nombre y tipo de tu programa. Diseñamos esta cabecera para que sepas, de un solo vistazo, dónde estás dentro del aula.';
$string['step_view_toggle_title'] = 'Elige cómo prefieres estudiar';
$string['step_view_toggle_body'] = 'Ahora puedes alternar entre <strong>Mosaico</strong> (vista visual con tarjetas) y <strong>Acordeón</strong> (vista compacta, ideal para repasar). Tú eliges el formato que mejor acompaña tu manera de aprender.';
$string['step_adipainfo_title'] = 'Toda tu información, en un solo lugar';
$string['step_adipainfo_body'] = 'Sesiones, evaluaciones y constancia, organizadas con claridad. Te avisamos siempre cuál es tu próxima fecha clave para que nada se te escape.';
$string['step_session_pill_title'] = 'Tu próxima sesión en vivo';
$string['step_session_pill_body'] = 'Aquí ves cuántas sesiones llevas completadas y cuándo es la siguiente. Pensamos esta vista para que nunca pierdas el ritmo de tu programa.';
$string['step_countdown_title'] = 'Cuenta regresiva en tiempo real';
$string['step_countdown_body'] = 'El reloj se actualiza solo, en tu zona horaria. Es tecnología pensada para acompañarte minuto a minuto hasta tu próxima sesión.';
$string['step_first_module_title'] = 'Empieza por aquí';
$string['step_first_module_body'] = 'Cada tarjeta es un módulo de tu programa. Avanza a tu ritmo: nuestro sistema registra tu progreso para que siempre sepas por dónde retomar.';
$string['step_certification_title'] = 'Tu logro, asegurado';
$string['step_certification_body'] = 'Al completar tu programa, podrás descargar tu constancia desde aquí. Cada paso que das te acerca un poco más a este logro.';
$string['step_nid_title'] = 'Tu documento de identidad';
$string['step_nid_body']  = 'Antes de tu constancia, sube tu documento de identidad. Es un paso clave para que tu constancia salga con tus datos correctos.';

$string['step_documentation_title'] = 'Documentación del programa';
$string['step_documentation_body']  = 'Toda la documentación oficial de tu acreditación está en este tile destacado. Revísala cuando lo necesites — es tu referencia.';

$string['setting_restricted_courses']      = 'Rollout: mostrar tour solo en estos cursos (IDs)';
$string['setting_restricted_courses_desc'] = 'IDs de curso separados por coma (ej: <code>2203, 2204</code>). Si está vacío, el tour aparece en TODOS los cursos elegibles. <strong>Úsalo para rollout gradual</strong>: empezás con uno o dos cursos piloto, observas en Reportabilidad, y después vacías el campo para liberar a todos. NO confundir con el "ID de prueba" del editor de step/tour (ese es solo para previsualizar como admin).';

$string['trigger_button_label'] = '¿Cómo usar el aula?';
$string['trigger_button_aria'] = 'Volver a ver el recorrido de bienvenida del aula';
$string['btn_next'] = 'Siguiente';
$string['btn_prev'] = 'Atrás';
$string['btn_done'] = 'Finalizar';
$string['btn_skip'] = 'Saltar recorrido';
$string['progress_text'] = '{{current}} de {{total}}';

$string['step_actions_label']     = 'Pre-acciones (JSON)';
$string['step_actions_help']      = 'Opcional. Lista de acciones a ejecutar ANTES de mostrar el popover, en orden. Útil para abrir una tab antes de highlightear. <br><strong>Tipos:</strong> <code>click</code> (requiere <code>selector</code>), <code>wait</code> (requiere <code>ms</code>).<br><strong>Ejemplo:</strong> <code>[{"type":"click","selector":".adv-tab-btn[data-tab=\"apuntes\"]"},{"type":"wait","ms":200}]</code>';

$string['admin_section_settings']  = 'Configuración';
$string['admin_section_tours']     = 'Tours';
$string['admin_section_steps']     = 'Step Library';
$string['admin_section_telemetry'] = 'Reportabilidad';

$string['setting_enabled'] = 'Activar onboarding';
$string['setting_enabled_desc'] = 'Cuando está desactivado, ningún estudiante verá el recorrido (ni el botón de reactivar).';
$string['setting_default_delay'] = 'Retraso por defecto (ms)';
$string['setting_default_delay_desc'] = 'Tiempo que espera el recorrido antes de aparecer cuando el estudiante carga la página por primera vez.';
$string['setting_reset_all'] = 'Resetear "ya visto" para todos los estudiantes';
$string['setting_reset_all_desc'] = 'Útil cuando lanzas features nuevas y quieres que todos los estudiantes vean el recorrido nuevamente.';
$string['setting_reset_all_btn'] = 'Resetear ahora';
$string['setting_reset_all_done'] = 'Se reseteó el "ya visto" de {$a} registros.';

$string['tours_list_col_scope']       = 'Scope';
$string['tours_list_col_course_type'] = 'Course type';
$string['tours_list_col_steps']       = 'Steps';
$string['tours_list_col_version']     = 'Versión';
$string['tours_list_col_enabled']     = 'Activo';
$string['tours_list_col_actions']     = 'Acciones';
$string['tours_list_action_edit']     = 'Editar';
$string['tours_list_dirty_badge']     = 'Modificado manualmente — el upgrade no lo sobrescribirá';
$string['tours_list_empty']           = 'Aún no hay tours en la base de datos. Reinstala el plugin o ejecuta el seeder.';
$string['tours_list_enabled_msg']     = 'Tour activado.';
$string['tours_list_disabled_msg']    = 'Tour desactivado.';

$string['tour_edit_title']              = 'Editar tour';
$string['tour_edit_back_btn']           = 'Volver a la lista';
$string['tour_edit_steps_panel']        = 'Steps de este tour';
$string['tour_edit_library_panel']      = 'Step Library (arrastra para agregar)';
$string['tour_edit_library_empty']      = 'Todos los steps ya están en este tour.';
$string['tour_edit_save_btn']           = 'Guardar cambios';
$string['tour_edit_bump_version_btn']   = 'Subir versión y relanzar';
$string['tour_edit_saved']              = 'Tour guardado correctamente.';
$string['tour_edit_version_bumped']     = 'Versión actualizada a v{$a}. Los estudiantes lo verán nuevamente.';
$string['tour_edit_dirty_warning']      = 'Este tour fue modificado manualmente. Las actualizaciones del plugin no lo sobrescribirán.';
$string['tour_edit_visibility_section'] = 'Reglas de visibilidad';
$string['tour_edit_lbl_enabled']        = 'Tour activo';
$string['tour_edit_lbl_delay']          = 'Delay (ms)';
$string['tour_edit_lbl_min_viewport']   = 'Viewport mínimo (px)';
$string['tour_edit_lbl_frequency']      = 'Frecuencia';
$string['tour_edit_drag_hint']          = 'Arrastra los steps para reordenarlos o moverlos entre paneles. Los cambios se guardan automáticamente.';
$string['tour_step_remove']             = 'Quitar de este tour';

$string['freq_once_per_user']  = 'Una vez por usuario';
$string['freq_once_per_day']   = 'Una vez al día';
$string['freq_every_visit']    = 'Cada visita';

$string['steps_list_col_key']          = 'Step key';
$string['steps_list_col_selector']     = 'Selector';
$string['steps_list_col_title']        = 'Título';
$string['steps_list_col_placement']    = 'Placement';
$string['steps_list_empty']            = 'La Step Library está vacía. Reinstala el plugin para correr el seeder.';
$string['steps_list_read_only_notice'] = 'Vista read-only. El editor de Step Library llega en el próximo sprint.';

$string['adipaonboarding:reset_all_seen'] = 'Resetear los recorridos vistos por todos los usuarios';
$string['adipaonboarding:manage_tours']   = 'Editar tours del onboarding';
$string['adipaonboarding:manage_steps']   = 'Editar la Step Library del onboarding';
$string['adipaonboarding:view_telemetry'] = 'Ver telemetría del onboarding';

$string['step_edit_title']         = 'Editar step';
$string['step_edit_new_title']     = 'Nuevo step';
$string['step_edit_lbl_step_key']  = 'Step key';
$string['step_edit_lbl_selector']  = 'CSS selector';
$string['step_edit_lbl_title_text'] = 'Título (texto directo)';
$string['step_edit_lbl_body_text']  = 'Cuerpo (texto directo)';
$string['step_edit_lbl_placement']  = 'Placement';
$string['step_edit_hint_step_key']  = 'Identificador estable: solo letras, números y guiones bajos. No se puede cambiar después.';
$string['step_edit_hint_text_overrides_lang'] = 'Si dejas vacío, se usa la traducción del lang key';
$string['step_edit_shipped_info']  = 'Este step viene con el plugin. Puedes sobrescribir el texto, pero el lang key se conserva para multipaís.';
$string['step_edit_custom_info']   = 'Step creado por administrador. Usa texto directo (sin lang key).';
$string['step_edit_preview_title'] = 'Vista previa del título resuelto';
$string['step_edit_preview_body']  = 'Vista previa del cuerpo resuelto';
$string['step_edit_save_btn']      = 'Guardar';
$string['step_edit_reset_btn']     = 'Restaurar defaults';
$string['step_edit_delete_btn']    = 'Borrar';
$string['step_edit_back_btn']      = 'Volver a la lista';
$string['step_edit_confirm_delete'] = '¿Borrar este step?';
$string['step_edit_confirm_reset']  = '¿Restaurar este step a sus valores originales del seed? Se perderán los cambios manuales.';
$string['step_edit_saved']         = 'Step guardado correctamente.';
$string['step_edit_deleted']       = 'Step borrado.';
$string['step_edit_reset_done']    = 'Step restaurado a defaults.';
$string['step_edit_validation_required'] = 'Step key y selector son obligatorios.';
$string['step_edit_delete_blocked'] = 'Este step lo usan {$a} tour(s):';

$string['steps_list_new_btn']        = 'Nuevo step';
$string['steps_list_shipped_badge']  = 'Step shipped (viene con el plugin)';
$string['steps_list_custom_badge']   = 'Step custom (creado por admin)';

$string['tour_edit_reset_btn']       = 'Restaurar defaults';
$string['tour_edit_confirm_reset']   = '¿Restaurar este tour a su seed? Se perderán los cambios manuales (steps, overrides, visibilidad).';
$string['tour_edit_reset_done']      = 'Tour restaurado a defaults.';
$string['tour_step_override_btn']    = 'Override';
$string['tour_step_override_title']  = 'Override del step en este tour';
$string['tour_step_override_lbl_selector']  = 'Selector (vacío = usar el del step library)';
$string['tour_step_override_lbl_placement'] = 'Placement (— = usar el del step library)';
$string['tour_step_override_clear']  = 'Quitar override';
$string['tour_step_override_apply']  = 'Aplicar';
$string['tour_step_override_cancel'] = 'Cancelar';

$string['telemetry_overview_title']   = 'Resumen general';
$string['telemetry_card_completed']   = 'Recorridos completados';
$string['telemetry_card_dismissed']   = 'Cerrados sin completar';
$string['telemetry_card_finished']    = 'Recorridos finalizados';
$string['telemetry_card_completion']  = 'Tasa de finalización';
$string['telemetry_tours_table_title'] = 'Por tour';
$string['telemetry_col_tourid']    = 'Tour';
$string['telemetry_col_finished']  = 'Finalizados';
$string['telemetry_col_completed'] = 'Completados';
$string['telemetry_col_dismissed'] = 'Cerrados';
$string['telemetry_col_rate']      = 'Tasa';
$string['telemetry_dropoff_title'] = 'Drop-off por step';
$string['telemetry_empty']         = 'Aún no hay datos de telemetría. Espera a que los estudiantes recorran los tours.';
$string['telemetry_reset_btn']     = 'Limpiar reportes';
$string['telemetry_reset_hint']    = 'Borra todos los eventos registrados. Útil si arrastras datos de pruebas anteriores.';
$string['telemetry_reset_confirm'] = '¿Borrar TODOS los eventos de telemetría? Esto no se puede deshacer.';
$string['telemetry_reset_done']    = 'Se borraron {$a} eventos de telemetría.';

$string['course_optout_nav_label']    = 'Onboarding';
$string['course_optout_page_title']   = 'Onboarding del curso';
$string['course_optout_page_heading'] = 'Onboarding · {$a}';
$string['course_optout_description']  = 'Desactiva el recorrido de bienvenida para este curso. Útil cuando un curso no se beneficia del tour (por ejemplo, un curso de uso interno).';
$string['course_optout_lbl_toggle']   = 'Desactivar tour para este curso';
$string['course_optout_hint_on']      = 'El tour NO se muestra a los estudiantes de este curso.';
$string['course_optout_hint_off']     = 'El tour SÍ se muestra (comportamiento normal).';
$string['course_optout_saved']        = 'Configuración guardada.';

$string['step_edit_test_selector_btn']         = 'Probar selector';
$string['step_edit_test_selector_label']       = 'Probar selector en un curso';
$string['step_edit_test_selector_hint']        = 'Abre el curso en una pestaña nueva y resalta los elementos que matchean.';
$string['step_edit_test_selector_no_selector'] = 'Escribe un selector primero.';
$string['test_course_id_label']                = 'Curso ID:';
$string['test_course_id_required']             = 'Configura un curso de prueba (ID del curso).';
$string['tour_edit_preview_btn']               = 'Previsualizar tour';
$string['preview_banner_label']                = 'Modo preview — solo admin (no se guarda en seen ni en telemetría)';
$string['selector_tester_matches']             = '{$a} match(es) encontrados';
$string['selector_tester_error']               = 'Selector inválido';
$string['selector_tester_close']               = 'Cerrar';

$string['video_step_welcome_title'] = '🎬 Tu nuevo reproductor';
$string['video_step_welcome_body']  = 'Renovamos por completo el reproductor para que aproveches al máximo cada cápsula. Apuntes, transcripción, marcadores y más: todo pensado para acompañarte en tu aprendizaje.';
$string['video_step_sidebar_title'] = 'Tus cápsulas, siempre a mano';
$string['video_step_sidebar_body']  = 'En el panel lateral ves todas las cápsulas del programa. Tu progreso se guarda automáticamente: el sistema sabe dónde quedaste.';
$string['video_step_player_title']  = 'Tu reproductor con superpoderes';
$string['video_step_player_body']   = 'Calidad adaptativa, marcadores y velocidad ajustable. Diseñado para que aprendas con comodidad, a tu ritmo.';
$string['video_step_tabs_title']    = 'Más que un video';
$string['video_step_tabs_body']     = 'En las pestañas tienes apuntes, transcripción, resumen y recursos. Todo lo que necesitas en un solo lugar.';
$string['video_step_notes_title']   = 'Apuntes con timestamp';
$string['video_step_notes_body']    = 'Mientras miras, anota lo que quieras. El sistema guarda el minuto exacto en el que escribiste — para que vuelvas justo a ese punto cuando repases.';
$string['video_step_transcript_title'] = 'Busca cualquier palabra del video';
$string['video_step_transcript_body']  = 'La transcripción completa está disponible y es buscable. Si quieres repasar un concepto, escríbelo en el buscador y saltas directo a ese minuto.';
$string['video_step_palette_title'] = 'Elige cómo se ve';
$string['video_step_palette_body']  = 'Cambia entre tema claro y oscuro según tu preferencia. Pequeños detalles para hacer tu estudio más agradable.';
$string['video_step_progress_title'] = 'Tu progreso en tiempo real';
$string['video_step_progress_body']  = 'La barra superior muestra cuánto avanzaste del programa. Cada cápsula completada suma — y te acerca a tu constancia.';
$string['video_step_closing_title'] = '¡Disfruta tu programa!';
$string['video_step_closing_body']  = 'Cuando quieras revisar el recorrido, encontrarás el botón "¿Cómo usar el aula?" abajo a la derecha. Estamos para acompañarte.';

$string['privacy:metadata:local_adipaonboarding_seen'] = 'Registro de qué recorridos ha visto cada estudiante.';
$string['privacy:metadata:local_adipaonboarding_seen:userid'] = 'ID del usuario';
$string['privacy:metadata:local_adipaonboarding_seen:tourid'] = 'ID del recorrido visto';
$string['privacy:metadata:local_adipaonboarding_seen:tourversion'] = 'Versión del recorrido cuando se vio';
$string['privacy:metadata:local_adipaonboarding_seen:seenat'] = 'Fecha en que se completó el recorrido';
$string['privacy:metadata:local_adipaonboarding_events'] = 'Eventos de telemetría (step viewed, skipped, completed) del recorrido.';
