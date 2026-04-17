<?php
namespace LDQIE;
/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$setting_class = $GLOBALS['ldqie_options'];
$values = get_option( "quiz_default" );
?>
<div id="setting_tabs" class="cs_ld_tabs">
    <form method="post">
        <div class="setting-table-wrapper">
            <div class="input-row">
                <?php $setting_class->create_fields( __( "Description", "ldqie" ), "text", "textarea", stripslashes( esc_textarea($values["text"]) ) ); ?>
            </div>

            <div class="submit-button">
                <input type="submit" class="button-primary" value="Update Settings">
            </div>
        </div>
        <?php wp_nonce_field( 'ldqie_settings', 'ldqie_settings_field' ); ?>
    </form>
</div>