<?php
/**
 * Front-end rendering: injects tooltip icons next to field labels
 * and enqueues the stylesheet + lightweight JS.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Allowed HTML tags inside tooltip bubbles
 */
function gftl_allowed_html() {
    return [
        'a'      => [ 'href' => true, 'title' => true, 'target' => true, 'rel' => true ],
        'strong' => [],
        'b'      => [],
        'em'     => [],
        'i'      => [],
        'br'     => [],
        'span'   => [ 'class' => true ],
    ];
}

// enqueue assets only on pages that contain a Gravity Form

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
        [ 'jquery' ],
        GFTL_VERSION,
        true
    );
} );

add_action( 'gform_enqueue_scripts', function () {
    wp_enqueue_style( 'gf-tooltip-lite' );
    wp_enqueue_script( 'gf-tooltip-lite' );
} );

// Admin editor preview

add_filter( 'gform_field_content', function ( $field_content, $field ) {
    if ( ! is_admin() ) {
        return $field_content;
    }

    $tooltip_text = isset( $field->gftlTooltip ) ? trim( $field->gftlTooltip ) : '';
    if ( $tooltip_text === '' ) {
        return $field_content;
    }

    $icon = sprintf(
        '<span class="gftl-tooltip-wrap" role="tooltip" aria-label="%1$s" tabindex="0">'
            . '<span class="gftl-icon" aria-hidden="true">?</span>'
            . '<span class="gftl-bubble">%2$s</span>'
        . '</span>',
        esc_attr( wp_strip_all_tags( $tooltip_text ) ),  // plain text for aria-label
        wp_kses( $tooltip_text, gftl_allowed_html() )    // HTML allowed in bubble
    );

    $dom = new DOMDocument();
    libxml_use_internal_errors( true );
    $dom->loadHTML( '<meta charset="utf-8">' . $field_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    libxml_clear_errors();

    $xpath  = new DOMXPath( $dom );
    $labels = $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " gfield_label ")]' );

    if ( $labels->length > 0 ) {
        $frag = $dom->createDocumentFragment();
        $frag->appendXML( $icon );
        $labels->item( 0 )->appendChild( $frag );

        $body = $dom->getElementsByTagName( 'body' )->item( 0 );
        if ( $body ) {
            $field_content = '';
            foreach ( $body->childNodes as $child ) {
                $field_content .= $dom->saveHTML( $child );
            }
        }
    }

    return $field_content;
}, 10, 2 );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( strpos( $hook, 'gf_' ) === false && strpos( $hook, 'gravityforms' ) === false ) {
        return;
    }
    wp_enqueue_style(
        'gf-tooltip-lite-admin',
        GFTL_URL . 'assets/tooltip.css',
        [],
        GFTL_VERSION
    );
} );

// Front-end: collect tooltip data and inject via JS DOM 

add_filter( 'gform_get_form_filter', function ( $form_string, $form ) {
    $tooltips = [];

    foreach ( $form['fields'] as $field ) {
        $text = isset( $field->gftlTooltip ) ? trim( $field->gftlTooltip ) : '';
        if ( $text !== '' ) {
            // Sanitise but preserve allowed HTML; wp_json_encode handles JS escaping.
            $tooltips[ (int) $field->id ] = wp_kses( $text, gftl_allowed_html() );
        }
    }

    if ( empty( $tooltips ) ) {
        return $form_string;
    }

    $json    = wp_json_encode( $tooltips );
    $form_id = (int) $form['id'];

    $script = "<script>
(function(){
    var data = {$json};
    var formId = {$form_id};

    function inject() {
        Object.keys(data).forEach(function(fieldId) {
            var wrapper = document.getElementById('field_' + formId + '_' + fieldId);
            if (!wrapper) return;

            if (wrapper.querySelector('.gftl-tooltip-wrap')) return;

            var label = wrapper.querySelector('.gfield_label');
            if (!label) return;

            var span = document.createElement('span');
            span.className = 'gftl-tooltip-wrap';
            span.setAttribute('role', 'tooltip');
            // aria-label should be plain text for screen readers.
            span.setAttribute('aria-label', data[fieldId].replace(/<[^>]*>/g, ''));
            span.setAttribute('tabindex', '0');

            var icon   = document.createElement('span');
            icon.className = 'gftl-icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = '?';

            var bubble = document.createElement('span');
            bubble.className = 'gftl-bubble';
            // innerHTML is safe here: content has been through wp_kses on the server.
            bubble.innerHTML = data[fieldId];

            span.appendChild(icon);
            span.appendChild(bubble);
            label.appendChild(span);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inject);
    } else {
        inject();
    }

    if (window.jQuery) {
        jQuery(document).on('gform_post_render', function(e, fId) {
            if (fId === formId) inject();
        });
    }
}());
</script>";

    return $form_string . $script;
}, 10, 2 );
