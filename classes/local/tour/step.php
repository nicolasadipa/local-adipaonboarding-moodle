<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * Value object inmutable de un step: pre-resuelto (title/body son strings
 * ya finales, no claves). El repositorio decide en hydrate() si los strings
 * vienen de un lang_key (steps shipped) o de text directo (steps custom).
 *
 * PHP 7.4-safe: sin constructor promotion ni readonly.
 */
class step {

    /** @var string stepkey (id estable del step, usado en eventos de telemetria) */
    public $id;

    /** @var string CSS selector, o 'modal' para popover centrado sin highlight */
    public $element;

    /** @var string titulo resuelto */
    public $title;

    /** @var string cuerpo resuelto (HTML basico permitido) */
    public $body;

    /** @var string top|bottom|left|right|over|auto */
    public $placement;

    /** @var array overrides por breakpoint */
    public $responsive;

    /**
     * @var array Lista de pre-actions a ejecutar antes del popover.
     * Forma: [{type:'click'|'wait', selector?:string, ms?:int}].
     */
    public $actions;

    public function __construct(array $data) {
        $this->id         = $data['id'];
        $this->element    = $data['element']    ?? 'modal';
        $this->title      = $data['title']      ?? '';
        $this->body       = $data['body']       ?? '';
        $this->placement  = $data['placement']  ?? 'auto';
        $this->responsive = $data['responsive'] ?? [];
        $this->actions    = isset($data['actions']) && is_array($data['actions']) ? $data['actions'] : [];
    }

    public function to_array(): array {
        return [
            'id'         => $this->id,
            'element'    => $this->element,
            'title'      => $this->title,
            'body'       => $this->body,
            'placement'  => $this->placement,
            'responsive' => $this->responsive,
            'actions'    => $this->actions,
        ];
    }
}
