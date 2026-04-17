<?php
/**
 * View group codes tab single row template.
 *
 * @since 4.1.0
 */
?>
<tr id="ldgr-group-code-row-<?php echo esc_attr( $group_code['id'] ); ?>">
    <td class="group-code-title">
        <span><?php echo esc_html( $group_code['title'] ); ?></span>
    </td>
    <td class="group-code-summary">
        <span class="group-code-schedule">
            <?php echo esc_attr( $group_code['schedule'] ); ?>
        </span>
        <?php if ( ! empty( $group_code['user_count'] ) ) : ?>
            <span class="group-code-user-count">
                <?php echo sprintf( __( '%d users enrolled', WDM_LDGR_TXT_DOMAIN ), $group_code['user_count'] ); ?>
            </span>
        <?php endif; ?>
    </td>
    <td class="group-code-actions">
        <a
            class="group-code-copy"
            title="<?php esc_html_e( 'Copy', WDM_LDGR_TXT_DOMAIN ); ?>"
            data-id="<?php echo esc_attr( $group_code['id'] ); ?>">
            <span class="dashicons dashicons-clipboard"></span>
        </a>
        <a
            class="group-code-edit"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr-group-code-edit-'.$group_code['id'].'-'.get_current_user_id() ) ); ?>"
            data-id="<?php echo esc_attr( $group_code['id'] ); ?>">
            <span class="dashicons dashicons-edit" title="<?php esc_html_e( 'Edit', WDM_LDGR_TXT_DOMAIN ); ?>"></span>
        </a>
        <a
            class="group-code-delete"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr-group-code-delete-'.$group_code['id'].'-'.get_current_user_id() ) ); ?>"
            data-id="<?php echo esc_attr( $group_code['id'] ); ?>">
            <span class="dashicons dashicons-trash" title="<?php esc_html_e( 'Delete', WDM_LDGR_TXT_DOMAIN ); ?>"></span>
        </a>
    </td>
    <td>
        <div class="group-code-status">
            <label class="ldgr-switch">
                <input
                    type="checkbox"
                    class="ldgr-code-status-toggle"
                    name="group-code-<?php echo esc_attr($group_code['id']); ?>"
                    data-id="<?php echo esc_attr( $group_code['id'] ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr-group-code-'.$group_code['id'].'-'.get_current_user_id() ) ); ?>"
                    <?php checked( $group_code['status'], 'publish' ); ?>
                />
                <span class="ldgr-switch-slider round"></span>
            </label>
            <span class="dashicons dashicons-update"></span>
        </div>
    </td>
</tr>