<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

/**
 * The ProgressBar Class
 */
class ProgressBar{
    /**
     * Progressbar Construct.
     */
    public function __construct() {
        add_filter( 'ultp_settings', array($this, 'get_porgressbar_settings'), 10, 1 );
        add_action( 'wp_footer', array($this, 'progressbar_render') );
    }


    /**
	 * Progress Bar Conditions
     * 
     * @since v.
     * @param | NULL
	 * @return NULL
	 */
    public function progressbar_render() {
       $ultp_setting = get_option('ultp_options');

       $post_id     = get_the_ID();
       $post_type   = get_post_type( $post_id );
       $allpage     = ultimate_post()->get_setting('progressbar_allpage');
       $homepage    = ultimate_post()->get_setting('progressbar_homepage');
       $progressbar = ultimate_post()->get_setting('choice_progressbar');

        if ($allpage === 'yes' && ! is_home()) {
            $this->progressbar_html();
        }

        if ( (is_home() || is_front_page()) && 'yes'== $homepage ){
            $this->progressbar_html();
        } else if ( is_single() || is_page() ) {
            if ( ! empty( $progressbar ) ) {
                if ( in_array( $post_type, $progressbar, true ) ) {
                    $this->progressbar_html();
                }
            }
        }
    }


    /**
	 * Progress Bar HTML CSS & JS
     * 
     * @since v.
     * @param | NULL
	 * @return NULL
	 */
    public function progressbar_html() {
        $color = ultimate_post()->get_setting('progressbar_color');
        $height = ultimate_post()->get_setting('progressbar_height');
        $position = ultimate_post()->get_setting('progressbar_position');
        $css = 'position:fixed; width:0%; z-index: 9999; background-color:'.$color.'; height:'.$height.'px;';
        $css .= $position == 'bottom' ? 'bottom:0;' : (is_user_logged_in() ? 'top:32px;' : 'top:0;');
        ?>
        <div id="ultp-progressbar" style="<?php echo $css; ?>"></div>
        <script type="text/javascript">
            window.onscroll = function () { 
                const el = document.documentElement;
                const scroll = (( (document.body.scrollTop || el.scrollTop) / (el.scrollHeight - el.clientHeight)) * 100);
                document.getElementById('ultp-progressbar').style.width = scroll + "%";
            }
        </script>
        <?php
    }

    
    /**
     * ProgressBar Settings Field.
     *
     * @since v.
     * @param $config
     * @return array
     */
    public static function get_porgressbar_settings($config) {
        $arr = array(
            'ultp_progressbar' => array(
                'label' => __('Progress Bar', 'ultimate-post-pro'),
                'attr' => array(
                    'compare_heading' => array(
                        'type'  => 'heading',
                        'label' => __('Progress Bar Settings', 'ultimate-post-pro'),
                    ),
                    'progressbar_height' => array(
                        'type'     => 'number',
                        'label'   => __( 'Select Height', 'ultimate-post-pro' ),
                        'desc'    => __( 'Select Progress Bar height', 'ultimate-post-pro' ),
                        'default' => '5'
                    ),
                    'progressbar_color' => array(
                        'type'    => 'color',
                        'label'   => __( 'Select Color', 'ultimate-post-pro'),
                        'desc'    => __( 'Select Progress Bar Color', 'ultimate-post-pro' ),
                        'default' => '#037fff',
                    ),
                    'progressbar_position' => array(
                        'type'    => 'select',
                        'label'   => __( 'Progress Bar position', 'ultimate-post-pro' ),
                        'desc'    => __( 'Select your progress Bar position', 'ultimate-post-pro' ),
                        'options' => array(
                            'top' => __( 'Top','ultimate-post-pro' ),
                            'bottom' => __( 'Bottom','ultimate-post-pro' ),
                        ),
                    ),
                    'progressbar_allpage' => array(
                        'type'    => 'switch',
                        'label'   => __( 'All Page', 'ultimate-post-pro' ),
                        'desc'    => __( 'Show progress Bar in All page', 'ultimate-post-pro' ),
                    ),
                    'progressbar_homepage' => array(
                        'type'    => 'switch',
                        'label'   => __( 'Home Page', 'ultimate-post-pro' ),
                        'desc'    => __( 'Show progress Bar in home page', 'ultimate-post-pro' ),
                    ),
                    'choice_progressbar' => array(
                        'type'    => 'multiselect',
                        'label'   => __( 'Choice Progress Bar', 'ultimate-post-pro' ),
                        'desc'    => __( 'Select progress bar where you want to show', 'ultimate-post-pro' ),
                        'options' => ultimate_post()->get_post_type(),
                        'default' => array('post'),
                    )
                )
            )
        );

        return array_merge($config, $arr);
    }
}
