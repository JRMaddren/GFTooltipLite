<?php
/**
 * Adds a "Tooltip Text" setting to every field in the Gravity Forms editor,
 * and saves / reads it as a custom field property.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Whitelist the custom property so GF doesn't strip it on save

add_filter( 'gform_field_type_pre_save_choices', function ( $field ) {
    return $field;
} );

add_filter( 'gform_pre_update_form', function ( $form ) {
    return $form;
} );

// Inject the custom setting HTML into the Advanced tab

add_action( 'gform_field_advanced_settings', function ( $position, $form_id ) {
    if ( 25 !== $position ) {
        return;
    }
    ?>
    <li class="gftl_tooltip_setting field_setting">
        <label for="gftl_tooltip_text" class="section_label">
            <?php esc_html_e( 'Tooltip Text', 'gf-tooltip-lite' ); ?>
        </label>
        <input
            type="text"
            id="gftl_tooltip_text"
            class="fieldwidth-3"
            placeholder="<?php esc_attr_e( 'Enter tooltip text…', 'gf-tooltip-lite' ); ?>"
        />
        <p class="description" style="font-size:11px;margin-top:4px;">
            <?php esc_html_e( 'Supports HTML e.g. <strong>, <a href="">. Shown as a hover tooltip next to the field label.', 'gf-tooltip-lite' ); ?>
        </p>
    </li>
    <?php
}, 10, 2 );

// Editor JS: register setting, sync property, and update preview live

add_action( 'gform_editor_js', function () {
    ?>
    <script type="text/javascript">
    (function($) {

        var gftlTypes = [
            'text','textarea','select','multiselect','number','checkbox','radio',
            'name','date','time','phone','address','website','email','fileupload',
            'hidden','html','section','captcha','list','post_title','post_body',
            'post_excerpt','post_tags','post_category','post_image','post_custom_field',
            'product','quantity','option','total','donation','creditcard','singleproduct',
            'hiddenproduct','calculation','consent','chainedselect','page'
        ];

        $(document).ready(function() {
            $.each(gftlTypes, function(i, type) {
                if (typeof fieldSettings[type] !== 'undefined') {
                    fieldSettings[type] += ', .gftl_tooltip_setting';
                } else {
                    fieldSettings[type] = '.gftl_tooltip_setting';
                }
            });
        });

        // When a field is selected in the editor, populate the input
        $(document).on('gform_load_field_settings', function(event, field, form) {
            var val = (field.gftlTooltip !== undefined && field.gftlTooltip !== null)
                ? field.gftlTooltip
                : '';
            $('#gftl_tooltip_text').val(val);

            // Update the preview for this field immediately when it's selected
            gftlUpdatePreview(field.id, val);
        });

        // When the input changes, write back to the field object and update preview.
        $(document).on('input change', '#gftl_tooltip_text', function() {
            var val = $(this).val();
            SetFieldProperty('gftlTooltip', val);
            gftlUpdatePreview(GetSelectedField().id, val);
        });

        /**
         * Insert or remove the tooltip icon in the editor field preview.
         * The GF editor renders each field inside #field_{id}.
         */
        function gftlUpdatePreview(fieldId, tooltipText) {
            var $fieldRow = $('#field_' + fieldId);
            if (!$fieldRow.length) return;

            // Remove any existing icon first to avoid duplicates.
            $fieldRow.find('.gftl-tooltip-wrap').remove();

            if (!tooltipText || tooltipText.trim() === '') return;

            var $label = $fieldRow.find('.gfield_label').first();
            if (!$label.length) return;

            var $icon = $(
                '<span class="gftl-tooltip-wrap" role="tooltip" tabindex="0">' +
                    '<span class="gftl-icon" aria-hidden="true">?</span>' +
                    '<span class="gftl-bubble"></span>' +
                '</span>'
            );

            // Set bubble content as HTML 
            $icon.find('.gftl-bubble').html(tooltipText);
            // Set plain-text aria-label for accessibility
            $icon.attr('aria-label', $('<div>').html(tooltipText).text());

            $label.append($icon);
        }

        // re-run on gform_load_field_settings in case the editor
        // re-renders the field preview
        $(document).on('gform_load_field_settings', function(event, field) {
            gftlUpdatePreview(field.id, field.gftlTooltip || '');
        });

    }(jQuery));
    </script>
    <?php
} );
