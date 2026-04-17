<?php
/**
 * Group Codes Tab contents display template
 *
 * @since      4.1.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users/tabs
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
?>
<div id="tab-<?php echo esc_attr( $content['id'] );?>" class="tab-content ldgr-group-code-tab">
    <div class="ldgr-group-code-view ldgr-group-code-screen">
        <div class="ldgr-group-code-container-head">
            <div class="ldgr-group-code-messages">
                <span class="ldgr-message-close">&times;</span>
                <span class="ldgr-message-text"></span>
            </div>
            <button id="ldgr-add-group-code">
                <?php esc_html_e( 'Create Group Code', WDM_LDGR_TXT_DOMAIN ); ?>
            </button>
        </div>
        <?php if ( ! empty( $content['data']['enrollment_page_id'] ) ) : ?>
            <div class="ldgr-group-code-reg-form-details">
                <a href="<?php echo get_permalink( $content['data']['enrollment_page_id'] ); ?>">
                    <span class="dashicons dashicons-admin-links"></span>
                    <span class="ldgr-tooltip"><?php esc_html_e( 'Share this link with students to Enroll/Register using Group Codes', WDM_LDGR_TXT_DOMAIN ); ?></span>
                </a>
            </div>
        <?php endif; ?>
        <table id="ldgr-group-code-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Code', WDM_LDGR_TXT_DOMAIN );?></th>
                    <th><?php esc_html_e( 'Summary', WDM_LDGR_TXT_DOMAIN );?></th>
                    <th><?php esc_html_e( 'Actions', WDM_LDGR_TXT_DOMAIN );?></th>
                    <th><?php esc_html_e( 'Status', WDM_LDGR_TXT_DOMAIN );?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($content['data']['group_codes_data']) ) : ?>
                    <?php foreach( $content['data']['group_codes_data'] as $group_code ) : ?>
                    <?php
                        ldgr_get_template(
                            WDM_LDGR_PLUGIN_DIR . '/modules/templates/group-code-screens/ldgr-view-group-code-single.template.php',
                            array(
                                'group_code' => $group_code
                            )
                        );
                    ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="ldgr-empty-list">
                        <td colspan="4">
                            <span>
                                <?php esc_html_e( 'No group codes found', WDM_LDGR_TXT_DOMAIN ); ?>
                            </span>
                        </td>
                    </tr>
                <?php endif;  ?>
            </tbody>
        </table>
    </div>
    <div class="ldgr-group-code-create ldgr-group-code-screen" style="display:none;">
        <?php
            ldgr_get_template(
                WDM_LDGR_PLUGIN_DIR . '/modules/templates/group-code-screens/ldgr-create-group-code-screen.template.php',
                array(
                    'group_id'  =>  $content['data']['group_id'],
                    'is_unlimited' => $content['data']['is_unlimited']
                )
            );
        ?>
    </div>
    <div class="ldgr-group-code-edit ldgr-group-code-screen" style="display:none;">
        <?php
            ldgr_get_template(
                WDM_LDGR_PLUGIN_DIR . '/modules/templates/group-code-screens/ldgr-edit-group-code-screen.template.php',
                array(
                    'group_id'  =>  $content['data']['group_id'],
                    'is_unlimited' => $content['data']['is_unlimited']
                )
            );
        ?>
    </div>
    <div class="ldgr-black-screen">
        <span style="margin-bottom:10px;"><?php esc_html_e( 'Loading...', WDM_LDGR_TXT_DOMAIN ); ?></span>
        <span class="dashicons dashicons-update spin"></span>
    </div>
</div>