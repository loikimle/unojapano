<?php
/**
 * Partial: Page - Extensions.
 *
 * @var object
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div id="ldgr-other-extensions">
    <?php
    if ($extensions) {
        echo "<h2>".__("Check Out Our Other Extensions",WDM_LDGR_TXT_DOMAIN)."</h2>";
    ?>
        <!-- <div> -->
        <ul class="extensions">
        <?php
            $extensions = $extensions->ld_extension;
            $i = 0;
        foreach ($extensions as $extension) {
            if ($i > 7) {
                break;
            }

            // If plugin is already installed, don't list this plugin.
            if (file_exists(WP_PLUGIN_DIR . "/" . $extension->dir . "/" . $extension->plug_file)) {
                continue;
            }

            echo '<li class="product" title="' . __('Click here to know more', WDM_LDGR_TXT_DOMAIN) . '">';
            echo '<a href="'.$extension->link.'" target="_blank">';
            echo '<h3>'.$extension->title.'</h3>';
            if (!empty($extension->image)) {
                echo '<img src="'.$extension->image.'"/>';
            } else {
                // echo '<h3>'.$extension->title.'</h3>';
            }
            //echo '<span class="price">' . $extension->price . '</span>';
            echo '<p>'.$extension->excerpt.'</p>';
            echo '</a>';
            echo '</li>';
            ++$i;
        }
        ?>
        </ul>
    <!-- </div> -->
    <?php
        // If all the extensions have been installed on the site.
    if (0 == $i) {
        ?>
        <h1 class="thank-you"><?php _e('You have all of our extensions. Thank you for your support!', WDM_LDGR_TXT_DOMAIN); ?></h1>
            <?php
    }
    }
    ?>
    <p>
        <a href="https://wisdmlabs.com/learndash-extensions/" target="_blank" class="browse-all">
        <?php _e('Browse all our extensions', WDM_LDGR_TXT_DOMAIN); ?>
        </a>
    </p>
</div>
