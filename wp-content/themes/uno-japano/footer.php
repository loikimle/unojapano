<?php
/**
 * The template for displaying the footer
 *
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly


do_action( 'TieLabs/after_main_content' );


TIELABS_HELPER::get_template_part( 'templates/footer' );

?>

		</div><!-- #tie-wrapper /-->

		<?php get_sidebar( 'slide' ); ?>

	</div><!-- #tie-container /-->
</div><!-- .background-overlay /-->

<?php wp_footer(); ?>
<script src="//code.tidio.co/37z13ssxw1ul5rylwl2oyz0e8rluc0x6.js" async></script>
</body>
</html>
