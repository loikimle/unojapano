<?php
/**
 * Subscriptions Module
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'Ld_Group_Registration_Subscriptions' ) ) {
	/**
	 * Class LD Group Registration Subscriptions
	 */
	class Ld_Group_Registration_Subscriptions {
		/**
		 * Handle variation settings fields for groups
		 *
		 * @param mixed $loop           Loop.
		 * @param array $variation_data Variation data.
		 * @param obj   $variation      Variation object.
		 */
		public function handle_variation_settings_fields( $loop, $variation_data, $variation ) {
			$loop           = $loop;
			$variation_data = $variation_data;
			if ( version_compare( LEARNDASH_WOOCOMMERCE_VERSION, '1.5.0', '<' ) ) {
				$courses_options = array( 0 => __( 'No Related Courses', WDM_LDGR_TXT_DOMAIN ) );
				$courses         = $this->list_courses();
				if ( ( is_array( $courses ) ) && ( ! empty( $courses ) ) ) {
					$courses_options = $courses_options + $courses;
				}
				$values = get_post_meta( $variation->ID, '_related_course', true );
				woocommerce_wp_select(
					array(
						'id'          => '_related_course[' . $variation->ID . '][]',
						'label'       => __( 'Related courses', WDM_LDGR_TXT_DOMAIN ),
						'multiple'    => true,
						'desc_tip'    => true,
						'description' => __( 'You can select multiple courses to sell together holding the SHIFT key when clicking.', WDM_LDGR_TXT_DOMAIN ),
						'value'       => get_post_meta( $variation->ID, '_related_course', true ),
						'options'     => $courses_options,
					)
				);
					echo '<script>wdm_ldRelatedCourses = ' . json_encode( $values ) . '</script>';
					echo '<script>variation_id = ' . $variation->ID . '</script>';
				?>
				<script>
				jQuery(function($){
						$(document.getElementById("_related_course["+ variation_id + "][]"))
				.attr('multiple', true)
				.val(wdm_ldRelatedCourses);
				});
				</script>
				<?php
			}
			$parent_product_id   = $variation->post_parent;
			$parent_product_type = ldgr_get_woo_product_type( $parent_product_id );
			if ( 'variable' == $parent_product_type ) {
				// Add checkbox for the package quantity.
				woocommerce_wp_checkbox(
					array(
						'id'          => 'wdm_gr_package_' . $variation->ID,
						'label'       => __( 'Available as Package', WDM_LDGR_TXT_DOMAIN ),
						'desc_tip'    => true,
						'description' => __( 'Enable this option if you want to provide fix package to your customers for Group Purchase.', WDM_LDGR_TXT_DOMAIN ),
						'value'       => get_post_meta( $variation->ID, 'wdm_gr_package_' . $variation->ID, true ),
						'style'       => 'float:none;',
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'                => 'wdm_gr_package_seat_' . $variation->ID,
						'label'             => __( 'No. of Group Member', WDM_LDGR_TXT_DOMAIN ),
						'placeholder'       => '0',
						'desc_tip'          => 'true',
						'description'       => __( 'Enter the maximum Group Members allowed for the package.', WDM_LDGR_TXT_DOMAIN ),
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '1',
						),
						'value'             => get_post_meta( $variation->ID, 'wdm_gr_package_seat_' . $variation->ID, true ),
						'style'             => 'width:initial;',
					)
				);
				echo '<script>variation_id = ' . $variation->ID . '</script>';
				?>
					<script type="text/javascript">
						function update_wdm_gr_package_seat_field() {
							if (jQuery("#wdm_gr_package_"+variation_id).is(":checked")) {
							jQuery(".wdm_gr_package_seat_"+variation_id+"_field").show();
							} else {
								jQuery(".wdm_gr_package_seat_"+variation_id+"_field").hide();
							}
						}
						update_wdm_gr_package_seat_field();
						jQuery("body").on("change","#wdm_gr_package_"+variation_id,function() {
							jQuery(this).parent().next().toggle();
						});
					</script>
					<?php
			}
		}

		public function list_courses() {
			global $post;
			$postid = $post->ID;
			// Display only Published Courses.
			query_posts(
				array(
					'post_type'      => 'sfwd-courses',
					'posts_per_page' => - 1,
					'post_status'    => 'publish',
				)
			);
			$courses = array();
			while ( have_posts() ) {
				the_post();
				$courses[ get_the_ID() ] = get_the_title();
			}
			wp_reset_query();
			$post = get_post( $postid );

			return $courses;
		}
		/**
		 * Save variation settings fields for groups
		 *
		 * @param int $post_id  ID of the post.
		 */
		public function save_variation_settings_fields( $post_id ) {
			if ( isset( $_POST['_related_course'][ $post_id ] ) && version_compare( LEARNDASH_WOOCOMMERCE_VERSION, '1.5.0', '<' ) ) {
				$related_courses = $_POST['_related_course'][ $post_id ];
				update_post_meta( $post_id, '_related_course', $related_courses );
			}
			if ( isset( $_POST[ 'wdm_gr_package_' . $post_id ] ) ) {
				update_post_meta( $post_id, 'wdm_gr_package_' . $post_id, $_POST[ 'wdm_gr_package_' . $post_id ] );
				if ( isset( $_POST[ 'wdm_gr_package_seat_' . $post_id ] ) && ! empty( $_POST[ 'wdm_gr_package_seat_' . $post_id ] ) ) {
					update_post_meta( $post_id, 'wdm_gr_package_seat_' . $post_id, $_POST[ 'wdm_gr_package_seat_' . $post_id ] );
				} else {
					delete_post_meta( $post_id, 'wdm_gr_package_seat_' . $post_id );
				}
			} else {
				delete_post_meta( $post_id, 'wdm_gr_package_seat_' . $post_id );
				delete_post_meta( $post_id, 'wdm_gr_package_' . $post_id );
			}
		}
	}
}
