	<?php 
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !current_user_can( 'update_plugins' ) ) {
    wp_die( __( 'You are not allowed to update plugins on this blog.', 'seo-booster' ) );
}
global  $wpdb, $seobooster2 ;
//$seobooster2->send_email_update(365,true); // lars ssss todo

if ( isset( $_POST['page'] ) && $_POST['page'] === 'sb2_settings' ) {
    // todo - verify nonce
    $nonce = $_REQUEST['_wpnonce'];
    
    if ( !wp_verify_nonce( $nonce, 'seobooster_save_settings' ) ) {
        die( 'Security check' );
    } else {
        // Do stuff here.
    }
    
    $rss_key = get_option( 'seobooster_rss_key' );
    
    if ( !$rss_key ) {
        // no rss key exists. Generate one...
        update_option( 'seobooster_rss_key', trim( md5( microtime() . rand() ) ) );
        $rss_key = get_option( 'seobooster_rss_key' );
    }
    
    // todo - merge in to one option
    // todo - to anyone reading this, yes - I need to review the settings API.
    if ( isset( $_POST['seobooster_dynamic_tag_taxonomy'] ) ) {
        update_option( 'seobooster_dynamic_tag_taxonomy', esc_attr( $_POST['seobooster_dynamic_tag_taxonomy'] ) );
    }
    if ( isset( $_POST['seobooster_dynamic_tag_maximum'] ) ) {
        update_option( 'seobooster_dynamic_tag_maximum', esc_attr( $_POST['seobooster_dynamic_tag_maximum'] ) );
    }
    if ( isset( $_POST['seobooster_dynamic_tag_minlength'] ) ) {
        update_option( 'seobooster_dynamic_tag_minlength', esc_attr( $_POST['seobooster_dynamic_tag_minlength'] ) );
    }
    if ( isset( $_POST['seobooster_dynamic_tag_maxlength'] ) ) {
        update_option( 'seobooster_dynamic_tag_maxlength', esc_attr( $_POST['seobooster_dynamic_tag_maxlength'] ) );
    }
    if ( isset( $_POST['seobooster_pagespeed_api_key'] ) ) {
        update_option( 'seobooster_pagespeed_api_key', esc_attr( $_POST['seobooster_pagespeed_api_key'] ) );
    }
    
    if ( isset( $_POST['seobooster_enable_pagespeed'] ) ) {
        update_option( 'seobooster_enable_pagespeed', esc_attr( $_POST['seobooster_enable_pagespeed'] ) );
    } else {
        delete_option( 'seobooster_enable_pagespeed' );
    }
    
    if ( isset( $_POST['seobooster_internal_links_limit'] ) ) {
        update_option( 'seobooster_internal_links_limit', esc_attr( $_POST['seobooster_internal_links_limit'] ) );
    }
    
    if ( isset( $_POST['seobooster_dynamic_tagging_related'] ) ) {
        update_option( 'seobooster_dynamic_tagging_related', esc_attr( $_POST['seobooster_dynamic_tagging_related'] ) );
    } else {
        delete_option( 'seobooster_dynamic_tagging_related' );
    }
    
    
    if ( isset( $_POST['seobooster_ignore_internal_searches'] ) ) {
        update_option( 'seobooster_ignore_internal_searches', esc_attr( $_POST['seobooster_ignore_internal_searches'] ) );
    } else {
        delete_option( 'seobooster_ignore_internal_searches' );
    }
    
    
    if ( isset( $_POST['seobooster_internal_linking'] ) ) {
        update_option( 'seobooster_internal_linking', esc_attr( $_POST['seobooster_internal_linking'] ) );
    } else {
        delete_option( 'seobooster_internal_linking' );
    }
    
    
    if ( isset( $_POST['seobooster_use_yoast_focus_kw'] ) ) {
        update_option( 'seobooster_use_yoast_focus_kw', esc_attr( $_POST['seobooster_use_yoast_focus_kw'] ) );
    } else {
        delete_option( 'seobooster_use_yoast_focus_kw' );
    }
    
    
    if ( isset( $_POST['seobooster_use_sb_kwdata'] ) ) {
        update_option( 'seobooster_use_sb_kwdata', esc_attr( $_POST['seobooster_use_sb_kwdata'] ) );
    } else {
        delete_option( 'seobooster_use_sb_kwdata' );
    }
    
    
    if ( isset( $_POST['seobooster_delete_deactivate'] ) ) {
        update_option( 'seobooster_delete_deactivate', esc_attr( $_POST['seobooster_delete_deactivate'] ) );
    } else {
        delete_option( 'seobooster_delete_deactivate' );
    }
    
    
    if ( isset( $_POST['seobooster_dynamic_tagging'] ) ) {
        update_option( 'seobooster_dynamic_tagging', esc_attr( $_POST['seobooster_dynamic_tagging'] ) );
    } else {
        delete_option( 'seobooster_dynamic_tagging' );
    }
    
    
    if ( isset( $_POST['seobooster_dynamic_tag_assigncpts'] ) ) {
        update_option( 'seobooster_dynamic_tag_assigncpts', esc_attr( $_POST['seobooster_dynamic_tag_assigncpts'] ) );
    } else {
        delete_option( 'seobooster_dynamic_tag_assigncpts' );
    }
    
    
    if ( isset( $_POST['seobooster_weekly_email'] ) ) {
        update_option( 'seobooster_weekly_email', esc_attr( $_POST['seobooster_weekly_email'] ) );
    } else {
        delete_option( 'seobooster_weekly_email' );
    }
    
    
    if ( isset( $_POST['seobooster_weekly_email_recipient'] ) ) {
        update_option( 'seobooster_weekly_email_recipient', esc_attr( $_POST['seobooster_weekly_email_recipient'] ) );
    } else {
        delete_option( 'seobooster_weekly_email_recipient' );
    }
    
    
    if ( isset( $_POST['seobooster_debug_logging'] ) ) {
        update_option( 'seobooster_debug_logging', esc_attr( $_POST['seobooster_debug_logging'] ) );
    } else {
        delete_option( 'seobooster_debug_logging' );
    }
    
    
    if ( isset( $_POST['seobooster_fof_monitoring'] ) ) {
        update_option( 'seobooster_fof_monitoring', esc_attr( $_POST['seobooster_fof_monitoring'] ) );
    } else {
        delete_option( 'seobooster_fof_monitoring' );
    }
    
    if ( isset( $_POST['seobooster_ignorelist'] ) ) {
        update_option( 'seobooster_ignorelist', esc_attr( stripslashes( $_POST['seobooster_ignorelist'] ) ) );
    }
    // if ( seobooster_fs()->is__premium_only() )
}

// *********** MIGRATING OLD DATA

if ( isset( $_POST['submit_migrate'] ) ) {
    // start migrate old data process..
    $oldtable = $wpdb->prefix . "seobooster";
    $newtable = $wpdb->prefix . "sb2_kw";
    $oldquery = "SELECT * FROM {$oldtable} order by RAND() limit 10000;";
    $oldies = $wpdb->get_results( $oldquery );
    
    if ( $oldies ) {
        $oldcount = 0;
        foreach ( $oldies as $old ) {
            $oldpermalink = get_permalink( $old->PostId );
            
            if ( $oldpermalink ) {
                $parsedurl = parse_url( $oldpermalink );
                $oldpermalink = $parsedurl['path'];
                $ref = $old->SeRef;
                $newkw = array(
                    'id'         => NULL,
                    'ig'         => '0',
                    'kw'         => $old->SeQuery,
                    'lp'         => $oldpermalink,
                    'visits'     => $old->SeHits,
                    'firstvisit' => $old->SeDate,
                    'lastvisit'  => '0000-00-00 00:00:00',
                    'engine'     => $old->SeDomain,
                );
                $wpdb->insert( $newtable, $newkw );
                $lastid = $wpdb->insert_id;
                
                if ( $lastid ) {
                    $wpdb->query( "DELETE FROM `{$oldtable}` WHERE `id` = '" . $old->id . "' limit 1;" );
                    $oldcount++;
                }
            
            } else {
                //post/page not found, perhaps deleted - lets remove from list
                $wpdb->query( "DELETE FROM `{$oldtable}` WHERE `id` = '" . $old->id . "' limit 1;" );
            }
        
        }
        if ( $oldcount > 0 ) {
            $this->log( sprintf( __( '%s old keywords have been migrated.', 'seo-booster' ), number_format_i18n( $oldcount ) ) );
        }
    } else {
        //	$wpdb->query("DROP TABLE IF EXISTS $oldtable"); // we cannot delete until we are certain all keywords have migrated
    }

}

// END *********** MIGRATING OLD DATA
// TODO - CHECK NONCE

if ( isset( $_POST['delete_old_kws'] ) && $_POST['delete_old_kws'] ) {
    $oldkws = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb2_kw WHERE lastvisit < DATE_SUB(NOW(), INTERVAL 90 DAY) LIMIT 10000;" );
    if ( $oldkws ) {
        foreach ( $oldkws as $oldkw ) {
            $query = "DELETE FROM {$wpdb->prefix}sb2_kw where id=" . $oldkw->id . " LIMIT 1;";
            $wpdb->query( $query );
        }
    }
}

// TODO - CHECK NONCE

if ( isset( $_POST['submit_dbupdates'] ) && $_POST['submit_dbupdates'] ) {
    // start migrate old data process..
    $this->seobooster_activate( false );
    // Running only on current installation.
    $this->do_seobooster_maintenance();
}

// Resets and starts the guided tour again.

if ( isset( $_POST['reset_guided_tours'] ) && $_POST['reset_guided_tours'] ) {
    $pointer = 'sbp_tour_pointer';
    $dismissed = array_filter( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) );
    // finds and unsets the pointer if it has been set.
    if ( ($key = array_search( $pointer, $dismissed )) !== false ) {
        unset( $dismissed[$key] );
    }
    update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );
}

global  $seobooster_fs ;
?>
	<div class="wrap">
		<div style="float:right;">
			<a href="https://cleverplugins.com/" target="_blank"><img src='<?php 
echo  plugin_dir_url( __FILE__ ) ;
?>images/cleverpluginslogo.png' height="27" width="150" alt="<?php 
_e( 'Visit cleverplugins.com', 'seo-booster' );
?>"></a>
		</div>
		<h1>SEO Booster v.<?php 
echo  SEOBOOSTER_VERSION ;
?> <?php 
_e( 'Settings', 'seo-booster' );
?></h1>
		<?php 
$dynamic_keywords = get_option( 'seobooster_dynamic_keywords' );
$ignore_internal_searches = get_option( 'seobooster_ignore_internal_searches' );
$dynamic_tagging = get_option( 'seobooster_dynamic_tagging' );
$dynamic_tag_tax = get_option( 'seobooster_dynamic_tag_taxonomy' );
$dynamic_tag_assigncpts = get_option( 'seobooster_dynamic_tag_assigncpts' );
$dynamic_tag_max = get_option( 'seobooster_dynamic_tag_maximum' );
$dynamic_tag_minlength = get_option( 'seobooster_dynamic_tag_minlength' );
$dynamic_tag_maxlength = get_option( 'seobooster_dynamic_tag_maxlength' );
$dynamic_tagging_related = get_option( 'seobooster_dynamic_tagging_related' );
$seobooster_weekly_email = get_option( 'seobooster_weekly_email' );
$seobooster_weekly_email_recipient = get_option( 'seobooster_weekly_email_recipient' );
$debug_logging = get_option( 'seobooster_debug_logging' );
$fof_monitoring = get_option( 'seobooster_fof_monitoring' );
$internal_linking = get_option( 'seobooster_internal_linking' );
$seobooster_internal_links_limit = get_option( 'seobooster_internal_links_limit' );
$use_yoast_focus_kw = get_option( 'seobooster_use_yoast_focus_kw' );
$seobooster_use_sb_kwdata = get_option( 'seobooster_use_sb_kwdata' );
$seobooster_delete_deactivate = get_option( 'seobooster_delete_deactivate' );
$ignorelist = get_option( 'seobooster_ignorelist' );
$ignorelist = preg_replace( "/\r|\n/", ',', $ignorelist );
$ignorelist = preg_replace( '/,+/', ',', $ignorelist );
$ignorelist = strtolower( $ignorelist );
?>
		<div class="wrap">
			<?php 
// PHP VERSION CHECK / WARNING
include_once 'inc/phpcheck.php';
if ( seobooster_fs()->is_not_paying() ) {
    include_once 'inc/proonly.php';
}

if ( !$seobooster_fs->is_registered() && !$seobooster_fs->is_pending_activation() ) {
    // Website is not registered, so...
    ?>
				<div id="sbpoptin">
					<?php 
    echo  sprintf( __( 'Never miss an important update. Opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking. <a href="%s">Click here</a>', 'seo-booster' ), $seobooster_fs->get_reconnect_url() ) ;
    ?>
				</div>
				<?php 
}


if ( $seobooster_fs->is_pending_activation() && !$seobooster_fs->is_registered() ) {
    ?>
					<div id="sbpoptin">
						<?php 
    _e( 'Thank you for activating, please check your email to complete the process.', 'seo-booster' );
    ?>
					</div>
					<?php 
}

?>
				<div class="notice notice-info">
					<p><?php 
printf( __( 'To get help with the settings check out the <a href="%s" target="_blank">Knowledgebase</a>.', 'seo-booster' ), 'https://cleverplugins.helpscoutdocs.com/' );
?></p>
			</div>
				<?php 
// Old - Pre v.3 data migration
$oldtable = $wpdb->prefix . "seobooster";

if ( $wpdb->get_var( "SHOW TABLES LIKE '{$oldtable}'" ) == $oldtable ) {
    $oldcount = $wpdb->get_var( "SELECT COUNT(*) FROM {$oldtable};" );
    
    if ( $oldcount > 0 ) {
        ?>
						<div id="migrateolddata">
							<h2><?php 
        _e( 'Migrate old database tables', 'seo-booster' );
        ?></h2>
							<?php 
        printf( __( '%s keyword data from SEO Booster PRO v.1 found!', 'seo-booster' ), number_format_i18n( $oldcount ) );
        ?>
							<p><?php 
        _e( 'The button below migrates the old data to the new structure. I suggest taking a backup before doing this.', 'seo-booster' );
        ?></p>

							<p><em><?php 
        _e( 'Conversion is done in batches of 10.000 and not all data can be converted.', 'seo-booster' );
        ?></em></p>
							<form method="post" id="migratedb_form">
								<?php 
        submit_button( __( 'Migrate old data', 'seo-booster' ), 'primary', 'submit_migrate' );
        wp_nonce_field( 'seobooster_save_settings' );
        ?>
							</form>
						</div>
						<?php 
    }
    
    // oldcount>0
}

// if($wpdb->get_var("SHOW TABLES LIKE '$oldtable'") != $oldtable)
?>




				<form method="post" id="seobooster_settings_form">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th colspan="2">
									<h2><?php 
_e( 'Auto link keywords', 'seo-booster' );
?></h2>
									<a href=#" class="quickhelp" data-beacon-article="5a512dad2c7d3a194367ea57"><?php 
_e( 'Quick help', 'seo-booster' );
?></a>
								</th>
							</tr>

							<tr valign="top">
								<th scope="row" valign="top">
									<?php 
_e( 'Enable', 'seo-booster' );
?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php 
_e( 'Change keywords in text to links to relevant pages on your site.', 'seo-booster' );
?></span></legend>
										<label for="seobooster_internal_linking">
											<input type="checkbox" id="seobooster_internal_linking" name="seobooster_internal_linking" value="on" <?php 
if ( $internal_linking ) {
    echo  " checked='checked'" ;
}
?>/>
											<p class="description"><?php 
_e( 'Change keywords in text to links to relevant pages on your site.', 'seo-booster' );
?></p>

										</label>
									</fieldset>
								</td>
							</tr>

							<tr valign="top" class="linkingrelated <?php 
if ( !$internal_linking ) {
    echo  " muted" ;
}
?>">


							<th scope="row" valign="top">
								<?php 
_e( 'SEO Booster', 'seo-booster' );
?>
							</th>
							<td>

								<fieldset>
									<legend class="screen-reader-text"><span><?php 
_e( 'Use keywords from SEO Booster.', 'seo-booster' );
?></span></legend>
									<label for="seobooster_use_sb_kwdata">
										<input type="checkbox" id="seobooster_use_sb_kwdata" name="seobooster_use_sb_kwdata" value="on" <?php 
if ( $internal_linking ) {
    echo  " checked='checked'" ;
}
?>/>
										<?php 
_e( 'Use keywords from SEO Booster.', 'seo-booster' );
?>
									</label>

									<p class="description"><?php 
_e( 'Use the keyword data gathered by SEO Booster to generate internal links automatically.', 'seo-booster' );
?></p>
								</fieldset>
							</td>
						</tr>




						<?php 

if ( !defined( 'WPSEO_VERSION' ) ) {
    ?>
							<tr valign="top" class="linkingrelated<?php 
    if ( !$internal_linking ) {
        echo  " muted" ;
    }
    ?>">
								<th scope="row" valign="top">
									<?php 
    _e( 'Yoast SEO', 'seo-booster' );
    ?>
								</th>
								<td>
									<p><?php 
    _e( 'Yoast WordPress SEO Plugin either not installed or activated.', 'seo-booster' );
    ?></p>
								</td>
							</tr>
							<?php 
} else {
    ?>
							<tr valign="top"  class="linkingrelated <?php 
    if ( !$internal_linking ) {
        echo  " muted" ;
    }
    ?>">


							<th scope="row" valign="top">
								<?php 
    _e( 'Yoast SEO', 'seo-booster' );
    ?>
							</th>
							<td>


								<fieldset>
									<legend class="screen-reader-text"><span><?php 
    _e( 'Use Focus Keyword from Yoast SEO plugin', 'seo-booster' );
    ?></span></legend>
									<label for="seobooster_use_yoast_focus_kw">
										<input type="checkbox" id="seobooster_use_yoast_focus_kw" name="seobooster_use_yoast_focus_kw" value="on" <?php 
    if ( $use_yoast_focus_kw ) {
        echo  " checked='checked'" ;
    }
    ?> />
										<?php 
    _e( 'Use Focus Keyword from Yoast SEO plugin', 'seo-booster' );
    ?>
									</label>
									<p class="description"><?php 
    _e( 'Use the "Focus Keyword" on individual pages as a source for creating the internal links.', 'seo-booster' );
    ?></p>
								</fieldset>

							</td>
						</tr>
						<?php 
}

?>
				</tr>
				<tr valign="top" class="linkingrelated <?php 
if ( !$internal_linking ) {
    echo  " muted" ;
}
?>">
				<th scope="row" valign="top">
					<?php 
_e( 'Maximum keywords->links', 'seo-booster' );
?>
				</th>
				<td>
					<input type="number" id="seobooster_internal_links_limit" name="seobooster_internal_links_limit" value="<?php 
echo  $seobooster_internal_links_limit ;
?>" class="small-text" step="1" min="1" max="20" />
					<p class="description"><?php 
_e( 'Maximum amount of keyword to link replacements. This limit not include existing links in the content.', 'seo-booster' );
?></p>

				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<?php 
_e( 'Filter out keywords', 'seo-booster' );
?>
				</th>
				<td>
					<textarea id="seobooster_ignorelist" name="seobooster_ignorelist" class="large-text code"><?php 
echo  $ignorelist ;
?></textarea>
					<p class="description"><?php 
_e( 'Enter keywords to be filtered out. Separate keywords with comma or linebreak.', 'seo-booster' );
?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" valign="top">
				<?php 
_e( 'Ignore internal searches', 'seo-booster' );
?>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php 
_e( 'Do not monitor regular searches on your website.', 'seo-booster' );
?></span></legend>
					<label for="seobooster_ignore_internal_searches">
						<input type="checkbox" id="seobooster_ignore_internal_searches" name="seobooster_ignore_internal_searches" value="on" <?php 
if ( $ignore_internal_searches ) {
    echo  " checked='checked'" ;
}
?>/>
						<?php 
_e( 'Do not monitor searches made directly on your site', 'seo-booster' );
?>
					</label>
				<p><a href="#" class="quickhelp" data-beacon-article="5ad556770428630750927b8e"><?php 
_e( 'Quick help', 'seo-booster' );
?></a></p>
					<p class="description"><?php 
_e( 'People searching internally on your site and clicking the results can also help gather keyword information. Turn this on to ignore.', 'seo-booster' );
?></p>

				</fieldset>

			</td>
		</tr>





		<tr valign="top">
			<th colspan="2">
				<h2><?php 
_e( 'Automatic Tagging', 'seo-booster' );
?></h2>
				<p><a href="#" class="quickhelp" data-beacon-article="5ae26a0704286328a4149c9b"><?php 
_e( 'Quick help', 'seo-booster' );
?></a></p>
			</th>
		</tr>

		<tr valign="top">
			<th scope="row" valign="top">
				<?php 
_e( 'Turn on automatic tagging:', 'seo-booster' );
?>
			</th>
			<td>
				<input type="checkbox" id="seobooster_dynamic_tagging" name="seobooster_dynamic_tagging" value="on" <?php 
if ( $dynamic_tagging ) {
    echo  " checked='checked'" ;
}
?>/>
				<p class="description"><label for="seobooster_dynamic_tagging"><?php 
_e( 'Tags can be created automatically using the search terms that visitors use. This works with both internal searches and visitors from search engines.', 'seo-booster' );
?></label></p>

			</td>
		</tr>

		<tr valign="top" class="taggingrelated <?php 
if ( !$dynamic_tagging ) {
    echo  " muted" ;
}
?>">
			<th scope="row" valign="top">
				<?php 
_e( 'Tag Related Posts:', 'seo-booster' );
?>
			</th>
			<td>
				<input type="checkbox" id="seobooster_dynamic_tagging_related" name="seobooster_dynamic_tagging_related" value="on" <?php 
if ( $dynamic_tagging_related ) {
    echo  " checked='checked'" ;
}
?>/>
				<p class="description"><label for="seobooster_dynamic_tagging_related"><?php 
_e( 'SEO Booster will attempt to find related posts and also tag with the search term.', 'seo-booster' );
?></label></p>

			</td>
		</tr>

		<tr valign="top" class="taggingrelated <?php 
if ( !$dynamic_tagging ) {
    echo  " muted" ;
}
?>">
		<th scope="row" valign="top">
			<?php 
_e( 'Choose Taxonomy:', 'seo-booster' );
?>
		</th>
		<td>
			<?php 
$customtaxonomies = get_taxonomies( array(
    'public'   => true,
    '_builtin' => false,
), 'objects', 'and' );
$builtintaxonomies = get_taxonomies( array(
    'public'   => true,
    '_builtin' => true,
), 'objects', 'and' );
$taxonomies = array_merge( $builtintaxonomies, $customtaxonomies );
?>
			<select name="seobooster_dynamic_tag_taxonomy" id="seobooster_dynamic_tag_taxonomy">
				<?php 
if ( $taxonomies ) {
    foreach ( $taxonomies as $name => $atax ) {
        echo  "<option name='{$name}' value='{$name}'" ;
        if ( $dynamic_tag_tax == $name ) {
            echo  " selected='selected' " ;
        }
        echo  ">" . $atax->labels->name . " (" . $name . ")</option>" ;
    }
}
?>
			</select>


			<p class="description"><?php 
_e( "If you use Custom Post Types on your site, they might not be configured to use the taxonomy you have chosen above.", 'seo-booster' );
?></p>
			<p class="description"><?php 
_e( "By clicking this checkbox, the taxonomy is properly 'assigned'.", 'seo-booster' );
?></p>
		</td>
	</tr>
	<tr valign="top" class="taggingrelated <?php 
if ( !$dynamic_tagging ) {
    echo  " muted" ;
}
?>">
	<th scope="row" valign="top">
		<?php 
_e( 'Assign Custom Post Types:', 'seo-booster' );
?>
	</th>
	<td>
		<?php 
$cpts = get_post_types( array(
    'public'             => true,
    'publicly_queryable' => true,
), 'names', 'and' );
$cpts = array_merge( $cpts, array( 'page' ) );
$cptlist = implode( $cpts, ', ' );
$cptlist = trim( $cptlist, ',' );
?>
		<input type="checkbox" id="seobooster_dynamic_tag_assigncpts" name="seobooster_dynamic_tag_assigncpts" value="on" <?php 
if ( $dynamic_tag_assigncpts ) {
    echo  " checked='checked'" ;
}
?>/>
		<p class="description"><label for="seobooster_dynamic_tag_assigncpts"><?php 
_e( 'Turning this on ensures that the chosen taxonomy will be used on all custom post types listed below.', 'seo-booster' );
?></label></p>
		<p class="description"><?php 
_e( 'Custom Post Types', 'seo-booster' );
?>: <strong><?php 
echo  $cptlist ;
?></strong>.</p>
	</td>
</tr>
<tr valign="top" class="taggingrelated <?php 
if ( !$dynamic_tagging ) {
    echo  " muted" ;
}
?>">
<th scope="row" valign="top">
	<?php 
_e( 'Minimum length of term', 'seo-booster' );
?>
</th>
<td>
	<input type="number" id="seobooster_dynamic_tag_minlength" name="seobooster_dynamic_tag_minlength" value="<?php 
echo  $dynamic_tag_minlength ;
?>" class="small-text" step="1" min="1" />
	<p class="description"><?php 
_e( 'If the number of characters in the query is less than the specified number, no tag (taxonomy term) will be created.', 'seo-booster' );
?></p>

</td>
</tr>

</tr>
<tr valign="top" class="taggingrelated <?php 
if ( !$dynamic_tagging ) {
    echo  " muted" ;
}
?>">
<th scope="row" valign="top">
	<?php 
_e( 'Maximum length of term', 'seo-booster' );
?>
</th>
<td>
	<input type="number" id="seobooster_dynamic_tag_maxlength" name="seobooster_dynamic_tag_maxlength" value="<?php 
echo  $dynamic_tag_maxlength ;
?>" class="small-text" step="1" min="1" />
	<p class="description"><label for="seobooster_dynamic_tag_maxlength"><?php 
_e( 'If the number of characters in the query is MORE than the specified number, no tag (taxonomy term) will be created.', 'seo-booster' );
?></label></p>
</td>
</tr>
<tr valign="top" class="taggingrelated <?php 
if ( !$dynamic_tagging ) {
    echo  " muted" ;
}
?>">
<th scope="row" valign="top">
	<?php 
_e( 'Maximum tags per post', 'seo-booster' );
?>
</th>
<td>
	<input type="number" id="seobooster_dynamic_tag_maximum" name="seobooster_dynamic_tag_maximum" value="<?php 
echo  $dynamic_tag_max ;
?>" class="small-text" step="1" min="1" />
</td>
</tr>

<?php 
// premium only
?>


<tr valign="top">
	<th colspan="2">
		<h2><?php 
_e( 'Weekly Email Reports', 'seo-booster' );
?></h2>
		<p><a href="#" class="quickhelp" data-beacon-article="5ae26a4204286328a4149ca0"><?php 
_e( 'Quick help', 'seo-booster' );
?></a></p>
	</th>
</tr>
<tr valign="top">
	<th scope="row" valign="top">
		<?php 
_e( 'Weekly emails:', 'seo-booster' );
?>
	</th>
	<td>
		<input type="checkbox" id="seobooster_weekly_email" name="seobooster_weekly_email" value="on" <?php 
if ( $seobooster_weekly_email == 'on' ) {
    echo  " checked='checked'" ;
}
?>/>
		<p class="description"><label for="seobooster_weekly_email"><?php 
_e( 'Send a weekly email with information of keyword stats, new backlinks and 404 errors detected the past week.', 'seo-booster' );
?></label></p>

	</td>
</tr>


<th scope="row" valign="top">
	<?php 
_e( 'Email recipient', 'seo-booster' );
?>
</th>
<td>
	<input type="text" id="seobooster_weekly_email_recipient" name="seobooster_weekly_email_recipient" value="<?php 
echo  $seobooster_weekly_email_recipient ;
?>" class="regular-text">
	<p class="description"><label for="seobooster_weekly_email_recipient"><?php 
_e( 'Email recipent.', 'seo-booster' );
?></label></p>
</td>
</tr>


<tr valign="top">
	<th scope="row" valign="top">
		<?php 
_e( 'DEBUG Logging:', 'seo-booster' );
?>
	</th>
	<td>
		<input type="checkbox" id="seobooster_debug_logging" name="seobooster_debug_logging" value="on" <?php 
if ( $debug_logging == 'on' ) {
    echo  " checked='checked'" ;
}
?>/>
		<p class="description"><label for="seobooster_debug_logging"><?php 
_e( 'If turned on, the log will have debug information that can be helpful for finding errors or configuration issues.', 'seo-booster' );
?></label></p>
	</td>
</tr>


<tr valign="top">
	<th scope="row" valign="top">
		<p><?php 
_e( '404 Errors', 'seo-booster' );
?></p>

	</th>
	<td>
		<input type="checkbox" id="seobooster_fof_monitoring" name="seobooster_fof_monitoring" value="on" <?php 
if ( $fof_monitoring == 'on' ) {
    echo  " checked='checked'" ;
}
?>/>
		<a href="#" class="quickhelp" data-beacon-article="592f86542c7d3a074e8af8d8"><?php 
_e( 'Quick help', 'seo-booster' );
?></a>
		<p class="description"><label for="seobooster_fof_monitoring"><?php 
_e( 'If turned on, 404 errors will be monitored.', 'seo-booster' );
?></label></p>

	</td>
</tr>





<?php 
// premium only
?>

<tr valign="top">
	<th colspan="2">
		<hr>
	</th>
</tr>
<tr valign="top">
	<th scope="row" valign="top">
		<?php 
_e( 'Delete data on deactivate:', 'seo-booster' );
?>
	</th>
	<td>
		<input type="checkbox" id="seobooster_delete_deactivate" name="seobooster_delete_deactivate" value="on" <?php 
if ( $seobooster_delete_deactivate == 'on' ) {
    echo  " checked='checked'" ;
}
?>/>
		<p class="description"><label for="seobooster_delete_deactivate"><?php 
_e( 'Turn this on to delete all data when deactivating the plugin. This cannot be undone.', 'seo-booster' );
?></label></p>
	</td>
</tr>


<tr>
	<td colspan="2">
		<input type="hidden" name="page" value="sb2_settings">
		<?php 
wp_nonce_field( 'seobooster_save_settings' );
?>
		<?php 
submit_button();
?>
	</td>
</tr>
</tbody>
</table>
</form>

<hr>
<h3><?php 
_e( 'Tools', 'seo-booster' );
?></h3>
<form method="post">
	<?php 
wp_nonce_field( 'seobooster_do_actions' );
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" valign="top">
					<?php 
_e( 'Database Update', 'seo-booster' );
?>
				</th>
				<td>
					<?php 
submit_button( __( 'Run Update Database', 'seo-booster' ), 'secondary', 'submit_dbupdates' );
?>
					<label class="description" for="submit"><?php 
_e( 'If you need to manually run the database updates. No need to use unless directed by support.', 'seo-booster' );
?></label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" valign="top">
					<?php 
_e( 'Guided Tour', 'seo-booster' );
?>

				</th>
				<td>
					<?php 
submit_button( __( 'See Guided Tour', 'seo-booster' ), 'secondary', 'reset_guided_tours' );
?>
					<a href="#" class="quickhelp" data-beacon-article="5a5129cb2c7d3a194367ea4e"><?php 
_e( 'Quick help', 'seo-booster' );
?></a>
					<p class="description" for="reset_guided_tours"><?php 
_e( 'Click to start the Guided Tour again.', 'seo-booster' );
?></p>
				</td>
			</tr>
		</tbody>
	</table>
</form>
</div>