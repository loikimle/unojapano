<?php
namespace ULTP;

defined('ABSPATH') || exit;

class Option_Blocks{
    public function __construct() {
        $this->create_admin_page();
    }

    public static function get_blocks_settings() {
        $arr = array(
            'grid' => array(
                'label' => __('Post Grid Blocks', 'ultimate-post'),
                'attr' => array(
                    'post_grid_1' => array(
                        'label' => __('Post Grid #1', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6829',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-grid-1.svg'
                    ),
                    'post_grid_2' => array(
                        'label' => __('Post Grid #2', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6830',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-grid-2.svg'
                    ),
                    'post_grid_3' => array(
                        'label' => __('Post Grid #3', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6831',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-grid-3.svg'
                    ),
                    'post_grid_4' => array(
                        'label' => __('Post Grid #4', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6832',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-grid-4.svg'
                    ),
                    'post_grid_5' => array(
                        'label' => __('Post Grid #5', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6833',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-grid-5.svg'
                    ),
                    'post_grid_6' => array(
                        'label' => __('Post Grid #6', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6834',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-grid-6.svg'
                    ),
                    'post_grid_7' => array(
                        'label' => __('Post Grid #7', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6835',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-grid-7.svg'
                    )
                )
            ),
            'list' => array(
                'label' => __('Post List Blocks', 'ultimate-post'),
                'attr' => array(
                    'post_list_1' => array(
                        'label' => __('Post List #1', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6836',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-list-1.svg'
                    ),
                    'post_list_2' => array(
                        'label' => __('Post List #2', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6837',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-list-2.svg'
                    ),
                    'post_list_3' => array(
                        'label' => __('Post List #3', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6838',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-list-3.svg'
                    ),
                    'post_list_4' => array(
                        'label' => __('Post List #4', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6839',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'assets/img/blocks/post-list-4.svg'
                    ),
                    
                )
                ),
            'slider' => array(
                'label' => __('Post Slider', 'ultimate-post'),
                'attr' => array(
                    'post_slider_1' => array(
                        'label' => __('Post Slider #1', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6840',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'/assets/img/blocks/post-slider-1.svg'
                    ),
                    'post_slider_2' => array(
                        'label' => __('Post Slider #2', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid7487',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/all-blocks/post-slider-2/',
                        'icon' => ULTP_URL.'/assets/img/blocks/post-slider-2.svg'
                    ),
                )
            ),
            'other' => array(
                'label' => __('Others Post Blocks', 'ultimate-post'),
                'attr' => array(
                    'post_module_1' => array(
                        'label' => __('Post Module #1', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6825',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'/assets/img/blocks/post-module-1.svg'
                    ),
                    'post_module_2' => array(
                        'label' => __('Post Module #2', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6827',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'/assets/img/blocks/post-module-2.svg'
                    ),
                    'heading' => array(
                        'label' => __('Heading', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6842',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/',
                        'icon' => ULTP_URL.'/assets/img/blocks/heading.svg'
                    ),
                    'image' => array(
                        'label' => __('Image', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6843',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/all-blocks/image-blocks/',
                        'icon' => ULTP_URL.'/assets/img/blocks/image.svg'
                    ),
                    'taxonomy' => array(
                        'label' => __('Taxonomy', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6841',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/all-blocks/taxonomy-style-1/',
                        'icon' => ULTP_URL.'/assets/img/blocks/taxonomy.svg'
                    ),
                    'wrapper' => array(
                        'label' => __('Wrapper', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6844',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/add-on/wrapper-blocks/',
                        'icon' => ULTP_URL.'/assets/img/blocks/wrapper.svg'
                    ),
                    'news_ticker' => array(
                        'label' => __('News Ticker', 'ultimate-post'),
                        'default' => true,
                        'live' => 'https://www.wpxpo.com/postx/blocks/#demoid6845',
                        'docs' => 'https://docs.wpxpo.com/docs/postx/all-blocks/news-ticker-block/',
                        'icon' => ULTP_URL.'/assets/img/blocks/news-ticker.svg'
                    ),
                )
            ),
            'builder' => array(
                'label' => __('Site Builder Blocks', 'ultimate-post'),
                'attr' => array(
                    'builder_post_title' => array(
                        'label' => __('Post Title', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/post_title.svg'
                    ),
                    'builder_advance_post_meta' => array(
                        'label' => __('Advance Post Meta', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/post_meta.svg'
                    ),
                    'builder_archive_title' => array(
                        'label' => __('Archive Title', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/archive-title.svg'
                    ),
                    'builder_author_box' => array(
                        'label' => __('Post Author Box', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/author_box.svg'
                    ),
                    'builder_post_next_previous' => array(
                        'label' => __('Post Next Previous', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/next_previous.svg'
                    ),
                    'builder_post_author_meta' => array(
                        'label' => __('Post Author Meta', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/author.svg'
                    ),
                    'builder_post_breadcrumb' => array(
                        'label' => __('Post Breadcrumb', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/breadcrumb.svg'
                    ),
                    'builder_post_category' => array(
                        'label' => __('Post Category', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/category.svg'
                    ),
                    'builder_post_comment_count' => array(
                        'label' => __('Post Comment Count', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/comment_count.svg'
                    ),
                    'builder_post_comments' => array(
                        'label' => __('Post Comments', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/comments.svg'
                    ),
                    'builder_post_content' => array(
                        'label' => __('Post Content', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/content.svg'
                    ),
                    'builder_post_date_meta' => array(
                        'label' => __('Post Date Meta', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/post_date.svg'
                    ),
                    'builder_post_excerpt' => array(
                        'label' => __('Post Excerpt', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/excerpt.svg'
                    ),
                    'builder_post_featured_image' => array(
                        'label' => __('Post Featured Image', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/featured_img.svg'
                    ),
                    'builder_post_reading_time' => array(
                        'label' => __('Post Reading Time', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/reading_time.svg'
                    ),
                    'builder_post_social_share' => array(
                        'label' => __('Post Social Share', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/share.svg'
                    ),
                    'builder_post_tag' => array(
                        'label' => __('Post Tag', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/post_tag.svg'
                    ),
                    'builder_post_view_count' => array(
                        'label' => __('Post View Count', 'ultimate-post'),
                        'default' => true,
                        'docs' => 'https://docs.wpxpo.com/docs/postx/dynamic-site-builder/',
                        'icon' => ULTP_URL.'/assets/img/blocks/builder/view_count.svg'
                    ),
                )
            )
        );

        return $arr;
    }


    public static function create_admin_page() { ?>
        <div class="ultp-dashboard-container ultp-block-option">
            
            <div class="ultp-dashboard-body">
                <?php
                $option_data = ultimate_post()->get_setting();
                $blocks_settings = self::get_blocks_settings();

                foreach ($blocks_settings as $blocks) { ?>
                    <h4 class="ultp-sm-heading ultp-mb25"><?php echo esc_html($blocks['label']); ?></h4>
                    <div class="ultp-block-grid-content">
                        <?php foreach ($blocks['attr'] as $key => $val) { ?>
                                <div class="ultp-card ultp-p15">
                                    <div class="ultp-control-meta">
                                        <img src="<?php echo esc_url($val['icon']); ?>" alt="overview content">
                                        <div><?php echo esc_html($val['label']); ?></div>
                                    </div>
                                    <div class="ultp-control-option">
                                        <?php 
                                        if (isset($val['docs'])) { ?>
                                            <a href="<?php echo esc_url($val['docs']); ?>"  target="_blank" class="ultp-option-tooltip">
                                                <span><?php esc_html_e('Documentation', 'ultimate-post'); ?></span>
                                                <div class="dashicons dashicons-book"></div>
                                            </a>
                                        <?php } ?>
                                        <?php 
                                        if (isset($val['live'])) { ?>
                                            <a href="<?php echo esc_url($val['live']); ?>" target="_blank" class="ultp-option-tooltip">
                                                <span><?php esc_html_e('Live View', 'ultimate-post'); ?></span>
                                                <div class="dashicons dashicons-visibility"></div>
                                            </a>
                                        <?php } ?>
                                        <?php
                                            $output = $val['default'] ? 'checked' : '';
                                            if (isset($option_data[$key])) {
                                                $output = $option_data[$key] == 'yes' ? 'checked' : '';
                                            }
                                        ?>
                                        <input type="checkbox" data-type="blocks" class="ultp-addons-enable" id="<?php echo esc_attr($key); ?>" <?php echo esc_attr($output); ?>/><label class="ultp-control__label" for="<?php echo esc_attr($key); ?>">Toggle</label>
                                    </div>
                                </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                    
            </div>
        </div>
        <?php
    }
}