<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
	<h1>SEO Booster v.<?php echo SEOBOOSTER_VERSION; ?> <?php _e('Crawled Pages', 'seo-booster');?></h1>
	<div id="sb2fof" class="clearfix clear">
		<div class="lead">
			<p><?php _e('See which of your pages have been crawled and by which crawlers.','seo-booster'); ?></p>
		</div>
	</div>
	<form id="urls-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

		<?php
		$crawled_pages_list_table->search_box( __( 'Search','seo-booster'), 'search-box-id' );
		?>

		<?php $crawled_pages_list_table->display() ?>

	</form>
	<?php
	if ( seobooster_fs()->is_not_paying() ) {
		include(SEOBOOSTER_PLUGINPATH.'inc/proonly.php');
	}
	?>
	<form id="resetcrawls" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="action" value="deleteall" />
		<?php
		submit_button( __('Reset Crawl data','seo-booster'), 'secondary' );
		?>
	</form>

</div>
