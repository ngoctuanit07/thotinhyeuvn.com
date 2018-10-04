<?php
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="wrap">
	<div style="float:right;">
		<a href="https://cleverplugins.com/" target="_blank"><img src='<?php echo plugin_dir_url(__FILE__); ?>images/cleverpluginslogo.png' height="27" width="150" alt="<?php _e('Visit cleverplugins.com','seo-booster'); ?>"></a>
	</div>
	<h1>SEO Booster v.<?php echo SEOBOOSTER_VERSION; ?> <?php _e('Log', 'seo-booster');?></h1>
	<div class="innercont">
		<?php
		global $wpdb, $seobooster2;

		$loglimit = 1000;
		$logtable = $wpdb->prefix . "sb2_log";
		$query    = "SELECT * FROM `$logtable` order by `logtime` DESC   limit $loglimit;";

		if (!empty($_POST) && check_admin_referer('reset_log', 'seobooster2_nonce')) {

			if ((isset($_POST['sb2_log_action'])) && ($_POST['sb2_log_action'] == 'resetlog')) {
				$wpdb->query("TRUNCATE TABLE `$logtable`;");
				$this->log(__("Log emptied manually", 'seo-booster'));
			}

		}
			// PHP VERSION CHECK / WARNING
		include_once 'inc/phpcheck.php';

		if ( seobooster_fs()->is_not_paying() ) {
			include_once('inc/proonly.php');
		}

		?>
		<div id="log-pointer-target"></div>
		<?php

		$logs = $wpdb->get_results($query, ARRAY_A);

		if ($logs) {
	//$time = date('Y-m-d H:i:s ', time());
			$time = current_time( 'mysql' );
			?>

			<table class="wp-list-table widefat logtable">
				<thead>
					<tr>
						<th scope="shortcol" class="shortcol"><?php _e('Time ago', 'seo-booster');?></th>
						<th scope="col"><?php _e('Event', 'seo-booster');?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($logs as $log) {
						$extraclass = '';
						if ($log['prio'] == '0') {
							$extraclass = 'muted';
						}

						if ($log['prio'] == '2') {
							$extraclass = 'error';
						}

						if ($log['prio'] == '3') {
							$extraclass = 'warning';
						}

						if ($log['prio'] == '5') {
							$extraclass = 'info';
						}

						if ($log['prio'] == '10') {
							$extraclass = 'success';
						}

						echo "<tr><td class='shortcol prio-" . $log['prio'] . " $extraclass'>" . human_time_diff(strtotime($log['logtime']), strtotime($time)) . "</td><td class='prio-" . $log['prio'] . " $extraclass'>" . stripslashes($log['log']) . "</td></tr>";
					}
					?>

				</tbody>
				<tfoot>
					<tr>
						<th scope="shortcol" class="shortcol"><?php _e('Time ago', 'seo-booster');?></th>
						<th scope="col"><?php _e('Event', 'seo-booster');?></th>
					</tr>
				</tfoot>
			</table>
			<form method="post">
				<input type="hidden" name="sb2_log_action" value="resetlog">
				<?php
				wp_nonce_field('reset_log', 'seobooster2_nonce');
				submit_button(__('Reset Log', 'seo-booster'), 'secondary');
				?>
			</form>


			<?php
} // if ($logs)
else {
	?>
	<p class="text-info"><?php _e('No activity logged yet.', 'seo-booster');?></p>
	<?php
}

?>

</div><!-- .innercont -->
</div>