<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
	<h1>SEO Booster v.<?php echo SEOBOOSTER_VERSION; ?> - <?php _e('Lost Traffic', 'seo-booster');?></h1>
	<div id="sb2forgotten" class="clearfix clear">
		<div class="lead">

			<p class="stat headline"><?php _e('Pages that have not received search engine traffic for a while.','seo-booster'); ?></p>
			<?php
			global $seobooster2;
			?>
		</div>
	</div>
	<?php
	if ( seobooster_fs()->is_not_paying() ) {
		include(SEOBOOSTER_PLUGINPATH.'inc/proonly.php');
	}
	?>
	<div id="wp_pointer-target"></div>
	<form id="urls-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $test_list_table->display() ?>
	</form>

</div>
