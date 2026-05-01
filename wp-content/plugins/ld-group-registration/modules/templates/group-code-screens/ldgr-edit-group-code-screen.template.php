<?php
/**
 * Template : LDGR edit group code screen template
 *
 * @since 4.1.0
 */
?>
<div class="ldgr-group-code-container">
    <div class="ldgr-group-code-container-head">
        <div class="ldgr-group-code-messages">
            <span class="ldgr-message-close">&times;</span>
            <span class="ldgr-message-text"></span>
        </div>
        <button class="ldgr-back-to-view"><?php esc_html_e( 'Back', WDM_LDGR_TXT_DOMAIN ); ?></button>
    </div>
    <form method="post" id="ldgr-group-code-edit-form" class="ldgr-form">
        <p>
            <label for="ldgr-code-string">
                <?php esc_html_e( 'Code', WDM_LDGR_TXT_DOMAIN )?>
            </label>
            <input type="text" name="ldgr-code-string" class="ldgr-code-string" autocomplete="off" required>
            <input type="button" class="ldgr-generate-group-code" value="<?php esc_html_e( 'Regenerate', WDM_LDGR_TXT_DOMAIN ); ?>">
            <span class="dashicons dashicons-update"></span>
        </p>
        <p>
            <label for="ldgr-code-date-range-from">
                <?php esc_html_e( 'From :', WDM_LDGR_TXT_DOMAIN )?>
            </label>
            <input type="text" name="ldgr-code-date-range-from" class="ldgr-code-date-range-from" autocomplete="off" readonly />
            <label for="ldgr-code-date-range-to">
                <?php esc_html_e( 'To :', WDM_LDGR_TXT_DOMAIN )?>
            </label>
            <input type="text" name="ldgr-code-date-range-to" class="ldgr-code-date-range-to" autocomplete="off" readonly />
        </p>
        <?php if ( $is_unlimited ) : ?>
            <p>
                <label for="ldgr-code-limit">
                    <?php esc_html_e( 'Number of Enrollments', WDM_LDGR_TXT_DOMAIN ); ?>
                </label>
                <input type="number" name="ldgr-code-limit" class="ldgr-code-limit" min="1" required/>
            </p>
        <?php else: ?>
            <input type="hidden" name="ldgr-code-limit" value="<?php echo get_post_meta( $group_id, 'wdm_group_users_limit_'.$group_id, 1 ); ?>" />
        <?php endif; ?>
        <p>
            <label><?php esc_html_e( 'Number of Enrolled Users :', WDM_LDGR_TXT_DOMAIN )?></label>
            <strong><span class="ldgr-code-enrolled-users-count">0</span></strong>
        </p>
        <p>
            <label class="ldgr-switch">
                <input type="checkbox" name="ldgr-code-validation-check" class="ldgr-code-validation-check" />
                <span class="ldgr-switch-slider round"></span>
            </label>
            <span>
                <?php esc_html_e( 'Validation Rules', WDM_LDGR_TXT_DOMAIN ); ?>
            </span>
        </p>
        <p>
        <div class="ldgr-code-validation" style="display:none;">
                <div>
                    <label for="ldgr-code-ip-validation">
                        <span><?php esc_html_e( 'IP Address', WDM_LDGR_TXT_DOMAIN ); ?></span>
                        <span class="dashicons dashicons-info"></span>
                        <span class="ldgr-tooltip"><?php esc_html_e( 'Enter IP address to validate during enrollment ( eg. 10.10.10.10 )', WDM_LDGR_TXT_DOMAIN ); ?></span>
                    </label>
                    <input name="ldgr-code-ip-validation" class="ldgr-code-ip-validation" type="text" />
                </div>
                <div>
                    <label for="ldgr-code-domain-validation">
                        <span><?php esc_html_e( 'Domain Name', WDM_LDGR_TXT_DOMAIN ); ?></span>
                        <span class="dashicons dashicons-info"></span>
                        <span class="ldgr-tooltip"><?php esc_html_e( 'Enter email domain name to validate during enrollment ( eg. gmail.com )', WDM_LDGR_TXT_DOMAIN ); ?></span>
                    </label>
                    <input name="ldgr-code-domain-validation" class="ldgr-code-domain-validation" type="text" />
                </div>
            </div>
        </p>
        <p>
            <label class="ldgr-switch">
                <input type="checkbox" name="ldgr-code-status" class="ldgr-code-status">
                <span class="ldgr-switch-slider round"></span>
            </label>
            <span>
                <?php esc_html_e( 'Status', WDM_LDGR_TXT_DOMAIN ); ?>
            </span>
        </p>
        
        <?php wp_nonce_field('ldgr-update-group-code-'. get_current_user_id(), 'ldgr_edit_nonce'); ?>

        <input type="hidden" name="ldgr-code-groups" value="<?php echo esc_attr( $group_id ); ?>" />

        <input type="hidden" name="ldgr-edit-group-code-id" id="ldgr-edit-group-code-id" value="" />

        <p>
            <input type="submit" value="<?php esc_html_e( 'Update', WDM_LDGR_TXT_DOMAIN ); ?>">
        </p>
    </form>
</div>