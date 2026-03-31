<?php
/**
 * Adds a "Tooltip Text" setting to every field in the Gravity Forms editor,
 * and saves / reads it as a custom field property.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'gform_field_advanced_settings', function ( $position, $form_id ) {
    // Position 25 renders just below the "Custom CSS Class" setting.
    if ( $position !== 25 ) {
        return;
    }
    ?>
    <li class="gftl_tooltip_setting field_setting">
        <label for="gftl_tooltip_text">
            <?php esc_html_e( 'Tooltip Text', 'gf-tooltip-lite' ); ?>
            <?php gform_tooltip( 'gftl_tooltip_text' ); ?>
        </label>
        <input
            type="text"
            id="gftl_tooltip_text"
            class="fieldwidth-3"
            placeholder="<?php esc_attr_e( 'Enter tooltip text…', 'gf-tooltip-lite' ); ?>"
            onchange="SetFieldProperty('gftlTooltip', this.value);"
        />
    </li>
    <?php
}, 10, 2 );

add_filter( 'gform_tooltips', function ( $tooltips ) {
    $tooltips['gftl_tooltip_text'] = '<h6>' . esc_html__( 'Tooltip Text', 'gf-tooltip-lite' ) . '</h6>'
        . esc_html__( 'Text shown in a hover tooltip next to the field label.', 'gf-tooltip-lite' );
    return $tooltips;
} );

add_action( 'gform_editor_js', function () {
    ?>
    <script>
    // Show our setting for every field type.
    jQuery(document).on('gform_load_field_settings', function(event, field) {
        jQuery('#gftl_tooltip_text').val(field.gftlTooltip || '');
    });

    // Register the setting so GF copies it across all field types.
    fieldSettings['text']            += ', .gftl_tooltip_setting';
    fieldSettings['textarea']        += ', .gftl_tooltip_setting';
    fieldSettings['select']          += ', .gftl_tooltip_setting';
    fieldSettings['multiselect']     += ', .gftl_tooltip_setting';
    fieldSettings['number']          += ', .gftl_tooltip_setting';
    fieldSettings['checkbox']        += ', .gftl_tooltip_setting';
    fieldSettings['radio']           += ', .gftl_tooltip_setting';
    fieldSettings['name']            += ', .gftl_tooltip_setting';
    fieldSettings['date']            += ', .gftl_tooltip_setting';
    fieldSettings['time']            += ', .gftl_tooltip_setting';
    fieldSettings['phone']           += ', .gftl_tooltip_setting';
    fieldSettings['address']         += ', .gftl_tooltip_setting';
    fieldSettings['website']         += ', .gftl_tooltip_setting';
    fieldSettings['email']           += ', .gftl_tooltip_setting';
    fieldSettings['fileupload']      += ', .gftl_tooltip_setting';
    fieldSettings['hidden']          += ', .gftl_tooltip_setting';
    fieldSettings['html']            += ', .gftl_tooltip_setting';
    fieldSettings['section']         += ', .gftl_tooltip_setting';
    fieldSettings['captcha']         += ', .gftl_tooltip_setting';
    fieldSettings['list']            += ', .gftl_tooltip_setting';
    </script>
    <?php
} );
