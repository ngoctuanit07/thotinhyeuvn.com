<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
	<h1>SEO Booster v.<?php echo SEOBOOSTER_VERSION; ?> <?php _e('404 Errors', 'seo-booster');?></h1>

	<div id="sb2fof" class="clearfix clear">
		<div class="lead">
		</div>
	</div>
	<?php
	if ( seobooster_fs()->is_not_paying() ) {
		include(SEOBOOSTER_PLUGINPATH.'inc/proonly.php');
	}

	$fof_monitoring = get_option('seobooster_fof_monitoring');

	if (!$fof_monitoring) {
		?>
		<div class="notice notice-info"><p><?php _e('404 Error monitoring is turned off, turn on in Settings page.'.'seo-booster'); ?></p></div>
		<?php
	}
	?>
	<div id="404table-target"></div>
	<form id="reset404s" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="action" value="deleteall" />
		<?php
		submit_button( __('Reset 404 Errors','seo-booster'), 'secondary' );
		?>
	</form>
	<form id="urls-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$fof_list_table->search_box( __( 'Search','seo-booster'), 'search-box-id' );
		?>
		<?php $fof_list_table->display() ?>
	</form>

	<form id="reset404s" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="action" value="deleteall" />
		<?php
		submit_button( __('Reset 404 Errors','seo-booster'), 'secondary' );
		?>
	</form>

	<form id="export404s" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="action" value="sbp_404_export_csv" />
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'sbp-nonce' ); ?>" />
		<?php
		submit_button( __('Export to .csv','seo-booster'), 'secondary' );
		?>
	</form>
</div>