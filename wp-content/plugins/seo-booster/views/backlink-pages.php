<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
	<h1>SEO Booster v.<?php echo SEOBOOSTER_VERSION; ?> <?php _e('Backlinks', 'seo-booster');?></h1>

	<div id="sb2fof" class="clearfix clear">
		<div class="lead"><?php _e('Who links to you - Visits from other websites that links to you.', 'seo-booster'); ?></div>
	</div>
		<?php
	if ( seobooster_fs()->is_not_paying() ) {
		include(SEOBOOSTER_PLUGINPATH.'inc/proonly.php');
	}
	?>
	<div id="backlinkstable-target"></div>
	<form id="urls-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$backlink_pages_list_table->search_box( __( 'Search' ), 'seo-booster');
		$backlink_pages_list_table->display();
		 ?>
		<div>
			<p><?php _e('Note - Backlinks you delete from this list will reappear next time someone visits again.','seo-booster'); ?></p>
		</div>

	</form>
	<form id="exportbacklinks" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="action" value="sbp_backlinks_export_csv" />
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'sbp-nonce' ); ?>" />
		<?php
		submit_button( __('Export to .csv','seo-booster'), 'secondary' );
		?>
	</form>
</div>
