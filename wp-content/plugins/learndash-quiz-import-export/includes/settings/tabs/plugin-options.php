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

$minimum_role_to_administer = "manage_options";
if( isset( $values["minimum_role_to_administer"] ) ) {
    $minimum_role_to_administer = $values["minimum_role_to_administer"];
}
$allow_quiz_publish = "yes"; 
if( isset( $values["allow_quiz_publish"] ) ) {
    $allow_quiz_publish = $values["allow_quiz_publish"];
}
?>
<div id="setting_tabs" class="cs_ld_tabs">
    <form method="post">
        <div class="setting-table-wrapper">
            <table border="0">
                <thead></thead>
                <tbody>
                    <?php
                        if ( current_user_can( 'manage_options' ) ) {
                    ?>
                        <tr valign="top">
                            <td scope="row"><label class="label" for="minimum_role_to_administer"><?php _e( 'Minimum Role to Administer plugin: ', 'ldqie' ); ?></label></td>
                            <td>
                                <select id="minimum_role_to_administer" name="minimum_role_to_administer">
                                    <option value="manage_options" <?php selected( $minimum_role_to_administer, 'manage_options' ); ?>><?php _e( 'Administrator', 'ldqie' ); ?></option>
                                    <option value="delete_others_posts" <?php selected( $minimum_role_to_administer, 'delete_others_posts' ); ?>><?php _e( 'Editor', 'ldqie' ); ?></option>
                                    <option value="publish_posts" <?php selected( $minimum_role_to_administer, 'publish_posts' ); ?>><?php _e( 'Author', 'ldqie' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td scope="row"><label class="label" for="allow_quiz_publish"><?php _e( 'Allow Quiz Publish: ', 'ldqie' ); ?></label></td>
                            <td>
                                <select id="allow_quiz_publish" name="allow_quiz_publish">
                                    <option value="yes" <?php selected( $allow_quiz_publish, 'yes' ); ?>><?php _e( 'Yes', 'ldqie' ); ?></option>
                                    <option value="no" <?php selected( $allow_quiz_publish, 'no' ); ?>><?php _e( 'No', 'ldqie' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <?php $setting_class->create_fields( __( "Remove Questions on Quiz Delete", "ldqie" ), "DiscardQuizQuestion", "checkbox", "", ($values["DiscardQuizQuestion"] == 'yes') ? "checked" : "", __( "If you enable this option, questions will be discarded when the associated quiz is deleted by admin automatically. ", "ldqie" ), "", __( "Yes", "ldqie" ) ); ?>
                    </tr>
                    <tr>
                        <?php $setting_class->create_fields( __( "Discard Old Questions in Import", "ldqie" ), "DiscardOldQuestion", "checkbox", "", ($values["DiscardOldQuestion"] == 'yes') ? "checked" : "", __( "If you enable this option, questions will be discarded if quiz already exists. ", "ldqie" ), "", __( "Yes", "ldqie" ) ); ?>
                    </tr>
                    <tr>
                        <?php $setting_class->create_fields( __( "Allow Import of Existing Questions", "ldqie" ), "AllowExistingQuestionImport", "checkbox", "", ($values["AllowExistingQuestionImport"] == 'yes') ? "checked" : "", __( "If you enable this option, an extra step will show up during the import process to choose exisitng questions for import. ", "ldqie" ), "", __( "Yes", "ldqie" ) ); ?>
                    </tr>
                </tbody>
            </table>
            <div class="submit-button">
                <input type="submit" class="button-primary" value="Update Settings">
            </div>
        </div>
        <?php wp_nonce_field( 'ldqie_settings', 'ldqie_settings_field' ); ?>
    </form>
</div>           