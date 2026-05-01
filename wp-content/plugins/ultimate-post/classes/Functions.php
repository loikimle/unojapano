<?php
/**
 * Common Functions.
 *
 * @package ULTP\Functions
 * @since v.1.0.0
 */

namespace ULTP;

use ULTP\Includes\Durbin\Xpo;

defined( 'ABSPATH' ) || exit;

/**
 * Functions class.
 *
 * Provides utility functions for Ultimate Post plugin.
 *
 * @package ULTP\Functions
 * @since 4.0.0
 */
class Functions {

	/**
	 * Funtion
	 *
	 * @since v.3.2.4
	 *
	 * Instance of the class.
	 *
	 * @var Functions | null
	 */
	private static $instance = null;

	/**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Gets the instance of \ULTP\Functions class
	 *
	 * @return Functions
	 * @since v.3.2.4
	 */
	public static function get_instance() {
		if ( ! isset( $GLOBALS['ultp_settings'] ) ) {
			$GLOBALS['ultp_settings'] = get_option( 'ultp_options' );
		}

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * ID for the Builder Post or Normal Post
	 *
	 * @since v.2.3.1
	 * @return NUMBER | is Builder or not
	 */
	public function get_ID() {
		$id = $this->is_builder();
		return $id ? $id : ( function_exists( 'is_shop' ) ? ( is_shop() ? wc_get_page_id( 'shop' ) : get_the_ID() ) : get_the_ID() );
	}

	/**
	 * Checking Statement of Dynamic Site Builder
	 *
	 * @since v.2.3.1
	 * @return BOOLEAN | is Builder or not
	 */
	public function is_archive_builder() {
		$id = get_the_ID();
		return get_post_type( $id ) == 'ultp_builder' ? get_post_meta( $id, '__ultp_builder_type', true ) : false;
	}

	/**
	 * Set Link with the Parameters
	 *
	 * @since v.1.1.0
	 * @param string $url .
	 * @param string $tag .
	 * @return STRING | URL with Arg
	 */
	public function get_premium_link( $url = null, $tag = '' ) {
		return Xpo::generate_utm_link(
			array(
				'url'    => $url,
				'utmKey' => $tag,
			)
		);
	}


	/**
	 * Quick Query
	 *
	 * @since v.1.1.0
	 * @param ARRAY $prams Quick query parameters.
	 * @param ARRAY $args Query arguments to modify.
	 * @return ARRAY
	 */
	public function get_quick_query( $prams, $args ) {
		switch ( $prams['queryQuick'] ) {
			case 'related_posts':
				global $post;
				$p_id = isset( $post->ID ) && $post->ID ? $post->ID : ( isset( $_POST['postId'] ) ? sanitize_text_field( $_POST['postId'] ) : '' ); //phpcs:disable WordPress.Security.NonceVerification.Missing
				if ( $p_id ) {
					$args['post__not_in'] = array( $p_id );
				}
				break;
			case 'related_tag':
				global $post;
				$p_id = isset( $post->ID ) && $post->ID ? $post->ID : ( isset( $prams['current_post'] ) && $prams['current_post'] ? $prams['current_post'] : ( isset( $_POST['postId'] ) ? sanitize_text_field( $_POST['postId'] ) : '' ) ); //phpcs:disable ordPress.Security.NonceVerification.Missing
				if ( $p_id ) {
					$args['tax_query']    = array(
						array(
							'taxonomy' => 'post_tag',
							'terms'    => $this->get_terms_id( $p_id, 'post_tag' ),
							'field'    => 'term_id',
						),
					);
					$args['post__not_in'] = array( $p_id );
				}
				break;
			case 'related_category':
				global $post;
				$p_id = isset( $post->ID ) && $post->ID ? $post->ID : ( isset( $prams['current_post'] ) && $prams['current_post'] ? $prams['current_post'] : ( isset( $_POST['postId'] ) ? sanitize_text_field( $_POST['postId'] ) : '' ) ); //phpcs:disable ordPress.Security.NonceVerification.Missing
				if ( $p_id ) {
					$args['tax_query']    = array(
						array(
							'taxonomy' => 'category',
							'terms'    => $this->get_terms_id( $p_id, 'category' ),
							'field'    => 'term_id',
						),
					);
					$args['post__not_in'] = array( $p_id );
				}
				break;
			case 'related_cat_tag':
				global $post;
				$p_id = isset( $post->ID ) && $post->ID ? $post->ID : ( isset( $prams['current_post'] ) && $prams['current_post'] ? $prams['current_post'] : ( isset( $_POST['postId'] ) ? sanitize_text_field( $_POST['postId'] ) : '' ) ); //phpcs:disable ordPress.Security.NonceVerification.Missing
				if ( $p_id ) {
					$args['tax_query']    = array(
						array(
							'taxonomy' => 'post_tag',
							'terms'    => $this->get_terms_id( $p_id, 'post_tag' ),
							'field'    => 'term_id',
						),
						array(
							'taxonomy' => 'category',
							'terms'    => $this->get_terms_id( $p_id, 'category' ),
							'field'    => 'term_id',
						),
					);
					$args['post__not_in'] = array( $p_id );
				}
				break;
			case 'sticky_posts':
				$sticky = get_option( 'sticky_posts' );
				if ( is_array( $sticky ) ) {
					rsort( $sticky );
					// $sticky = array_slice($sticky, 0, $args['posts_per_page']);
				}
				$args['ignore_sticky_posts'] = 1;
				$args['post__in']            = $sticky;
				break;
			case 'latest_post_published':
				$args['orderby']             = 'date';
				$args['order']               = 'DESC';
				$args['ignore_sticky_posts'] = 1;
				break;
			case 'latest_post_modified':
				$args['orderby']             = 'modified';
				$args['order']               = 'DESC';
				$args['ignore_sticky_posts'] = 1;
				break;
			case 'oldest_post_published':
				$args['orderby'] = 'date';
				$args['order']   = 'ASC';
				break;
			case 'oldest_post_modified':
				$args['orderby'] = 'modified';
				$args['order']   = 'ASC';
				break;
			case 'alphabet_asc':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'alphabet_desc':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
				break;
			case 'random_post':
				$args['orderby'] = 'rand';
				$args['order']   = 'ASC';
				break;
			case 'random_post_7_days':
				$args['orderby']    = 'rand';
				$args['order']      = 'ASC';
				$args['date_query'] = array( array( 'after' => '1 week ago' ) );
				break;
			case 'random_post_30_days':
				$args['orderby']    = 'rand';
				$args['order']      = 'ASC';
				$args['date_query'] = array( array( 'after' => '1 month ago' ) );
				break;
			case 'most_comment':
				$args['orderby'] = 'comment_count';
				$args['order']   = 'DESC';
				break;
			case 'most_comment_1_day':
				$args['orderby']    = 'comment_count';
				$args['order']      = 'DESC';
				$args['date_query'] = array( array( 'after' => '1 day ago' ) );
				break;
			case 'most_comment_7_days':
				$args['orderby']    = 'comment_count';
				$args['order']      = 'DESC';
				$args['date_query'] = array( array( 'after' => '1 week ago' ) );
				break;
			case 'most_comment_30_days':
				$args['orderby']    = 'comment_count';
				$args['order']      = 'DESC';
				$args['date_query'] = array( array( 'after' => '1 month ago' ) );
				break;
			case 'popular_post_1_day_view':
				$args['meta_key']   = '__post_views_count';
				$args['orderby']    = 'meta_value_num';
				$args['order']      = 'DESC';
				$args['date_query'] = array( array( 'after' => '1 day ago' ) );
				break;
			case 'popular_post_7_days_view':
				$args['meta_key']   = '__post_views_count';
				$args['orderby']    = 'meta_value_num';
				$args['order']      = 'DESC';
				$args['date_query'] = array( array( 'after' => '1 week ago' ) );
				break;
			case 'popular_post_30_days_view':
				$args['meta_key']   = '__post_views_count';
				$args['orderby']    = 'meta_value_num';
				$args['order']      = 'DESC';
				$args['date_query'] = array( array( 'after' => '1 month ago' ) );
				break;
			case 'popular_post_all_times_view':
				$args['meta_key'] = '__post_views_count';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			default:
				// code...
				break;
		}
		return $args;
	}

	/**
	 * Get All Term IDs for a Post in a Taxonomy
	 *
	 * @since v.2.4.12
	 * @param INT|STRING $id Post ID to get terms for.
	 * @param STRING     $type Taxonomy name (e.g. 'category', 'post_tag').
	 * @return ARRAY
	 */
	public function get_terms_id( $id, $type ) {
		$data = array();
		$arr  = get_the_terms( $id, $type );
		if ( is_array( $arr ) ) {
			foreach ( $arr as $key => $val ) {
				$data[] = $val->term_id;
			}
		}
		return $data;
	}

	/**
	 * Get all reusable block IDs referenced in a post's content
	 *
	 * @since v.1.1.0
	 * @param INT|STRING $post_id .
	 * @return ARRAY
	 */
	public function get_reusable_ids( $post_id ) {
		$reusable_id = array();
		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( isset( $post->post_content ) ) {
				if (
					has_blocks( $post->post_content ) &&
					strpos( $post->post_content, 'wp:block' ) &&
					strpos( $post->post_content, '"ref"' ) !== false
				) {
					$blocks = parse_blocks( $post->post_content );
					foreach ( $blocks as $key => $value ) {
						if ( isset( $value['attrs']['ref'] ) ) {
							$reusable_id[] = $value['attrs']['ref'];
						}
					}
				}
			}
		}
		return $reusable_id;
	}

	/**
	 * Safely reads file contents using WordPress Filesystem API
	 *
	 * @since v.4.1.8
	 * @param STRING $path .
	 * @return STRING|ARRAY.
	 */
	public function get_path_file_contents( $path ) {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			$init_fs = WP_Filesystem();
			if ( ! $init_fs ) {
				return '';
			}
		}

		if ( $wp_filesystem->exists( $path ) ) {
			return $wp_filesystem->get_contents( $path );
		} else {
			return '';
		}
	}

	/**
	 * Builds and returns inline CSS for a post and its reusable blocks
	 *
	 * @since v.4.1.8
	 * @param INT|STRING $post_id .
	 * @param BOOLEAN    $call_common .
	 * @return STRING|ARRAY.
	 */
	public function build_css_for_inline_print( $post_id, $call_common = true ) {
		if ( $post_id ) {
			$upload_dir_url     = wp_get_upload_dir();
			$upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );

			$css_dir_url = trailingslashit( $upload_dir_url['baseurl'] );
			if ( is_ssl() ) {
				$css_dir_url = str_replace( 'http://', 'https://', $css_dir_url );
			}
			$reusable_css = '';
			$reusable_id  = $this->get_reusable_ids( $post_id );
			if ( ! empty( $reusable_id ) ) {
				foreach ( $reusable_id as $id ) {
					$reusable_dir_path = $upload_css_dir_url . "ultimate-post/ultp-css-{$id}.css";
					if ( file_exists( $reusable_dir_path ) ) {
						$reusable_css .= $this->get_path_file_contents( $reusable_dir_path );
					} else {
						$reusable_css .= get_post_meta( $reusable_id, '_ultp_css', true );
					}
				}
			}

			$css_dir_path = $upload_css_dir_url . "ultimate-post/ultp-css-{$post_id}.css";
			$css          = '';
			if ( file_exists( $css_dir_path ) ) {
				$css = $this->get_path_file_contents( $css_dir_path );
			} else {
				$css = get_post_meta( $post_id, '_ultp_css', true );
			}
			if ( $reusable_css . $css ) {
				if ( $call_common ) {
					$this->register_scripts_common();
				}
				return '<style id="ultp-post-' . $post_id . '" type="text/css">' . wp_strip_all_tags( $reusable_css . $css ) . '</style>';
			}
		}
		return '';
	}


	/**
	 * Get Global Plugin Settings
	 *
	 * @since v.1.0.0
	 * @param STRING $key of the Option .
	 * @return ARRAY|STRING .
	 */
	public function get_setting( $key = '' ) {
		$data = $GLOBALS['ultp_settings'];
		if ( $key != '' ) {
			return isset( $data[ $key ] ) ? $data[ $key ] : '';
		} else {
			return $data;
		}
	}


	/**
	 * Set Option Settings
	 *
	 * @since v.1.0.0
	 * @param STRING | $key of the Option (STRING) .
	 * @param STRING | $val Value (STRING) .
	 */
	public function set_setting( $key = '', $val = '' ) {
		if ( $key != '' ) {
			$data         = $GLOBALS['ultp_settings'];
			$data[ $key ] = $val;
			update_option( 'ultp_options', $data );
			$GLOBALS['ultp_settings'] = $data;
			do_action( 'ultp_settings_updated', $key, $val );
		}
	}


	/**
	 * Get Image HTML
	 *
	 * @since v.1.0.0
	 * @param STRING | $url .
	 * @param STRING | $size .
	 * @param STRING | $class .
	 * @param STRING | $alt .
	 * @param STRING | $lazy .
	 * @param ARRAY |  $darkImg .
	 * @param STRING | $srcset .
	 * @return STRING
	 */
	public function get_image_html( $url = '', $size = 'full', $class = '', $alt = '', $lazy = '', $darkImg = array(), $srcset = '' ) {
		$class        = sanitize_html_class( $class );
		$alt          = preg_replace( '/[^A-Za-z0-9_ -]/', '', $alt );
		$hasDarkImage = ( $darkImg['enable'] && $darkImg['url'] ) ? ' ultp-light-image-block ' : '';
		$alt          = $alt ? ' alt="' . $alt . '" ' : '';
		$lazy_data    = $lazy ? ' loading="lazy"' : '';

		$dlMode = isset( $_COOKIE['ultplocalDLMode'] ) ? $_COOKIE['ultplocalDLMode'] : ultimate_post()->get_dl_mode();

		$lightMode = $hasDarkImage ? ( $dlMode == 'ultplight' ? '' : 'inactive ' ) : '';
		$darkMode  = $hasDarkImage ? ( $hasDarkImage && $dlMode == 'ultpdark' ? '' : ' inactive ' ) : '';

		$image = '<img ' . $lazy_data . $srcset . ' class="' . $class . $hasDarkImage . $lightMode . '" ' . $alt . ' src="' . esc_url( $url ) . '" />';
		if ( $hasDarkImage ) {
			$image .= '<img ' . $lazy_data . $darkImg['srcset'] . ' class="' . $class . $darkMode . ' ultp-dark-image-block " ' . $alt . ' src="' . esc_url( $darkImg['url'] ) . '" />';
		}
		return $image;
	}


	/**
	 * Get Image HTML
	 *
	 * @since v.1.0.0
	 * @param STRING | $attach_id .
	 * @param STRING | $size .
	 * @param STRING | $class .
	 * @param STRING | $alt .
	 * @param STRING | $srcset .
	 * @param STRING | $lazy .
	 * @return STRING
	 */
	public function get_image( $attach_id, $size = 'full', $class = '', $alt = '', $srcset = '', $lazy = '' ) {
		$img_alt     = get_post_meta( $attach_id, '_wp_attachment_image_alt', true );
		$alt         = $img_alt ? $img_alt : $alt;
		$alt         = $alt ? ' alt="' . esc_html( $alt ) . '" ' : '';
		$class       = $class ? ' class="' . $class . '" ' : '';
		$size        = ( ultimate_post()->get_setting( 'disable_image_size' ) == 'yes' && strpos( $size, 'ultp_layout_' ) !== false ) ? 'full' : $size;
		$lazy_data   = $lazy ? ' loading="lazy"' : '';
		$srcset_data = $srcset ? ' srcset="' . esc_attr( wp_get_attachment_image_srcset( $attach_id ) ) . '"' : '';
		return '<img ' . $srcset_data . $lazy_data . $class . $alt . ' src="' . wp_get_attachment_image_url( $attach_id, $size ) . '" />';
	}


	/**
	 * Get Excerpt Text
	 *
	 * @since v.1.0.0
	 * @param STRING | $post_id .
	 * @param NUMBER | $limit .
	 * @return STRING
	 */
	public function excerpt( $post_id, $limit = 55 ) {
		$content = preg_replace( '/(\[postx_template[\s\w="]*)]/m', '', get_the_excerpt( $post_id ) ); // Remove postx_template shortcode form Content
		return apply_filters( 'the_excerpt', wp_trim_words( $content, $limit ) );
	}

	/**
	 * Builder Attributes
	 *
	 * @since v.1.0.0
	 * @param STRING | $type .
	 * @return STRING
	 */
	public function get_builder_attr( $type ) {
		$builder_data = '';
		if ( $type == 'archiveBuilder' || $type == 'archiveBuilderFilter' ) {
			if ( is_archive() ) {
				if ( is_date() ) {
					if ( is_year() ) {
						$builder_data = 'date###' . get_the_date( 'Y' );
					} elseif ( is_month() ) {
						$builder_data = 'date###' . get_the_date( 'Y-n' );
					} elseif ( is_day() ) {
						$builder_data = 'date###' . get_the_date( 'Y-n-j' );
					}
				} elseif ( is_author() ) {
					$builder_data = 'author###' . get_the_author_meta( 'ID' );
				} else {
					$obj = get_queried_object();
					if ( isset( $obj->taxonomy ) ) {
						$builder_data = 'taxonomy###' . $obj->taxonomy . '###' . $obj->slug;
					}
				}
			} elseif ( is_search() ) {
				$builder_data = 'search###' . get_search_query( true );
			}
		}
		if ( $type == 'archiveBuilderFilter' ) {
			return $builder_data ? $builder_data : '';
		}
		return $builder_data ? 'data-builder="' . $builder_data . '"' : '';
	}


	/**
	 * Determines if the builder is enabled based on the plugin settings.
	 *
	 * @param STRING $builder Optional. The builder name (currently unused).
	 * @return BOOL True if the builder is enabled, false otherwise.
	 */
	public function is_builder( $builder = '' ) {
		$id = '';
		if ( ultimate_post()->get_setting( 'ultp_builder' ) != 'false' ) {
			$page_id = ultimate_post()->builder_check_conditions( 'return_ib' );
			if ( $page_id ) {
				$id = $page_id;
			}
		}
		return $id;
	}


	/**
	 * Get Post Number Depending On Device
	 *
	 * @since v.2.5.4
	 * @param MIXED | $preDef .
	 * @param MIXED | $prev .
	 * @param MIXED | $current .
	 * @return STRING
	 */
	public function get_post_number( $preDef, $prev, $current ) {

		$current = is_object( $current ) ? json_decode( wp_json_encode( $current ), true ) : $current;
		if ( array(
			'lg' => $preDef,
			'sm' => $preDef,
			'xs' => $preDef,
		) == $current ) {
			if ( $preDef != $prev ) {
				return $prev;
			}
		}

		$lg = isset( $current['lg'] ) ? $current['lg'] : $prev;
		$sm = isset( $current['sm'] ) ? $current['sm'] : $lg;
		$xs = isset( $current['xs'] ) ? $current['xs'] : $lg;
		if ( $lg == $sm && $sm == $xs ) {
			return $lg;
		} else {
			global $ultpDevide;
			$currentDevice = ! empty( $ultpDevide ) ? $ultpDevide : $this->isDevice();
			if ( $currentDevice == 'mobile' ) {
				return $xs;
			} elseif ( $currentDevice == 'tablet' ) {
				return $sm;
			} else {
				return $lg;
			}
		}
	}


	/**
	 * Get Post Number Depending On Device
	 *
	 * @since v.2.5.4
	 * @return STRING | Device Type
	 */
	public function isDevice() {
		$useragent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_key( $_SERVER['HTTP_USER_AGENT'] ) : '';
		$device    = 'desktop';
		if ( $useragent ) {
			if ( preg_match( '/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower( $useragent ) ) ) {
				$device = 'tablet';
			} elseif ( preg_match( '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent ) || preg_match( '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr( $useragent, 0, 4 ) ) ) {
				$device = 'mobile';
			}
		}
		global $ultpDevide;
		$ultpDevide = $device;
		return $device;
	}


	/**
	 * Get Raw Value from Objects
	 *
	 * @since v.2.5.3
	 * @param ARRAY | $attr .
	 * @return STRING | Device Type
	 */
	public function get_value( $attr ) {
		$data = array();
		if ( is_array( $attr ) ) {
			foreach ( $attr as $val ) {
				$data[] = $val->value;
			}
		}
		return $data;
	}

	/**
	 * QueryArgs for Filter
	 *
	 * @since v.2.8.9
	 * @param ARRAY | $attr .
	 * @return ARRAY
	 */
	public function getFilterQueryArgs( $attr ) {
		$tax_value = ( strlen( $attr['queryTaxValue'] ) > 2 ) ? $attr['queryTaxValue'] : array();
		$tax_value = is_array( $tax_value ) ? $tax_value : json_decode( $tax_value );

		$tax_value = ( isset( $tax_value[0] ) && is_object( $tax_value[0] ) ) ? $this->get_value( $tax_value ) : $tax_value;

		if ( is_array( $tax_value ) && count( $tax_value ) > 0 ) {
			$relation = isset( $attr['queryRelation'] ) ? $attr['queryRelation'] : 'OR';

			// Adv Filter Query Relation Override
			if ( isset( $attr['advFilterEnable'] ) && $attr['advFilterEnable'] === true && isset( $attr['advRelation'] ) ) {
				$relation = 'AND' === $attr['advRelation'] || 'OR' === $attr['advRelation'] ? $attr['advRelation'] : $relation;
			}

			$var = array( 'relation' => $relation );
			foreach ( $tax_value as $val ) {
				$tax_name = $attr['queryTax'];
				// For Custom Terms
				if ( $attr['queryTax'] == 'multiTaxonomy' ) {
					$temp = explode( '###', $val );
					if ( isset( $temp[1] ) ) {

						if ( $temp[1] === '_all' ) {
							continue;
						}

						$val      = $temp[1];
						$tax_name = $temp[0];
					}
				}
				$var[] = array(
					'taxonomy' => $tax_name,
					'field'    => 'slug',
					'terms'    => $val,
				);
			}
		}
		return isset( $var ) ? $var : array();
	}

	/**
	 * Query Builder
	 *
	 * @since v.1.0.0
	 * @param ARRAY | $attr .
	 * @return ARRAY
	 */
	public function get_query( $attr ) {
		$builder       = isset( $attr['builder'] ) ? $attr['builder'] : '';
		$post_type     = isset( $attr['queryType'] ) ? $attr['queryType'] : 'post';
		$get_post_type = isset( $attr['queryPostType'] ) ? $attr['queryPostType'] : '';
		if ( $post_type != 'archiveBuilder' && ! empty( $get_post_type ) && $post_type == 'customPostType' ) {
			$post_type = $get_post_type;
		}

		if ( $post_type == 'archiveBuilder' && ( $builder || $this->is_builder( $builder ) ) ) {
			$archive_query = array();
			if ( $builder ) {
				$str = explode( '###', $builder );
				if ( isset( $str[0] ) ) {
					if ( $str[0] == 'taxonomy' ) {
						if ( isset( $str[1] ) && isset( $str[2] ) ) {
							$archive_query['tax_query'] = array(
								array(
									'taxonomy' => $str[1],
									'field'    => 'slug',
									'terms'    => $str[2],
								),
							);
						}
					} elseif ( $str[0] == 'author' ) {
						if ( isset( $str[1] ) ) {
							$archive_query['author'] = $str[1];
						}
					} elseif ( $str[0] == 'search' ) {
						if ( isset( $str[1] ) ) {
							$archive_query['s'] = $str[1];
						}
					} elseif ( $str[0] == 'date' ) {
						if ( isset( $str[1] ) ) {
							$all_date = explode( '-', $str[1] );
							if ( ! empty( $all_date ) ) {
								$arg = array();
								if ( isset( $all_date[0] ) ) {
									$arg['year'] = $all_date[0];
								}
								if ( isset( $all_date[1] ) ) {
									$arg['month'] = $all_date[1];
								}
								if ( isset( $all_date[2] ) ) {
									$arg['day'] = $all_date[2];
								}
								$archive_query['date_query'][] = $arg;
							}
						}
					}
				}
			} else {
				global $wp_query;
				$archive_query = $wp_query->query_vars;
			}
			$archive_query['posts_per_page'] = isset( $attr['queryNumber'] ) ? $attr['queryNumber'] : 1;
			$archive_query['paged']          = isset( $attr['paged'] ) ? $attr['paged'] : 1;

			$archive_query['paged'] = ! wp_doing_ajax() && isset( $_GET[ $attr['blockId'] . '_page' ] ) && is_numeric( $_GET[ $attr['blockId'] . '_page' ] ) ?
				intval( $_GET[ $attr['blockId'] . '_page' ] ) :
				$archive_query['paged'];

			if ( isset( $attr['queryOffset'] ) && $attr['queryOffset'] ) {
				$offset        = $this->get_offset( $attr['queryOffset'], $archive_query );
				$archive_query = array_merge( $archive_query, $offset );
			}

			// Include Remove from Version 2.5.4
			if ( isset( $attr['queryInclude'] ) && $attr['queryInclude'] ) {
				$_include = explode( ',', $attr['queryInclude'] );
				if ( is_array( $_include ) && count( $_include ) ) {
					$archive_query['post__in']            = isset( $archive_query['post__in'] ) ? array_merge( $archive_query['post__in'], $_include ) : $_include;
					$archive_query['ignore_sticky_posts'] = 1;
					$archive_query['orderby']             = 'post__in';
				}
			}

			if ( isset( $attr['queryExclude'] ) && $attr['queryExclude'] ) {
				$_exclude = ( substr( $attr['queryExclude'], 0, 1 ) === '[' ) ? $this->get_value( json_decode( $attr['queryExclude'] ) ) : explode( ',', $attr['queryExclude'] );
				if ( is_array( $_exclude ) && count( $_exclude ) ) {
					$archive_query['post__not_in'] = isset( $archive_query['post__not_in'] ) ? array_merge( $archive_query['post__not_in'], $_exclude ) : $_exclude;
				}
			}

			if ( isset( $attr['querySticky'] ) && $attr['querySticky'] ) {
				if ( filter_var( $attr['querySticky'], FILTER_VALIDATE_BOOLEAN ) ) {
					$sticky                        = get_option( 'sticky_posts', array() );
					$archive_query['post__not_in'] = isset( $archive_query['post__not_in'] ) ? array_merge( $archive_query['post__not_in'], $sticky ) : $sticky;
				}
			}
			// ===============
			if ( isset( $attr['queryUnique'] ) && $attr['queryUnique'] ) {
				global $unique_ID;
				if ( isset( $unique_ID[ $attr['queryUnique'] ] ) ) {
					$archive_query['post__not_in'] = isset( $archive_query['post__not_in'] ) ? array_merge( $archive_query['post__not_in'], $unique_ID[ $attr['queryUnique'] ] ) : $unique_ID[ $attr['queryUnique'] ];
				}
			}
			// ===============

			$archive_query['post_status'] = 'publish';
			if ( is_user_logged_in() ) {
				if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
					$archive_query['post_status'] = array( 'publish', 'private' );
				}
			}
			// queryOrder
			if ( ! isset( $archive_query['orderby'] ) ) {
				$archive_query['orderby'] = isset( $attr['queryOrderBy'] ) ? $attr['queryOrderBy'] : 'date';
			}
			if ( ! isset( $archive_query['queryOrder'] ) ) {
				$archive_query['order'] = isset( $attr['queryOrder'] ) ? $attr['queryOrder'] : 'ASC';
			}

			// Quick Query Support for Builder
			if ( isset( $attr['queryQuick'] ) ) {
				if ( $attr['queryQuick'] != '' ) {
					$archive_query = ultimate_post()->get_quick_query( $attr, $archive_query );
				}
			}

			if ( ! isset( $attr['ajaxCall'] ) ) {
				if ( isset( $attr['filterShow'] ) && $attr['filterShow'] && $attr['filterShow'] != 'false' ) {
					if ( isset( $attr['filterType'] ) && isset( $attr['filterValue'] ) ) {
						$filterValue = strlen( $attr['filterValue'] ) > 2 ? $attr['filterValue'] : array();
						$filterValue = is_array( $filterValue ) ? $filterValue : json_decode( $filterValue );
						if ( count( $filterValue ) > 0 && $attr['filterType'] ) {
							$final = array( 'relation' => 'OR' );
							foreach ( $filterValue as $key => $val ) {
								$final[] = array(
									'taxonomy' => $attr['filterType'],
									'field'    => 'slug',
									'terms'    => $val,
								);
							}
							$archive_query['tax_query'] = $final;
						}
					}
				}
			}
			// from v.3.0.7 - for search builder
			if ( is_search() ) {
				$archive_query['orderby'] = 'relevance';
				if ( isset( $_GET['ultp_exclude'] ) ) {       // Added support for search block exclude
					$ultp_exclude = json_decode( stripslashes( $_GET['ultp_exclude'] ), true );
					if ( is_array( $ultp_exclude ) ) {
						$all_types                  = get_post_types( array( 'public' => true ), 'names' );
						$post_type                  = array_diff( $all_types, $ultp_exclude );
						$archive_query['post_type'] = $post_type;
					}
				}
			}

			return apply_filters( 'ultp_archive_query', $archive_query );
		}

		// When you use load more pagination block for a grid that did not have load more feature,
		// the incoming posts would not align properly with the blocks design and looked bad.
		// This fixes that.
		if (
			isset( $attr['paginationType'] ) &&
			isset( $attr['queryNumber2'] ) &&
			isset( $attr['notFirstLoad'] ) &&
			$attr['paginationType'] === 'loadMore' &&
			$attr['notFirstLoad']
		) {
			$query_number = $attr['queryNumber2'];
			// $query_number = isset($attr['queryNumber']) ? $attr['queryNumber'] : 3;
		} else {
			$query_number = isset( $attr['queryNumber'] ) ? $attr['queryNumber'] : 3;
		}

		$paged = isset( $attr['paged'] ) ? $attr['paged'] : 1;
		$paged = ! wp_doing_ajax() &&
			isset( $attr['blockId'] ) &&
			isset( $_GET[ $attr['blockId'] . '_page' ] ) &&
			is_numeric( $_GET[ $attr['blockId'] . '_page' ] ) ?
			intval( $_GET[ $attr['blockId'] . '_page' ] ) :
			$paged;

		$query_args = array(
			'posts_per_page' => $query_number,
			'post_type'      => is_array( $post_type ) && ! empty( $post_type ) ? $post_type : ( 'archiveBuilder' == $post_type ? 'post' : ( is_string( $post_type ) ? is_array( json_decode( $post_type ) ) ? json_decode( $post_type ) : $post_type : $post_type ) ),
			'orderby'        => isset( $attr['queryOrderBy'] ) ? $attr['queryOrderBy'] : 'date',
			'order'          => isset( $attr['queryOrder'] ) ? $attr['queryOrder'] : 'desc',
			'paged'          => $paged,
			'post_status'    => 'publish',
		);

		// For Private Post 'private'.
		if ( is_user_logged_in() ) {
			if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
				$query_args['post_status'] = array( 'publish', 'private' );
			}
		}

		if ( $attr['queryType'] == 'posts' ) {
			if ( isset( $attr['queryPosts'] ) && $attr['queryPosts'] ) {
				unset( $query_args['post_type'] );
				$data  = json_decode( isset( $attr['queryPosts'] ) ? $attr['queryPosts'] : '[]' );
				$final = $this->get_value( $data );
				if ( count( $final ) > 0 ) {
					$query_args['post__in'] = $final;
					// $query_args['posts_per_page'] = -1;
				}
				$query_args['ignore_sticky_posts'] = 1;
				return $query_args;
			}
		} elseif ( $attr['queryType'] == 'customPosts' ) {
			if ( isset( $attr['queryCustomPosts'] ) && $attr['queryCustomPosts'] ) {
				$query_args['post_type'] = $this->get_post_type();
				$data                    = json_decode( isset( $attr['queryCustomPosts'] ) ? $attr['queryCustomPosts'] : '[]' );
				$final                   = $this->get_value( $data );
				if ( count( $final ) > 0 ) {
					$query_args['post__in'] = $final;
					// $query_args['posts_per_page'] = -1;
				}
				$query_args['ignore_sticky_posts'] = 1;
				return $query_args;
			}
		}

		if ( isset( $attr['queryExcludeAuthor'] ) && $attr['queryExcludeAuthor'] ) {
			$data  = json_decode( isset( $attr['queryExcludeAuthor'] ) ? $attr['queryExcludeAuthor'] : '[]' );
			$final = $this->get_value( $data );
			if ( count( $final ) > 0 ) {
				$query_args['author__not_in'] = $final;
			}
		}

		if ( isset( $attr['queryOrderBy'] ) && isset( $attr['metaKey'] ) ) {
			if ( $attr['queryOrderBy'] == 'meta_value_num' ) {
				$query_args['meta_key'] = $attr['metaKey'];
			}
		}

		// Include Remove from Version 2.5.4.
		if ( isset( $attr['queryInclude'] ) && $attr['queryInclude'] ) {
			$_include = explode( ',', $attr['queryInclude'] );
			if ( is_array( $_include ) && count( $_include ) ) {
				$query_args['post__in']            = isset( $query_args['post__in'] ) ? array_merge( $query_args['post__in'], $_include ) : $_include;
				$query_args['ignore_sticky_posts'] = 1;
				$query_args['orderby']             = 'post__in';
			}
		}

		if ( isset( $attr['queryTax'] ) ) {
			if ( isset( $attr['queryTaxValue'] ) ) {
				$var = $this->getFilterQueryArgs( $attr );
				if ( isset( $var ) && count( $var ) > 1 ) {
					$query_args['tax_query'] = $var;
				}
			}
		}

		if ( isset( $attr['queryExcludeTerm'] ) && $attr['queryExcludeTerm'] ) {
			$temp  = json_decode( $attr['queryExcludeTerm'] );
			$_term = array();
			foreach ( $temp as $val ) {
				$temp = explode( '###', $val->value );
				if ( isset( $temp[1] ) ) {
					if ( is_array( $_term ) && array_key_exists( $temp[0], $_term ) ) {
						$_term[ $temp[0] ][] = $temp[1];
					} else {
						$_term[ $temp[0] ] = array( $temp[1] );
					}
				}
			}
			if ( count( $_term ) > 0 ) {
				$final = array( 'relation' => 'AND' );
				foreach ( $_term as $key => $val ) {
					$final[] = array(
						'taxonomy' => $key,
						'field'    => 'slug',
						'terms'    => $val,
						'operator' => 'NOT IN',
					);
				}

				if ( is_array( $query_args ) && array_key_exists( 'tax_query', $query_args ) ) {
					$query_args['tax_query']   = array(
						'relation' => 'AND',
						$query_args['tax_query'],
					);
					$query_args['tax_query'][] = $final;
				} else {
					$query_args['tax_query'] = $final;
				}
			}
		}

		if ( isset( $attr['queryExclude'] ) && $attr['queryExclude'] ) {
			$_exclude = ( substr( $attr['queryExclude'], 0, 1 ) === '[' ) ? $this->get_value( json_decode( $attr['queryExclude'] ) ) : explode( ',', $attr['queryExclude'] );
			if ( is_array( $_exclude ) && count( $_exclude ) ) {
				$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ? array_merge( $query_args['post__not_in'], $_exclude ) : $_exclude;
			}
		}

		if ( isset( $attr['queryUnique'] ) && $attr['queryUnique'] ) {
			global $unique_ID;
			if ( isset( $unique_ID[ $attr['queryUnique'] ] ) ) {
				$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ? array_merge( $query_args['post__not_in'], $unique_ID[ $attr['queryUnique'] ] ) : $unique_ID[ $attr['queryUnique'] ];
			}
		}
		// exclude current post from Post blocks // v.2.9.6.
		if ( is_single() && get_the_ID() ) {
			$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ? array_merge( $query_args['post__not_in'], array( get_the_ID() ) ) : array( get_the_ID() );
		}

		if ( isset( $attr['queryQuick'] ) ) {
			if ( $attr['queryQuick'] != '' && $post_type != 'archiveBuilder' ) {
				$query_args = ultimate_post()->get_quick_query( $attr, $query_args );
			}
		}

		if ( isset( $attr['queryOffset'] ) && $attr['queryOffset'] ) {
			$offset     = $this->get_offset( $attr['queryOffset'], $query_args );
			$query_args = array_merge( $query_args, $offset );
		}

		if ( isset( $attr['queryAuthor'] ) && $attr['queryAuthor'] ) {
			$_include = ( substr( $attr['queryAuthor'], 0, 1 ) === '[' ) ? $this->get_value( json_decode( $attr['queryAuthor'] ) ) : explode( ',', $attr['queryAuthor'] );
			if ( is_array( $_include ) && count( $_include ) ) {
				$query_args['author__in'] = $_include;
			}
		}

		if ( isset( $attr['querySearch'] ) && $attr['querySearch'] ) {
			$query_args['s'] = $attr['querySearch'];
		}

		if ( isset( $attr['querySticky'] ) && $attr['querySticky'] ) {
			if ( filter_var( $attr['querySticky'], FILTER_VALIDATE_BOOLEAN ) ) {
				$sticky                     = get_option( 'sticky_posts', array() );
				$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ? array_merge( $query_args['post__not_in'], $sticky ) : $sticky;
			}
		}

		if ( ! isset( $attr['ajaxCall'] ) ) {
			if ( isset( $attr['filterShow'] ) && $attr['filterShow'] && $attr['filterShow'] != 'false' ) {
				if ( isset( $attr['checkFilter'] ) && isset( $attr['queryTax'] ) && isset( $attr['queryTaxValue'] ) ) {
					$var = $this->getFilterQueryArgs( $attr );
					if ( isset( $var ) && count( $var ) > 1 ) {
						$query_args['tax_query'] = $var;
					}
				} elseif ( isset( $attr['filterType'] ) && isset( $attr['filterValue'] ) ) {
					$filterValue = strlen( $attr['filterValue'] ) > 2 ? $attr['filterValue'] : array();
					$filterValue = is_array( $filterValue ) ? $filterValue : json_decode( $filterValue );
					if ( is_array( $filterValue ) && count( $filterValue ) > 0 && $attr['filterType'] ) {
						$final = array( 'relation' => 'OR' );
						foreach ( $filterValue as $key => $val ) {
							$final[] = array(
								'taxonomy' => $attr['filterType'],
								'field'    => 'slug',
								'terms'    => $val,
								// 'operator' => '=',
							);
						}
						$query_args['tax_query'] = $final;
					}
				}
			}
		}

		// Advanced Filter Select Dropdown Default value.
		// only on initial page load.
		if (
			! wp_doing_ajax() &&
			isset( $attr['advFilterEnable'] ) &&
			$attr['advFilterEnable'] === true &&
			isset( $attr['defQueryTax'] ) &&
			count( $attr['defQueryTax'] ) > 0
		) {
			$tax_query = array(
				'relation' =>
				isset( $attr['advRelation'] ) &&
					( 'AND' === $attr['advRelation'] || 'OR' === $attr['advRelation'] )
					? $attr['advRelation'] : 'AND',
			);
			$is_valid  = false;

			foreach ( $attr['defQueryTax'] as $_ => $defQueryTaxValue ) {
				$term = explode( '###', $defQueryTaxValue );

				// Validation
				if (
					! in_array( $term[0], array( 'category', 'tags', 'author', 'custom_tax' ), true ) ||
					'_all' === $term[1] ||
					empty( $term[1] )
				) {
					continue;
				}

				if ( 'author' === $term[0] ) {

					if ( ! is_numeric( $term[1] ) ) {
						continue;
					}

					$query_args['author__in'] = $term[1];
				} elseif ( 'custom_tax' === $term[0] && isset( $attr['queryType'] ) ) {
					$post_type = sanitize_key( $attr['queryType'] );
					$slug      = sanitize_key( $term[1] );

					// These are not custom post type.
					if ( in_array( $post_type, array( 'posts', 'customPosts' ), true ) ) {
						continue;
					}

					$taxonomy = $this->get_taxonomy_by_term_slug( $post_type, $slug );

					if ( ! empty( $taxonomy ) ) {
						$q = array(
							'field'    => 'slug',
							'taxonomy' => $taxonomy,
							'terms'    => $slug,
						);

						$tax_query[] = $q;
						$is_valid    = true;
					}
				} else {
					if ( ! is_numeric( $term[1] ) ) {
						continue;
					}

					$q = array(
						'field'    => 'term_id',
						'taxonomy' => 'tags' === $term[0] ? 'post_tag' : 'category',
						'terms'    => $term[1],
					);

					$tax_query[] = $q;
					$is_valid    = true;
				}
			}

			if ( $is_valid ) {
				$query_args['tax_query'] = $tax_query;
			}
		}
		// $query_args['test'] = is_array( $post_type ) && ! empty( $post_type ) ? $post_type : ( 'archiveBuilder' === $post_type ? 'post' : ( is_string( $post_type ) ? json_decode( $post_type ) : $post_type ) );
		$query_args['wpnonce'] = wp_create_nonce( 'ultp-nonce' );

		return apply_filters( 'ultp_frontend_query', $query_args );
	}

	/**
	 * Get Page Offset
	 *
	 * @since v.1.0.0
	 * @param NUMBER | $queryOffset Offset .
	 * @param ARRAY |  $query_args Query .
	 * @return ARRAY
	 */
	function get_offset( $queryOffset, $query_args ) {
		$query = array();
		if ( $query_args['paged'] > 1 ) {
			$offset_post = wp_get_recent_posts( $query_args, OBJECT );
			if ( count( $offset_post ) > 0 ) {
				$offset = array();
				for ( $x = count( $offset_post ); $x > count( $offset_post ) - $queryOffset; $x-- ) {
					$offset[] = $offset_post[ $x - 1 ]->ID;
				}
				$query['post__not_in'] = $offset;
			}
		} else {
			$query['offset'] = isset( $queryOffset ) ? $queryOffset : 0;
		}
		return $query;
	}


	/**
	 * Get Page Number
	 *
	 * @since v.1.0.0
	 * @param array $attr Attribute of the Query.
	 * @param int   $post_number Post Number.
	 * @return ARRAY
	 */
	public function get_page_number( $attr, $post_number ) {
		if ( $post_number > 0 ) {
			if ( isset( $attr['queryOffset'] ) && $attr['queryOffset'] ) {
				$post_number = $post_number - (int) $attr['queryOffset'];
			}
			$post_per_page = isset( $attr['queryNumber'] ) ? ( $attr['queryNumber'] ? $attr['queryNumber'] : 1 ) : 3;
			$pages         = ceil( $post_number / $post_per_page );
			return $pages ? $pages : 1;
		} else {
			return 1;
		}
	}


	/**
	 * Get Image Size
	 *
	 * @since v.1.0.0
	 * @return ARRAY
	 */
	public function get_image_size() {
		$sizes  = get_intermediate_image_sizes();
		$filter = array( 'full' => 'Full' );
		foreach ( $sizes as $value ) {
			$title = ucwords( str_replace( array( '_', '-', 'ultp' ), array( ' ', ' ', 'PostX' ), $value ) );
			switch ( $value ) {
				case 'thumbnail':
					$title = $title . ' [150x150]';
					break;
				case 'medium':
					$title = $title . ' [300x300]';
					break;
				case 'large':
					$title = $title . ' [1024x1024]';
					break;
				case 'ultp_layout_landscape_large':
					$title = $title . ' [1200x800]';
					break;
				case 'ultp_layout_landscape':
					$title = $title . ' [870x570]';
					break;
				case 'ultp_layout_portrait':
					$title = $title . ' [600x900]';
					break;
				case 'ultp_layout_square':
					$title = $title . ' [600x600]';
					break;
				default:
					break;
			}
			$filter[ $value ] = $title;
		}
		return $filter;
	}


	/**
	 * Get All PostType Registered
	 *
	 * @since v.1.0.0
	 * @return ARRAY
	 */
	public function get_post_type() {
		$filter    = apply_filters( 'ultp_public_post_type', true );
		$post_type = get_post_types( ( $filter ? array( 'public' => true ) : '' ), 'names' );
		return array_diff( $post_type, array( 'attachment' ) );
	}

	/**
	 * Get Pagination Url
	 *
	 * @since v.2.8.9
	 *
	 * @param NUMBER $pageNo  The page number for pagination.
	 * @param STRING $baseUrl The base URL to append the pagination parameter to.
	 *
	 * @return STRING The generated pagination URL.
	 */
	public function generatePaginationUrl( $pageNo, $baseUrl ) {
		if ( $baseUrl ) {
			$url = $baseUrl . ( $pageNo == 1 ? '' : 'page/' . $pageNo );
		} else {
			$url = get_pagenum_link( $pageNo );
		}
		return $url;
	}
	/**
	 * Get Pagination HTML
	 *
	 * @since v.1.0.0
	 * @param INT    $pages           Number of pages.
	 * @param STRING $paginationNav   Pagination navigation type.
	 * @param STRING $paginationText  Pagination text.
	 * @param BOOL   $paginationAjax  Whether to use AJAX pagination.
	 * @param STRING $baseUrl         Base URL for pagination links.
	 * @param STRING $blockId         Block ID for pagination.
	 * @return STRING
	 */
	public function pagination( $pages = '', $paginationNav = '', $paginationText = '', $paginationAjax = true, $baseUrl = '', $blockId = '' ) {
		$html      = '';
		$showitems = 3;
		$paged     = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
		$paged     = $paged ? $paged : 1;

		$paged = ! wp_doing_ajax() &&
			isset( $_GET[ $blockId . '_page' ] ) &&
			is_numeric( $_GET[ $blockId . '_page' ] ) ?
			intval( $_GET[ $blockId . '_page' ] ) : $paged;

		if ( $pages == '' ) {
			global $wp_query;
			$pages = $wp_query->max_num_pages;
			if ( ! $pages ) {
				$pages = 1;
			}
		}

		$data = ( $paged >= 3 ? array( ( $paged - 1 ), $paged, $paged + 1 ) : array( 1, 2, 3 ) );

		$paginationText = explode( '|', $paginationText );

		$prev_text = isset( $paginationText[0] ) ? esc_html( $paginationText[0] ) : __( 'Previous', 'ultimate-post' );
		$next_text = isset( $paginationText[1] ) ? esc_html( $paginationText[1] ) : __( 'Next', 'ultimate-post' );

		if ( 1 != $pages ) {
			$html        .= '<ul class="ultp-pagination">';
			$display_none = 'style="display:none"';
			if ( $paged > 1 || $paginationAjax && $pages > 1 ) {
				$html .= '<li class="ultp-prev-page-numbers" ' . ( $paged == 1 ? $display_none : '' ) . '><a href="' . $this->generatePaginationUrl( $paged - 1, $baseUrl ) . '">' . ultimate_post()->get_svg_icon( 'leftAngle2' ) . ' ' . ( $paginationNav == 'textArrow' ? $prev_text : '' ) . '</a></li>';
			}
			if ( $pages > 3 ) {
				$html .= '<li class="ultp-first-pages" ' . ( $paged < 2 ? $display_none : '' ) . ' data-current="1"><a href="' . $this->generatePaginationUrl( 1, $baseUrl ) . '">1</a></li>';
			}
			if ( $paged > 3 || $paginationAjax && $pages > 4 ) {
				$html .= '<li class="ultp-first-dot"' . ( $paged == 1 ? $display_none : '' ) . '><a href="#">...</a></li>';
			}
			foreach ( $data as $i ) {
				if ( $pages > 3 && $pages == $i ) {
					continue;
				}
				if ( $pages >= $i ) {
					$html .= ( $paged == $i ) ? '<li class="ultp-center-item pagination-active" data-current="' . $i . '"><a href="' . $this->generatePaginationUrl( $i, $baseUrl ) . '">' . $i . '</a></li>' : '<li class="ultp-center-item" data-current="' . $i . '" ' . ( ( $pages > 4 && $paged == 2 && $i == 1 && ! $paginationAjax ) ? $display_none : '' ) . '><a href="' . $this->generatePaginationUrl( $i, $baseUrl ) . '">' . $i . '</a></li>';
				}
			}
			if ( $pages != ( $paged + 2 ) || $paginationAjax && $pages > 4 ) {
				$html .= '<li class="ultp-last-dot" ' . ( $pages <= $paged + 1 ? $display_none : '' ) . '><a href="#">...</a></li>';
			}
			if ( $pages > 3 ) {
				$html .= '<li class="ultp-last-pages' . ( $paged == $pages ? ' pagination-active' : '' ) . '" data-current="' . $pages . '"><a href="' . $this->generatePaginationUrl( $pages, $baseUrl ) . '">' . $pages . '</a></li>';
			}
			if ( $paged != $pages ) {
				$html .= '<li class="ultp-next-page-numbers"><a href="' . $this->generatePaginationUrl( $paged + 1, $baseUrl ) . '">' . ( $paginationNav == 'textArrow' ? $next_text : '' ) . ultimate_post()->get_svg_icon( 'rightAngle2' ) . '</a></li>';
			}
			$html .= '</ul>';
		}
		return $html;
	}

	/**
	 * Svg Icon Source
	 *
	 * @since v.1.0.0
	 * @param STRING $ultp_icons Name of the SVG icon file (without extension).
	 * @return STRING
	 */
	public function get_svg_icon( $ultp_icons = '' ) {
		$svg = '';
		if ( $ultp_icons ) {
			$icon = $this->svg_icon_compatibility( $ultp_icons );
			$svg  = $this->get_path_file_contents( ULTP_PATH . 'assets/img/iconpack/' . $icon . '.svg' );
		}
		return $svg;
	}

	/**
	 * Get Taxonomy Lists
	 *
	 * @since v.1.0.0
	 * @param STRING|ARRAY $prams Taxonomy slug or arguments array.
	 * @return ARRAY
	 */
	public function taxonomy( $prams = 'category' ) {
		$data = array();

		$terms = get_terms(
			is_string( $prams ) ? array(
				'taxonomy'   => $prams,
				'hide_empty' => false,
			) : $prams
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $val ) {
				$data[ urldecode_deep( $val->slug ) ] = $val->name;
			}
		}
		return $data;
	}


	/**
	 * Get Taxonomy Lists
	 *
	 * @since v.2.9.0
	 * @param STRING $tax_slug Taxonomy slug.
	 * @param INT    $number   Number of terms to retrieve.
	 * @return ARRAY
	 */
	public function builder_preview( $tax_slug, $number ) {
		$data      = array();
		$term_data = get_terms(
			array(
				'taxonomy'   => $tax_slug,
				'hide_empty' => true,
				'number'     => $number,
				'parent'     => 0,
			)
		);
		if ( ! empty( $term_data ) ) {
			foreach ( $term_data as $terms ) {
				$data[] = $this->get_tax_data( $terms );
			}
		}
		return $data;
	}

	/**
	 * Get Taxonomy Data Lists
	 *
	 * @since v.1.0.0
	 * @param object $terms Taxonomy Object.
	 * @return ARRAY
	 */
	public function get_tax_data( $terms ) {
		$temp         = array();
		$thumbnail_id = get_term_meta( $terms->term_id, 'ultp_category_image', true );
		$image_src    = array();
		if ( $thumbnail_id ) {
			$image_sizes = ultimate_post()->get_image_size();
			foreach ( $image_sizes as $key => $value ) {
				$img_src = wp_get_attachment_image_src( $thumbnail_id, $key, false );
				if ( $img_src ) {
					$image_src[ $key ] = $img_src[0];
				}
			}
		}
		$temp['url']          = get_term_link( $terms );
		$temp['thumbnail_id'] = $thumbnail_id;
		$temp['name']         = $terms->name;
		$temp['desc']         = $terms->description;
		$temp['count']        = $terms->count;
		$temp['image']        = $image_src;
		$color                = get_term_meta( $terms->term_id, 'ultp_category_color', true );
		$temp['color']        = $color ? $color : '#037fff';
		return $temp;
	}

	public function get_category_data( $catSlug, $number = 40, $type = '', $tax_slug = 'category', $archiveBuilder = '' ) {
		$data = array();
		if ( $type == 'child' ) {
			if ( $archiveBuilder ) {
				$data = $this->builder_preview( $tax_slug, $number );
			} else {
				if ( is_category() ) {
					$catSlug = array( get_queried_object()->slug );
				}
				$image = '';
				if ( ! empty( $catSlug ) ) {
					foreach ( $catSlug as $cat ) {
						$parent_term = get_term_by( 'slug', $cat, $tax_slug );
						if ( ! empty( $parent_term ) ) {
							$term_data = get_terms(
								array(
									'taxonomy'   => $tax_slug,
									'hide_empty' => true,
									'number'     => $number,
									'parent'     => $parent_term->term_id,
								)
							);
							if ( ! empty( $term_data ) ) {
								foreach ( $term_data as $terms ) {
									$data[] = $this->get_tax_data( $terms );
								}
							}
						}
					}
				}
			}
		} elseif ( $type == 'parent' ) {
			$term_data = is_category() ? array( get_term( get_queried_object()->parent, 'category' ) ) : get_terms(
				array(
					'taxonomy'   => $tax_slug,
					'hide_empty' => true,
					'number'     => $number,
					'parent'     => 0,
				)
			);
			if ( $archiveBuilder && ! empty( $term_data ) ) {
				$term_data = array( $term_data[0] );
			}
			if ( ! empty( $term_data ) ) {
				foreach ( $term_data as $terms ) {
					if ( ! empty( $terms->term_id ) ) {
						$data[] = $this->get_tax_data( $terms );
					}
				}
			}
		} elseif ( $type == 'custom' ) {
			foreach ( $catSlug as $cat ) {
				$terms = get_term_by( 'slug', $cat, $tax_slug );
				if ( ! empty( $terms ) ) {
					$data[] = $this->get_tax_data( $terms );
				}
			}
		} elseif ( $type == 'immediate_child' ) {
			if ( $archiveBuilder ) {
				$data = $this->builder_preview( $tax_slug, $number );
			} else {
				$id_ = get_queried_object();
				if ( isset( $id_->term_id ) ) {
					$term_data = get_terms(
						array(
							'taxonomy'   => $tax_slug,
							'hide_empty' => true,
							'number'     => 100,
							'parent'     => $id_->term_id,
						)
					);
					if ( ! empty( $term_data ) ) {
						foreach ( $term_data as $terms ) {
							$data[] = $this->get_tax_data( $terms );
						}
					}
				}
			}
		} elseif ( $type == 'allchild' ) {
			if ( $archiveBuilder ) {
				$data = $this->builder_preview( $tax_slug, $number );
			} else {
				$id_ = get_queried_object();
				if ( isset( $id_->term_taxonomy_id ) ) {
					$termchildren = get_term_children( $id_->term_taxonomy_id, $tax_slug );
					foreach ( $termchildren as $key => $value ) {
						$terms  = get_term_by( 'id', $value, $tax_slug );
						$data[] = $this->get_tax_data( $terms );
					}
				}
			}
		} elseif ( $type == 'current_level' ) {
			if ( $archiveBuilder ) {
				$data = $this->builder_preview( $tax_slug, $number );
			} else {
				$id_ = get_queried_object();
				if ( isset( $id_->parent ) ) {
					$term_data = get_terms(
						array(
							'taxonomy'   => $tax_slug,
							'hide_empty' => true,
							'number'     => $number,
							'parent'     => $id_->parent,
						)
					);
					if ( ! empty( $term_data ) ) {
						foreach ( $term_data as $terms ) {
							if ( $terms->term_id != $id_->term_id ) {
								$data[] = $this->get_tax_data( $terms );
							}
						}
					}
				}
			}
		} else {
			$term_data = get_terms(
				array(
					'taxonomy'   => $tax_slug,
					'hide_empty' => true,
					'number'     => $number,
				)
			);
			if ( ! empty( $term_data ) ) {
				foreach ( $term_data as $terms ) {
					$data[] = $this->get_tax_data( $terms );
				}
			}
		}
		return $data;
	}


	/**
	 * Get Next Previous HTML.
	 *
	 * @since v.1.0.0
	 * @return STRING
	 */
	public function next_prev() {
		$html  = '';
		$html .= '<ul>';
		$html .= '<li>';
		$html .= '<a class="ultp-prev-action ultp-disable" href="#">';
		$html .= ultimate_post()->get_svg_icon( 'leftAngle2' ) . '<span class="screen-reader-text">' . esc_html__( 'Previous', 'ultimate-post' ) . '</span>';
		$html .= '</a>';
		$html .= '</li>';
		$html .= '<li>';
		$html .= '<a class="ultp-next-action">';
		$html .= ultimate_post()->get_svg_icon( 'rightAngle2' ) . '<span class="screen-reader-text">' . esc_html__( 'Next', 'ultimate-post' ) . '</span>';
		$html .= '</a>';
		$html .= '</li>';
		$html .= '</ul>';
		return $html;
	}


	/**
	 * Get Loading HTML
	 *
	 * @since v.1.0.0
	 * @return STRING
	 */
	public function postx_loading() {
		$html  = '';
		$style = ultimate_post()->get_setting( 'preloader_style' );
		if ( $style == 'style2' ) {
			$html .= '<div class="ultp-loading-spinner" style="width:100%;height:100%"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'; // ultp-block-items-wrap
		} else {
			$html .= '<div class="ultp-loading-blocks" style="width:100%;height:100%;"><div style="left: 0;top: 0;animation-delay:0s;"></div><div style="left: 21px;top: 0;animation-delay:0.125s;"></div><div style="left: 42px;top: 0;animation-delay:0.25s;"></div><div style="left: 0;top: 21px;animation-delay:0.875s;"></div><div style="left: 42px;top: 21px;animation-delay:0.375s;"></div><div style="left: 0;top: 42px;animation-delay:0.75s;"></div><div style="left: 42px;top: 42px;animation-delay:0.625s;"></div><div style="left: 21px;top: 42px;animation-delay:0.5s;"></div></div>';
		}
		return '<div class="ultp-loading">' . $html . '</div>';
	}


	/**
	 * Get Filter HTML
	 *
	 * @since v.1.0.0
	 * @param STRING       $filterText        Filter Text.
	 * @param STRING       $filterType        Filter Type.
	 * @param ARRAY|STRING $filterValue Filter Value.
	 * @param STRING       $filterMobileText  Filter Mobile Text.
	 * @param BOOL         $filterMobile        Enable Filter Mobile.
	 * @return STRING
	 */
	public function filter_html( $filterText = '', $filterType = '', $filterValue = '[]', $filterMobileText = '...', $filterMobile = true ) {
		$html  = '';
		$html .= '<ul ' . ( $filterMobile ? 'class="ultp-flex-menu"' : '' ) . ' data-name="' . ( $filterMobileText ? $filterMobileText : '&nbsp;' ) . '">';
		$cat   = $this->taxonomy( $filterType );
		if ( $filterText ) {
			$html .= '<li class="filter-item"><a class="filter-active" data-taxonomy="" href="#">' . $filterText . '</a></li>';
		}
		if ( $filterValue ) {
			$filterValue = strlen( $filterValue ) > 2 ? $filterValue : array();
			$filterValue = is_array( $filterValue ) ? $filterValue : json_decode( $filterValue );
			if ( is_array( $filterValue ) && count( $filterValue ) ) {
				foreach ( $filterValue as $val ) {
					$val   = isset( $val->value ) ? $val->value : $val;
					$html .= '<li class="filter-item"><a data-taxonomy="' . $val . '" href="#">' . ( isset( $cat[ $val ] ) ? $cat[ $val ] : $val ) . '</a></li>';
				}
			}
		}
		$html .= '</ul>';
		return $html;
	}


	/**
	 * Check License Status
	 *
	 * @since v.2.4.2
	 * @return BOOLEAN | Is pro license active or not
	 */
	public function is_lc_active() {
		return Xpo::is_lc_active();
	}


	/**
	 * Check if a pro feature is visible based on license expiration date.
	 *
	 * @since v.2.4.2
	 * @param STRING $date Date to compare with license expiration.
	 * @return BOOL True if the pro feature is visible, false otherwise.
	 */
	public function is_pro_feature_visible( $date ) {
		if ( function_exists( 'ultimate_post_pro' ) ) {
			$license_data = get_option( 'edd_ultp_license_data' );
			if ( isset( $license_data['expires'] ) ) {
				$expires = $license_data['expires'];
				if ( 'lifetime' === $expires ) {
					return true;
				}
				return strtotime( $date ) < strtotime( $expires );
			}
			return true;
		}
		return false;
	}

	/**
	 * Get SEO Meta
	 *
	 * @since v.2.4.3
	 * @param INT $post_id Post ID.
	 * @param INT $show_seo_meta Show SEO meta flag.
	 * @param INT $show_full_excerpt Show full excerpt flag.
	 * @param INT $excerpt_limit Excerpt word limit.
	 * @return STRING SEO Meta Description or Excerpt.
	 */
	public function get_excerpt( $post_id = 0, $show_seo_meta = 0, $show_full_excerpt = 0, $excerpt_limit = 55 ) {
		$html = '';
		if ( $show_seo_meta ) {
			$str = '';
			if ( function_exists( 'ultimate_post_pro' ) ) {
				if ( ultimate_post()->get_setting( 'ultp_yoast' ) == 'true' ) {
					$str = method_exists( ultimate_post_pro(), 'get_yoast_meta' ) ? ultimate_post_pro()->get_yoast_meta( $post_id ) : '';
				} elseif ( ultimate_post()->get_setting( 'ultp_rankmath' ) == 'true' ) {
					$str = method_exists( ultimate_post_pro(), 'get_rankmath_meta' ) ? ultimate_post_pro()->get_rankmath_meta( $post_id ) : '';
				} elseif ( ultimate_post()->get_setting( 'ultp_aioseo' ) == 'true' ) {
					$str = method_exists( ultimate_post_pro(), 'get_aioseo_meta' ) ? ultimate_post_pro()->get_aioseo_meta( $post_id ) : '';
				} elseif ( ultimate_post()->get_setting( 'ultp_seopress' ) == 'true' ) {
					$str = method_exists( ultimate_post_pro(), 'get_seopress_meta' ) ? ultimate_post_pro()->get_seopress_meta( $post_id ) : '';
				} elseif ( ultimate_post()->get_setting( 'ultp_squirrly' ) == 'true' ) {
					$str = method_exists( ultimate_post_pro(), 'get_squirrly_meta' ) ? ultimate_post_pro()->get_squirrly_meta( $post_id ) : '';
				}
			}
			$html = $str ? $str : ultimate_post()->excerpt( $post_id, $excerpt_limit );
		} elseif ( $show_full_excerpt == 0 ) {
			$html = ultimate_post()->excerpt( $post_id, $excerpt_limit );
		} else {
			$html = get_the_excerpt();
		}
		return $html;
	}

	/**
	 * Get embedded video HTML.
	 *
	 * @since v.1.0.0
	 * @param STRING         $url Video URL.
	 * @param BOOL           $autoPlay Whether to autoplay the video.
	 * @param BOOL           $loop Whether to loop the video.
	 * @param BOOL           $mute Whether to mute the video.
	 * @param BOOL           $playback Whether to show playback controls.
	 * @param STRING         $preload Preload attribute value.
	 * @param ARRAY | string $poster Poster image for the video.
	 * @param BOOL           $inline Whether to play inline.
	 * @param ARRAY | string $size Size for oEmbed.
	 * @return STRING | false Video embed HTML or false on failure.
	 */
	public function get_embeded_video( $url, $autoPlay, $loop, $mute, $playback, $preload, $poster, $inline, $size, $location = '' ) {
		$vidAutoPlay = $vidloop = $vidloop = $vidmute = $vidplayback = $vidPoster = $vidInline = '';

		$video_type = '';
		// Determine video type
		$video_type = 'local';
		if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
			$video_type = 'youtube';
		} elseif ( strpos( $url, 'vimeo.com' ) !== false ) {
			$video_type = 'vimeo';
		}

		// Get poster URL
		$poster_url = '';
		if ( is_array( $poster ) && isset( $poster['url'] ) ) {
			$poster_url = $poster['url'];
		} elseif ( is_string( $poster ) ) {
			$poster_url = $poster;
		}

		// Prepare size attributes
		$width  = is_array( $size ) && isset( $size['width'] ) ? $size['width'] : 560;
		$height = is_array( $size ) && isset( $size['height'] ) ? $size['height'] : 315;

		$video_id = '';

		if ( $url ) {
			$regex = '/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})/';
			if ( preg_match( $regex, $url, $matches ) ) {
				$video_id = $matches[1];
			}
		}

		// Create data attributes for lazy loading
		$data_attrs = array(
			'data-video-url'   => is_scalar( $url ) ? esc_attr( $url ) : '',
			'data-video-type'  => is_scalar( $video_type ) ? esc_attr( $video_type ) : '',
			'data-autoplay'    => ! empty( $autoPlay ) ? '1' : '0',
			'data-loop'        => ! empty( $loop ) ? '1' : '0',
			'data-mute'        => ! empty( $mute ) ? '1' : '0',
			'data-controls'    => ! empty( $playback ) ? '1' : '0',
			'data-preload'     => is_scalar( $preload ) ? esc_attr( $preload ) : '',
			'data-poster'      => is_scalar( $poster_url ) ? esc_attr( $poster_url ) : '',
			'data-playsinline' => ! empty( $inline ) ? '1' : '0',
			'data-width'       => is_scalar( $width ) ? esc_attr( $width ) : '',
			'data-height'      => is_scalar( $height ) ? esc_attr( $height ) : '',
			'data-video-id'    => is_scalar( $video_id ) ? esc_attr( $video_id ) : '',
		);

		$data_string = implode(
			' ',
			array_map(
				function ( $key, $value ) {
					return $key . '="' . $value . '"';
				},
				array_keys( $data_attrs ),
				$data_attrs
			)
		);

		$html = '<div class="ultp-video-wrapper ultp-embaded-video"' . $data_string . '></div>';

		if ( $location == '' ) {
			return $html;
		}

		if ( $autoPlay ) {
			$vidAutoPlay = 'autoplay';
		}
		if ( $poster ) {
			$vidPoster = 'poster="' . $poster['url'] . '"';
		}
		if ( $loop ) {
			$vidloop = 'loop';
		}
		if ( $mute ) {
			$vidmute = 'muted';
		}
		if ( $playback ) {
			$vidplayback = 'controls';
		}
		if ( $inline ) {
			$vidInline = 'playsinline';
		}
		if ( ! empty( $url ) ) {
			$embeded = wp_oembed_get( $url, $size );
			if ( $embeded == false ) {
				$format = '';
				$url    = strtolower( $url );
				if ( strpos( $url, '.mp4' ) ) {
					$format = 'mp4';
				} elseif ( strpos( $url, '.ogg' ) ) {
					$format = 'ogg';
				} elseif ( strpos( $url, '.webm' ) ) {
					$format = 'WebM';
				}
				$embeded = '<video 
                ' . $vidloop . '
                ' . $vidmute . '
                ' . $vidplayback . '
                preload="' . $preload . '"
                ' . $vidAutoPlay . '
                ' . $vidPoster . '
                ' . $vidInline . '
                class="ultp-video-html"
            ><source src="' . $url . '" type="video/' . $format . '">' . __( 'Your browser does not support the video tag.', 'ultimate-post' ) . '</video>';
				return '<div class="ultp-video-wrapper">' . $embeded . '</div>';
			}
			return '<div class="ultp-video-wrapper ultp-embaded-video">' . $embeded . '</div>';
		} else {
			return false;
		}
	}

	/**
	 * Get Public taxonomy Lists
	 *
	 * @since v.2.7.0
	 * @return ARRAY | Taxonomy Lists as array
	 */
	public function get_taxonomy_list() {
		$taxonomy = get_taxonomies( array( 'public' => true ) );
		return empty( $taxonomy ) ? array() : array_keys( $taxonomy );
	}

	/**
	 * Content Print
	 *
	 * @since v.2.7.0
	 * @param int    $post_id      Post ID.
	 * @param string $builder_type Builder type.
	 * @return void | Content of the Post
	 */
	public function get_post_content( $post_id, $builder_type = '' ) {
		$content_post = get_post( $post_id );
		$content      = $content_post->post_content;
		if ( $builder_type == 'divi' || $builder_type == 'elementor' ) {
			$content = apply_filters( 'the_content', $content );
		} else {
			global $wp_embed;
			$content = $wp_embed->autoembed( $content );
			$content = do_blocks( $content );
			$content = do_shortcode( $content );
		}
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content );
		$content = preg_replace( '/^(?:<br\s*\/?>\s*)+/', '', $content );
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
	}

	/**
	 * String Part finder inside array.
	 *
	 * @since v.2.7.0 updated 4.1.12
	 *
	 * @param STRING $part The part of the string to search for.
	 * @param ARRAY  $data The array to search within.
	 * @param STRING $toR  Return type: 'bool', 'value', or default (array).
	 * @return MIXED Returns true/false, value, or array depending on $toR.
	 * @since v.2.7.0 updated 4.1.12
	 */
	public function in_array_part( $part, $data, $toR = '' ) {
		$res = array();
		foreach ( $data as $key => $val ) {
			if ( strpos( $val, $part ) !== false ) {
				if ( $toR == 'bool' ) {
					return true;
				} elseif ( $toR == 'value' ) {
					return $val;
				}
				$res[ $key ] = $val;
			}
		}
		if ( $toR == 'bool' ) {
			return false;
		} elseif ( $toR == 'value' ) {
			return '';
		}
		return $res;
	}

	/**
	 * Builder Conditions
	 *
	 * @since v.2.7.0 updated 4.1.12
	 * @param STRING $type          Type of return value.
	 * @param MIXED  $cus_condition Custom conditions array or value.
	 * @return MIXED                ID or Path.
	 */
	public function builder_check_conditions( $type = 'return', $cus_condition = '' ) {
		$page_id    = '';
		$conditions = $cus_condition ? $cus_condition : get_option( 'ultp_builder_conditions', array() );

		if ( $type == 'header' || $type == 'footer' ) {
			if ( ! empty( $conditions[ $type ] ) ) {
				foreach ( $conditions[ $type ] as $key => $val ) {
					if ( 'publish' == get_post_status( $key ) && ! empty( $val ) ) {
						if ( in_array( 'include/' . $type . '/entire_site', $val ) ) {
							$page_id = $key;
						} elseif ( in_array( 'exclude/' . $type . '/entire_site', $val ) ) {
							$page_id = '';
						}

						$c_page     = ( is_archive() || is_search() ) ? 'archive' : 'singular';
						$hf_archive = $this->in_array_part( $type . '/' . $c_page, $val, '' );
						if ( ! empty( $hf_archive ) ) {
							foreach ( $hf_archive as $k => $v ) {
								if ( strpos( $v, 'include/' . $type . '/' . $c_page ) !== false ) {
									$temp    = $this->builder_check_conditions( 'return_' . $type, array( $c_page => array( $key => array( str_replace( $type . '/', '', $v ) ) ) ) );
									$page_id = $temp ? $temp : $page_id;
								} elseif ( strpos( $v, 'exclude/' . $type . '/' . $c_page ) !== false ) {
									$temp    = $this->builder_check_conditions( 'return_' . $type, array( $c_page => array( $key => array( str_replace( 'exclude/' . $type, 'include', $v ) ) ) ) );
									$page_id = $temp ? '' : $page_id;
								}
							}
						}
					}
				}
			}
		} else {
			// 404 page .
			if ( ! empty( $conditions['404'] ) && is_404() ) {
				foreach ( $conditions['404'] as $key => $val ) {
					if ( 'publish' == get_post_status( $key ) ) {
						$page_id = $key;
					}
				}
			}
			// Singular or Front Page .
			elseif (
				(
					! empty( $conditions['singular'] ) ||
					! empty( $conditions['front_page'] )
				) &&
				(
					is_singular() ||
					is_front_page() ||
					is_home()
				)
			) {
				$obj            = get_queried_object();
				$queried_is_obj = is_object( $obj );
				// front page only .
				if (
					( is_front_page() && is_home() ) ||
					( is_home() && ! $queried_is_obj ) ||
					( is_singular() && is_front_page() && $queried_is_obj )
				) {
					$f_conditions = array();
					if ( ! empty( $conditions['front_page'] ) ) {
						$f_conditions = $conditions['front_page'];
					} elseif ( ! empty( $conditions['singular'] ) ) {
						$f_conditions = $conditions['singular'];
					}
					if ( ! empty( $f_conditions ) ) {
						foreach ( $f_conditions as $key => $val ) {
							if ( ( is_array( $val ) && in_array( 'include/singular/front_page', $val ) ) ||
								( is_array( $val ) && in_array( 'include/front_page', $val ) ) ||
								'include/front_page' == $val
							) {
								if ( 'publish' == get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							// Default Front / Home page Header Footer Compatibility .
							elseif ( is_array( $val ) && 'publish' == get_post_status( $key ) && ( $type == 'return_header' || $type == 'return_footer' ) ) {
								$base_include     = 'include/singular/' . $obj->post_type;
								$base_exclude     = 'exclude/singular/' . $obj->post_type;
								$specific_include = $base_include . '/' . $obj->ID;
								$specific_exclude = $base_exclude . '/' . $obj->ID;

								if ( in_array( $specific_include, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( $specific_exclude, $val ) ) {
									$page_id = '';
								} elseif ( in_array( $base_include, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( $base_exclude, $val ) ) {
									$page_id = '';
								}
							}
						}
					}
				}
				// Singular page .
				elseif ( ! empty( $conditions['singular'] ) ) {
					foreach ( $conditions['singular'] as $key => $val ) {
						if ( 'publish' == get_post_status( $key ) ) {
							// All Post Type.                -----Prority 1 .
							if ( $queried_is_obj ) {
								if ( in_array( 'include/singular/' . $obj->post_type, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/singular/' . $obj->post_type, $val ) ) {
									$page_id = '';
								}
							}

							$singular_in_tax = $this->in_array_part( '/singular/in_', $val, '' );
							if ( ! empty( $singular_in_tax ) ) {
								$tax_list = $this->get_taxonomy_list();
								foreach ( $tax_list as $tax ) {
									if ( in_array( 'include/singular/in_' . $tax . '_children', $singular_in_tax ) ) {
										$page_id = $key;
									} elseif ( in_array( 'exclude/singular/in_' . $tax . '_children', $singular_in_tax ) ) {
										$page_id = '';
									}

									if ( $queried_is_obj ) {
										$singular_in_tax_children = $this->in_array_part( '/singular/in_' . $tax . '_children/', $singular_in_tax, '' );
										foreach ( $singular_in_tax_children as $v ) {
											$data = explode( '/', $v );
											if ( isset( $data[3] ) && $data[3] ) {
												$childs = get_term_children( $data[3], $tax );
												if ( ! empty( $childs ) && has_term( $childs, $tax ) ) {
													$page_id = $data[0] == 'exclude' ? '' : $key;
												}
											}
										}
									}

									if ( in_array( 'include/singular/in_' . $tax, $singular_in_tax ) ) {
										if ( $tax == 'post_tag' && $queried_is_obj ) {
											$cat = get_the_terms( $obj->ID, 'post_tag' );
											if ( ! empty( $cat ) ) {
												$page_id = $key;
											}
										} else {
											$page_id = $key;
										}
									} elseif ( in_array( 'exclude/singular/in_' . $tax, $singular_in_tax ) ) {
										$page_id = '';
									}

									if ( $queried_is_obj ) {
										$singular_in_tax_only = $this->in_array_part( '/singular/in_' . $tax . '/', $singular_in_tax, '' );
										foreach ( $singular_in_tax_only as $v ) {
											$data = explode( '/', $v );
											if ( isset( $data[3] ) && $data[3] ) {
												if ( is_object_in_term( $obj->ID, $tax, $data[3] ) ) {
													$page_id = $data[0] == 'exclude' ? '' : $key;
												}
											}
										}
									}
								}
							}

							// Posts by author              -----Prority 9 .
							if ( in_array( 'include/singular/post_by_author', $val ) ) {
								$page_id = $key;
							} elseif ( in_array( 'exclude/singular/post_by_author', $val ) ) {
								$page_id = '';
							}
							if ( $queried_is_obj ) {
								if ( in_array( 'include/singular/post_by_author/' . $obj->post_author, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/singular/post_by_author/' . $obj->post_author, $val ) ) {
									$page_id = '';
								}
								// Post Type with specific id  -----Prority 10 .
								if ( in_array( 'include/singular/' . $obj->post_type . '/' . $obj->ID, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/singular/' . $obj->post_type . '/' . $obj->ID, $val ) ) {
									$page_id = '';
								}
							}
						}
					}
				}
			}

			// Archive and Search Page .
			elseif ( ! empty( $conditions['archive'] ) ) {
				// Search Page .
				if ( is_search() ) {
					foreach ( $conditions['archive'] as $key => $val ) {
						if ( 'publish' == get_post_status( $key ) ) {
							if ( in_array( 'include/archive/search', $val ) ) {
								$page_id = $key;
							} elseif ( in_array( 'exclude/archive/search', $val ) ) {
								$page_id = '';
							}
						}
					}
				}
				// Archive Page .
				elseif ( is_archive() ) {
					$is_cat    = is_category();
					$is_tag    = is_tag();
					$is_tax    = is_tax();
					$is_date   = is_date();
					$is_author = is_author();
					$taxonomy  = ( $is_cat || $is_tag || $is_tax ) ? get_queried_object() : (object) array();

					foreach ( $conditions['archive'] as $key => $val ) {
						if ( 'publish' == get_post_status( $key ) ) {
							// This section is for all archive page .
							if ( in_array( 'include/archive', $val ) ) {
								$page_id = $key;
							} elseif ( in_array( 'exclude/archive', $val ) ) {
								$page_id = '';
							}
							// For Category .
							if ( $is_cat ) {
								// all category .
								if ( in_array( 'include/archive/category', $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/category', $val ) ) {
									$page_id = '';
								}
								// specific category .
								if ( in_array( 'include/archive/category/' . $taxonomy->term_id, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/category/' . $taxonomy->term_id, $val ) ) {
									$page_id = '';
								}

								// any child of category .
								if ( in_array( 'include/archive/any_child_of_category', $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/any_child_of_category', $val ) ) {
									$page_id = '';
								}

								$any_child_of_cat = $this->in_array_part( 'archive/any_child_of_category/', $val, '' );
								if ( ! empty( $any_child_of_cat ) ) {
									foreach ( $any_child_of_cat as $v ) {
										$data = explode( '/', $v );
										if ( isset( $data[3] ) && $data[3] ) {
											if ( term_is_ancestor_of( $data[3], $taxonomy->term_id, 'category' ) ) {
												$page_id = $data[0] == 'exclude' ? '' : $key;
											}
										}
									}
								}

								// all child of category .
								if ( in_array( 'include/archive/child_of_category', $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/child_of_category', $val ) ) {
									$page_id = '';
								}
								// specific child of category .
								if ( in_array( 'include/archive/child_of_category/' . $taxonomy->parent, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/child_of_category/' . $taxonomy->parent, $val ) ) {
									$page_id = '';
								}
							}
							// For Tag .
							elseif ( $is_tag ) {
								if ( in_array( 'include/archive/post_tag', $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/post_tag', $val ) ) {
									$page_id = '';
								}
								if ( in_array( 'include/archive/post_tag/' . $taxonomy->term_id, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/post_tag/' . $taxonomy->term_id, $val ) ) {
									$page_id = '';
								}
							}
							// Other taxonomy .
							elseif ( $is_tax ) {
								if ( in_array( 'include/archive/' . $taxonomy->taxonomy, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/' . $taxonomy->taxonomy, $val ) ) {
									$page_id = '';
								}

								if ( in_array( 'include/archive/' . $taxonomy->taxonomy . '/' . $taxonomy->term_id, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/' . $taxonomy->taxonomy . '/' . $taxonomy->term_id, $val ) ) {
									$page_id = '';
								}

								if ( in_array( 'include/archive/any_child_of_' . $taxonomy->taxonomy, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/any_child_of_' . $taxonomy->taxonomy, $val ) ) {
									$page_id = '';
								}

								$any_child_of_tax = $this->in_array_part( 'archive/any_child_of_' . $taxonomy->taxonomy . '/', $val, '' );
								if ( ! empty( $any_child_of_tax ) ) {
									foreach ( $any_child_of_tax as $v ) {
										$data = explode( '/', $v );
										if ( isset( $data[3] ) && $data[3] ) {
											if ( term_is_ancestor_of( $data[3], $taxonomy->term_id, $taxonomy->taxonomy ) ) {
												$page_id = $data[0] == 'exclude' ? '' : $key;
											}
										}
									}
								}

								if ( in_array( 'include/archive/child_of_' . $taxonomy->taxonomy, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/child_of_' . $taxonomy->taxonomy, $val ) ) {
									$page_id = '';
								}

								if ( in_array( 'include/archive/child_of_' . $taxonomy->taxonomy . '/' . $taxonomy->parent, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/child_of_' . $taxonomy->taxonomy . '/' . $taxonomy->parent, $val ) ) {
									$page_id = '';
								}
							}
							// For Date.
							elseif ( $is_date ) {
								if ( in_array( 'include/archive/date', $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/date', $val ) ) {
									$page_id = '';
								}
							}
							// For Author.
							elseif ( $is_author ) {
								if ( in_array( 'include/archive/author', $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/author', $val ) ) {
									$page_id = '';
								}
								$author_id = get_the_author_meta( 'ID' );
								if ( in_array( 'include/archive/author/' . $author_id, $val ) ) {
									$page_id = $key;
								} elseif ( in_array( 'exclude/archive/author/' . $author_id, $val ) ) {
									$page_id = '';
								}
							}
						}
					}
				}
			}
		}
		return $page_id;
	}

	/**
	 * Get Date Default Format
	 *
	 * @since v.2.7.2
	 * @param STRING $format Format type.
	 * @return ARRAY | Default Data
	 */
	public function get_format( $format ) {
		if ( $format == 'default_date' ) {
			return get_option( 'date_format' );
		} elseif ( $format == 'default_date_time' ) {
			return get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		} else {
			return $format;
		}
	}

	/**
	 * Common Frontend and Backend CSS and JS Scripts
	 *
	 * @since v.1.0.0
	 */
	public function register_scripts_common() {
		if ( ! ( isset( $GLOBALS['wp_scripts'] ) && isset( $GLOBALS['wp_scripts']->registered ) && isset( $GLOBALS['wp_scripts']->registered['ultp-script'] ) ) ) {
			wp_enqueue_style( 'ultp-style', ULTP_URL . 'assets/css/style.min.css', array(), ULTP_VER );
			wp_enqueue_script( 'ultp-script', ULTP_URL . 'assets/js/ultp.min.js', array( 'jquery', 'wp-api-fetch' ), ULTP_VER, true );
			wp_localize_script(
				'ultp-script',
				'ultp_data_frontend',
				array(
					'url'             => ULTP_URL,
					'active'          => ultimate_post()->is_lc_active(),
					'ultpSavedDLMode' => ultimate_post()->get_dl_mode(),
					'ajax'            => admin_url( 'admin-ajax.php' ),
					'security'        => wp_create_nonce( 'ultp-nonce' ),
					'home_url'        => home_url(),
					'dark_logo'       => get_option( 'ultp_site_dark_logo', false ),
				)
			);
		}
	}

	/**
	 * Get Dark Light Mode
	 *
	 * @since 4.0.0
	 * @return STRING | Dark Light Mode .
	 */
	public function get_dl_mode() {
		$data = get_option( 'postx_global', array() );
		$mode = 'ultplight';
		if ( ! empty( $data ) ) {
			$mode = isset( $data['enableDark'] ) && $data['enableDark'] ? 'ultpdark' : 'ultplight';
		}
		return $mode;
	}

	/**
	 * Get Page Post Id ( Kadence element )
	 *
	 * @since v.2.8.9
	 * @param STRING|INT $blockId Block ID to search for.
	 * @return NULL
	 */
	public function get_page_post_id( $blockId ) {
		global $wpdb;
		$post_meta = $wpdb->get_row( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key=%s AND meta_value LIKE %s', '_ultp_css', '%.ultp-block-' . $blockId . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		// For FSE theme
		if ( ! $post_meta ) {
			if ( wp_is_block_theme() ) {
				$template = $wpdb->get_row( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE post_content LIKE %s', '%"blockId":"' . $blockId . '"%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				if ( isset( $template->ID ) ) {
					return $template->ID;
				}
			}
		}
		if ( $post_meta && isset( $post_meta->post_id ) ) {
			return $post_meta->post_id;
		}
		return ultimate_post()->get_ID();
	}


	/**
	 * Get no Result found html
	 *
	 * @since v.2.9.0
	 * @param STRING $text No result found text.
	 * @return STRING | Taxonomy Lists as array
	 */
	public function get_no_result_found_html( $text ) {
		return $text ? '<div class="ultp-not-found-message" role="alert">' . wp_kses( $text, ultimate_post()->ultp_allowed_html_tags() ) . '</div>' : '';
	}

	/**
	 * Get all Saved Templates Lists
	 *
	 * @since v.2.9.9
	 * @param STRING $post_type Post type to retrieve.
	 * @param STRING $empty     Empty value for the select field.
	 * @return STRING | Taxonomy Lists as array
	 */
	public function get_all_lists( $post_type = 'post', $empty = '' ) {
		$args                         = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		$loop                         = new \WP_Query( $args );
		$data[ $empty ? $empty : '' ] = __( '- Select Template -', 'ultimate-post' );
		if ( $loop->have_posts() ) {
			foreach ( $loop->posts as $post ) {
				$data[ $post->ID ] = $post->post_title;
			}
		}
		wp_reset_postdata();
		return $data;
	}

	/**
	 * Get ID from the Youtube URL
	 *
	 * @since v.3.1.7
	 * @param STRING $url The Youtube URL .
	 * @return STRING | Taxonomy Lists as array
	 */
	public function get_youtube_id( $url = '' ) {
		if ( strpos( $url, 'youtu' ) !== false ) {
			$reg = '/youtu(?:.*\/v\/|.*v\=|\.be\/)([A-Za-z0-9_\-]{11})/m';
			preg_match( $reg, $url, $matches );
			if ( isset( $matches[1] ) ) {
				return $matches[1];
			}
		}
		return false;
	}

	/**
	 * Get Option Value bypassing cache
	 * Inspired By WordPress Core get_option
	 *
	 * @since v.3.1.6
	 * @param STRING  $option Option Name.
	 * @param BOOLEAN $default_value option default value.
	 * @return mixed
	 */
	public function get_option_without_cache( $option, $default_value = false ) {
		global $wpdb;

		if ( is_scalar( $option ) ) {
			$option = trim( $option );
		}

		if ( empty( $option ) ) {
			return false;
		}

		$value = $default_value;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( is_object( $row ) ) {
			$value = $row->option_value;
		} else {
			return apply_filters( "ultp_default_option_{$option}", $default_value, $option );
		}

		return apply_filters( "ultp_option_{$option}", maybe_unserialize( $value ), $option );
	}

	/**
	 * Get Transient Value bypassing cache
	 * Inspired By WordPress Core get_transient
	 *
	 * @since v.3.1.6
	 * @param STRING $transient Transient Name.
	 * @return  MIXED
	 */
	public function get_transient_without_cache( $transient ) {
		$transient_option  = '_transient_' . $transient;
		$transient_timeout = '_transient_timeout_' . $transient;
		$timeout           = $this->get_option_without_cache( $transient_timeout );

		if ( false !== $timeout && $timeout < time() ) {
			delete_option( $transient_option );
			delete_option( $transient_timeout );
			$value = false;
		}

		if ( ! isset( $value ) ) {
			$value = $this->get_option_without_cache( $transient_option );
		}

		return apply_filters( "ultp_transient_{$transient}", $value, $transient );
	}

	/**
	 * Set transient without adding to the cache
	 * Inspired By WordPress Core set_transient
	 *
	 * @since v.3.1.6
	 * @param STRING  $transient Transient Name.
	 * @param MIXED   $value Transient Value.
	 * @param INTEGER $expiration Time until expiration in seconds.
	 * @return BOOL
	 */
	public function set_transient_without_cache( $transient, $value, $expiration = 0 ) {
		$expiration = (int) $expiration;

		$transient_timeout = '_transient_timeout_' . $transient;
		$transient_option  = '_transient_' . $transient;

		$result = false;

		if ( false === $this->get_option_without_cache( $transient_option ) ) {
			$autoload = 'yes';
			if ( $expiration ) {
				$autoload = 'no';
				$this->add_option_without_cache( $transient_timeout, time() + $expiration, 'no' );
			}
			$result = $this->add_option_without_cache( $transient_option, $value, $autoload );
		} else {
			/*
			 * If expiration is requested, but the transient has no timeout option,
			 * delete, then re-create transient rather than update.
			 */
			$update = true;

			if ( $expiration ) {
				if ( false === $this->get_option_without_cache( $transient_timeout ) ) {
					delete_option( $transient_option );
					$this->add_option_without_cache( $transient_timeout, time() + $expiration, 'no' );
					$result = $this->add_option_without_cache( $transient_option, $value, 'no' );
					$update = false;
				} else {
					update_option( $transient_timeout, time() + $expiration );
				}
			}

			if ( $update ) {
				$result = update_option( $transient_option, $value );
			}
		}

		return $result;
	}

	/**
	 * Add option without adding to the cache
	 * Inspired By WordPress Core set_transient
	 *
	 * @since v.3.1.6
	 * @param STRING $option option name.
	 * @param STRING $value option value.
	 * @param STRING $autoload whether to load WordPress startup.
	 * @return BOOL
	 */
	public function add_option_without_cache( $option, $value = '', $autoload = 'yes' ) {
		global $wpdb;

		if ( is_scalar( $option ) ) {
			$option = trim( $option );
		}

		if ( empty( $option ) ) {
			return false;
		}

		wp_protect_special_option( $option );

		if ( is_object( $value ) ) {
			$value = clone $value;
		}

		$value = sanitize_option( $option, $value );

		/*
		 * Make sure the option doesn't already exist.
		 */

		if ( apply_filters( "ultp_default_option_{$option}", false, $option, false ) !== $this->get_option_without_cache( $option ) ) {
			return false;
		}

		$serialized_value = maybe_serialize( $value );
		$autoload         = ( 'no' === $autoload || false === $autoload ) ? 'no' : 'yes';

		$result = $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)", $option, $serialized_value, $autoload ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! $result ) {
			return false;
		}

		return true;
	}

	/**
	 * Permission_check_for_restapi
	 *
	 * @since v.3.2.4
	 * @param STRING/Bool $post_id .
	 * @param STRING      $cap STRING .
	 * @return BOOL
	 */
	public function permission_check_for_restapi( $post_id = false, $cap = '' ) {
		$cap       = $cap ? $cap : 'edit_others_posts';
		$is_passed = false;
		if ( $post_id ) {
			$post_author = (int) get_post_field( 'post_author', $post_id );
			$is_passed   = (int) get_current_user_id() === $post_author;
		}
		return $is_passed || current_user_can( $cap );
	}

	/**
	 * Sanitize params
	 *
	 * @param MIXED $params Parameters to sanitize.
	 * @return array|bool|mixed|string
	 * @since v.4.0.0
	 */
	public function ultp_rest_sanitize_params( $params ) {
		if ( is_array( $params ) ) {
			return array_map( array( $this, 'ultp_rest_sanitize_params' ), $params );
		} elseif ( is_bool( $params ) ) {
			return rest_sanitize_boolean( $params );
		} elseif ( is_object( $params ) ) {
			return $params;
		} else {
			return sanitize_text_field( $params );
		}
	}

	/**
	 * Get Adv Filter Data Attributes
	 *
	 * @param ARRAY|NULL $attr .
	 * @param ARRAY|NULL $filter_attributes .
	 * @return string
	 * @since v.3.2.5
	 */
	public function get_adv_data_attrs( $attr, $filter_attributes = null ) {
		// Adv Filter Integration.
		$adv_filter_dataset = array();

		if ( $filter_attributes ) {
			$adv_filter_dataset[] = 'data-filter-value="' . htmlspecialchars( $filter_attributes['queryTaxValue'] ) . '"';
			$adv_filter_dataset[] = 'data-filter-type="' . htmlspecialchars( $filter_attributes['queryTax'] ) . '"';

			$is_adv               = isset( $filter_attributes['isAdv'] ) ? $filter_attributes['isAdv'] : 0;
			$adv_filter_dataset[] = 'data-filter-is-adv="' . $is_adv . '"';

			if ( $is_adv ) {
				if ( isset( $filter_attributes['queryAuthor'] ) ) {
					$adv_filter_dataset[] = "data-filter-author='" . $filter_attributes['queryAuthor'] . "'";
				}

				if ( isset( $filter_attributes['queryOrder'] ) ) {
					$adv_filter_dataset[] = "data-filter-order='" . $filter_attributes['queryOrder'] . "'";
				}

				if ( isset( $filter_attributes['queryOrderBy'] ) ) {
					$adv_filter_dataset[] = "data-filter-orderby='" . $filter_attributes['queryOrderBy'] . "'";
				}

				if ( isset( $filter_attributes['search'] ) ) {
					$adv_filter_dataset[] = 'data-filter-search="' . $filter_attributes['search'] . '"';
				}

				if ( isset( $filter_attributes['queryQuick'] ) && $filter_attributes['queryQuick'] ) {
					$adv_filter_dataset[] = 'data-filter-adv-sort="' . $filter_attributes['queryQuick'] . '"';
				}
			}
		}

		if ( $attr && isset( $attr['advFilterEnable'] ) && $attr['advFilterEnable'] ) {
			$adv_filter_dataset[] = 'data-filter-is-adv="1"';
			$adv_filter_dataset[] = 'data-filter-type="multiTaxonomy"';

			$data_filter_value    = isset( $attr['filterValue'] ) && $attr['filterValue'] ? $attr['filterValue'] : '[]';
			$adv_filter_dataset[] = 'data-filter-value="' . htmlspecialchars( $data_filter_value ) . '"';

			$data_author          = isset( $attr['queryAuthor'] ) && $attr['queryAuthor'] ? $attr['queryAuthor'] : '[]';
			$adv_filter_dataset[] = 'data-filter-author="' . $data_author . '"';

			$data_order           = isset( $attr['queryOrder'] ) && $attr['queryOrder'] ? $attr['queryOrder'] : 'DESC';
			$adv_filter_dataset[] = 'data-filter-order="' . $data_order . '"';

			$data_orderby         = isset( $attr['queryOrderBy'] ) && $attr['queryOrderBy'] ? $attr['queryOrderBy'] : 'date';
			$adv_filter_dataset[] = 'data-filter-orderby="' . $data_orderby . '"';

			$adv_filter_dataset[] = 'data-filter-search="' . ( isset( $attr['querySearch'] ) ? $attr['querySearch'] : '' ) . '"';

			$adv_filter_dataset[] = 'data-filter-adv-sort="' . ( isset( $attr['queryQuick'] ) ? $attr['queryQuick'] : '' ) . '"';
		}

		$adv_filter_dataset = implode( ' ', $adv_filter_dataset );
		return $adv_filter_dataset;
	}

	/**
	 * Custom Text kses
	 *
	 * @since v.4.0.2
	 * @param MIXED $extras .
	 * @return ARRAY|BOOL|MIXED|STRING
	 */
	public function ultp_allowed_html_tags( $extras = array() ) {
		$allowed = array(
			'a'          => array(
				'href'  => true,
				'title' => true,
			),
			'abbr'       => array(
				'title' => true,
			),
			'b'          => array(),
			'br'         => array(),
			'blockquote' => array(
				'cite' => true,
			),
			'em'         => array(),
			'i'          => array(),
			'q'          => array(
				'cite' => true,
			),
			'strong'     => array(
				'class' => true,
				'style' => true,
			),
			'mark'       => array(
				'class' => true,
				'style' => true,
			),
		);

		return array_merge( $allowed, $extras );
	}

	/**
	 * Allowed Block Tags
	 *
	 * @since v.4.0.2
	 * @param STRING $search Optional. Tag name to check.
	 * @return ARRAY|BOOL Returns array of allowed tags or bool if $search is provided.
	 */
	public function ultp_allowed_block_tags( $search = '' ) {
		$array_lists = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p', 'div', 'section', 'article' );
		return $search ? in_array( $search, $array_lists ) : $array_lists;
	}

	/**
	 * Formats datasets for html
	 *
	 * @since v.4.0.2
	 *
	 * @param ARRAY $datasets .
	 * @return STRING
	 */
	public function get_formatted_datasets( &$datasets ) {
		$res = '';
		foreach ( $datasets as $key => $value ) {
			$res .= ' data-' . $key . '="' . $value . '" ';
		}
		return $res;
	}

	/**
	 * Sanitizes attributes after running necessary checks
	 *
	 * @since v.4.1.0
	 *
	 * @param ARRAY         $attr              The attributes array to sanitize.
	 * @param STRING        $key               The key to check in the attributes array.
	 * @param callable|null $sanitize_callback Callback function to sanitize the value.
	 * @param MIXED         $def_value         Default value to return if key is not set.
	 * @return MIXED               Sanitized value or default value.
	 */
	public function sanitize_attr( &$attr, $key, $sanitize_callback = null, $def_value = '' ) {
		return isset( $attr[ $key ] ) && $attr[ $key ] ?
			(
				$sanitize_callback ?
				$sanitize_callback( $attr[ $key ] ) :
				$attr[ $key ]
			)
			: $def_value;
	}

	/**
	 * Checks if dynamic content is active
	 *
	 * @since v.4.1.1
	 *
	 * @param ARRAY $attr The attributes to check for dynamic content.
	 * @return BOOLEAN
	 */
	public function is_dc_active( &$attr ) {
		if ( class_exists( '\ULTP\DCService' ) ) {
			return \ULTP\DCService::is_dc_active( $attr );
		}
		return false;
	}


	/**
	 * Checks all the taxonomy of a post type for a given slug. Returns the taxonomy
	 * if it exists.
	 *
	 * @since v.4.1.16
	 *
	 * @param STRING $post_type  The post type.
	 * @param STRING $slug       The slug.
	 * @return STRING|NULL       The taxonomy name or null if not found.
	 */
	public function get_taxonomy_by_term_slug( $post_type, $slug ) {
		$taxonomies = get_object_taxonomies( $post_type );
		foreach ( $taxonomies as $taxonomy ) {
			$res = get_term_by( 'slug', $slug, $taxonomy );

			if ( ! empty( $res ) ) {
				return $taxonomy;
			}
		}

		return null;
	}
	/**
	 * Resolve Icon Alias
	 *
	 * @since v.4.0.0
	 * @param STRING $icon_name The icon name or alias to resolve.
	 * @return STRING|FALSE Returns the resolved icon name or false if no alias found.
	 */
	public function svg_icon_compatibility( $icon_name = '' ) {
		if ( empty( $icon_name ) ) {
			return 'warning_triangle_solid';
		}
		// Get icon aliases mapping
		$icon_aliases = array(
			// Arrow aliases
			'angle_bottom_left_line'   => 'arrow_down_bottom_left_solid',
			'angle_bottom_right_line'  => 'arrow_down_bottom_right_solid',
			'angle_top_left_line'      => 'arrow_up_top_left_solid',
			'angle_top_right_line'     => 'arrow_up_top_right_solid',
			'rightFillAngle'           => 'right_triangle_angle_play_arrow_forward_solid',
			'leftAngle2'               => 'arrow_left_previous_backward_chevron_line',
			'rightAngle2'              => 'arrow_right_next_forward_chevron_line',
			'collapse_bottom_line'     => 'arrow_down_dropdown_maximize_chevron_line',
			'arrowUp2'                 => 'arrow_up_dropdown_minimize_chevron_line',
			'longArrowUp2'             => 'long_arrow_up_top_increase_solid',

			// Circle arrow aliases
			'arrow_left_circle_line'   => 'arrow_left_backward_circle_line',
			'arrow_bottom_circle_line' => 'arrow_down_bottom_downward_circle_line',
			'arrow_right_circle_line'  => 'arrow_right_forward_circle_line',
			'arrow_top_circle_line'    => 'arrow_up_top_upward_circle_line',

			// Close/Cross aliases
			'close_circle_line'        => 'cross_close_x_minimize_circle_line',
			'close_line'               => 'cross_x_close_minimize_line',

			// Direction aliases
			'arrow_down_line'          => 'arrow_down_bottom_downward_line',
			'leftArrowLg'              => 'arrow_left_backward_line',
			'rightArrowLg'             => 'arrow_left_forward_line',
			'arrow_up_line'            => 'long_arrow_up_top_increase_line',

			// Solid direction aliases
			'down_solid'               => 'arrow_down_bottom_downward_circle_solid',
			'right_solid'              => 'arrow_right_forward_circle_solid',
			'left_solid'               => 'arrow_left_backward_circle_solid',
			'up_solid'                 => 'arrow_up_top_upward_circle_solid',

			// Move aliases
			'bottom_right_line'        => 'arrow_move_up_right_line',
			'bottom_left_line'         => 'arrow_move_up_left_line',
			'top_left_angle_line'      => 'arrow_move_down_left_line',
			'top_right_line'           => 'arrow_move_down_right_line',

			// Utility aliases
			'at_line'                  => 'at_a_mail_line',
			'refresh'                  => 'refresh_reset_cycle_loop_infinity_line',
			'cart_line'                => 'shopping_cart_line',
			'cart_solid'               => 'add_plus_shopping_cart_solid',
			'cog_line'                 => 'settings_tool_function_line',
			'cog_solid'                => 'settings_tool_function_solid',
			'clock'                    => 'clock_reading_time_1_line',
			'book'                     => 'book_line',
			'download_line'            => 'download_1_line',
			'download_solid'           => 'download_1_solid',
			'downlod_bottom_solid'     => 'download_1_solid',

			// Visibility aliases
			'eye'                      => 'view_count_show_visible_eye_open_2_line',
			'hidden_line'              => 'hidden_hide_invisible_line',

			// Location aliases
			'home_line'                => 'home_house_line',
			'home_solid'               => 'home_house_solid',
			'location_line'            => 'location_gps_map_line',
			'location_solid'           => 'location_gps_map_solid',

			// Emotion aliases
			'love_line'                => 'heart_love_wishlist_favourite_line',
			'love_solid'               => 'heart_love_wishlist_favourite_solid',

			// Media aliases
			'play_line'                => 'play_media_video_circle_line',
			'videoplay'                => 'right_triangle_angle_play_arrow_forward_solid',
			'left_angle_solid'         => 'left_triangle_angle_arrow_backward_solid',

			// Shape aliases
			'caretArrow'               => 'caret_up_top_triangle_angle_arrow_upward_solid',
			'rectangle_solid'          => 'square_rounded_solid',
			'triangle_solid'           => 'triangle_shape_solid',

			// Status aliases
			'restriction_line'         => 'restriction_no_stop_line',
			'right_circle_line'        => 'correct_save_check_circle_line',
			'save_line'                => 'correct_save_check_line',
			'search_line'              => 'search_magnify_line',
			'search_solid'             => 'search_magnify_solid',

			// Warning aliases
			'notice_circle_solid'      => 'warning_circle_solid',
			'notice_solid'             => 'warning_triangle_solid',
			'warning_circle_line'      => 'warning_circle_line',
			'warning_triangle_line'    => 'warning_triangle_line',

			// Category aliases
			'cat1'                     => 'category_file_documents_1_solid',
			'cat2'                     => 'category_book_line',
			'cat3'                     => 'category_file_documents_2_line',
			'cat4'                     => 'category_file_documents_3_line',
			'cat5'                     => 'category_file_documents_3_solid',
			'cat6'                     => 'category_file_documents_4_line',
			'cat7'                     => 'category_book_line',

			// Comment aliases
			'commentCount1'            => 'messege_comment_1_line',
			'commentCount2'            => 'messege_comment_3_solid',
			'commentCount3'            => 'messege_comment_3_line',
			'commentCount4'            => 'messege_comment_6_line',
			'commentCount5'            => 'messege_comment_7_line',
			'commentCount6'            => 'messege_comment_8_line',
			'comment'                  => 'messege_comment_4_line',

			// Date aliases
			'date1'                    => 'calendar_date_4_line',
			'date2'                    => 'calendar_date_1_solid',
			'date3'                    => 'calendar_date_2_line',
			'date4'                    => 'calendar_date_4_solid',
			'date5'                    => 'calendar_date_3_line',
			'calendar'                 => 'calendar_date_3_line',

			// Reading time aliases
			'readingTime1'             => 'clock_reading_time_3_line',
			'readingTime2'             => 'clock_reading_time_2_line',
			'readingTime3'             => 'book_reading_time_line',
			'readingTime4'             => 'clock_reading_time_1_line',
			'readingTime5'             => 'hourglass_timer_time_line',

			// Tag aliases
			'tag1'                     => 'tag_bookmark_save_favourite_mark_discount_sale_line',
			'tag2'                     => 'price_tag_label_category_sale_discount_solid',
			'tag3'                     => 'price_tag_label_category_sale_discount_line',
			'tag4'                     => 'price_tag_offer_sale_coupon_solid',
			'tag5'                     => 'price_tag_label_category_sale_discount_line',
			'tag6'                     => 'growth_increase_up_solid',

			// View count aliases
			'viewCount1'               => 'view_count_show_visible_eye_open_1_line',
			'viewCount2'               => 'view_count_show_visible_eye_open_2_line',
			'viewCount3'               => 'view_count_show_visible_eye_open_3_line',
			'viewCount4'               => 'view_count_show_visible_eye_open_4_solid',
			'viewCount5'               => 'view_count_show_visible_eye_open_5_solid',
			'viewCount6'               => 'view_count_show_visible_eye_open_5_solid',

			// Author aliases
			'author1'                  => 'author_user_human_1_line',
			'author2'                  => 'author_user_human_4_line',
			'author3'                  => 'author_user_human_4_solid',
			'author4'                  => 'author_user_human_4_line',
			'author5'                  => 'author_user_human_3_solid',
			'author6'                  => 'author_user_human_6_line',
			'user'                     => 'author_user_human_3_line',

			// Device aliases
			'desktop'                  => 'desktop_monitor_computer_line',
			'laptop'                   => 'laptop_computer_line',
			'tablet'                   => 'tablet_ipad_pad_line',
			'mobile'                   => 'mobile_smartphone_phone_line',

			// Emoji aliases
			'angry_line'               => 'angry_emoji_line',
			'angry_solid'              => 'angry_emoji_solid',
			'confused_line'            => 'confused_emoji_line',
			'confused_solid'           => 'confused_emoji_solid',
			'happy_line'               => 'happy_emoji_line',
			'happy_solid'              => 'happy_emoji_solid',
			'smile_line'               => 'smile_emoji_line',
			'smile_solid'              => 'smile_emoji_solid',

			// Social aliases
			'share_line'               => 'social_community_line',
			'share'                    => 'share_social_solid',
			'apple_solid'              => 'apple_logo_icon_solid',
			'android_solid'            => 'android_logo_icon_solid',
			'google_solid'             => 'google_logo_icon_solid',
			'messenger'                => 'messenger_logo_icon_solid',
			'microsoft_solid'          => 'microsoft_logo_icon_solid',
			'mail'                     => 'mail_email_messege_solid',
			'facebook'                 => 'facebook_logo_icon_solid',
			'twitter'                  => 'twitter_x_logo_icon_line',
			'link'                     => 'link_chains_line',

			// Misc aliases
			'media_document'           => 'media_document',
			'arrowDown2'               => 'arrow_down_dropdown_maximize_chevron_line',
			'setting'                  => 'settings_tool_function_solid',
			'upload_solid'             => 'upload_1_solid',

			// Empty aliases for missing icons (return empty string)
			'correct_solid'            => 'correct_save_check_circle_line',
			'dot_solid'                => 'dot_circle_solid',
			'right_circle_solid'       => 'correct_save_check_circle_solid',
			'full_screen'              => 'full_screen_corners_out_solid',
			'zoom_in'                  => 'zoom_in_magnifying_glass_plus_line',
			'zoom_out'                 => 'zoom_out_magnifying_glass_minus_line',
			'gallery_indicator'        => 'gallery_indicator_image_solid',
			'ascending'                => 'sort_ascending_order_solid',
			'descending'               => 'sort_descending_order_line',
			'unlink'                   => 'unlink_link_break_line',
			'rocket'                   => 'rocket_fly_boost_launch_pro_line',
			'unlock'                   => 'unlocked_open_security_solid',
			'connect'                  => 'plugin_connect_socket_integration_solid',
			'leftAngle'                => 'arrow_left_previous_backward_chevron_line',
			'rightAngle'               => 'right_triangle_angle_play_arrow_forward_line',
			'plus2'                    => '',
			'hamicon_1'                => 'left_align_1_line',
			// extra in assets\img\iconpack folder
			'hamicon_2'                => 'hemicon_2_line',
			'hamicon_3'                => 'hemicon_3_line',
			'hamicon_4'                => 'hamicon_5_line',
			'hamicon_5'                => 'hamicon_4_sloid',
			'hamicon_6'                => 'hamicon_6_line',
			'instagram_solid'          => 'instagram_logo_icon_solid',
			'linkedin'                 => 'linkedin_logo_icon_solid',
			'pause_solid'              => 'pause_solid',
			'pinterest'                => 'pinterest_logo_icon_solid',
			'reddit'                   => 'reddit_logo_icon_solid',
			'skype'                    => 'skype_logo_icon_solid',
			'tiktok_lite_solid'        => 'tiktok_logo_icon_line',
			'tiktok_solid'             => 'tiktok_logo_icon_circle_solid',
			'whatsapp'                 => 'whatsapp_logo_icon_solid',
			'wordpress_lite_solid'     => 'wordpress_logo_icon_solid',
			'wordpress_solid'          => 'wordpress_logo_icon_2_solid',
			'wrong_solid'              => 'cross_close_x_minimize_circle_solid',
			'youtube_solid'            => 'youtube_logo_icon_solid',
			'five_star_line'           => 'star_rating_line',
			'rightAngleBold'           => 'arrow_right_next_forward_chevron_line',
			'leftAngleBold'            => 'arrow_left_previous_backward_chevron_line',
			'plus'                     => 'plus',
			'reset_left_line'          => 'refresh_reset_cycle_loop_infinity_line',
		);
		// Return resolved alias or false if not found
		return isset( $icon_aliases[ $icon_name ] ) ? $icon_aliases[ $icon_name ] : $icon_name;
	}
}
