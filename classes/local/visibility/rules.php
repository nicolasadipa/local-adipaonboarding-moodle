<?php
namespace local_adipaonboarding\local\visibility;

use local_adipaonboarding\local\tour\manifest;
use local_adipaonboarding\local\tour\storage;

defined('MOODLE_INTERNAL') || die();

/**
 * Unica fuente de verdad para "¿corresponde mostrar este tour ahora?".
 *
 * Server-side. El cliente solo aplica delay y filtra elementos no presentes
 * en el DOM. Si el server dice "no", el cliente no recibe payload para
 * auto-launch (solo recibe el trigger button para replay manual).
 */
class rules {

    public static function should_show(manifest $manifest, int $userid): bool {
        $frequency = isset($manifest->visibility['frequency'])
            ? $manifest->visibility['frequency']
            : 'once_per_user';

        switch ($frequency) {
            case 'every_visit':
                return true;
            case 'once_per_user':
                return !storage::has_seen($userid, $manifest->id, $manifest->version);
            case 'once_per_day':
                return !storage::seen_today($userid, $manifest->id);
            default:
                return false;
        }
    }
}
