<?php
/**
 * Your code here.
 *
 */

add_action( 'wp_enqueue_scripts', 'ujEnqueueStyles', 9999 );
/**
 * Enqueue Uno Japano main stylesheet and script
 */
function ujEnqueueStyles() {
	$uno_japano_main_style  = 'uno-japano-main-style';
	$uno_japano_main_script = 'uno-japano-main-script';
	wp_enqueue_style( $uno_japano_main_style, get_stylesheet_directory_uri() . '/assets/dist/css/main.css', '',
		'1.1.0' );
	wp_enqueue_script( $uno_japano_main_script, get_stylesheet_directory_uri() . '/assets/dist/js/main.js',
		[ 'jquery' ], '',
		true );

	if ( 'sfwd-quiz' === get_post_type() ) {
		wp_add_inline_script( $uno_japano_main_script, '
            (function ($) {
                $(document).ready(function () {
                    $(".wpProQuiz_button").click(function () {
                        const sticky = 428;
                        $(window).scroll(function () {
                            if (window.pageYOffset >= sticky) {
                                $(".wpProQuiz_time_limit").addClass("sticky-timer")
                            } else {
                                $(".wpProQuiz_time_limit").removeClass("sticky-timer");
                            }
                        })
                    });
                });
            })(jQuery);
        '
		);

	}
}

function uno_japano_enqueue_admin_styles() {
    $uno_japano_admin_style = 'uno-japano-admin-style';

    wp_enqueue_style(
        $uno_japano_admin_style,
        get_stylesheet_directory_uri() . '/assets/dist/css/admin.css',
        array(), // dependencies if any
        filemtime( get_stylesheet_directory() . '/assets/dist/css/admin.css' ) // optional versioning with cache busting
    );
}
add_action( 'admin_enqueue_scripts', 'uno_japano_enqueue_admin_styles' );


add_action( 'init', 'tie_custom_image_sizes' );
function tie_custom_image_sizes() {
	add_image_size( TIELABS_THEME_SLUG . '-image-small', 256, 128, false );
	add_image_size( TIELABS_THEME_SLUG . '-image-large', 500, 250, false );
	add_image_size( TIELABS_THEME_SLUG . '-image-post', 768, 384, false );
}

add_action( 'wp_enqueue_scripts', 'tie_theme_child_styles_scripts', 80 );
function tie_theme_child_styles_scripts() {

	/* Load the RTL.css file of the parent theme */
	if ( is_rtl() ) {
		wp_enqueue_style( 'tie-theme-rtl-css', get_template_directory_uri() . '/rtl.css', '' );
	}

	/* THIS WILL ALLOW ADDING CUSTOM CSS TO THE style.css */
	wp_enqueue_style( 'tie-theme-child-css', get_stylesheet_directory_uri() . '/style.css', '' );

	/* Uncomment this line if you want to add custom javascript */
	//wp_enqueue_script( 'jannah-child-js', get_stylesheet_directory_uri() .'/js/scripts.js', '', false, true );
}

add_shortcode( 'UJ_count_users', 'ujPopulateUsersCount' );
/**
 * Shortcode to count total users of mainsite
 */
function ujPopulateUsersCount() {
	echo count_users()['total_users'] ?? '6489';
}

add_shortcode( 'UJ_count_questions', 'ujPopulateQuestionsCount' );
/**
 * Shortcode to count total questions of JLPT
 */
function ujPopulateQuestionsCount() {
	echo wp_count_posts( 'sfwd-question' )->publish ?? '5048';
}

add_shortcode( 'UJ_count_JLTP_quizzes', 'ujPopulateJLPTQuizzesCount' );
/**
 * Shortcode to count total quizzes of JLPT
 */
function ujPopulateJLPTQuizzesCount() {
	$count = 0;
	foreach ( JLPT_ARRAY as $type ) {
		$postsInJLPT = get_term_by( 'name', $type, 'category' );
		if ( $postsInJLPT ) {
			$count += (int) $postsInJLPT->count;
		}
	}
	echo $count;
}

add_shortcode( 'UJ_count_total_pageview', 'ujPopulateTotalPageView' );
/**
 * Shortcode to count total pageview
 */
function ujPopulateTotalPageView() {
	$ahc_sum_stats = ahcpro_get_summary_statistics();
	echo ahc_pro_NumFormat( $ahc_sum_stats['total']['visits'] ) ?? '68498';
}

add_filter( 'comment_form_defaults', 'ujEditPostCommentButtonLabel' );
/**
 * Edit DWQA Post Comment Button Label to Reply
 *
 * @param $defaults
 *
 * @return mixed
 */
function ujEditPostCommentButtonLabel( $defaults ) {
	$defaults['label_submit'] = __( 'Reply', 'dwqa' );

	return $defaults;
}


add_action( 'wp_print_scripts', 'ujRemoveWCPasswordStrengthMeter', 10 );
/**
 * Remove WC password strength meter
 */
function ujRemoveWCPasswordStrengthMeter() {
	wp_dequeue_script( 'wc-password-strength-meter' );
}

// Code to be placed in functions.php of your theme or a custom plugin file.
//add_filter( 'load_textdomain_mofile', 'load_custom_plugin_translation_file', 10, 2 );
/*
 * Replace 'textdomain' with your plugin's textdomain. e.g. 'woocommerce'.
 * File to be named, for example, yourtranslationfile-en_GB.mo
 * File to be placed, for example, wp-content/lanaguages/textdomain/yourtranslationfile-en_GB.mo
 */
function load_custom_plugin_translation_file( $mofile, $domain ) {
	if ( 'textdomain' === $domain ) {
		$mofile = WP_LANG_DIR . '/textdomain/yourtranslationfile-' . get_locale() . '.mo';
	}

	return $mofile;
}

add_filter( 'dwqa_tinymce_toolbar1', 'removeUploadButtonOnDWQA' );
/**
 * Remove upload media button on DWQA editor
 */
function removeUploadButtonOnDWQA() {
	return 'bold,italic,underline,|,' . 'bullist,numlist,blockquote,|,' . 'link,unlink,|,' . 'code,|,' . 'spellchecker,fullscreen,dwqaCodeEmbed,|,';
}

add_filter( 'learndash_quiz_continue_link', 'returnNextTestUrlOnQuizContinueLink', 10, 2 );
/**
 * Add custom url for Next Test button on Quiz complete screen
 *
 * @param $returnLink
 * @param $redirectUrl
 *
 * @return string
 */
function returnNextTestUrlOnQuizContinueLink( $returnLink, $redirectUrl ) {
	$redirectUrl  = home_url( 'activity' );
	$nextTestLink = get_field( 'next_test_link' );

	if ( ! empty( $nextTestLink ) ) {
		$redirectUrl = $nextTestLink;
		$returnLink  = '<a id="quiz_continue_link" href="' . esc_url( $redirectUrl ) . '">' . __( 'Next Test',
				'learndash' ) . '</a>';
	} else {
		$returnLink = '<a id="quiz_continue_link" href="' . esc_url( $redirectUrl ) . '">' . __( 'View Taken Tests',
				'learndash' ) . '</a>';
	}

	return $returnLink;
}

add_filter( 'TieLabs/Mobile/Header/Components', 'addLanguageSwitcherToMobileComponents', 11, 2 );
/**
 * Add language switcher to mobile component
 *
 * @param $components
 * @param $area
 *
 * @return mixed
 */
function addLanguageSwitcherToMobileComponents( $components, $area ) {
	if ( 'area_2' === $area ) {
		ob_start();
		do_action( 'wpml_add_language_selector' );
		$output = ob_get_contents();
		ob_end_clean();
		$key                = 'language-switcher';
		$components[ $key ] = '<li class="mobile-component_' . $key . ' custom-menu-link">' . $output . '</li>';
	}

	return $components;
}
/**
 * Edit password protected message
 * 
 * @param $output
 *
 * @return mixed
 */
function changeDefaultPasswordProtectedMessage ($output) {

    $default_text = 'This content is password protected. To view it please enter your password below:';
    $default_text_vi = 'Nội dung này được bảo mật. Hãy nhập mật khẩu để xem tiếp:';
    $default_text_ja = 'このコンテンツはパスワードで保護されています。閲覧するには以下にパスワードを入力してください。';
    $new_text = 'This content is password protected, you need a password to see it. 
    <br>Please join our Discord server to get the password: <a target="_blank" href="https://discord.gg/cuQGhGwD9A">Uno Japano Japanese Learning Discord Server</a>
    <br>Or our Telegram Japanese Learning Community: <a target="_blank" href="https://t.me/jlptlearnjapanesewithunojapano/">Uno Japano Telegram Japanese Learning Community</a>
    ';
    $new_text_vi = 'Nội dung này được bảo mật. Hãy nhập mật khẩu để xem tiếp. <br>Mật khẩu được chia sẻ trong cộng đồng Discord của chúng tôi: <a target="_blank" href="https://discord.gg/cuQGhGwD9A">https://discord.gg/cuQGhGwD9A</a>';
    $new_text_ja = 'この資料はパスワードで保護されています。ダウンロードするには、パスワードを入力してください。Passwordは無料でDiscordで共有しますので、どうぞよろしくお願いします。 <br><a target="_blank" href="https://discord.gg/cuQGhGwD9A">https://discord.gg/cuQGhGwD9A</a>';
        $output = str_replace($default_text, $new_text, $output);
        $output = str_replace($default_text_vi, $new_text_vi, $output);
        $output = str_replace($default_text_ja, $new_text_ja, $output);

    return $output;
}
add_filter('the_password_form', 'changeDefaultPasswordProtectedMessage');
