<?php
/**
 * The template for displaying the header
 *
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> 
    <script data-ad-client="ca-pub-8405013366502061" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8405013366502061"
     crossorigin="anonymous"></script>
    <link rel="profile" href="http://gmpg.org/xfn/11" />

    <?php wp_head(); ?>
</head>

<body id="tie-body" <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="background-overlay">

    <div id="tie-container" class="site tie-container">

        <?php do_action( 'TieLabs/before_wrapper' ); ?>

        <div id="tie-wrapper">

<?php

TIELABS_HELPER::get_template_part( 'templates/header/load' );

do_action( 'TieLabs/before_main_content' );

