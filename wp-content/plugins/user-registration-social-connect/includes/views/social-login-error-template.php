<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!empty($ursc_response_global['message'])){
?>
<div class="user-registration-social-connect-networks-error">
	<p>
		<?php
		echo esc_html( $ursc_response_global['message'] );
		?>
	</p>
</div>
<?php } ?>
