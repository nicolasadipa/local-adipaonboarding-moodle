<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Seed del scope mod_adipavideo: tour dentro del reproductor de video.
 * Coursetype = null → aplica a cualquier tipo de programa que tenga capsulas.
 */
function local_adipaonboarding_mod_adipavideo_seeds(): array {
    return [
        'steps' => [
            [
                'step_key'       => 'video_welcome',
                'selector'       => 'modal',
                'title_lang_key' => 'video_step_welcome_title',
                'body_lang_key'  => 'video_step_welcome_body',
                'placement'      => 'auto',
                'responsive'     => [],
            ],
            [
                'step_key'       => 'video_sidebar',
                'selector'       => '.adv-sidebar',
                'title_lang_key' => 'video_step_sidebar_title',
                'body_lang_key'  => 'video_step_sidebar_body',
                'placement'      => 'right',
                'responsive'     => ['mobile' => ['placement' => 'bottom']],
            ],
            [
                'step_key'       => 'video_player',
                'selector'       => '.adv-player-wrap',
                'title_lang_key' => 'video_step_player_title',
                'body_lang_key'  => 'video_step_player_body',
                'placement'      => 'bottom',
                'responsive'     => [],
            ],
            [
                'step_key'       => 'video_tabs',
                'selector'       => '.adv-tabs',
                'title_lang_key' => 'video_step_tabs_title',
                'body_lang_key'  => 'video_step_tabs_body',
                'placement'      => 'top',
                'responsive'     => [],
            ],
            [
                'step_key'       => 'video_notes',
                // Apunta al panel real de apuntes (editor) — pre_action lo abre primero.
                'selector'       => '.adv-tab-panel[data-panel="apuntes"]',
                'title_lang_key' => 'video_step_notes_title',
                'body_lang_key'  => 'video_step_notes_body',
                'placement'      => 'top',
                'responsive'     => [],
                'actions'        => [
                    ['type' => 'click', 'selector' => '.adv-tab-btn[data-tab="apuntes"]'],
                    ['type' => 'wait',  'ms' => 200],
                ],
            ],
            [
                'step_key'       => 'video_transcript',
                'selector'       => '.adv-tab-panel[data-panel="transcript"]',
                'title_lang_key' => 'video_step_transcript_title',
                'body_lang_key'  => 'video_step_transcript_body',
                'placement'      => 'top',
                'responsive'     => [],
                'actions'        => [
                    ['type' => 'click', 'selector' => '.adv-tab-btn[data-tab="transcript"]'],
                    ['type' => 'wait',  'ms' => 200],
                ],
            ],
            [
                'step_key'       => 'video_palette_toggle',
                'selector'       => '.adv-palette-toggle',
                'title_lang_key' => 'video_step_palette_title',
                'body_lang_key'  => 'video_step_palette_body',
                'placement'      => 'bottom',
                'responsive'     => [],
            ],
            [
                'step_key'       => 'video_progress',
                'selector'       => '.adv-topbar__progress, .adv-progress-bar',
                'title_lang_key' => 'video_step_progress_title',
                'body_lang_key'  => 'video_step_progress_body',
                'placement'      => 'bottom',
                'responsive'     => [],
            ],
            [
                'step_key'       => 'video_closing',
                'selector'       => 'modal',
                'title_lang_key' => 'video_step_closing_title',
                'body_lang_key'  => 'video_step_closing_body',
                'placement'      => 'auto',
                'responsive'     => [],
            ],
        ],
        'tours' => [
            [
                'scope'       => 'mod_adipavideo',
                'course_type' => null,
                'version'     => 3,
                'enabled'     => true,
                'visibility'  => [
                    'delay_ms'     => 5000,
                    'min_viewport' => 320,
                    'frequency'    => 'once_per_user',
                ],
                'step_keys' => [
                    'video_welcome',
                    'video_sidebar',
                    'video_player',
                    'video_tabs',
                    'video_notes',
                    'video_transcript',
                    'video_palette_toggle',
                    'video_progress',
                    'video_closing',
                ],
            ],
        ],
    ];
}
