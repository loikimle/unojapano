<?php
namespace ULTP\blocks;

defined( 'ABSPATH' ) || exit;

class Post_Breadcrumb {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}
	public function get_attributes() {

		return array(
			'blockId'             => '',

			/*
			============================
				Breadcrumb Settings
			============================*/
			'bcrumbSeparator'     => true,
			'bcrumbName'          => true,
			'bcrumbRootText'      => 'Home',

			/*
			============================
				Separator Style
			============================*/
			'bcrumbSeparatorIcon' => 'dash',

			/*
			============================
				Advance Settings
			============================*/
			'advanceId'           => '',
			'advanceZindex'       => '',
			'hideExtraLarge'      => false,
			'hideDesktop'         => false,
			'hideTablet'          => false,
			'hideMobile'          => false,
			'advanceCss'          => '',
		);
	}

	public function register() {
		register_block_type(
			'ultimate-post/post-breadcrumb',
			array(
				'editor_script'   => 'ultp-blocks-editor-script',
				'editor_style'    => 'ultp-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function content( $attr, $noAjax ) {
		$attr = wp_parse_args( $attr, $this->get_attributes() );
		global $post;
		$block_name     = 'post-breadcrumb';
		$wrapper_before = $wrapper_after = $content = '';

        $attr['className'] = isset($attr['className']) && $attr['className'] ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';
        $attr['align'] = isset($attr['align']) && $attr['align'] ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['align']) : '';
        $attr['advanceId'] = isset($attr['advanceId']) ? sanitize_html_class( $attr['advanceId'] ) : '';
        $attr['blockId'] = isset($attr['blockId']) ? sanitize_html_class( $attr['blockId'] ) : '';
        $attr['bcrumbRootText'] = wp_kses($attr['bcrumbRootText'], ultimate_post()->ultp_allowed_html_tags());
        $separator = '';

        $wrapper_before .= '<div '.( $attr['advanceId'] ? 'id="'.$attr['advanceId'].'" ':'' ).' class="wp-block-ultimate-post-'.$block_name.' ultp-block-'.$attr["blockId"].( $attr["className"] ?' '.$attr["className"]:'' ).''.( $attr["align"] ? ' align' .$attr["align"]:'' ).'">';
            $wrapper_before .= '<div class="ultp-block-wrapper">';
                $content .= '<ul class="ultp-builder-breadcrumb ultp-breadcrumb-'.sanitize_html_class( $attr['bcrumbSeparatorIcon'] ).'">';
                    if ($attr['bcrumbSeparator']) {
                        $separator .= '<li class="ultp-breadcrumb-separator"></li>';
                    }
                    $content .= '<li><a href="'.esc_url(home_url('/')).'">'.__( strlen($attr['bcrumbRootText']) > 0 ? $attr['bcrumbRootText'] : 'Home', 'ultimate-post').'</a></li>';
                    if (is_category() || is_single() || is_tag() || is_author()) {
                        if (is_category()) {
                            $cat = get_queried_object();
                            if (isset($cat) && is_object($cat)) {
                                $parents = [];
                                $parent_id = $cat->parent;
                                while ($parent_id) {
                                    $parent = get_category($parent_id);
                                    if ($parent && $parent->term_id != 0) {
                                        array_unshift($parents, $parent); // Add to the beginning
                                        $parent_id = $parent->parent;
                                    } else {
                                        break;
                                    }
                                }
                                foreach ($parents as $parent_cat) {
                                    $content .= $separator.'<li><a href="'.esc_url(get_term_link($parent_cat->term_id, 'category')).'">'.esc_html($parent_cat->name).'</a></li>';
                                }
                            }
                            $content .= $separator.'<li>'.single_cat_title('', false).'</li>';
                        }
                        if (is_tag()) {
                            $content .= $separator.'<li>'.single_tag_title('', false).'</li>';
                        }
                        if (is_author()) {
                            $content .= $separator.'<li>'.get_the_author_meta('display_name', false).'</li>';
                        }
                        if (is_single()) {
                            $cat = get_the_category();
                            if (isset($cat[0])) {
                                $content .= $separator.'<li><a href="'.get_term_link($cat[0]->term_id).'">'.$cat[0]->name.'</a></li>';
                            }
                            if ($attr['bcrumbName']) {
                                $content .= $separator.'<li>'.get_the_title().'</li>';
                            }
                        }
                    } elseif (is_page()) {
                        if ($post->post_parent) {
                            $ancestor = get_post_ancestors($post->post_parent);
                            if (isset($ancestor[0])) {
                                $content = $separator.'<li><a href="'.get_permalink($ancestor[0]).'">'.get_the_title($ancestor[0]).'</a></li>';
                            }
                        }
                        if ($attr['bcrumbName']) {
                            $content .= $separator.'<li>'.get_the_title().'</li>';
                        }
                    } elseif (is_search()) {
                        $content .= $separator.'<li>'.get_search_query().'</li>';
                    }
                $content .= '</ul>';
            $wrapper_after .= '</div>';
        $wrapper_after .= '</div>';

		return $wrapper_before . $content . $wrapper_after;
	}
}
