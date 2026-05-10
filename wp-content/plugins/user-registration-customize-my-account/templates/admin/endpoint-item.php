<?php
/**
 * MY ACCOUNT ENDPOINT FIELDS
 *
 * @package User Registration Customize My Account
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$editor_args = array(
	'wpautop'       => false, // use wpautop ?
	'media_buttons' => true, // show insert/upload button(s).
	'textarea_rows' => 10, // rows="..." .
	'tabindex'      => '',
	'editor_css'    => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
	'editor_class'  => '', // add extra class(es) to the editor textarea.
	'teeny'         => false, // output the minimal editor config used in Press This.
	'dfw'           => false, // replace the default fullscreen with DFW (needs specific DOM elements and css).
	'quicktags'     => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array().
);

$options['modify_default_content'] = isset( $options['modify_default_content'] ) ? ur_string_to_bool( $options['modify_default_content'] ) : false;
?>

<div class="urcma-endpoint-content" id="<?php echo $endpoint; ?>">
	<div class="urcma-endpoint-header">
		<h3><?php echo $options['label']; ?></h3>
		<div class="urcma-endpoint-header-option">
		<?php
		if ( '1' == $options['active'] ) {
			$label = esc_html__( 'Enabled', 'user-registration-customize-my-account' );
			$class = 'enabled';
		} else {
			$label = esc_html__( 'Disabled', 'user-registration-customize-my-account' );
			$class = '';
		}
		?>
			<div class="user-registration-switch">
				<input id="<?php echo $id . '_' . $endpoint; ?>" type="checkbox" class="user-registration-switch__control hide-show-check <?php echo $class; ?>" name="<?php echo $id . '_' . $endpoint; ?>[active]" id="<?php echo $id . '_' . $endpoint; ?>_active" value="<?php echo $endpoint; ?>" <?php checked( $options['active'] ); ?>>
				<label for="<?php echo $id . '_' . $endpoint; ?>"><?php echo $label; ?></label>
			</div>

			<?php if ( ! urcma_is_default_item( $endpoint ) && ! urcma_is_plugin_item( $endpoint ) ) : ?>
					<button type="button" class="button button-secondary button-medium button-danger remove-trigger" data-endpoint="<?php echo $endpoint; ?>"><?php _e( 'Remove', 'user-registration-customize-my-account' ); ?></button>
			<?php endif; ?>
		</div>
	</div>
	<div class="urcma-endpoint-options" style="display: none;">
		<table class="options-table form-table">
			<tbody>
			<?php
			if ( 'dashboard' !== $endpoint ) {
				$disabled = '';
			} else {
				$disabled = 'disabled';
			}
			?>

				<tr>
					<th>
						<label class="ur-label" for="<?php echo $id . '_' . $endpoint; ?>_slug"><?php echo __( 'Endpoint slug', 'user-registration-customize-my-account' ); ?></label>
						<span class="user-registration-help-tip" data-tip="<?php esc_attr_e( 'Text appended to your page URLs to manage new contents in account pages. It must be unique for every page.', 'user-registration-customize-my-account' ); ?>"></span>
					</th>
					<td>
						<input class="regular-text urcma_slug_input" type="text" name="<?php echo $id . '_' . $endpoint; ?>[slug]" id="<?php echo $id . '_' . $endpoint; ?>_slug" value="<?php echo $options['slug']; ?> " <?php echo $disabled; ?>>
					</td>
				</tr>

			<tr>
				<th>
					<label class="ur-label" for="<?php echo $id . '_' . $endpoint; ?>_label"><?php echo __( 'Endpoint label', 'user-registration-customize-my-account' ); ?></label>
						<span class="user-registration-help-tip" data-tip="
						<?php
						esc_attr_e(
							'Menu item for this endpoint in "My Account".',
							'user-registration-customize-my-account'
						)
						?>
							"></span>
				</th>
				<td>
					<input class="regular-text urcma_label_input" type="text" name="<?php echo $id . '_' . $endpoint; ?>[label]" id="<?php echo $id . '_' . $endpoint; ?>_label" value="<?php echo $options['label']; ?>">
				</td>
			</tr>

			<tr>
				<th>
					<label class="ur-label" for="<?php echo $id . '_' . $endpoint; ?>_icon"><?php echo __( 'Endpoint icon', 'user-registration-customize-my-account' ); ?></label>
					<span class="user-registration-help-tip" data-tip="<?php esc_attr_e( 'Endpoint icon for "My Account" menu option', 'user-registration-customize-my-account' ); ?>"></span>
				</th>
				<td>
					<select name="<?php echo $id . '_' . $endpoint; ?>[icon]" id="<?php echo $id . '_' . $endpoint; ?>_icon" class="icon-select">
						<option value=""><?php _e( 'No icon', 'user-registration-customize-my-account' ); ?></option>
						<?php foreach ( $icon_list as $icon => $label ) : ?>
							<option value="<?php echo $label; ?>" <?php selected( $options['icon'], $label ); ?>><?php echo $label; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>

			<tr>
				<th>
					<label class="ur-label" for="<?php echo $id . '_' . $endpoint; ?>_usr_roles">
						<?php
						echo __(
							'Restrict to user roles',
							'user-registration-customize-my-account'
						);
						?>
					</label>
					<span class="user-registration-help-tip" data-tip="
						<?php
						esc_attr_e(
							'Restrict endpoint visibility to the following user role(s).',
							'user-registration-customize-my-account'
						)
						?>
							" ></span>
				</th>
				<td>
					<select name="<?php echo $id . '_' . $endpoint; ?>[usr_roles][]" id="<?php echo $id . '_' . $endpoint; ?>_usr_roles" multiple="multiple">
						<?php
						foreach ( $usr_roles as $role => $role_name ) :
							! isset( $options['usr_roles'] ) && $options['usr_roles'] = array();
							?>
							<option value="<?php echo $role; ?>" <?php selected( in_array( $role, (array) $options['usr_roles'] ), true ); ?>><?php echo $role_name; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>

			<?php if ( 'dashboard' === $endpoint ) { ?>
			<tr>
				<th>
					<label class="ur-label" for="<?php echo $id . '_' . $endpoint; ?>_usr_roles">
						<?php
						echo __(
							'Modify default content',
							'user-registration-customize-my-account'
						);
						?>
					</label>
					<span class="user-registration-help-tip" data-tip="
						<?php
						esc_attr_e(
							'Modify default content of the ednpoint.',
							'user-registration-customize-my-account'
						)
						?>
							" ></span>
				</th>
				<td>
				<div class="user-registration-switch">
					<input name="<?php echo $id . '_' . $endpoint; ?>[modify_default_content]" id="<?php echo $id . '_' . $endpoint; ?>_modify_default_content" type="checkbox" class="user-registration-switch__control urcma-modify-default-content" <?php echo esc_attr( checked( true, ur_string_to_bool( isset( $options['modify_default_content'] ) ? $options['modify_default_content'] : false ), false ) ); ?> >
				</div>
				</td>
			</tr>
				<?php
				$editor_args['textarea_name'] = $id . '_' . $endpoint . '[modified_default_content]';
				$modified_content             = '';
				if ( ! isset( $options['modify_default_content'] ) || ( isset( $options['modify_default_content'] ) && ! $options['modify_default_content'] ) ) {
					$modified_content = 'display:none;';
				}
				?>

			<tr style="<?php echo $modified_content; ?>">
				<th>
					<label class="ur-label"><?php echo __( 'Endpoint content', 'user-registration-customize-my-account' ); ?></label>
					<span class="user-registration-help-tip" data-tip="
						<?php
						esc_attr_e(
							'Default endpoint content. Modify this to modify default content.',
							'user-registration-customize-my-account'
						)
						?>
							" ></span>
				</th>
				<td>
					<?php
					if ( ! isset( $options['modified_default_content'] ) ) {
						ob_start();
						include UR_ABSPATH . 'templates/myaccount/dashboard.php';
						$options['modified_default_content'] = ob_get_clean();
					}
					?>
					<div class="editor urcma-modified-content"><?php wp_editor( stripslashes( $options['modified_default_content'] ), $id . '_' . $endpoint . '_modified_default_content', $editor_args ); ?></div>
				</td>
			</tr>
			<?php } ?>

			<?php
				$modify_default_enable = '';
			if ( 'dashboard' === $endpoint && isset( $options['modify_default_content'] ) && $options['modify_default_content'] ) {
				$modify_default_enable = 'display: none;';
			}
			?>

			<!-- Check to see if the endpoint is default or other plugin endpoint and place the override content settings-->
			<?php if ( urcma_is_default_item( $endpoint ) || urcma_is_plugin_item( $endpoint ) ) : ?>
				<?php
				$custom_content_postion = 'bottom';
				if ( isset( $options['custom_content_postion'] ) ) {
					$custom_content_postion = $options['custom_content_postion'];
				} elseif ( isset( $options['override_content'] ) && 'on' === $options['override_content'] ) {
					$custom_content_postion = 'override';
				}
				?>
			<tr style="<?php echo $modify_default_enable; ?>">
				<th>
					<label class="ur-label" for="<?php echo $id . '_' . $endpoint; ?>_custom_content_postion"><?php echo __( 'Custom content placement', 'user-registration-customize-my-account' ); ?></label>
					<span class="user-registration-help-tip" data-tip="<?php esc_attr_e( 'Custom endpoint content display position', 'user-registration-customize-my-account' ); ?>"></span>
				</th>
				<td>
					<select name="<?php echo $id . '_' . $endpoint; ?>[custom_content_postion]" id="<?php echo $id . '_' . $endpoint; ?>_custom_content_postion" class="urcma-custom-content-placement">
						<option value="override" <?php selected( 'override' === $custom_content_postion, true ); ?>><?php esc_html_e( 'Override default content', 'user-registration-customize-my-account' ); ?></option>
						<option value="top" <?php selected( 'top' === $custom_content_postion, true ); ?>><?php esc_html_e( 'Before default content', 'user-registration-customize-my-account' ); ?></option>
						<option value="bottom" <?php selected( 'bottom' === $custom_content_postion, true ); ?>><?php esc_html_e( 'After default content', 'user-registration-customize-my-account' ); ?></option>
					</select>
				</td>
			</tr>
			<?php endif; ?>

			<tr style="<?php echo $modify_default_enable; ?>">
				<th>
					<label class="ur-label"><?php echo __( 'Endpoint custom content', 'user-registration-customize-my-account' ); ?></label>
					<span class="user-registration-help-tip" data-tip="
						<?php
						esc_attr_e(
							'Custom endpoint content. Leave it blank to use default content.',
							'user-registration-customize-my-account'
						)
						?>
							" ></span>
				</th>
				<td>
					<?php
						$editor_args['textarea_name'] = $id . '_' . $endpoint . '[content]';
					?>
					<div class="editor urcma-custom-content"><?php wp_editor( stripslashes( $options['content'] ), $id . '_' . $endpoint . '_content', $editor_args ); ?></div>
				</td>
			</tr>

			</tbody>
		</table>
	</div>

</div>
