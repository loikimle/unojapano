<?php
namespace LDQIE;
/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$exports_total = intval( get_option( 'ld_exports_total' ) );
$imports_total = intval( get_option( 'ld_imports_total' ) );
?>
<div id="setting_tabs" class="cs_ld_tabs">
    <form method="post">
        <div class="setting-table-wrapper">
            <div class="tab-content" style="height:200px;">
                <div class="earned-user-credits-wrapper">
                    <div class="columns">
                        <div class="price">
                            <div class="header" style="background-color:#bbb"><?php _e( 'Import Count', 'ldqie' ); ?></div>
                            <div class="grey"><?php echo $imports_total;?></div>
                        </div>
                    </div>
                    <div class="columns">
                        <div class="price">
                            <div class="header" style="background-color:#bbb"><?php _e( 'Export Count', 'ldqie' ); ?></div>
                            <div class="grey"><?php echo $exports_total;?></div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>