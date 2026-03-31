<?php
/**
 * Front-end rendering: injects tooltip icons next to field labels
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Enqueue assets only on pages that contain a Gravity Form ──

add_action( 'wp_enqueue_scripts', function () {
    wp_register_style(
        'gf-tooltip-lite',
        GFTL_URL . 'assets/tooltip.css',
        [],
        GFTL_VERSION
    );
    wp_register_script(
        'gf-tooltip-lite',
        GFTL_URL . 'assets/tooltip.js',
        [],
        GFTL_VERSION,
        true   // load in footer
    );
} );

// GF fires this action when it outputs a form — safe moment to enqueue.
add_action( 'gform_enqueue_scripts', function () {
    wp_enqueue_style( 'gf-tooltip-lite' );
    wp_enqueue_script( 'gf-tooltip-lite' );
} );

add_filter( 'gform_field_content', function ( $field_content, $field ) {
    $tooltip_text = isset( $field->gftlTooltip ) ? trim( $field->gftlTooltip ) : '';

    if ( $tooltip_text === '' ) {
        return $field_content;
    }

    $icon = sprintf(
        '<span class="gftl-tooltip-wrap" aria-label="%1$s" role="tooltip" tabindex="0">'
            . '<span class="gftl-icon" aria-hidden="true">?</span>'
            . '<span class="gftl-bubble">%2$s</span>'
        . '</span>',
        esc_attr( $tooltip_text ),
        esc_html( $tooltip_text )
    );

    // Insert the icon right after the closing </label> tag of the field label.
    $field_content = preg_replace(
        '/(<label[^>]*class="[^"]*gfield_label[^"]*"[^>]*>)(.*?)(<\/label>)/s',
        '$1$2$3' . $icon,
        $field_content,
        1
    );

    return $field_content;
}, 10, 2 );
