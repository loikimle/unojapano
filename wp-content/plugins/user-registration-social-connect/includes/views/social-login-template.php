<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp;

$social_networks = user_registration_social_networks();
$enabled_networks = array();

if ( $form_id ) {
	$social_login_btn =	ur_get_single_post_meta( $form_id, 'user_registration_social_connect_btn', 'false' );

	if( "false" !== $social_login_btn ) {
		if ( is_serialized( $social_login_btn ) ) {
			$social_login_btn = unserialize($social_login_btn);
		} else {
			$social_login_btn = $social_login_btn;
		}
	} else {
		foreach ( $social_networks as $network_key => $network_data ) {
			if( 'yes' === get_option( $network_data['enable_id'] )) {
				$enabled_networks[$network_key] = $network_data;
			}
		}
	}

	foreach ( $social_networks as $network_key => $network_data ) {
		if ( is_array($social_login_btn) ? in_array(ucfirst($network_key),$social_login_btn) : $social_login_btn === ucfirst($network_key) ) {
			$enabled_networks[$network_key] = $network_data;
		  }
		}
} else {
	foreach ( $social_networks as $network_key => $network_data ) {
		if( 'yes' === get_option( $network_data['enable_id'] )) {
			$enabled_networks[$network_key] = $network_data;
		}
	}
}

$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

$url = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$formatted_url = substr( $url, 0, strpos( $url, '?' ) );

$encoded_url = '';
?>
<div class="user-registration-social-connect-networks">

	<?php

	if ( count( $enabled_networks ) > 0 ) {
		?>
		<ul class="ursc-network-lists <?php echo get_option( 'user_registration_social_login_template', 'ursc_theme_4' ); ?>">
			<?php
			foreach ( $enabled_networks as $network_key => $network_data ) {
					?>
					<li class="ursc-login-media ursc-login-media--<?php echo $network_key; ?>">
						<a href="<?php echo $formatted_url; ?>?user_registration_social_login=<?php echo $network_key; ?>&ursc_action=login
											<?php
											if ( $encoded_url ) {
												echo '&state=' . base64_encode( "redirect_to=$encoded_url" );
											}
											?>
						" title='
						<?php
						_e( 'Login with', 'user-registration-social-connect' );
						echo ' ' . $network_key;
						?>
						'>
							<span class="ursc-icon-block icon-<?php echo $network_key; ?> ursc-login-with-<?php echo $network_key; ?>"></span>

							<?php if ( 'ursc_theme_4' === get_option( 'user_registration_social_login_template' ) ) { ?>
								<span class="ursc-login-text"><?php echo esc_html( get_option( $network_data['login_text'] ) ); ?></span>
							<?php } ?>
						</a>
					</li>
					<?php
				}
			?>
		</ul>

		<?php
	}
	?>
</div>
