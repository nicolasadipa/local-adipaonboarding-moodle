<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * Manifest = recorrido completo: id, version, steps[] y reglas de visibilidad.
 *
 * Versioning: bump $version cuando agregues/cambies steps para relanzar
 * incrementalmente. La tabla local_adipaonboarding_seen indexa por
 * (userid, tourid, tourversion), asi que subir la version vuelve a marcar
 * el tour como "no visto" para todos los usuarios — sin reset masivo.
 */
class manifest {

    /** @var string */
    public $id;

    /** @var int */
    public $version;

    /** @var step[] */
    public $steps;

    /** @var array */
    public $visibility;

    public function __construct(string $id, int $version, array $steps, array $visibility) {
        $this->id         = $id;
        $this->version    = $version;
        $this->steps      = $steps;
        $this->visibility = $visibility;
    }

    /**
     * Payload listo para enviar al AMD. Resuelve strings de UI y serializa steps.
     */
    public function payload_for_client(): array {
        $stepspayload = [];
        foreach ($this->steps as $s) {
            $stepspayload[] = $s->to_array();
        }
        return [
            'id'      => $this->id,
            'version' => $this->version,
            'steps'   => $stepspayload,
            'visibility' => [
                'delay_ms'     => isset($this->visibility['delay_ms']) ? (int)$this->visibility['delay_ms'] : 0,
                'min_viewport' => isset($this->visibility['min_viewport']) ? (int)$this->visibility['min_viewport'] : 320,
            ],
            'i18n' => [
                'next'           => get_string('btn_next', 'local_adipaonboarding'),
                'prev'           => get_string('btn_prev', 'local_adipaonboarding'),
                'done'           => get_string('btn_done', 'local_adipaonboarding'),
                'progress'       => get_string('progress_text', 'local_adipaonboarding'),
                'trigger'        => get_string('trigger_button_label', 'local_adipaonboarding'),
                'trigger_aria'   => get_string('trigger_button_aria', 'local_adipaonboarding'),
                'preview_banner' => get_string('preview_banner_label', 'local_adipaonboarding'),
            ],
        ];
    }
}
