<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !current_user_can( 'update_plugins' ) ) {
    wp_die( __( 'You are not allowed to update plugins on this blog.', 'seo-booster' ) );
}
global  $seobooster2, $wpdb ;
$kwtable = $wpdb->prefix . "sb2_kw";
$kwdttable = $wpdb->prefix . "sb2_kwdt";
if ( isset( $_COOKIE['sbp_kw_length'] ) ) {
    $the_length = $_COOKIE['sbp_kw_length'];
}

if ( isset( $_COOKIE['sbp_the_showkws'] ) ) {
    $the_showkws = $_COOKIE['sbp_the_showkws'];
} else {
    $the_showkws = 'all';
}


if ( isset( $_COOKIE['sbp_kw_hideinternal'] ) ) {
    $the_hideinternal = $_COOKIE['sbp_kw_hideinternal'];
} else {
    $the_hideinternal = '';
}

?>
<div class="wrap">
	<div style="float:right;">
		<a href="https://cleverplugins.com/" target="_blank"><img src='<?php 
echo  plugin_dir_url( __FILE__ ) ;
?>images/cleverpluginslogo.png' height="27" width="150" alt="Visit cleverplugins.com"></a>
	</div>
	<h1>SEO Booster v.<?php 
echo  SEOBOOSTER_VERSION ;
?> <?php 
_e( 'Keywords', 'seo-booster' );
?></h1>
	<?php 
// PHP VERSION CHECK / WARNING
include_once 'inc/phpcheck.php';
if ( seobooster_fs()->is_not_paying() ) {
    include SEOBOOSTER_PLUGINPATH . 'inc/proonly.php';
}
?>
	<div class="ajax-loading"><?php 
_e( 'Loading...', 'seo-booster' );
?></div>
	<?php 
if ( isset( $_COOKIE['sbp_kw_length'] ) ) {
    $the_length = $_COOKIE['sbp_kw_length'];
}
if ( isset( $_COOKIE['sbp_the_showkws'] ) ) {
    $the_showkws = $_COOKIE['sbp_the_showkws'];
}
if ( isset( $_COOKIE['sbp_kw_hideinternal'] ) ) {
    $the_hideinternal = $_COOKIE['sbp_kw_hideinternal'];
}
?>
	<p><a href=#" class="quickhelp" data-beacon-article="5935efdc04286305c68cd608"><?php 
_e( 'Quick help', 'seo-booster' );
?></a></p>
	<div id="filtering">
		<h3><?php 
_e( 'Filtering options', 'seo-booster' );
?></h3>
		<input type="checkbox" name="hideinternal" id="hideinternal" <?php 
if ( $the_hideinternal ) {
    echo  'checked="checked"' ;
}
?>><label for="hideinternal"><?php 
_e( 'Hide internal searches', 'seo-booster' );
?></label>

		<form role="form" id="keywordsfilter">
			<label class="radio-inline"><input type="radio" name="showkws" value="all" <?php 
if ( $the_showkws == 'all' ) {
    echo  ' checked' ;
}
?>><?php 
_e( 'Show all keywords', 'seo-booster' );
?></label>
			<label class="radio-inline"><input type="radio" name="showkws" value="knowns" <?php 
if ( $the_showkws == 'knowns' ) {
    echo  ' checked' ;
}
?>><?php 
_e( 'Show only known keywords', 'seo-booster' );
?></label>
			<label class="radio-inline"><input type="radio" name="showkws" value="unknowns" <?php 
if ( $the_showkws == 'unknowns' ) {
    echo  ' checked' ;
}
?>><?php 
_e( 'Show only unknown traffic', 'seo-booster' );
?></label>
		</form>
	</div><!-- #filtering -->

	<div id="datatable-target"></div>
	<table cellpadding="0" cellspacing="0" border="0" class="wp-list-table widefat" id="datatable">
		<thead>
			<tr>
				<th scope="col" role="columnheader" class="header"><?php 
_e( 'Keyword', 'seo-booster' );
?></th>
				<th scope="col" role="columnheader" class="header"><?php 
_e( 'Landing Page', 'seo-booster' );
?></th>
				<th scope="col" role="columnheader" class="header"><?php 
_e( 'Search Engine', 'seo-booster' );
?></th>
				<th scope="col" role="columnheader" class="manage-column header"><?php 
_e( 'Visits', 'seo-booster' );
?></th>
				<th scope="col" role="columnheader" class="header"><?php 
_e( 'First Visit', 'seo-booster' );
?></th>
				<th scope="col" role="columnheader" class="header"><?php 
_e( 'Latest Visit', 'seo-booster' );
?></th>
			</tr>
		</thead>
		<tbody>

		</tbody>
		<tfoot>
			<tr>
				<th scope="col"><?php 
_e( 'Keyword', 'seo-booster' );
?></th>
				<th scope="col"><?php 
_e( 'Landing Page', 'seo-booster' );
?></th>
				<th scope="col"><?php 
_e( 'Search Engine', 'seo-booster' );
?></th>
				<th scope="col"><?php 
_e( 'Visits', 'seo-booster' );
?></th>
				<th scope="col"><?php 
_e( 'First Visit', 'seo-booster' );
?></th>
				<th scope="col"><?php 
_e( 'Latest Visit', 'seo-booster' );
?></th>
			</tr>
		</tfoot>
	</table>


	<hr>

	<?php 
$query = "SELECT engine, COUNT(*) as cnt, SUM(visits) as visits FROM `{$kwtable}` where `ig`='0' AND `kw`<>'#' AND engine<>'Internal Search' GROUP BY `engine` ORDER BY `visits` DESC limit 25;";
$engines = $wpdb->get_results( $query, ARRAY_A );
$enginecount = count( $engines );

if ( $enginecount > 0 ) {
    // todo - fjern (?)
    $position = 0;
    ?>
		<h2><?php 
    _e( 'Top Search Engines', 'seo-booster' );
    ?></h2>
		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th scope="col"><?php 
    _e( 'Position', 'seo-booster' );
    ?></th>
					<th scope="col"><?php 
    _e( 'Search Engine', 'seo-booster' );
    ?></th>
					<th scope="col"><?php 
    _e( 'Different Keywords', 'seo-booster' );
    ?></th>
					<th scope="col"><?php 
    _e( 'Visitors', 'seo-booster' );
    ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
    foreach ( $engines as $eng ) {
        $position++;
        ?>
					<tr>
						<td><?php 
        echo  $position ;
        ?></td>
						<td><?php 
        echo  str_replace( 'www.', '', $eng['engine'] ) ;
        ?></td>
						<td><?php 
        echo  number_format_i18n( $eng['cnt'] ) ;
        ?></td>
						<td><?php 
        echo  number_format_i18n( $eng['visits'] ) ;
        ?></td>
					</tr>
					<?php 
    }
    ?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="col"><?php 
    _e( 'Position', 'seo-booster' );
    ?></th>
					<th scope="col"><?php 
    _e( 'Search Engine', 'seo-booster' );
    ?></th>
					<th scope="col"><?php 
    _e( 'Different Keywords', 'seo-booster' );
    ?></th>
					<th scope="col"><?php 
    _e( 'Visitors', 'seo-booster' );
    ?></th>
				</tr>
			</tfoot>
		</table>
		<?php 
}

?>
</div>