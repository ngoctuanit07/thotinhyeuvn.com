<?php

/**
* Plugin Name: SEO Booster
* Version: 3.3.32
* Plugin URI: https://cleverplugins.com/
* Description: Monitor keywords from hundreds of search engines + 404 errors tracking + backlink collecting  and verification (PRO) + Crosslinking widgets and template functions.
* Author: cleverplugins.com
* Author URI: https://cleverplugins.com
* Text Domain: seo-booster
* Domain Path: /languages

This plugin uses the following 3rd party MIT licensed projects - Thank you for making other developer lives easier :-)

* Country flags Copyright (c) 2017 Go Squared Ltd. http://www.gosquared.com/ - https://github.com/gosquared/flags. MIT license.

* datatables.net library for the keywords ajax interface. MIT license.

* Jose Solorzano (https://sourceforge.net/projects/php-html/) for the Simple HTML DOM parser.

* Lazy Load plugin from Mika Tuupola - http://www.appelsiini.net/projects/lazyload. MIT license.

* Thank you Matt van Andel for the WP List Table Example class - https://github.com/Veraxus/wp-list-table-example/blob/master/list-table-example.php

* The email template is brought to you by EmailOctopus https://emailoctopus.com/ - email marketing for less, via Amazon SES. MIT License

* https://github.com/themefoundation/custom-meta-box-template/blob/master/custom-meta-box-template.php

Copyright 2008-2018 cleverplugins.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

TODO - Hvad skete der med $searchtrafficbyday på dashboard siden?
TODO - One thing I WOULD pay for - if there was a suggestion list of keywords. I mean, I'm writing a new post and I'm trying to remember 'did I use Mennonite dress or Mennonites dress?' - it would be nice if I could easily see the keywords I had already set in other posts.

TODO - Moz API integration for backlinks and/or audit - https://moz.com/help/guides/moz-api/mozscape/api-reference/link-metrics og https://moz.com/help/guides/moz-api/mozscape/anatomy-of-a-mozscape-api-call

TODO -

TODO - 404 errors - sorter efter sidste visit?
TODO - SEOPress integration - analysis target
TODO - filtrer backlinks fra - https://wordpress.org/support/topic/problem-with-backlinks/
TODO - Hvilke sites er installeret og aktive?

TODO - I only want the system to link to pages, not posts, because in our site's configuration (abnicholas.com) the POSTS are only for registered agents, not the public. Pages are public. ("Dan")

TODO - Optimer seobooster_generateignorelist()
TODO - Brug samme table for alle sites, men indsæt blog_id istedet i tabellen, så er det lettere at flytte data sammen i fremtiden.
TODO - future allow for email customization
TODO - export log for debugging - perhaps extend table size
TODO - custom ignore liste
TODO - Slet individuelle keywords fra listen - måske genskabe listen med AJAX i WP tables?
TODO - ignorelink() check both referer (as is now) and also the currenturl (if parsed)
TODO - Highlight / filter keywords marked as ignored i AJAX kw table
TODO - Delete/remove keywords from AJAX table
TODO - Ignore referrer /
TODO - "how to remove keyword Not Available" - https://wordpress.org/support/topic/how-to-remove-keyword-not-available/
*/
// don't load directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'seobooster_fs' ) ) {
    define( 'SEOBOOSTER_VERSION', '3.3.32' );
    define( 'SEOBOOSTER_PLUGINPATH', plugin_dir_path( __FILE__ ) );
    define( 'SEOBOOSTER_PLUGINURL', plugin_dir_url( __FILE__ ) );
    define( 'SEOBOOSTER_DB_VERSION', '3.3.16' );
    // Last time database was updated
    // Create a helper function for easy SDK access.
    function seobooster_fs()
    {
        global  $seobooster_fs ;
        
        if ( !isset( $seobooster_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $seobooster_fs = fs_dynamic_init( array(
                'id'              => '987',
                'slug'            => 'seo-booster',
                'type'            => 'plugin',
                'public_key'      => 'pk_a58b7605ac6e9e90cd7bd9458cfbc',
                'is_premium'      => false,
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'trial'           => array(
                'days'               => 30,
                'is_require_payment' => true,
            ),
                'has_affiliation' => 'all',
                'menu'            => array(
                'slug'    => 'sb2_dashboard',
                'support' => false,
            ),
                'is_live'         => true,
            ) );
        }
        
        return $seobooster_fs;
    }
    
    // Init Freemius.
    seobooster_fs();
    // Signal that SDK was initiated.
    do_action( 'seobooster_fs_loaded' );
    seobooster_fs()->add_filter( 'handle_gdpr_admin_notice', '__return_true' );
    seobooster_fs()->add_action( 'after_uninstall', 'seobooster_do_after_uninstall' );
    /*
    if ( file_exists( plugin_dir_path(__FILE__) . '/vendor/autoload.php' ) ) {
    	require plugin_dir_path(__FILE__) . '/vendor/autoload.php';
    }
    */
    add_filter( 'whip_hosting_page_url_wordpress', '__return_true' );
    function seobooster_do_after_uninstall()
    {
        wp_clear_scheduled_hook( 'sbp_dailymaintenance' );
        wp_clear_scheduled_hook( 'sbp_checkbacklink' );
        wp_clear_scheduled_hook( 'sbp_email_update' );
        wp_clear_scheduled_hook( 'sbp_crawl_internal' );
    }
    
    
    if ( !class_exists( "seobooster2" ) ) {
        class seobooster2
        {
            var  $localizationDomain = "seobooster2" ;
            function __construct()
            {
                add_action( 'do_action_sbp_email_update', array( $this, 'send_email_update' ) );
                add_action( 'sbp_email_update', array( &$this, "send_email_update" ) );
                add_action( 'sbp_dailymaintenance', array( &$this, "do_seobooster_maintenance" ) );
                //add_action('admin_notices', array(&$this, 'do_admin_notices')); // todo
                add_action( 'admin_init', array( &$this, 'admin_init' ) );
                add_action( 'init', array( &$this, 'on_init' ) );
                add_action( 'plugins_loaded', array( &$this, 'do_plugins_loaded' ) );
                add_action( 'wp', array( &$this, 'prefixsetupschedule' ) );
                //add_action('plugins_loaded', array(&$this, 'plugins_loaded_register_visitor')); // too early - conflicts with cache
                add_action( 'wp_loaded', array( &$this, 'plugins_loaded_register_visitor' ) );
                
                if ( seobooster_fs()->is_plan_or_trial( 'pro' ) ) {
                    // <title> - Covering old and new filters
                    add_filter(
                        'document_title_parts',
                        array( &$this, 'set_page_title__premium_only' ),
                        99,
                        2
                    );
                    // New WP
                    add_filter(
                        'aioseop_title',
                        array( &$this, 'aio_set_page_title__premium_only' ),
                        99,
                        1
                    );
                    // AIO
                    add_filter(
                        'wp_title',
                        array( &$this, 'set_page_title__premium_only' ),
                        99,
                        2
                    );
                    // Old WP
                    add_filter(
                        'wpseo_title',
                        array( &$this, 'set_page_title__premium_only' ),
                        99,
                        2
                    );
                    // Yoast SEO
                    add_filter(
                        'seopress_titles_title',
                        array( &$this, 'seopress_set_page_title__premium_only' ),
                        99,
                        2
                    );
                    // Yoast SEO
                    add_action( 'sbp_checkbacklink', array( &$this, "seobooster_croncheckbl__premium_only" ) );
                    add_action( 'sbp_crawl_internal', array( &$this, "do_crawl_internal__premium_only" ) );
                }
                
                add_filter( 'the_content', array( &$this, 'do_filter_the_content' ), 999999 );
                // Laaaaaate init
                //add_filter('term_description', array(&$this, 'do_filter_the_content'),10,1); // Laaaaaate init
                add_action( 'template_redirect', array( &$this, 'template_redirect_action' ) );
                // 404 error detection - wp_head works, parse_request is higher - wp too early, maybe template_redirect perfect.
                // 3.3
                add_action( 'add_meta_boxes', array( &$this, 'do_custom_meta' ) );
                add_action( 'save_post', array( &$this, 'do_meta_save' ) );
                add_action( 'admin_menu', array( &$this, 'add_pages' ) );
                add_action( 'widgets_init', array( &$this, 'src_load_widgets' ) );
                add_action( 'wp_dashboard_setup', array( &$this, 'add_dashboard_widget' ) );
                // the dashboard widget
                add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
                // loading scripts
                add_filter( 'cron_schedules', array( &$this, 'filter_cron_schedules' ) );
                add_action( 'wp_ajax_fn_my_ajaxified_dataloader_ajax', array( &$this, 'fn_my_ajaxified_dataloader_ajax' ) );
                add_action( 'wpmu_drop_tables', array( &$this, 'on_delete_blog' ) );
                // Adds links in the plugins page
                add_filter(
                    'plugin_action_links',
                    array( &$this, 'add_settings_link' ),
                    10,
                    5
                );
                // Ref - en Whip installation
                //add_action('current_screen', array(&$this, 'do_action_current_screen'));
                // Lars - forsøg på at fikse problem med content injection
                // add_action( 'pre_get_posts', array(&$this, 'wpse162747_pre_get_posts_callback') );
                // add_action( 'loop_end', array(&$this, 'wpse162747_loop_end_callback'));
                register_activation_hook( __FILE__, array( &$this, 'seobooster_activate' ) );
                register_deactivation_hook( __FILE__, array( &$this, 'seobooster_deactivate' ) );
            }
            
            function do_custom_meta()
            {
                $post_types = get_post_types( array(
                    'public'   => true,
                    '_builtin' => false,
                ) );
                array_push( $post_types, 'post', 'page' );
                add_meta_box(
                    'sbp_meta',
                    __( 'SEO Booster', 'seo-booster' ),
                    array( &$this, 'sbp_meta_callback' ),
                    $post_types,
                    $context = 'side',
                    $priority = 'default',
                    $callback_args = null
                );
            }
            
            function sbp_meta_callback( $post )
            {
                wp_nonce_field( basename( __FILE__ ), 'sbp_nonce' );
                $sbp_stored_meta = get_post_meta( $post->ID );
                // first time - lets set the default value to yes, so to replace keywords to links automatically.
                
                if ( !isset( $sbp_stored_meta['_sbp-autolink'] ) ) {
                    add_post_meta( $post->ID, '_sbp-autolink', 'yes' );
                    $sbp_stored_meta = get_post_meta( $post->ID );
                }
                
                ?>
		<strong><?php 
                _e( 'Automatic Linking', 'seo-booster' );
                ?></strong>
		<p>
			<label for="sbp-autolink">
				<input type="checkbox" name="sbp-autolink" id="sbp-autolink" value="yes" <?php 
                if ( isset( $sbp_stored_meta['_sbp-autolink'] ) ) {
                    checked( $sbp_stored_meta['_sbp-autolink'][0], 'yes' );
                }
                ?> />
				<?php 
                _e( 'Change keywords on this page to links.', 'seo-booster' );
                ?>
			</label>
			<div class="howto"><?php 
                _e( 'Remove the checkmark to disable on this page.', 'seo-booster' );
                ?></div>
		</p>
		<strong><?php 
                _e( 'Automatic Linking', 'seo-booster' );
                ?></strong>
		<p>
			<label for="sbp-focuskw"><?php 
                _e( 'This Page Keyword', 'seo-booster' );
                ?></label>
			<input type="text" name="sbp-focuskw" id="sbp-focuskw" value="<?php 
                if ( isset( $sbp_stored_meta['_sbp-focuskw'] ) ) {
                    echo  $sbp_stored_meta['_sbp-focuskw'][0] ;
                }
                ?>" class="large-text" placeholder="<?php 
                _e( 'Word or phrase', 'seo-booster' );
                ?>"/>
			<div class="howto"><?php 
                _e( 'The keyword other pages will change in to a link to this page.', 'seo-booster' );
                ?></div>
		</p>

		<?php 
            }
            
            /**
             * Saves the custom meta input
             */
            /**
             * @param  int
             * @return [type]
             */
            function do_meta_save( $post_id )
            {
                // Checks save status
                $is_autosave = wp_is_post_autosave( $post_id );
                $is_revision = wp_is_post_revision( $post_id );
                $is_valid_nonce = ( isset( $_POST['sbp_nonce'] ) && wp_verify_nonce( $_POST['sbp_nonce'], basename( __FILE__ ) ) ? 'true' : 'false' );
                // Exits script depending on save status
                if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
                    return;
                }
                // Checks for input and sanitizes/saves if needed
                if ( isset( $_POST['sbp-focuskw'] ) ) {
                    update_post_meta( $post_id, '_sbp-focuskw', sanitize_text_field( $_POST['sbp-focuskw'] ) );
                }
                // Checks for input and saves
                
                if ( isset( $_POST['sbp-autolink'] ) ) {
                    update_post_meta( $post_id, '_sbp-autolink', 'yes' );
                } else {
                    update_post_meta( $post_id, '_sbp-autolink', '' );
                }
            
            }
            
            function prefixsetupschedule()
            {
                if ( !wp_next_scheduled( 'sbp_dailymaintenance' ) ) {
                    wp_schedule_event( time(), 'hourly', 'sbp_dailymaintenance' );
                }
                if ( !wp_next_scheduled( 'sbp_email_update' ) ) {
                    wp_schedule_event( time(), 'weekly', 'sbp_email_update' );
                }
            }
            
            /**
             * Groups an array by a given key.
             *
             * Groups an array into arrays by a given key, or set of keys, shared between all array members.
             *
             * Based on {@author Jake Zatecky}'s {@link https://github.com/jakezatecky/array_group_by array_group_by()} function.
             * This variant allows $key to be closures.
             *
             * @param array $array   The array to have grouping performed on.
             * @param mixed $key,... The key to group or split by. Can be a _string_,
             *                       an _integer_, a _float_, or a _callable_.
             *
             *                       If the key is a callback, it must return
             *                       a valid key from the array.
             *
             *                       If the key is _NULL_, the iterated element is skipped.
             *
             *                       ```
             *                       string|int callback ( mixed $item )
             *                       ```
             *
             * @return array|null Returns a multidimensional array or `null` if `$key` is invalid.
             */
            function array_group_by( array $array, $key )
            {
                
                if ( !is_string( $key ) && !is_int( $key ) && !is_float( $key ) && !is_callable( $key ) ) {
                    trigger_error( 'array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR );
                    return null;
                }
                
                $func = ( !is_string( $key ) && is_callable( $key ) ? $key : null );
                $_key = $key;
                // Load the new array, splitting by the target key
                $grouped = [];
                foreach ( $array as $value ) {
                    $key = null;
                    
                    if ( is_callable( $func ) ) {
                        $key = call_user_func( $func, $value );
                    } elseif ( is_object( $value ) && isset( $value->{$_key} ) ) {
                        $key = $value->{$_key};
                    } elseif ( isset( $value[$_key] ) ) {
                        $key = $value[$_key];
                    }
                    
                    if ( $key === null ) {
                        continue;
                    }
                    $grouped[$key][] = $value;
                }
                // Recursively build a nested grouping if more parameters are supplied
                // Each grouped array value is grouped according to the next sequential key
                
                if ( func_num_args() > 2 ) {
                    $args = func_get_args();
                    foreach ( $grouped as $key => $value ) {
                        $params = array_merge( [ $value ], array_slice( $args, 2, func_num_args() ) );
                        $grouped[$key] = call_user_func_array( 'array_group_by', $params );
                    }
                }
                
                return $grouped;
            }
            
            /**
             * Find a word in a string
             * Ref https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word
             * @param  string $str String to search in
             * @param  string $word Word to look for
             * @return array
             */
            function containsWord( $str, $word )
            {
                preg_match(
                    '#\\b' . preg_quote( $word, '#' ) . '\\b#i',
                    $str,
                    $matches,
                    PREG_OFFSET_CAPTURE
                );
                return $matches;
            }
            
            function do_filter_the_content( $content, $forced = false )
            {
                
                if ( $forced or is_singular() && in_the_loop() && is_main_query() ) {
                    // Thanks Pippin for this tip
                    $seobooster_append_keywords = get_option( 'seobooster_append_keywords' );
                    
                    if ( $seobooster_append_keywords ) {
                        $seobooster_append_title = get_option( 'seobooster_append_title' );
                        global  $wpdb ;
                        $introtext = '<div class="sbpappendcon"><div class="sbpappendtitle">' . $seobooster_append_title . '</div>';
                        $before = '<div class="sbplist">' . $introtext . '<ul class="sbplistul">';
                        // @todo - setting
                        $beforeeach = '<li>';
                        // @todo - setting
                        $aftereach = '</li>';
                        // @todo - setting
                        $after = '</ul></div></div>';
                        // @todo - setting
                        $limit = 20;
                        // @todo - setting
                        $currurl = $this->seobooster_currenturl();
                        $kwtable = $wpdb->prefix . "sb2_kw";
                        $ignorelist = get_option( 'seobooster_ignorelist' );
                        //$ignorelistsize = sizeof($ignorelist); //removed 3.3.28
                        
                        if ( $ignorelist ) {
                            $sqlignore = $this->seobooster_generateignorelist( $ignorelist );
                        } else {
                            $sqlignore = '';
                        }
                        
                        $query = "SELECT * FROM `{$kwtable}` WHERE {$sqlignore} `lp` = '" . $currurl . "' AND kw<>'#' and kw<>'' ORDER BY `visits` DESC limit {$limit};";
                        $posthits = $wpdb->get_results( $query, ARRAY_A );
                        
                        if ( $posthits ) {
                            $content .= "<style>\n\t\t\t\t.sbpappendcon {\n\n\t\t\t\t}\n\t\t\t\t.sbpappendtitle {\n\t\t\t\t\tfont-weight:bold;\n\t\t\t\t}\n\n\t\t\t\t.sbplist {\n\n\t\t\t\t}\n\n\t\t\t\t.sbplistul li {\n\t\t\t\t\tfloat: left;\n\t\t\t\t\tlist-style-type: none;\n\t\t\t\t}\n\t\t\t\t.sbplistul li:after {\n\t\t\t\t\tcontent: ',';\n\t\t\t\t\tmargin-right: 5px;\n\t\t\t\t}\n\t\t\t\t.sbplistul li:last-of-type:after {\n\t\t\t\t\tcontent: '';\n\t\t\t\t}\n\t\t\t\t</style>";
                            $content .= $before;
                            foreach ( $posthits as $hits ) {
                                $content .= $beforeeach;
                                $content .= $hits['kw'];
                                $content .= $aftereach;
                            }
                            $content .= $after;
                        }
                    
                    }
                    
                    // if append_keywords
                    $seobooster_internal_linking = get_option( 'seobooster_internal_linking' );
                    if ( !$seobooster_internal_linking ) {
                        return $content;
                    }
                    $thispostid = get_the_id();
                    // check if this page content is to be excluded - returning unmodified content
                    $sbp_stored_meta = get_post_meta( $thispostid );
                    // todo - load only necessary data not all meta
                    if ( isset( $sbp_stored_meta['_sbp-autolink'] ) && $sbp_stored_meta['_sbp-autolink'][0] == '' ) {
                        return $content;
                    }
                    $this->timerstart( 'autolink' );
                    global  $wpdb ;
                    $use_yoast_focus_kw = get_option( 'seobooster_use_yoast_focus_kw' );
                    // todo
                    $seobooster_use_sb_kwdata = get_option( 'seobooster_use_sb_kwdata' );
                    $currenturl = $this->seobooster_currenturl();
                    // all-round solution
                    $fullcurrenturl = site_url( $currenturl );
                    $lookupkwlimit = 250;
                    // todo - setting
                    $replaceLimit = intval( get_option( 'seobooster_internal_links_limit' ) );
                    if ( !is_integer( $replaceLimit ) ) {
                        $replaceLimit = 5;
                    }
                    // Default value
                    $replaced = 0;
                    // internal function use
                    $stepcount = 0;
                    // internal function use
                    
                    if ( false === ($searchReplaceArr = get_transient( 'sbp_searchReplaceArr' )) ) {
                        // No cache found
                        $this->timerstart( 'cachegen' );
                    } else {
                        // Cache found
                    }
                    
                    
                    if ( isset( $searchReplaceArr ) && false === $searchReplaceArr ) {
                        $searchReplaceArr = array();
                        $ignorelist = get_option( 'seobooster_ignorelist' );
                        // TODO - add to ignorelist the keywords this page is ranking for
                        $sqlignore = '';
                        if ( $ignorelist ) {
                            $sqlignore = $this->seobooster_generateignorelist( $ignorelist );
                        }
                        // *** Look up internal keyword
                        $spbkwquery = "SELECT p.ID,\n\t\t\tpm1.meta_value as keyword\n\t\t\tFROM {$wpdb->prefix}posts p LEFT JOIN {$wpdb->prefix}postmeta pm1 ON ( pm1.post_id = p.ID)\n\t\t\tWHERE p.post_status = 'publish'\n\t\t\tAND pm1.meta_value <> ''\n\t\t\tAND pm1.meta_key = '_sbp-focuskw'\n\t\t\tGROUP BY\n\t\t\tpm1.meta_value,p.ID";
                        $kwresults = $wpdb->get_results( $spbkwquery );
                        if ( $kwresults ) {
                            foreach ( $kwresults as $kw ) {
                                // Test det ikke er samme url
                                $kwlandingpage = get_permalink( $kw->ID );
                                
                                if ( $kwlandingpage != $fullcurrenturl ) {
                                    $searchReplaceArr[$stepcount]['kw'] = $kw->keyword;
                                    $searchReplaceArr[$stepcount]['lp'] = $kwlandingpage;
                                    $stepcount++;
                                }
                            
                            }
                        }
                        // TODO - TEMP SAVE RESULTS IN TRANSIENT ...
                        
                        if ( $use_yoast_focus_kw ) {
                            $fokuskwQuery = "SELECT {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts, {$wpdb->prefix}postmeta WHERE {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id AND (( {$wpdb->prefix}postmeta.meta_key LIKE '_yoast_wpseo_focuskw_text_input' ) OR ( {$wpdb->prefix}postmeta.meta_key = '_yoast_wpseo_focuskeywords' ) ) AND {$wpdb->prefix}postmeta.meta_value <> '' AND {$wpdb->prefix}posts.post_status = 'publish' LIMIT {$lookupkwlimit};";
                            // TODO
                            $kwresults = $wpdb->get_results( $fokuskwQuery );
                            if ( $kwresults ) {
                                foreach ( $kwresults as $kw ) {
                                    // Test det ikke er samme url
                                    $kwlandingpage = get_permalink( $kw->ID );
                                    
                                    if ( $kwlandingpage != $fullcurrenturl ) {
                                        $searchReplaceArr[$stepcount]['kw'] = get_post_meta( $kw->ID, '_yoast_wpseo_focuskw_text_input', true );
                                        $searchReplaceArr[$stepcount]['lp'] = get_permalink( $kw->ID );
                                    }
                                    
                                    // If Yoast SEO Premium is installed and page has multiple keywords entered.
                                    $focuskeywords = json_decode( get_post_meta( $kw->ID, '_yoast_wpseo_focuskeywords', true ) );
                                    
                                    if ( is_array( $focuskeywords ) && !empty($focuskeywords) ) {
                                        foreach ( $focuskeywords as $fkw ) {
                                            $permalink_kw = get_permalink( $kw->ID );
                                            
                                            if ( $fullcurrenturl != $permalink_kw ) {
                                                $searchReplaceArr[$stepcount]['kw'] = $fkw->keyword;
                                                $searchReplaceArr[$stepcount]['lp'] = $permalink_kw;
                                                $stepcount++;
                                            }
                                        
                                        }
                                    } else {
                                        $stepcount++;
                                    }
                                
                                }
                            }
                            // end lookup from postmeta
                            // Reading data from taxonomies, tags, categories etc.
                            $wpseo_taxonomy_meta = get_option( 'wpseo_taxonomy_meta' );
                            if ( $wpseo_taxonomy_meta ) {
                                if ( is_array( $wpseo_taxonomy_meta ) ) {
                                    foreach ( $wpseo_taxonomy_meta as $tax => $element ) {
                                        foreach ( $element as $daid => $data ) {
                                            
                                            if ( isset( $data['wpseo_focuskw'] ) ) {
                                                $permalink_kw = get_term_link( $daid, $tax );
                                                
                                                if ( !is_wp_error( $permalink_kw ) && $fullcurrenturl != $permalink_kw ) {
                                                    $searchReplaceArr[$stepcount]['kw'] = $data['wpseo_focuskw'];
                                                    $searchReplaceArr[$stepcount]['lp'] = $permalink_kw;
                                                    $stepcount++;
                                                }
                                            
                                            }
                                        
                                        }
                                    }
                                }
                            }
                        }
                        
                        // if ($use_yoast_focus_kw)
                        
                        if ( $seobooster_use_sb_kwdata ) {
                            $look = "SELECT distinct kw, lp FROM {$wpdb->prefix}sb2_kw WHERE {$sqlignore} kw <> '' AND kw <> '#' AND engine <> 'Internal Search' GROUP by kw, lp ORDER by visits DESC LIMIT {$lookupkwlimit};";
                            $internalkeywords = $wpdb->get_results( $look );
                            if ( $internalkeywords ) {
                                foreach ( $internalkeywords as $kw ) {
                                    // Hent landing page og check url er korrekt
                                    $landingpage = $kw->lp;
                                    if ( filter_var( $landingpage, FILTER_VALIDATE_URL ) === FALSE ) {
                                        $landingpage = site_url( $kw->lp );
                                    }
                                    
                                    if ( filter_var( $landingpage, FILTER_VALIDATE_URL ) ) {
                                        $searchReplaceArr[$stepcount]['kw'] = $kw->kw;
                                        $searchReplaceArr[$stepcount]['lp'] = $landingpage;
                                        $stepcount++;
                                    }
                                
                                }
                            }
                            // todo - LOOK through taxonomies data
                        }
                        
                        // if ($seobooster_use_sb_kwdata)
                        // cleaning up the array
                        
                        if ( is_array( $searchReplaceArr ) ) {
                            $newkwarr = array();
                            $usedkw = array();
                            $newstep = 0;
                            foreach ( $searchReplaceArr as $keyid => $se ) {
                                
                                if ( !in_array( $se['kw'], $usedkw ) && $se['kw'] != '' ) {
                                    $usedkw[] = $se['kw'];
                                    $newkwarr[$newstep] = $se;
                                    $newstep++;
                                }
                            
                            }
                            $searchReplaceArr = $newkwarr;
                        }
                        
                        $searchReplaceArr = array_map( "unserialize", array_unique( array_map( "serialize", $searchReplaceArr ) ) );
                    }
                    
                    // if ( false === $searchReplaceArr)
                    // In case there are no results found
                    if ( isset( $searchReplaceArr ) && !$searchReplaceArr && get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        $this->log( __( 'Auto link turned on, but no results found.', 'seo-booster' ) . ' ' . $currenturl );
                    }
                    // In case it is not an array
                    if ( isset( $searchReplaceArr ) && !is_array( $searchReplaceArr ) or isset( $searchReplaceArr ) && $searchReplaceArr == '' ) {
                        $this->log( __( 'Auto link turned on, but no keyword found.', 'seo-booster' ) . ' ' . $currenturl );
                    }
                    if ( !class_exists( 'simple_html_dom_node' ) ) {
                        include_once 'inc/simple_html_dom.php';
                    }
                    $html = new simple_html_dom();
                    $replaceCount = 0;
                    $totalStepCount = 0;
                    $html->load( $content );
                    $replaceNotes = '';
                    foreach ( $html->find( 'text' ) as $text ) {
                        // prevents links in elements we don't want.
                        if ( in_array( $text->parent->tag, array(
                            'a',
                            'li',
                            'h1',
                            'h2',
                            'h3',
                            'h4',
                            'h5',
                            'h6',
                            'h7',
                            'span'
                        ) ) ) {
                            continue;
                        }
                        $newArr = array();
                        // creating a new as a reset
                        $replaced = false;
                        $newArr = $searchReplaceArr;
                        $stepCount = 0;
                        $class = '';
                        // defaults to empty // todo ssss - make option to show?
                        $ReplaceKWArr = array();
                        foreach ( $searchReplaceArr as $key => $sr ) {
                            // lars - tilføj (!$replaced) ?? sssss todo						if ( ( !in_array($sr['kw'], $ReplaceKWArr) ) && ( $replaceCount < $replaceLimit ) ) {
                            
                            if ( !in_array( $sr['kw'], $ReplaceKWArr ) && $replaceCount < $replaceLimit && !$replaced ) {
                                $stepCount++;
                                $totalStepCount++;
                                
                                if ( isset( $sr['kw'] ) && $sr['kw'] != '' && !in_array( $sr['kw'], $ReplaceKWArr ) ) {
                                    $org = $text->outertext;
                                    //$foundPos = strpos($org,$sr['kw']);
                                    
                                    if ( isset( $temp ) && $org === $temp ) {
                                        // We just did a replacement on this string
                                        unset( $temp );
                                        continue;
                                    }
                                    
                                    $foundPos = $this->containsWord( $org, $sr['kw'] );
                                    //if ((isset($foundPos[0][1])) && (!$replaced))
                                    
                                    if ( isset( $foundPos[0][1] ) ) {
                                        $orgreplace = substr( $org, $foundPos[0][1], strlen( $sr['kw'] ) );
                                        // Finding the original capitilization
                                        $replacestring = '<a href="' . $sr['lp'] . '"' . $class . '>' . $orgreplace . '</a>';
                                        // replace
                                        $temp = substr_replace(
                                            $org,
                                            // haystack
                                            $replacestring,
                                            $foundPos[0][1],
                                            strlen( $sr['kw'] )
                                        );
                                        //error_log('Replaced with "'.print_r($temp,true).'"');
                                        
                                        if ( isset( $temp ) && $temp != $org && $org != ' ' && $org != '' && !in_array( $sr['kw'], $ReplaceKWArr ) ) {
                                            unset( $newArr[$key] );
                                            // unsetting the unsettling element :-D
                                            $replaceCount++;
                                            $text->outertext = $temp;
                                            $replaceNotes .= 'Linked <code>' . $orgreplace . '</code>. ';
                                            //todo i8n
                                            $replaced = true;
                                            $ReplaceKWArr[] = trim( $sr['kw'] );
                                        }
                                    
                                    }
                                
                                }
                                
                                // if (!in_array($sr['kw'], $ReplaceKWArr))
                            } else {
                            }
                        
                        }
                        $searchReplaceArr = $newArr;
                    }
                    $end = $this->timerstop( 'autolink' );
                    
                    if ( $replaceCount > 0 && get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        $logmessage = sprintf(
                            __( "Debug: <code>%d</code> auto link on %s (%s sec. %s loops)", 'seo-booster' ) . ' ' . $replaceNotes,
                            $replaceCount,
                            '<a href="' . $currenturl . '">' . $currenturl . '</a>',
                            $end,
                            number_format_i18n( $totalStepCount )
                        );
                        $this->log( $logmessage );
                    }
                    
                    $content = $html;
                    // Replace the $content with $html
                }
                
                return $content;
            }
            
            /**
             * Returns icon in SVG format
             *
             * Thanks Yoast for example code.
             *
             * @param type|bool $base64  Default true - returns content base64 encoded
             * @return string
             */
            function get_icon_svg( $base64 = true )
            {
                $svg = '<svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:bx="https://boxy-svg.com">
		<defs>
		<symbol id="symbol-0" viewBox="0 0 100 100">
		<path d="M 63.332 70.126 L 63.332 70.126 L 63.332 70.126 C 63.332 67.186 62.292 64.896 60.212 63.256 L 60.212 63.256 L 60.212 63.256 C 58.132 61.616 54.475 59.916 49.242 58.156 L 49.242 58.156 L 49.242 58.156 C 44.015 56.403 39.739 54.706 36.412 53.066 L 36.412 53.066 L 36.412 53.066 C 25.612 47.759 20.212 40.466 20.212 31.186 L 20.212 31.186 L 20.212 31.186 C 20.212 26.566 21.555 22.489 24.242 18.956 L 24.242 18.956 L 24.242 18.956 C 26.935 15.423 30.745 12.673 35.672 10.706 L 35.672 10.706 L 35.672 10.706 C 40.599 8.739 46.135 7.756 52.282 7.756 L 52.282 7.756 L 52.282 7.756 C 58.275 7.756 63.649 8.826 68.402 10.966 L 68.402 10.966 L 68.402 10.966 C 73.155 13.106 76.852 16.149 79.492 20.096 L 79.492 20.096 L 79.492 20.096 C 82.125 24.049 83.442 28.566 83.442 33.646 L 83.442 33.646 L 63.392 33.646 L 63.392 33.646 C 63.392 30.246 62.352 27.613 60.272 25.746 L 60.272 25.746 L 60.272 25.746 C 58.192 23.873 55.375 22.936 51.822 22.936 L 51.822 22.936 L 51.822 22.936 C 48.235 22.936 45.402 23.729 43.322 25.316 L 43.322 25.316 L 43.322 25.316 C 41.235 26.896 40.192 28.909 40.192 31.356 L 40.192 31.356 L 40.192 31.356 C 40.192 33.496 41.339 35.433 43.632 37.166 L 43.632 37.166 L 43.632 37.166 C 45.925 38.906 49.955 40.703 55.722 42.556 L 55.722 42.556 L 55.722 42.556 C 61.489 44.403 66.222 46.396 69.922 48.536 L 69.922 48.536 L 69.922 48.536 C 78.935 53.729 83.442 60.889 83.442 70.016 L 83.442 70.016 L 83.442 70.016 C 83.442 77.309 80.692 83.036 75.192 87.196 L 75.192 87.196 L 75.192 87.196 C 69.692 91.363 62.152 93.446 52.572 93.446 L 52.572 93.446 L 52.572 93.446 C 45.812 93.446 39.692 92.233 34.212 89.806 L 34.212 89.806 L 34.212 89.806 C 28.732 87.379 24.609 84.056 21.842 79.836 L 21.842 79.836 L 21.842 79.836 C 19.075 75.616 17.692 70.759 17.692 65.266 L 17.692 65.266 L 37.852 65.266 L 37.852 65.266 C 37.852 69.733 39.005 73.026 41.312 75.146 L 41.312 75.146 L 41.312 75.146 C 43.625 77.259 47.379 78.316 52.572 78.316 L 52.572 78.316 L 52.572 78.316 C 55.892 78.316 58.515 77.603 60.442 76.176 L 60.442 76.176 L 60.442 76.176 C 62.369 74.743 63.332 72.726 63.332 70.126 Z" transform="matrix(1, 0, 0, 1, 0, 0)" style="fill: rgb(130, 135, 140); white-space: pre;" id="s"/>
		</symbol>
		</defs>
		<use width="100" height="100" transform="matrix(4.947808, 0, 0, 4.947808, -20.354914, -11.482257)" xlink:href="#symbol-0"/>
		<path style="paint-order: stroke; fill: rgb(130, 135, 140);" d="M 349.355 16.098 C 333.687 49.355 248.938 171.838 248.938 171.838 C 248.938 171.838 228.3 199.676 236.116 203.927 C 247.584 210.168 267.795 206.135 284.389 206.805 C 309.456 207.816 329.639 205.313 341.68 205.786 C 341.68 205.786 359.942 201.1 363.11 211.672 C 365.18 218.581 354.131 230.067 354.131 230.067 L 105.339 481.212 L 213.627 310.542 C 213.627 310.542 221.796 293.779 216.787 287.127 C 210.653 278.986 186.557 281.117 186.557 281.117 C 186.557 281.117 140.259 279.657 117.109 279.939 C 108.054 280.05 99.5 279.319 99.082 272.877 C 98.532 264.365 100.711 262.353 110.047 252.866 C 188.089 173.584 349.355 16.098 349.355 16.098 Z"/>
		</svg>';
                if ( $base64 ) {
                    return 'data:image/svg+xml;base64,' . base64_encode( $svg );
                }
                return $svg;
            }
            
            /**
             * Load language files
             *
             * @return void
             */
            function do_plugins_loaded()
            {
                load_plugin_textdomain( "seo-booster", false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
                $seobooster_db_version = get_option( 'SEOBOOSTER_INSTALLED_DB_VERSION', '1.0' );
                // latest update
                if ( version_compare( $seobooster_db_version, SEOBOOSTER_DB_VERSION ) < 0 ) {
                    $this->create_database_tables();
                }
            }
            
            /**
             * Checks if user agent match a known crawler robot
             *
             * @param string User agent to check against list of known robot user agents
             * @return bool
             */
            function RobotDetect( $user_agent )
            {
                $common_browsers = "Firefox|Chrome|Opera|MSIE";
                if ( preg_match( "/" . $common_browsers . "/i", $user_agent ) ) {
                    return 0;
                }
                $agent = strstr( $user_agent, '/', TRUE );
                $robots = "bayspider|bbot|BingBot|checkbot|christcrawler|fastcrawler|Googlebot|infospider|lycos|slcrawler|Slurp|smartspider|spiderbot|spiderline|spiderman|Baiduspider|voyager|vwbot|MJ12bot|Screaming Frog";
                $isRobot = ( stripos( $robots, $agent ) === FALSE ? 0 : 1 );
                
                if ( $isRobot === 0 ) {
                    $robotarray = explode( '|', $robots );
                    foreach ( $robotarray as $key => $value ) {
                        $isRobot = ( stripos( $user_agent, $value ) === FALSE ? 0 : 1 );
                        if ( $isRobot === 1 ) {
                            return $value;
                        }
                    }
                }
                
                return $isRobot;
            }
            
            // Runs on action "template_redirect" - 404
            function template_redirect_action()
            {
                $currurl = $this->seobooster_currenturl();
                if ( empty($_POST) && defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) || defined( 'XMLRPC_REQUEST' ) || defined( 'DOING_AUTOSAVE' ) || defined( 'REST_REQUEST' ) ) {
                    return;
                }
                // List of args to ignore in query strings on 404 pages.
                $ignore_args = array( 'wordfence_lh' );
                // todo - make ignore list a setting?
                $parts = parse_url( $currurl );
                if ( isset( $parts['query'] ) ) {
                    parse_str( $parts['query'], $query_parms );
                }
                if ( isset( $query_parms ) && is_array( $query_parms ) ) {
                    foreach ( $query_parms as $qa ) {
                        if ( in_array( $qa, $ignore_args ) ) {
                            // if (get_option('seobooster_debug_logging') == 'on') {
                            // 	$this->log('template_redirect_action() Ignoring "'.$qa.'" from '.$currurl);
                            // }
                            return;
                        }
                    }
                }
                global  $wpdb ;
                
                if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                    $parsedurl = parse_url( $_SERVER['HTTP_REFERER'] );
                    $domain = $parsedurl['host'];
                }
                
                
                if ( is_404() ) {
                    
                    if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                        $referer = strtolower( $_SERVER['HTTP_REFERER'] );
                    } else {
                        $referer = '';
                    }
                    
                    // Filtrer .css og .js filer fra
                    $pathinfo = pathinfo( $currurl );
                    
                    if ( isset( $pathinfo['extension'] ) ) {
                        $extension = strtolower( $pathinfo['extension'] );
                        
                        if ( in_array( $extension, array( 'css', 'js' ) ) ) {
                            return;
                            // filter out unwanted .js and .css
                        }
                    
                    }
                    
                    $fourohfourtable = $wpdb->prefix . "sb2_404";
                    $excistingentry = $wpdb->get_var( "SELECT id FROM `{$fourohfourtable}` WHERE `lp` = '{$currurl}' limit 1;" );
                    // PREPARE
                    $rightnow = date( 'Y-m-d H:i:s' );
                    
                    if ( $excistingentry ) {
                        // not a NEW keyword entry, so lets update the visit count..
                        $query = "UPDATE `{$fourohfourtable}` SET `visits` = `visits`+1, `lastseen`='{$rightnow}'  WHERE `id` ='{$excistingentry}' LIMIT 1 ;";
                        // PREPARE
                        $wpdb->query( $query );
                    } else {
                        // a NEW keyword and/or tld, insert into database...
                        $wpdb->insert( $fourohfourtable, array(
                            'lp'        => $currurl,
                            'firstseen' => $rightnow,
                            'lastseen'  => $rightnow,
                            'visits'    => 1,
                            'referer'   => $referer,
                        ), array(
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%s'
                        ) );
                        
                        if ( isset( $referer ) && $referer != '' ) {
                            $this->log( sprintf(
                                __( 'New 404 - <a href="%s" target="_blank">%s</a> Referer: %s', 'seo-booster' ),
                                $currurl,
                                $currurl,
                                $referer
                            ), 2 );
                        } else {
                            $this->log( sprintf( __( 'New 404 - <a href="%s" target="_blank">%s</a>', 'seo-booster' ), $currurl, $currurl ), 2 );
                        }
                    
                    }
                
                }
                
                // if (is_404())
            }
            
            // template_redirect_action()
            /**
             * Returns true if $url is a local installation - Thanks EDD
             */
            function is_local_url( $url = '' )
            {
                $is_local_url = false;
                $url = strtolower( trim( $url ) );
                if ( false === strpos( $url, 'http://' ) && false === strpos( $url, 'https://' ) ) {
                    $url = 'http://' . $url;
                }
                $url_parts = parse_url( $url );
                $host = ( !empty($url_parts['host']) ? $url_parts['host'] : false );
                
                if ( !empty($url) && !empty($host) ) {
                    
                    if ( false !== ip2long( $host ) ) {
                        if ( !filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                            $is_local_url = true;
                        }
                    } else {
                        if ( 'localhost' === $host ) {
                            $is_local_url = true;
                        }
                    }
                    
                    $tlds_to_check = array( '.dev', '.local', '.loc' );
                    foreach ( $tlds_to_check as $tld ) {
                        
                        if ( false !== strpos( $host, $tld ) ) {
                            $is_local_url = true;
                            continue;
                        }
                    
                    }
                    
                    if ( substr_count( $host, '.' ) > 1 ) {
                        $subdomains_to_check = array( 'dev.', 'staging.' );
                        foreach ( $subdomains_to_check as $subdomain ) {
                            
                            if ( 0 === strpos( $host, $subdomain ) ) {
                                $is_local_url = true;
                                continue;
                            }
                        
                        }
                    }
                
                }
                
                return $is_local_url;
            }
            
            /**
             * When deleting a blog in multisite.
             */
            function on_delete_blog( $tables )
            {
                global  $wpdb ;
                $tables[] = $wpdb->prefix . 'sb2_bl';
                $tables[] = $wpdb->prefix . 'sb2_log';
                $tables[] = $wpdb->prefix . 'sb2_kwdt';
                $tables[] = $wpdb->prefix . 'sb2_kw';
                $tables[] = $wpdb->prefix . 'sb2_404';
                return $tables;
            }
            
            /**
             * Turns a relative URL to absolute URL.
             */
            function rel2abs( $rel, $base )
            {
                if ( strpos( $rel, "//" ) === 0 ) {
                    return "http:" . $rel;
                }
                /* return if  already absolute URL */
                if ( parse_url( $rel, PHP_URL_SCHEME ) != '' ) {
                    return $rel;
                }
                /* queries and  anchors */
                if ( $rel[0] == '#' || $rel[0] == '?' ) {
                    return $base . $rel;
                }
                /* parse base URL  and convert to local variables:
                	 $scheme, $host,  $path */
                $parse_url = parse_url( $base );
                /* remove  non-directory element from path */
                if ( isset( $parse_url['path'] ) ) {
                    $path = preg_replace( '#/[^/]*$#', '', $parse_url['path'] );
                }
                if ( $rel[0] == '/' ) {
                    $path = '';
                }
                /* dirty absolute  URL */
                $abs = $parse_url['host'] . $path . $rel;
                /* replace '//' or  '/./' or '/foo/../' with '/' */
                $re = array( '#(/.?/)#', '#/(?!..)[^/]+/../#' );
                for ( $n = 1 ;  $n > 0 ;  $abs = preg_replace(
                    $re,
                    '/',
                    $abs,
                    -1,
                    $n
                ) ) {
                }
                /* absolute URL is  ready! */
                //return  $scheme.'://'.$abs;
                return $abs;
            }
            
            function plugins_loaded_register_visitor()
            {
                // todo - se om referrer er siteurl, og om param ?s er sat eller ej - hvis det er fra site_url og der ikke er en søgning, så skal vi ikke videre, ikke bruge tid på dette.
                if ( empty($_POST) && defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) || defined( 'XMLRPC_REQUEST' ) || defined( 'DOING_AUTOSAVE' ) || defined( 'REST_REQUEST' ) ) {
                    return;
                }
                $currurl = $this->seobooster_currenturl();
                
                if ( !$currurl ) {
                    if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        // $this->log("Debug: plugins_loaded_register_visitor(): Current url could not be detected? ".print_r($_SERVER['REQUEST_URI'],true));
                    }
                    return;
                }
                
                // @todo - filter out urls wordfence_lh
                
                if ( $ignoreres = $this->ignore_current_url( $currurl ) ) {
                    if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        //	$this->log(sprintf(__("Debug: Ignoring referrer <code>%s</code> match <code>%s</code> ", 'seo-booster'), $currurl, $ignoreres));
                    }
                    //
                    return;
                }
                
                if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                    $referer = strtolower( $_SERVER['HTTP_REFERER'] );
                }
                
                if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
                    $useragent = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );
                    if ( $this->ignore_useragent( $useragent ) ) {
                        if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                            $this->log( sprintf( __( "Debug: Ignoring user agent <code>%s</code> ", 'seo-booster' ), $useragent ) );
                        }
                    }
                }
                
                if ( $this->ignorelink( $currurl ) ) {
                }
                //error_log('we have curr '.$currurl.' ref : '.$referer);
                if ( isset( $currurl ) && isset( $referer ) ) {
                    $this->checkreferrer( $currurl, $referer );
                }
            }
            
            function send_email_update( $days = 7, $forced = false )
            {
                $seobooster_weekly_email = get_option( 'seobooster_weekly_email' );
                if ( $seobooster_weekly_email != 'on' and !$forced ) {
                    return;
                }
                // testing if a local site - only allow if $forced set to true
                
                if ( !$forced && $this->is_local_url( site_url() ) ) {
                    $this->log( __( 'This is a local site, email not sent.', 'seo-booster' ) );
                    return;
                }
                
                $seobooster_weekly_email_recipient = get_option( 'seobooster_weekly_email_recipient' );
                // Take default admin in case missing
                if ( !is_email( $seobooster_weekly_email_recipient ) ) {
                    $seobooster_weekly_email_recipient = get_option( 'admin_email' );
                }
                if ( !is_int( $days ) ) {
                    $days = 7;
                }
                // default
                global  $wpdb ;
                $tablename = $wpdb->prefix . "sb2_kw";
                // reset
                $content = '';
                $sendme = false;
                $somethingnew = 0;
                // We assume nothing new has happened.
                // 			$knownkws_query = "SELECT lp, firstvisit, visits, engine FROM $tablename WHERE firstvisit BETWEEN DATE_SUB(NOW(), INTERVAL $days DAY) AND NOW() AND engine<>'Internal Search' GROUP BY lp order by visits DESC, firstvisit DESC limit 10;";
                $knownkws_query = "SELECT lp, firstvisit, visits, engine FROM {$tablename} WHERE firstvisit BETWEEN DATE_SUB(NOW(), INTERVAL {$days} DAY) AND NOW() GROUP BY lp order by visits DESC, firstvisit DESC limit 10;";
                $knownkws = $wpdb->get_results( $knownkws_query );
                
                if ( $knownkws ) {
                    $sendme = true;
                    $content .= '<p>' . sprintf( __( 'Top 10 pages with Search Engine traffic the past %s days.', 'seo-booster' ), $days ) . '</p>';
                    $pasturl = '';
                    foreach ( $knownkws as $keyword ) {
                        $somethingnew++;
                        $kws_comma_separated = '';
                        $kwarr = array();
                        foreach ( $wpdb->get_results( "SELECT kw FROM {$tablename} WHERE firstvisit BETWEEN DATE_SUB(NOW(), INTERVAL {$days} DAY) AND NOW() AND lp LIKE '" . $keyword->lp . "' AND engine<>'Internal Search' AND kw<>'#';" ) as $key => $row ) {
                            $kwarr[] = $row->kw;
                        }
                        $visits = $wpdb->get_var( "SELECT SUM(visits) FROM {$tablename} WHERE firstvisit BETWEEN DATE_SUB(NOW(), INTERVAL {$days} DAY) AND NOW() AND lp='" . $keyword->lp . "';" );
                        $kws_comma_separated = implode( ', ', $kwarr );
                        $content .= '<a href="' . $keyword->lp . '">' . $this->remove_http( $keyword->lp ) . '</a>' . "\r\n";
                        $content .= sprintf( _n( '%s visit from search engines past %s days', '%s visits from search engines past %s days', $visits ), $visits, $days );
                        $content .= "\r\n\r\n";
                        if ( $kws_comma_separated != '' ) {
                            $content .= __( 'New Keywords:', 'seo-booster' ) . ' ' . $kws_comma_separated . "\r\n\r\n";
                        }
                    }
                    $dashboardlink = admin_url( '?page=sb2_dashboard' );
                    $emailtitle = __( 'Report from SEO Booster on ', 'seo-booster' ) . ' ' . $this->remove_http( site_url() );
                    // todo i8n med dage
                    $dashboardlinkanchor = __( 'SEO Booster Dashboard', 'seo-booster' );
                    $emailintrotext = ' ';
                    //__('','seo-booster'); // todo
                    $myReplacements = array(
                        '%%emailintrotext%%'      => $emailintrotext,
                        '%%websitedomain%%'       => $this->remove_http( site_url() ),
                        '%%dashboardlink%%'       => $dashboardlink,
                        '%%dashboardlinkanchor%%' => $dashboardlinkanchor,
                        '%%cplogourl%%'           => SEOBOOSTER_PLUGINURL . 'images/cleverpluginslogo.png',
                        '%%emailtitle%%'          => $emailtitle,
                        '%%emailcontent%%'        => nl2br( $content ),
                    );
                    $html = file_get_contents( SEOBOOSTER_PLUGINURL . 'inc/emailtemplate-01.html' );
                    foreach ( $myReplacements as $needle => $replacement ) {
                        $html = str_replace( $needle, $replacement, $html );
                    }
                    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                    $sendresult = wp_mail(
                        $seobooster_weekly_email_recipient,
                        $emailtitle,
                        $html,
                        $headers
                    );
                    if ( $sendresult ) {
                        $this->log( sprintf( __( 'Status email was sent to %s', 'seo-booster' ), $seobooster_weekly_email_recipient ), 5 );
                    }
                } else {
                    $this->log( sprintf( __( 'No results for past %s days - no email was sent.', 'seo-booster' ), $days ), 3 );
                }
            
            }
            
            /**
             * Adds a direct link to settings from plugin overview page.
             */
            function add_settings_link( $actions, $plugin_file )
            {
                static  $plugin ;
                if ( !isset( $plugin ) ) {
                    $plugin = plugin_basename( __FILE__ );
                }
                
                if ( $plugin == $plugin_file ) {
                    $settings = array(
                        'settings' => '<a href="admin.php?page=sb2_settings">' . __( 'Settings', 'General' ) . '</a>',
                    );
                    $documentation = array(
                        'documentation' => '<a href="https://cleverplugins.helpscoutdocs.com/" target="_blank">' . __( 'Knowledgebase', 'General' ) . '</a>',
                    );
                    $actions = array_merge( $settings, $actions, $documentation );
                }
                
                return $actions;
            }
            
            /**
             * Generates keyword ignore list for database queries
             * @param  array $ignorelist
             * @return string
             */
            function seobooster_generateignorelist( $ignorelist )
            {
                if ( !$ignorelist ) {
                    return '';
                }
                $parsed = preg_replace( "/\r|\n/", ',', $ignorelist );
                $parsed = preg_replace( '/,+/', ',', $parsed );
                // TODO - optimized query? perhaps put in an array instead
                $ignorearray = explode( ",", $ignorelist );
                $ignores = count( $ignorearray );
                
                if ( $ignorelist != '' ) {
                    $ignoresearchstring = '';
                    $count = 0;
                    foreach ( $ignorearray as $tag ) {
                        $tag = str_replace( '\'', '', $tag );
                        $tag = trim( $tag );
                        
                        if ( $tag != '' ) {
                            if ( $count > 0 ) {
                                $ignoresearchstring .= ' OR ';
                            }
                            $ignoresearchstring .= " (kw LIKE '%" . $tag . "%') ";
                            // REMOVED esc_sc.l
                            $count++;
                        }
                    
                    }
                    return " NOT (" . $ignoresearchstring . ") AND ";
                } else {
                    return '';
                }
            
            }
            
            function fn_my_ajaxified_dataloader_ajax()
            {
                $this->timerstart( 'ajaxtable' );
                global  $wpdb ;
                $kwtable = $wpdb->prefix . "sb2_kw";
                $kwdttable = $wpdb->prefix . "sb2_kwdt";
                $drawval = 1;
                if ( isset( $_REQUEST['draw'] ) ) {
                    $drawval = intval( $_REQUEST['draw'] );
                }
                $aColumns = array(
                    'kw',
                    'lp',
                    'engine',
                    'visits',
                    'firstvisit',
                    'lastvisit'
                );
                $sIndexColumn = "id";
                // Used for counting results
                $sTable = $kwtable;
                $expiry = strtotime( '+1 year' );
                $path = parse_url( site_url(), PHP_URL_PATH );
                $host = parse_url( site_url(), PHP_URL_HOST );
                
                if ( isset( $_REQUEST['length'] ) ) {
                    $the_length = intval( $_REQUEST['length'] );
                    setcookie(
                        'sbp_kw_length',
                        $the_length,
                        $expiry,
                        $path,
                        $host
                    );
                }
                
                
                if ( isset( $_REQUEST['showkws'] ) ) {
                    $the_showkws = $_REQUEST['showkws'];
                    // todo sec
                    setcookie(
                        'sbp_the_showkws',
                        $the_showkws,
                        $expiry,
                        $path,
                        $host
                    );
                }
                
                
                if ( isset( $_REQUEST['hideinternal'] ) ) {
                    $the_hideinternal = $_REQUEST['hideinternal'];
                    // todo sec
                    setcookie(
                        'sbp_kw_hideinternal',
                        $the_hideinternal,
                        $expiry,
                        $path,
                        $host
                    );
                } else {
                    setcookie(
                        'sbp_kw_hideinternal',
                        '',
                        time() - 3600,
                        $path,
                        $host
                    );
                }
                
                $sLimit = '';
                $sLimit = "LIMIT 25";
                if ( isset( $_REQUEST['start'] ) && isset( $_REQUEST['start'] ) && $_REQUEST['length'] != '-1' ) {
                    $sLimit = "LIMIT " . intval( $_REQUEST['start'] ) . ", " . intval( $_REQUEST['length'] );
                }
                //	$sOrder = "ORDER BY lastvisit ASC"; // default = "" ...
                $sOrder = "ORDER BY lastvisit DESC";
                // default = "" ...
                
                if ( isset( $_REQUEST['order'] ) && $_REQUEST['order'][0]['column'] != '0' ) {
                    $sOrder = "ORDER BY ";
                    if ( $_REQUEST['order'][0]['column'] == 1 ) {
                        $sOrder .= ' lp';
                    }
                    if ( $_REQUEST['order'][0]['column'] == 2 ) {
                        $sOrder .= ' engine';
                    }
                    if ( $_REQUEST['order'][0]['column'] == 3 ) {
                        $sOrder .= ' visits';
                    }
                    if ( $_REQUEST['order'][0]['column'] == 4 ) {
                        $sOrder .= ' firstvisit';
                    }
                    if ( $_REQUEST['order'][0]['column'] == 5 ) {
                        $sOrder .= ' lastvisit';
                    }
                    $sOrder .= ' ' . $_REQUEST['order'][0]['dir'];
                    
                    if ( $sOrder == "ORDER BY" ) {
                        $sOrder = "ORDER BY lastvisit DESC";
                        // default = "" ...
                    }
                
                }
                
                $sWhere = "";
                
                if ( isset( $_REQUEST['search'] ) && $_REQUEST['search']['value'] != "" ) {
                    $sWhere = "WHERE (";
                    for ( $i = 0 ;  $i < count( $aColumns ) ;  $i++ ) {
                        if ( $aColumns[$i] == 'kw' or $aColumns[$i] == 'lp' ) {
                            $sWhere .= "`" . $aColumns[$i] . "` LIKE '%" . $_REQUEST['search']['value'] . "%' OR ";
                        }
                    }
                    $sWhere = substr_replace( $sWhere, "", -3 );
                    // removes the last 'OR ' ...
                    $sWhere .= ')';
                }
                
                for ( $i = 0 ;  $i < count( $aColumns ) ;  $i++ ) {
                    
                    if ( isset( $_REQUEST['bSearchable_' . $i] ) && $_REQUEST['bSearchable_' . $i] == "true" && $_REQUEST['sSearch_' . $i] != '' ) {
                        
                        if ( $sWhere == "" ) {
                            $sWhere = "WHERE ";
                        } else {
                            $sWhere .= " AND ";
                        }
                        
                        $sWhere .= "`" . $aColumns[$i] . "` LIKE '%" . $_REQUEST['sSearch_' . $i] . "%' ";
                    }
                
                }
                //  for ( $i=0 ; $i<count($aColumns) ; $i++ )
                // Hiding internal searches
                if ( isset( $_REQUEST['hideinternal'] ) && $_REQUEST['hideinternal'] == 'on' ) {
                    
                    if ( !$sWhere ) {
                        $sWhere = " WHERE engine<>'Internal Search' ";
                    } else {
                        $sWhere .= " AND engine<>'Internal Search' ";
                    }
                
                }
                if ( isset( $_REQUEST['showkws'] ) && $_REQUEST['showkws'] == 'all' ) {
                }
                // Show only unknown keywords
                if ( isset( $_REQUEST['showkws'] ) && $_REQUEST['showkws'] == 'unknowns' ) {
                    
                    if ( !$sWhere ) {
                        $sWhere = " WHERE kw='#' ";
                    } else {
                        $sWhere .= " AND kw='#' ";
                    }
                
                }
                // Show only known keywords
                if ( isset( $_REQUEST['showkws'] ) && $_REQUEST['showkws'] == 'knowns' ) {
                    
                    if ( !$sWhere ) {
                        $sWhere = " WHERE kw<>'#' ";
                    } else {
                        $sWhere .= " AND kw<>'#' ";
                    }
                
                }
                $sQuery = "\n\t\t\tSELECT SQL_CALC_FOUND_ROWS `" . str_replace( " , ", " ", implode( "`, `", $aColumns ) ) . "`\n\t\t\tFROM   {$sTable}\n\t\t\t{$sWhere}\n\t\t\t{$sOrder}\n\t\t\t{$sLimit}\n\t\t\t";
                //error_log($sQuery);
                $rResult = $wpdb->get_results( $sQuery, ARRAY_A );
                $iFilteredTotal = $wpdb->get_var( "SELECT count(*) FROM {$kwtable} {$sWhere} " );
                $iTotal = $wpdb->get_var( "SELECT count(*) FROM {$kwtable};" );
                // WHERE kw<>'#'
                $output = array(
                    "draw"            => $drawval,
                    "recordsTotal"    => $iTotal,
                    "recordsFiltered" => $iFilteredTotal,
                );
                $surl = site_url();
                // Bruges i loop til at filtrere domænet fra.
                $extraclasses = '';
                $row_arr = array();
                $datastring = array();
                foreach ( $rResult as $aRow ) {
                    $row = array();
                    for ( $i = 0 ;  $i < count( $aColumns ) ;  $i++ ) {
                        
                        if ( $aColumns[$i] == "kw" ) {
                            $row[] = ( $aRow[$aColumns[$i]] == "0" ? '-' : $aRow[$aColumns[$i]] );
                            // keyword
                            
                            if ( $row[0] == '#' ) {
                                $row[0] = '<span class="unknown">' . __( 'Not Provided', 'seo-booster' ) . '</span>';
                                // setting unknown class
                            }
                        
                        } else {
                            
                            if ( $aColumns[$i] == 'lp' ) {
                                $trimmed = str_replace( $surl, '', $aRow[$aColumns[$i]] );
                                $extraclasses = '';
                                $row[] = '<a href="' . $aRow[$aColumns[$i]] . '"' . $extraclasses . ' target="_blank">' . $trimmed . '</a>';
                                // landing page
                            } else {
                                
                                if ( $aColumns[$i] == 'firstvisit' ) {
                                    $row[] = '<small>' . $aRow[$aColumns[$i]] . '</small>';
                                    // first visit
                                } else {
                                    
                                    if ( $aColumns[$i] == 'lastvisit' ) {
                                        $row[] = '<small>' . $aRow[$aColumns[$i]] . '</small>';
                                        // last visit
                                    } else {
                                        
                                        if ( $aColumns[$i] == 'engine' ) {
                                            $row[] = str_replace( 'www.', '', $aRow[$aColumns[$i]] );
                                            // replace www. in engine name column
                                        } else {
                                            if ( $aColumns[$i] != ' ' ) {
                                                $row[] = $aRow[$aColumns[$i]];
                                            }
                                        }
                                    
                                    }
                                
                                }
                            
                            }
                        
                        }
                    
                    }
                    $row_arr[] = $row;
                }
                $output['data'] = $row_arr;
                $ajaxtable = $this->timerstop( 'ajaxtable' );
                //error_log('AJAX took '.$ajaxtable.' sec.');
                echo  json_encode( $output ) ;
                die;
            }
            
            // Returns true if on an admin page
            function is_sb2_admin_page()
            {
                $screen = get_current_screen();
                if ( is_object( $screen ) && $screen->id === 'toplevel_page_sb2_dashboard' or $screen->id === 'seo-booster_page_sb2_log' or $screen->id === 'seo-booster_page_sb2_settings' or $screen->id === 'seo-booster_page_sb2_404' or $screen->id === 'seo-booster_page_sb2_crawled' or $screen->id === 'seo-booster_page_sb2_forgotten' or $screen->id === 'seo-booster_page_sb2_keywords' or $screen->id === 'seo-booster_page_sb2_audit' or $screen->id === 'seo-booster_page_sb2_backlinks' or $screen->id === 'seo-sb2_dashboard' or $screen->id === 'dashboard' ) {
                    return true;
                }
                return false;
            }
            
            function admin_enqueue_scripts()
            {
                $is_sb2_admin_page = $this->is_sb2_admin_page();
                
                if ( $is_sb2_admin_page ) {
                    wp_enqueue_script( 'jquery-ui-core' );
                    wp_enqueue_script( 'googlejsapi', 'https://www.google.com/jsapi' );
                    wp_enqueue_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js' );
                    wp_enqueue_script(
                        'lazyload',
                        plugins_url( '/js/jquery.lazyload.min.js', __FILE__ ),
                        array(),
                        SEOBOOSTER_VERSION
                    );
                    // Indlæser specifikke data om bruger til Helpscout Beacon.
                    wp_register_script(
                        'seoboosterjs',
                        plugins_url( '/js/min/seo-booster-min.js', __FILE__ ),
                        array(),
                        SEOBOOSTER_VERSION
                    );
                    $current_user = wp_get_current_user();
                    $sbdata_array = array(
                        'email'         => $current_user->user_email,
                        'website'       => site_url(),
                        'enablecontact' => false,
                        'instructions'  => __( 'Support is only available in English and Danish.', 'seo-booster' ),
                    );
                    $screen = get_current_screen();
                    // Do not load this on admin dashboard
                    
                    if ( $screen->id != 'dashboard' ) {
                        wp_localize_script( 'seoboosterjs', 'sbbeacondata', $sbdata_array );
                        wp_enqueue_script( 'seoboosterjs' );
                        wp_enqueue_style(
                            'seoboostercss',
                            plugins_url( '/css/seo-booster-min.css', __FILE__ ),
                            array(),
                            SEOBOOSTER_VERSION
                        );
                    }
                
                }
                
                $screen = get_current_screen();
                // only load datatables on keywords page
                
                if ( is_object( $screen ) && $screen->id == 'seo-booster_page_sb2_keywords' ) {
                    wp_enqueue_script(
                        'dataTables',
                        plugins_url( '/js/datatables.min.js', __FILE__ ),
                        array(),
                        SEOBOOSTER_VERSION
                    );
                    wp_enqueue_style(
                        'dataTables',
                        plugins_url( '/js/jquery.dataTables.css', __FILE__ ),
                        array(),
                        SEOBOOSTER_VERSION
                    );
                    wp_register_script(
                        'dataTable',
                        plugins_url( '/js/datatable.js', __FILE__ ),
                        array(),
                        SEOBOOSTER_VERSION
                    );
                    $translation_array = array(
                        'search'          => __( 'Search:', 'seo-booster' ),
                        'sInfo'           => __( 'Showing _START_ to _END_ of _TOTAL_ keywords.', 'seo-booster' ),
                        'sProcessing'     => __( 'Loading...', 'seo-booster' ),
                        'sInfoFiltered'   => __( ' - filtering from _MAX_ records', 'seo-booster' ),
                        'sLengthMenu'     => __( 'Display _MENU_ keywords', 'seo-booster' ),
                        'sLoadingRecords' => __( 'Please wait - loading...', 'seo-booster' ),
                        'sZeroRecords'    => __( 'No records to display', 'seo-booster' ),
                        'sPrevious'       => __( 'Previous', 'seo-booster' ),
                        'sNext'           => __( 'Next', 'seo-booster' ),
                        'first'           => __( 'First', 'seo-booster' ),
                        'last'            => __( 'Last', 'seo-booster' ),
                        'sEmptyTable'     => __( 'No data available in table', 'seo-booster' ),
                    );
                    wp_localize_script( 'dataTable', 'interface_translations', $translation_array );
                    wp_enqueue_script( 'dataTable' );
                    //wp_enqueue_style('seoboostercss', plugins_url('/css/seo-booster.css', __FILE__)); // allerede indlæst ikke?
                }
                
                // Check to see if user has already dismissed the pointer tour
                $dismissed = array_filter( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) );
                $do_tour = !in_array( 'sbp_tour_pointer', $dismissed );
                // If not, we are good to continue - We check if the plugin has been registered or user wants to be anon
                global  $seobooster_fs ;
                
                if ( $do_tour && ($seobooster_fs->is_registered() or $seobooster_fs->is_anonymous() or $seobooster_fs->is_pending_activation()) ) {
                    //		if ($do_tour) {
                    wp_enqueue_style( 'wp-pointer' );
                    wp_enqueue_script( 'wp-pointer' );
                    add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
                    add_action( 'admin_head', array( $this, 'admin_head' ) );
                }
            
            }
            
            // Used to add spacing between the two buttons in the pointer overlay window.
            function admin_head()
            {
                ?>

		<style type="text/css" media="screen">
		#pointer-primary {
			margin: 0 5px 0 0;
		}
	</style>
	<?php 
            }
            
            // This function is used to reload the admin page.
            // -- $page = the admin page we are passing (index.php or options-general.php)
            // -- $tab = the NEXT pointer array key we want to display
            function get_admin_url( $page, $tab )
            {
                $url = admin_url();
                $url .= $page;
                $url = add_query_arg( 'tab', $tab, $url );
                return $url;
            }
            
            // Define footer scripts
            function admin_print_footer_scripts()
            {
                // Define global variables
                global  $pagenow ;
                global  $current_user ;
                //*****************************************************************************************************
                // This is our array of individual pointers.
                // -- The array key should be unique.  It is what will be used to 'advance' to the next pointer.
                // -- The 'id' should correspond to an html element id on the page.
                // -- The 'content' will be displayed inside the pointer overlay window.
                // -- The 'button2' is the text to show for the 'action' button in the pointer overlay window.
                // -- The 'function' is the method used to reload the window (or relocate to a new window).
                //    This also creates a query variable to add to the end of the url.
                //    The query variable is used to determine which pointer to display.
                //*****************************************************************************************************
                $tour = array(
                    'dashboard'         => array(
                    'id'       => '#welcome-panel',
                    'content'  => '<h3>' . __( 'The Dashboard Page', 'seo-booster' ) . ' (1/13) </h3>' . '<p>' . __( 'SEO Booster monitors traffic from hundreds of keyword sources, detects visitors from backlinks, 404 errors and much more.', 'seo-booster' ) . '</p>' . '<p><strong>' . __( 'The Dashboard page gives you a quick overview.', 'seo-booster' ) . '</strong></p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_keywords', 'keywords' ) . '";',
                ),
                    'keywords'          => array(
                    'id'       => '#datatable-target',
                    'content'  => '<h3>' . __( 'Keywords Module', 'seo-booster' ) . ' (2/13) </h3>' . '<p><strong>' . __( 'What your visitors searches for on search engines to find your website', 'seo-booster' ) . '</strong></p>' . '<p>' . __( 'Keyword traffic from 400+ sources are monitored and keywords will show up here.', 'seo-booster' ) . '</p>' . '<p>' . __( 'Google and Yandex have stopped sharing information about what people search for, but there are hundreds of other search engines who still shares.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_keywords', 'keyword_filtering' ) . '"',
                ),
                    'keyword_filtering' => array(
                    'id'       => '#filtering',
                    'content'  => '<h3>' . __( 'Filter Keywords', 'seo-booster' ) . ' (3/13) </h3>' . '<p><strong>' . __( 'All pages that receive search engine traffic are listed. Also when the keyword was not provided.', 'seo-booster' ) . '</strong></p>' . '<p>' . __( 'Use the filter settings to show only when keywords were provided.', 'seo-booster' ) . '</p>' . '<p>' . __( 'You can also filter out internal searches.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_backlinks', 'backlinks' ) . '"',
                ),
                    'backlinks'         => array(
                    'id'       => '#backlinkstable-target',
                    'content'  => '<h3>' . __( 'Who links to you?', 'seo-booster' ) . ' (4/13) </h3>' . '<p>' . __( 'Every new visitor that comes from another website is monitored and listed here.', 'seo-booster' ) . '</p>' . '<p><strong>' . __( 'Note - Pro version also checks each backlink and removes fake links.', 'seo-booster' ) . '</strong></p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_forgotten', 'forgotten' ) . '"',
                ),
                    'forgotten'         => array(
                    'id'       => '#wp_pointer-target',
                    'content'  => '<h3>' . __( 'Lost Traffic', 'seo-booster' ) . ' (5/13) </h3>' . '<p>' . __( 'Discover pages that used to receive traffic from Search Engines, but no longer gets any traffic.', 'seo-booster' ) . '</p>' . '<p>' . __( 'Do not worry if you see nothing listed here. That is good.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_404', '404' ) . '"',
                ),
                    '404'               => array(
                    'id'       => '#404table-target',
                    'content'  => '<h3>' . __( '404 Errors!', 'seo-booster' ) . ' (6/13) </h3>' . '<p>' . __( '404 Errors refers to wrong links or missing content on your website.', 'seo-booster' ) . '</p>' . '<p>' . __( 'You should fix any as soon as possible. Use this list to get started.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_404&step2', '404reset' ) . '"',
                ),
                    '404reset'          => array(
                    'id'       => '#reset404s',
                    'content'  => '<h3>' . __( 'Reset 404 errors', 'seo-booster' ) . ' (7/13) </h3>' . '<p>' . __( 'Sometimes the list fills up very quickly and you want to get a clean overview.', 'seo-booster' ) . '</p>' . '<p>' . __( 'Use the Reset button to clean the list and start over.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_log', 'log' ) . '"',
                ),
                    'log'               => array(
                    'id'       => '#log-pointer-target',
                    'content'  => '<h3>' . __( 'Whats going on?', 'seo-booster' ) . ' (8/13) </h3>' . '<p>' . __( 'Per default only most important events is logged.', 'seo-booster' ) . '</p>' . '<p>' . __( 'If you want more details, go to the settings page and turn on "Debug Logging".', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_settings', 'autolink' ) . '"',
                ),
                    'autolink'          => array(
                    'id'       => '#seobooster_internal_linking',
                    'content'  => '<h3>' . __( 'Turn keywords to links', 'seo-booster' ) . ' (9/13) </h3>' . '<p>' . __( 'Enable this to turn keywords in your content in to links to the appropriate pages.', 'seo-booster' ) . '</p>' . '<p>' . __( 'Keywords comes from the keywords tracked by the plugin, the keyword you enter manually per page or via the Focus Keyword in Yoast SEO plugin.', 'seo-booster' ) . '</p>' . '<p><strong>' . __( 'If you discover problems you can turn this feature off on individual pages or turn it off again.', 'seo-booster' ) . '</strong></p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_settings&step2', 'filterkeywords' ) . '"',
                ),
                    'filterkeywords'    => array(
                    'id'       => '#seobooster_ignorelist',
                    'content'  => '<h3>' . __( 'Filter out keywords', 'seo-booster' ) . ' (10/13) </h3>' . '<p>' . __( 'Sometimes you get a keyword in your system you do not want to use on your website, filter out any you do not want here.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_settings&step3', 'ignoreinternal' ) . '"',
                ),
                    'ignoreinternal'    => array(
                    'id'       => '#seobooster_ignore_internal_searches',
                    'content'  => '<h3>' . __( 'Ignore internal searches', 'seo-booster' ) . ' (11/13) </h3>' . '<p>' . __( 'Visitors clicking on search results on your own website are also tracked. You can turn this off here if you wish only keywords from external visitors.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_settings&step4', 'autotag' ) . '"',
                ),
                    'autotag'           => array(
                    'id'       => '#seobooster_dynamic_tagging',
                    'content'  => '<h3>' . __( 'Automatic Tagging', 'seo-booster' ) . ' (12/13) </h3>' . '<p>' . __( 'The keywords people use to find your content can be used as Tags for your posts and pages.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_settings&step5', 'emailreport' ) . '"',
                ),
                    'emailreport'       => array(
                    'id'       => '#seobooster_weekly_email',
                    'content'  => '<h3>' . __( 'Weekly Email Reports', 'seo-booster' ) . ' (13/13) </h3>' . '<p>' . __( 'Enable this and enter your e-mail to get a weekly update from your website about new keywords and incoming links.', 'seo-booster' ) . '</p>',
                    'button2'  => __( 'Next', 'seo-booster' ),
                    'function' => 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_dashboard', 'lastone' ) . '"',
                ),
                    'lastone'           => array(
                    'id'      => '#sbpmarketingbox',
                    'content' => '<h3>' . __( 'End of the Guided Tour', 'seo-booster' ) . '</h3>' . '<p>' . __( 'Thank you for finishing the tour. This was just a quick tour of the most important features - there are more cool features for you to discover.', 'seo-booster' ) . '</p>' . '<p><strong>' . __( 'Need Help?', 'seo-booster' ) . '</strong></p>' . '<p><a href="https://support.cleverplugins.com/" target="_blank">' . __( 'Knowledge Base', 'seo-booster' ) . '</br>
			<a href="https://cleverplugins.com/support/" target="_blank">' . __( 'Contact Support', 'seo-booster' ) . '</a></p>' . '<p>' . __( 'Please leave a review if you like the plugin.', 'seo-booster' ) . '</p>',
                ),
                );
                /*
                
                
                CRAWLED CONTENT
                - From version 3, visits by search engine crawlers are also monitored. This way you can see which pages are visited by robots and how often.
                
                // todo - scroll in to view?
                */
                // Determine which tab is set in the query variable
                $tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : '' );
                // Define other variables
                $function = '';
                $button2 = '';
                $options = array();
                $show_pointer = false;
                // *******************************************************************************************************
                // This will be the first pointer shown to the user.
                // If no query variable is set in the url.. then the 'tab' cannot be determined... and we start with this pointer.
                // *******************************************************************************************************
                
                if ( !array_key_exists( $tab, $tour ) ) {
                    $show_pointer = true;
                    $file_error = true;
                    $id = '#toplevel_page_sb2_dashboard';
                    // Define ID used on page html element where we want to display pointer
                    $content = '<h3>SEO Booster v.' . SEOBOOSTER_VERSION . '</h3>';
                    $content .= '<p><strong>' . __( 'Thank you for installing SEO Booster :-)', 'seo-booster' ) . '</strong></p>';
                    $content .= '<p>' . __( 'This Quick Guided Tour will help you learn the interface in a few minutes.', 'seo-booster' ) . '</p>';
                    $content .= '<p>' . __( 'Click the <em>Begin Tour</em> button to get started.', 'seo-booster' ) . '</p>';
                    $content .= '<p><small>' . __( 'If you want to watch it later use the <em>Close</em> button and start the tour from the bottom of the Settings page.', 'seo-booster' ) . '</small></p>';
                    $options = array(
                        'content'  => $content,
                        'position' => array(
                        'edge'  => 'bottom',
                        'align' => 'left',
                    ),
                    );
                    $button2 = __( 'Begin Tour', 'seo-booster' );
                    $function = 'document.location="' . $this->get_admin_url( 'admin.php?page=sb2_dashboard', 'dashboard' ) . '";';
                } else {
                    
                    if ( $tab != '' && in_array( $tab, array_keys( $tour ) ) ) {
                        $show_pointer = true;
                        if ( isset( $tour[$tab]['id'] ) ) {
                            $id = $tour[$tab]['id'];
                        }
                        $options = array(
                            'content'  => $tour[$tab]['content'],
                            'position' => array(
                            'edge'  => 'top',
                            'align' => 'left',
                        ),
                        );
                        $button2 = false;
                        $function = '';
                        if ( isset( $tour[$tab]['button2'] ) ) {
                            $button2 = $tour[$tab]['button2'];
                        }
                        if ( isset( $tour[$tab]['function'] ) ) {
                            $function = $tour[$tab]['function'];
                        }
                    }
                
                }
                
                // If we are showing a pointer... let's load the jQuery.
                if ( $show_pointer ) {
                    $this->make_pointer_script(
                        $id,
                        $options,
                        __( 'Close', 'seo-booster' ),
                        $button2,
                        $function
                    );
                }
            }
            
            // Print footer scripts
            function make_pointer_script(
                $id,
                $options,
                $button1,
                $button2 = false,
                $function = ''
            )
            {
                ?>
		<script type="text/javascript">
			(function ($) {
						// Define pointer options
						var wp_pointers_tour_opts = <?php 
                echo  json_encode( $options ) ;
                ?>, setup;

						wp_pointers_tour_opts = $.extend (wp_pointers_tour_opts, {

			// Add 'Close' button
			buttons: function (event, t) {

				button = jQuery ('<a id="pointer-close" class="button-secondary">' + '<?php 
                echo  $button1 ;
                ?>' + '</a>');
				button.bind ('click.pointer', function () {
					t.element.pointer ('close');
				});
				return button;
			},
			close: function () {

	// Post to admin ajax to disable pointers when user clicks "Close"
	$.post (ajaxurl, {
		pointer: 'sbp_tour_pointer',
		action: 'dismiss-wp-pointer'
	});
}
});

// This is used for our "button2" value above (advances the pointers)
setup = function () {

	$('<?php 
                echo  $id ;
                ?>').pointer(wp_pointers_tour_opts).pointer('open');
// Scrolls to top - Effective, but annoying.
/*
$('html, body').animate({
scrollTop: $("<?php 
                echo  $id ;
                ?>").offset().top-50
}, 2000);
*/
<?php 
                
                if ( $button2 ) {
                    ?>

	jQuery ('#pointer-close').after ('<a id="pointer-primary" class="button-primary">' + '<?php 
                    echo  $button2 ;
                    ?>' + '</a>');
	jQuery ('#pointer-primary').click (function () {
	<?php 
                    echo  $function ;
                    ?>  // Execute button2 function
});
	jQuery ('#pointer-close').click (function () {

// Post to admin ajax to disable pointers when user clicks "Close"
$.post (ajaxurl, {
	pointer: 'sbp_tour_pointer',
	action: 'dismiss-wp-pointer'
});
})
<?php 
                }
                
                ?>
};

if (wp_pointers_tour_opts.position && wp_pointers_tour_opts.position.defer_loading) {

	$(window).bind('load.wp-pointers', setup);
}
else {
	setup ();
}
}) (jQuery);
</script>
<?php 
            }
            
            function verifyurl( $inurl )
            {
                
                if ( preg_match( '/^(http|https):\\/\\/[a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.[a-z]{2,6}' . '((:[0-9]{1,5})?\\/.*)?$/i', $inurl ) ) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            
            }
            
            function src_load_widgets()
            {
                register_widget( 'seobooster_keywords_widget' );
                register_widget( 'seobooster_dyn_widget' );
            }
            
            /**
             *
             * list_keywords() - Lists keywords for current url, filters out old keywords over 30 days
             *
             **/
            function list_keywords( $limit = 10, $currurl = '' )
            {
                if ( !$currurl ) {
                    $currurl = $this->seobooster_currenturl();
                }
                if ( !$currurl ) {
                    return;
                }
                global  $post, $wpdb ;
                $kwtable = $wpdb->prefix . "sb2_kw";
                $ignorelist = get_option( 'seobooster_ignorelist' );
                // $ignorelistsize = sizeof($ignorelist); //removed 3.3.28
                $parsed = preg_replace( "/\r|\n/", ',', $ignorelist );
                $parsed = preg_replace( '/,+/', ',', $parsed );
                
                if ( $ignorelist ) {
                    $sqlignore = $this->seobooster_generateignorelist( $ignorelist );
                } else {
                    $sqlignore = '';
                }
                
                // AND lastvisit > DATE_SUB(NOW(), INTERVAL 30 DAY) removed in 2.3
                $query = "SELECT DISTINCT(kw) FROM {$wpdb->prefix}sb2_kw WHERE {$sqlignore} lp like '%{$currurl}' AND ig='0' AND kw<>'#'  ORDER BY visits DESC LIMIT {$limit};";
                $kws = $wpdb->get_results( $query, ARRAY_A );
                $kwlist = '';
                
                if ( $kws ) {
                    $count = count( $kws );
                    $step = 0;
                    foreach ( $kws as $kw ) {
                        $step++;
                        $kwlist .= stripslashes( trim( $kw['kw'] ) );
                        if ( $step > 0 and $step < $count ) {
                            $kwlist .= ', ';
                        }
                    }
                }
                
                
                if ( $kwlist ) {
                    return $kwlist;
                } else {
                    // no $kwlist, return false
                    return false;
                }
            
            }
            
            function on_init()
            {
                $dyntag = get_option( 'seobooster_dynamic_tagging' );
                $dynamic_tag_assigncpts = get_option( 'seobooster_dynamic_tag_assigncpts' );
                
                if ( $dyntag == 'on' and $dynamic_tag_assigncpts == 'on' ) {
                    $dyntagtaxonomy = get_option( 'seobooster_dynamic_tag_taxonomy' );
                    $cpts = get_post_types( array(
                        'public'             => true,
                        'publicly_queryable' => true,
                    ), 'names', 'and' );
                    $cpts = array_merge( $cpts, array( 'page' ) );
                    // add the cpt 'page' to the list.
                    
                    if ( $cpts ) {
                        foreach ( $cpts as $cpt ) {
                            register_taxonomy_for_object_type( $dyntagtaxonomy, $cpt );
                        }
                        // foreach ($cpts as $cpt)
                    }
                    
                    // if ($cpts)
                }
                
                // if ($dyntag)
            }
            
            function seobooster_activate( $network_wide )
            {
                global  $wpdb ;
                // Todo - put in rest here.
                $timestamp = wp_next_scheduled( 'sbp_email_update' );
                
                if ( $timestamp == false ) {
                    wp_schedule_event( time(), 'daily', 'do_action_sbp_email_update' );
                    // wp_schedule_event( time(), 'daily',  array(&$this, 'sbp_email_update') );
                }
                
                if ( !wp_next_scheduled( 'sbp_dailymaintenance' ) ) {
                    wp_schedule_event( time(), 'daily', 'sbp_dailymaintenance' );
                }
                if ( !wp_next_scheduled( 'sbp_email_update' ) ) {
                    wp_schedule_event( time(), 'weekly', 'sbp_email_update' );
                }
                
                if ( seobooster_fs()->is_plan_or_trial( 'pro' ) ) {
                    if ( !wp_next_scheduled( 'sbp_checkbacklink' ) ) {
                        wp_schedule_event( time() + 5, '5min', 'sbp_checkbacklink' );
                    }
                    // Temporarily removed in 3.3.2
                    
                    if ( !wp_next_scheduled( 'sbp_crawl_internal' ) ) {
                        wp_schedule_event( time() + 1, '5min', 'sbp_crawl_internal' );
                        // todo debug stoppppp
                    }
                
                }
                
                // Multisite
                
                if ( is_multisite() && $network_wide ) {
                    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
                    foreach ( $blog_ids as $blog_id ) {
                        switch_to_blog( $blog_id );
                        $this->create_database_tables();
                        restore_current_blog();
                    }
                } else {
                    $this->create_database_tables();
                }
            
            }
            
            function seobooster_deactivate( $network_wide )
            {
                global  $wpdb ;
                $seobooster_delete_deactivate = get_option( 'seobooster_delete_deactivate' );
                
                if ( $seobooster_delete_deactivate ) {
                    // Multisite
                    
                    if ( is_multisite() && $network_wide ) {
                        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
                        foreach ( $blog_ids as $blog_id ) {
                            switch_to_blog( $blog_id );
                            $table_name = $wpdb->prefix . 'sb2_404';
                            $sql = "DROP TABLE IF EXISTS {$table_name}";
                            $wpdb->query( $sql );
                            $table_name = $wpdb->prefix . 'sb2_bl';
                            $sql = "DROP TABLE IF EXISTS {$table_name}";
                            $wpdb->query( $sql );
                            $table_name = $wpdb->prefix . 'sb2_kw';
                            $sql = "DROP TABLE IF EXISTS {$table_name}";
                            $wpdb->query( $sql );
                            $table_name = $wpdb->prefix . 'sb2_kwdt';
                            $sql = "DROP TABLE IF EXISTS {$table_name}";
                            $wpdb->query( $sql );
                            $table_name = $wpdb->prefix . 'sb2_log';
                            $sql = "DROP TABLE IF EXISTS {$table_name}";
                            $wpdb->query( $sql );
                            $table_name = $wpdb->prefix . 'sb2_crawl';
                            $sql = "DROP TABLE IF EXISTS {$table_name}";
                            $wpdb->query( $sql );
                            restore_current_blog();
                        }
                    } else {
                        // This is not multisite
                        $table_name = $wpdb->prefix . 'sb2_404';
                        $sql = "DROP TABLE IF EXISTS {$table_name}";
                        $wpdb->query( $sql );
                        $table_name = $wpdb->prefix . 'sb2_bl';
                        $sql = "DROP TABLE IF EXISTS {$table_name}";
                        $wpdb->query( $sql );
                        $table_name = $wpdb->prefix . 'sb2_kw';
                        $sql = "DROP TABLE IF EXISTS {$table_name}";
                        $wpdb->query( $sql );
                        $table_name = $wpdb->prefix . 'sb2_kwdt';
                        $sql = "DROP TABLE IF EXISTS {$table_name}";
                        $wpdb->query( $sql );
                        $table_name = $wpdb->prefix . 'sb2_log';
                        $sql = "DROP TABLE IF EXISTS {$table_name}";
                        $wpdb->query( $sql );
                        $table_name = $wpdb->prefix . 'sb2_crawl';
                        $sql = "DROP TABLE IF EXISTS {$table_name}";
                        $wpdb->query( $sql );
                    }
                    
                    delete_option( 'seobooster_delete_deactivate' );
                    // Lets us go again
                }
                
                // if $seobooster_delete_deactivate
            }
            
            function create_database_tables()
            {
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                $time = date( 'Y-m-d H:i:s ', time() );
                global  $wpdb ;
                $charset_collate = $wpdb->get_charset_collate();
                $table_name = $wpdb->prefix . "sb2_bl";
                $sql = "CREATE TABLE {$table_name} (\n\t\tid bigint(20) NOT NULL AUTO_INCREMENT,\n\t\tig tinyint(1) NOT NULL,\n\t\tdomain text NOT NULL,\n\t\tref varchar(1024) NOT NULL,\n\t\thttpstatus varchar(3) NOT NULL,\n\t\terrorcount smallint(1) NOT NULL DEFAULT '0',\n\t\tverified smallint(1) NOT NULL DEFAULT '0',\n\t\tlp varchar(1024) NOT NULL,\n\t\tanchor text NOT NULL,\n\t\thref text NOT NULL,\n\t\timg tinyint(4) NOT NULL,\n\t\tvisits int(11) NOT NULL,\n\t\tfirstvisit timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n\t\tlastvisit timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',\n\t\tlastcheck timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',\n\t\tnflw tinyint(4) NOT NULL,\n\t\tPRIMARY KEY  (id),\n\t\tKEY ref (ref(255)),\n\t\tKEY ig (ig)\n\t) {$charset_collate};";
                dbDelta( $sql );
                $table_name = $wpdb->prefix . "sb2_kwdt";
                $sql = "CREATE TABLE {$table_name} (\n\tid int(11) NOT NULL AUTO_INCREMENT,\n\trefid int(11) NOT NULL,\n\tdaday date NOT NULL,\n\tvisits int(11) NOT NULL DEFAULT '1',\n\tavgpos int(11) NOT NULL DEFAULT '0',\n\tcdhits int(11) NOT NULL DEFAULT '0',\n\tPRIMARY KEY  (id),\n\tKEY refid (refid),\n\tKEY daday (daday)\n) {$charset_collate};";
                dbDelta( $sql );
                $table_name = $wpdb->prefix . "sb2_crawl";
                $sql = "CREATE TABLE {$table_name} (\nid bigint(20) NOT NULL AUTO_INCREMENT,\nurl varchar(1024) NOT NULL,\nlastcrawl timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\nvisits int(11) NOT NULL DEFAULT 1,\nengine varchar(50) NOT NULL,\nPRIMARY KEY  (id),\nKEY url (url),\nKEY engine (engine)\n) {$charset_collate};";
                dbDelta( $sql );
                $table_name = $wpdb->prefix . "sb2_urls";
                $sql = "CREATE TABLE {$table_name} (\nID int(11) NOT NULL AUTO_INCREMENT,\nurlkey varchar(32) NOT NULL,\nurl varchar(1024) NOT NULL,\nabsurl varchar(1024) NOT NULL,\nhttp_code varchar(3) NOT NULL,\nrefid int(11) DEFAULT NULL,\ncode varchar(3) NOT NULL,\nscraped timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,\nPRIMARY KEY  (ID),\nKEY urlkey (urlkey)\n) {$charset_collate};";
                dbDelta( $sql );
                $table_name = $wpdb->prefix . "sb2_urls_meta";
                $sql = "CREATE TABLE `{$table_name}` (\nmeta_id bigint(20) NOT NULL AUTO_INCREMENT,\nrefid int(11) NOT NULL,\nname varchar(255) NOT NULL,\nvalue text NOT NULL,\nPRIMARY KEY  (meta_id),\nKEY refid (refid),\nKEY name (name)\n) {$charset_collate};";
                dbDelta( $sql );
                $table_name = $wpdb->prefix . "sb2_404";
                $sql = "CREATE TABLE {$table_name} (\nid int(11) NOT NULL AUTO_INCREMENT,\nlp varchar(500) NOT NULL,\nfirstseen timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',\nlastseen timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',\nvisits int(11) NOT NULL,\nreferer text NOT NULL,\nPRIMARY KEY  (id)\n) {$charset_collate};";
                dbDelta( $sql );
                $logtable_name = $wpdb->prefix . "sb2_log";
                $sql = "CREATE TABLE {$logtable_name} (\nlogtime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\nprio tinyint(1) NOT NULL,\nlog varchar(2048) NOT NULL,\nKEY logtime (logtime)\n) {$charset_collate};";
                dbDelta( $sql );
                $table_name = $wpdb->prefix . "sb2_kw";
                $sql = "CREATE TABLE {$table_name} (\nid int(11) NOT NULL AUTO_INCREMENT,\nig tinyint(4) NOT NULL,\nkw varchar(255) NOT NULL,\nterm_id bigint(20) unsigned NOT NULL DEFAULT '0',\nlp varchar(500) NOT NULL,\ngoogletld varchar(30) NOT NULL,\nvisits int(11) NOT NULL,\nfirstvisit timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\nlastvisit timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',\n`engine` text NOT NULL,\nPRIMARY KEY  (id),\nKEY lp (lp),\nKEY id (id),\nKEY kw (kw)\n) {$charset_collate};";
                dbDelta( $sql );
                if ( get_option( 'SEOBOOSTER_INSTALLED_DB_VERSION' ) != SEOBOOSTER_DB_VERSION ) {
                    $this->log( __( 'Updated database tables', 'seo-booster' ) );
                }
                update_option( 'SEOBOOSTER_INSTALLED_DB_VERSION', SEOBOOSTER_DB_VERSION );
                // Storing DB version for later use
            }
            
            /*
            function do_admin_notices() {
            
            	$is_sb2_admin_page = $this->is_sb2_admin_page();
            // @todo
            }
            */
            function filter_cron_schedules( $param )
            {
                $newschedules = array(
                    '1min' => array(
                    'interval' => 60,
                    'display'  => __( 'Every minute', 'seo-booster' ),
                ),
                    '5min' => array(
                    'interval' => 300,
                    'display'  => __( 'Every 5 minutes', 'seo-booster' ),
                ),
                );
                return array_merge( $param, $newschedules );
            }
            
            function admin_init()
            {
                
                if ( seobooster_fs()->is_plan_or_trial( 'pro' ) ) {
                    if ( isset( $_GET['action'] ) && $_GET['action'] == 'sbp_404_export_csv' ) {
                        $this->download_404_csv__premium_only();
                    }
                    if ( isset( $_GET['action'] ) && $_GET['action'] == 'sbp_backlinks_export_csv' ) {
                        $this->download_backlinks_csv__premium_only();
                    }
                    if ( isset( $_GET['action'] ) && $_GET['action'] == 'sbp_backlinks_export_keywords' ) {
                        $this->download_keywords_csv__premium_only();
                    }
                }
                
                register_setting( 'seobooster', 'seobooster_enable_pagespeed' );
                register_setting( 'seobooster', 'seobooster_pagespeed_api_key' );
                register_setting( 'seobooster', 'seobooster_showsearch_queries' );
                register_setting( 'seobooster', 'seobooster_internal_linking' );
                register_setting( 'seobooster', 'seobooster_internal_links_limit' );
                register_setting( 'seobooster', 'seobooster_use_sb_kwdata' );
                register_setting( 'seobooster', 'seobooster_use_yoast_focus_kw' );
                register_setting( 'seobooster', 'seobooster_dynamic_tagging' );
                register_setting( 'seobooster', 'seobooster_dynamic_tagging_related' );
                register_setting( 'seobooster', 'seobooster_dynamic_tag_taxonomy' );
                register_setting( 'seobooster', 'seobooster_dynamic_tag_assigncpts' );
                register_setting( 'seobooster', 'seobooster_dynamic_tag_maximum' );
                register_setting( 'seobooster', 'seobooster_dynamic_tag_minlength' );
                register_setting( 'seobooster', 'seobooster_dynamic_tag_maxlength' );
                register_setting( 'seobooster', 'seobooster_weekly_email' );
                register_setting( 'seobooster', 'seobooster_weekly_email_recipient' );
                register_setting( 'seobooster', 'seobooster_rss_key' );
                register_setting( 'seobooster', 'seobooster_fof_monitoring' );
                register_setting( 'seobooster', 'seobooster_ignorelist' );
                register_setting( 'seobooster', 'seobooster_debug_logging' );
                register_setting( 'seobooster', 'seobooster_title_boost' );
                register_setting( 'seobooster', 'seobooster_ignore_internal_searches' );
            }
            
            /**
             *
             * add_pages() -
             *
             **/
            function add_pages()
            {
                add_menu_page(
                    __( 'SEO Booster', 'seo-booster' ) . ' ' . __( 'General Settings', 'seo-booster' ),
                    __( 'SEO Booster', 'seo-booster' ),
                    'manage_options',
                    'sb2_dashboard',
                    array( &$this, 'add_seobooster2_main' ),
                    $this->get_icon_svg()
                );
                add_submenu_page(
                    'sb2_dashboard',
                    __( 'Keywords', 'seo-booster' ),
                    __( 'Keywords', 'seo-booster' ),
                    'manage_options',
                    'sb2_keywords',
                    array( &$this, 'add_seobooster2_kwpage' )
                );
                add_submenu_page(
                    'sb2_dashboard',
                    __( 'Backlinks', 'seo-booster' ),
                    __( 'Backlinks', 'seo-booster' ),
                    'manage_options',
                    'sb2_backlinks',
                    array( &$this, 'add_seobooster2_blpage' )
                );
                add_submenu_page(
                    'sb2_dashboard',
                    __( 'Lost Traffic', 'seo-booster' ),
                    __( 'Lost Traffic', 'seo-booster' ),
                    'manage_options',
                    'sb2_forgotten',
                    array( &$this, 'add_seobooster2_forgottenpage' )
                );
                add_submenu_page(
                    'sb2_dashboard',
                    __( '404s', 'seo-booster' ),
                    __( '404 Errors', 'seo-booster' ),
                    'manage_options',
                    'sb2_404',
                    array( &$this, 'add_seobooster2_404page' )
                );
                add_submenu_page(
                    'sb2_dashboard',
                    __( 'Log', 'seo-booster' ),
                    __( 'Log', 'seo-booster' ),
                    'manage_options',
                    'sb2_log',
                    array( &$this, 'add_seobooster2_logpage' )
                );
                add_submenu_page(
                    'sb2_dashboard',
                    __( 'Settings', 'seo-booster' ),
                    __( 'Settings', 'seo-booster' ),
                    'manage_options',
                    'sb2_settings',
                    array( &$this, 'add_seobooster2_settings' )
                );
            }
            
            function add_seobooster2_main()
            {
                include SEOBOOSTER_PLUGINPATH . 'seo-booster-seobooster2.php';
            }
            
            function add_seobooster2_settings()
            {
                include SEOBOOSTER_PLUGINPATH . 'seo-booster-settings.php';
            }
            
            function add_seobooster2_kwpage()
            {
                include SEOBOOSTER_PLUGINPATH . 'seo-booster-keywords.php';
            }
            
            function add_seobooster2_blpage()
            {
                include SEOBOOSTER_PLUGINPATH . 'seo-booster-backlinks.php';
            }
            
            function add_seobooster2_forgottenpage()
            {
                include SEOBOOSTER_PLUGINPATH . 'seo-booster-forgotten.php';
            }
            
            function add_seobooster2_404page()
            {
                include SEOBOOSTER_PLUGINPATH . 'seo-booster-404s.php';
            }
            
            function add_seobooster2_logpage()
            {
                include SEOBOOSTER_PLUGINPATH . 'seo-booster-log.php';
            }
            
            /**
             *
             * log() -
             *
             **/
            function log( $text, $prio = 0 )
            {
                /*
                	0: normal, muted
                	1:
                	2: error
                	3: warning
                
                	5: info
                	10: SUCCESS
                */
                global  $wpdb ;
                $table_name_log = $wpdb->prefix . "sb2_log";
                $wpdb->insert( $table_name_log, array(
                    'logtime' => current_time( 'mysql' ),
                    'prio'    => $prio,
                    'log'     => $text,
                ), array( '%s', '%d', '%s' ) );
            }
            
            /**
             *
             * remove_http() - Function strips http:// or https://
             *
             **/
            function remove_http( $url = '' )
            {
                if ( $url == 'http://' or $url == 'https://' ) {
                    return $url;
                }
                $matches = substr( $url, 0, 7 );
                
                if ( $matches == 'http://' ) {
                    $url = substr( $url, 7 );
                } else {
                    $matches = substr( $url, 0, 8 );
                    if ( $matches == 'https://' ) {
                        $url = substr( $url, 8 );
                    }
                }
                
                return $url;
            }
            
            function timerstart( $watchname )
            {
                set_transient( 'sb2_' . $watchname, microtime( true ), 60 * 60 * 1 );
            }
            
            function timerstop( $watchname, $digits = 5 )
            {
                $return = round( microtime( true ) - get_transient( 'sb2_' . $watchname ), $digits );
                delete_transient( 'sb2_' . $watchname );
                return $return;
            }
            
            function truncatestring( $string, $del )
            {
                $len = strlen( $string );
                
                if ( $len > $del ) {
                    $new = substr( $string, 0, $del ) . "...";
                    return $new;
                } else {
                    return $string;
                }
            
            }
            
            function add_dashboard_widget()
            {
                wp_add_dashboard_widget( 'add_dashboard_widget', __( 'SEO Booster', 'seo-booster' ), array( &$this, 'dashboard_widget' ) );
            }
            
            function dashboard_widget()
            {
                global  $wpdb ;
                $kwtable = $wpdb->prefix . "sb2_kw";
                $query = "SELECT kw,visits,lp FROM `{$kwtable}` where `ig`<>'1' AND `kw`<>'#' GROUP BY kw ORDER BY visits DESC limit 10;";
                $keywords = $wpdb->get_results( $query, ARRAY_A );
                
                if ( $keywords ) {
                    ?>
			<div id="top-keywords">
				<h3><?php 
                    _e( 'Top Keywords', 'seo-booster' );
                    ?></h3>
				<table>
					<thead>
						<tr>
							<th><?php 
                    _e( 'Keywords', 'seo-booster' );
                    ?></th>
							<th><?php 
                    _e( 'Visits', 'seo-booster' );
                    ?></th>
							<th><?php 
                    _e( 'Page', 'seo-booster' );
                    ?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
                    foreach ( $keywords as $kw ) {
                        $output = '<tr>';
                        $output .= '<td>' . $kw['kw'] . '</td>';
                        $output .= '<td>' . number_format_i18n( $kw['visits'] ) . '</td>';
                        $output .= '<td><a href="' . $kw['lp'] . '" target="_blank">' . $kw['lp'] . '</a></td>';
                        $output .= '</tr>';
                        echo  $output ;
                    }
                    ?>
					</tbody>
				</table>
			</div>
			<?php 
                }
                
                // if ($keywords)
                $logtable = $wpdb->prefix . "sb2_log";
                $query = "SELECT * FROM `{$logtable}` order by `logtime` DESC limit 10;";
                $logs = $wpdb->get_results( $query, ARRAY_A );
                
                if ( $logs ) {
                    ?>
	<div id="recent-events">
		<h4><?php 
                    _e( 'Recent Events', 'seo-booster' );
                    ?></h4>
		<table>
			<?php 
                    $time = current_time( 'mysql' );
                    foreach ( $logs as $log ) {
                        echo  "<tr><td class='tid'>" . human_time_diff( strtotime( $log['logtime'] ), strtotime( $time ) ) . " " . __( 'ago', 'seo-booster' ) . "</td><td class='log'>" . stripslashes( $log['log'] ) . "</td></tr>" ;
                    }
                    ?>
		</table>
	</div>
	<?php 
                }
                
                // if ($logs)
            }
            
            // dashboard_widget()
            function _IsSearch( $inurl, $returnlist = FALSE )
            {
                if ( !$inurl ) {
                    return false;
                }
                include 'inc/search_engines.php';
                if ( $returnlist ) {
                    return $sengine;
                }
                $url_info = parse_url( $inurl );
                // parse the url
                if ( $url_info['host'] ) {
                    $rootdomain = $url_info['host'];
                }
                foreach ( $sengine as $se ) {
                    $strpos = strpos( $inurl, $se['u'] );
                    // First we try the classical parameter way
                    
                    if ( $strpos !== FALSE ) {
                        
                        if ( isset( $se['q'] ) ) {
                            $parsed = parse_url( $inurl, PHP_URL_QUERY );
                            parse_str( $parsed, $query_info );
                            $query_field_in_use = $se['q'];
                            $returnarr = array(
                                'sengine_name' => $rootdomain,
                                'Se'           => $url_info['host'],
                                'Referstring'  => $inurl,
                            );
                            
                            if ( isset( $query_info[$query_field_in_use] ) ) {
                                $returnarr['Query'] = strtolower( $query_info[$query_field_in_use] );
                            } else {
                                $returnarr['Query'] = '#';
                            }
                            
                            return $returnarr;
                        }
                        
                        // Lets try a regexp match
                        
                        if ( isset( $se['m'] ) ) {
                            $matches = array();
                            
                            if ( preg_match( $se['m'], $inurl, $matches ) ) {
                                $keyword = strtolower( $matches[1] );
                                $keyword = str_replace( '-', ' ', $keyword );
                                $returnarr = array(
                                    'sengine_name' => $rootdomain,
                                    'Se'           => $url_info['host'],
                                    'Query'        => $keyword,
                                    'Referstring'  => $inurl,
                                );
                                return $returnarr;
                            }
                        
                        }
                    
                    }
                
                }
                return false;
            }
            
            function ignore_useragent( $testua )
            {
                $uaignorelist = array(
                    'wprocketbot',
                    'Arachnophilia',
                    'AITCSRobot/1.1',
                    'BackDoorBot',
                    'BuiltBotTough',
                    'Mata Hari',
                    'LinkextractorPro',
                    'UptimeRobot/2.0'
                );
                foreach ( $uaignorelist as $uaig ) {
                    if ( stristr( $testua, $uaig ) ) {
                        return true;
                    }
                }
                return false;
            }
            
            function ignore_current_url( $currurl )
            {
                $ignorelist = array(
                    '/wp-login.php',
                    '/wp-content/cache/',
                    '/wp-json/jetpack/',
                    '/wp-admin/',
                    '?doing_wp_cron',
                    'wp-cron.php',
                    'exchange_token=',
                    'sessiontoken=',
                    'wpsc_action=',
                    'wordfence_lh=',
                    '_wfsf=view',
                    '_wfsf=diff',
                    'wordfence_syncAttackData=',
                    'wc-ajax=',
                    '.css',
                    '.js',
                    '.json',
                    '/feed/',
                    'wp-cron.php',
                    '/wp-json/',
                    'cdn.ampproject.org',
                    '/wc-api/',
                    'glid=',
                    'track.adform.net',
                    '/order-received/',
                    'essb_counter_cache=',
                    'uabb-name=',
                    '?add-to-cart=',
                    '&add-to-cart='
                );
                $url_info = parse_url( $currurl );
                // parse the url
                foreach ( $ignorelist as $ig ) {
                    if ( stristr( $currurl, $ig ) ) {
                        return $ig;
                    }
                }
                return false;
            }
            
            function ignorelink( $link )
            {
                $ignorelist = array(
                    '.dev/',
                    '.local/',
                    '.loc/',
                    'auth.miniorange.com/',
                    'hooks.stripe.com',
                    'linkedin.com/sales/accounts/',
                    'linkedin.com/messaging/',
                    'webmail.',
                    '/wp-login.php',
                    '/wp-content/cache/',
                    '/wp-json/jetpack/',
                    'www.uptimedoctor.com',
                    '.pricerunner.',
                    'anonym.to',
                    'googleusercontent.com',
                    'mail.',
                    '/webmail/',
                    'pipes.yahoo.com',
                    'plus.url.google.com',
                    'translate.google.',
                    '/wp-admin/',
                    '.facebook.com',
                    '.facebook.net',
                    'tinyurl.com',
                    'myspace.com',
                    'platform.twitter.com',
                    'm.yahoo.com',
                    'live.ru',
                    'bit.ly',
                    'baidu.com',
                    'stumbleupon.com',
                    'www.seoheap.com',
                    '1.1.1.1',
                    'list-manage.com',
                    'list-manage1.com',
                    'anonym.to',
                    'portal.attnet.mcore.com',
                    'linkwithin.com',
                    'domains.checkparams.com',
                    'whois.domaintools.com',
                    'static.ak.fbcdn.net',
                    'jetpack.wordpress.com',
                    'search.beamrise.com',
                    'htdocs/nokia/startc/',
                    'landing.secretbrowserapp',
                    'm.aol.com',
                    'smt.telcel.com',
                    'whois.domaintools.com',
                    'domains.checkparams.com',
                    'wsdsold.infospace.com',
                    'smt.telcel.com',
                    'scriptmafia.org',
                    'www.movistar.com',
                    'fapanga.com',
                    'wp-cron.php/?doing_wp_cron',
                    'translate.google.',
                    '.list-manage.com',
                    '.pinterest.com',
                    'plus.url.google.com',
                    'feedly.com',
                    'feeds.feedburner.com',
                    '.campaign-archive2.com',
                    '.campaign-archive2.com',
                    '.campaign-archive1.com',
                    '.campaign-archive.com',
                    '.quickpay.',
                    'plus.google.com',
                    '.paypal.',
                    '.doubleclick.net',
                    't.co',
                    'l.instagram.com',
                    'flipboard.com',
                    'googleapis.com',
                    'payment.quickpay.net',
                    '.netdna-cdn.com',
                    '.pinterest.com',
                    'outlook.live.com',
                    '.office.com',
                    '.proxysite.com',
                    '\\/wp-admin\\/',
                    '\\/cgi-bin\\/',
                    'localhost\\:',
                    '\\/\\/redditcom.org',
                    '.monitorbacklinks.com',
                    '.copyscape.com',
                    '.dnsrsearch.com',
                    'ht.ly',
                    'www.discretesearch.com',
                    'www.messenger.com',
                    'ebaydesc.com',
                    'backlinkwatch.com',
                    'tpc.googlesyndication.com',
                    'semrush.com',
                    'mouseflow.com',
                    'cashbackdeals.dk',
                    'adservicemedia.dk',
                    'admin.mailchimp.com',
                    '.googleadservices.',
                    'staff.adservice.com',
                    '.pricerunner.com',
                    'tradedoubler.com',
                    'insights.hotjar.com',
                    '	track.adform.net',
                    'trackcmp.net',
                    'app.intercom.io',
                    'app.accuranker.com',
                    'wordfence_lh=',
                    '_wfsf=view',
                    '_wfsf=diff'
                );
                $url_info = parse_url( $link );
                // parse the url
                foreach ( $ignorelist as $ig ) {
                    if ( stristr( $link, $ig ) ) {
                        return $ig;
                    }
                }
                return false;
            }
            
            function seobooster_currenturl( $full = false )
            {
                //no need to run in the admin...
                if ( is_admin() ) {
                    return;
                }
                $phpdetected = add_query_arg( NULL, NULL );
                if ( !$phpdetected ) {
                    $phpdetected = $_SERVER['REQUEST_URI'];
                }
                // Clean up URL
                
                if ( isset( $phpdetected ) ) {
                    $phpdetected = remove_query_arg( array( 'gclid' ), $phpdetected );
                    // removes various params from url
                }
                
                // Return absolute URL ($full)
                if ( $full ) {
                    return esc_url_raw( site_url( $phpdetected ) );
                }
                return esc_url_raw( $phpdetected );
            }
            
            function checkreferrer( $currurl = null, $referer = null )
            {
                //$this->log('I did not find <code>'.$this->remove_http(site_url()).'</code> related to <code>'.$this->remove_http($referer).'</code>'); // ssss todo
                // If missing parsed refererer, try to find it.
                if ( !$referer ) {
                    if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                        $referer = strtolower( $_SERVER['HTTP_REFERER'] );
                    }
                }
                $searchref = $this->_IsSearch( $referer );
                // returns the search term if detected
                if ( !isset( $currurl ) ) {
                    $currurl = $this->seobooster_currenturl();
                }
                
                if ( $igres = $this->ignore_current_url( $currurl ) ) {
                    if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        $this->log( sprintf( __( "Visit to <code>%s</code> ignored, matched <code>%s</code> in ignorelist.", 'seo-booster' ), str_replace( site_url(), '', $currurl ), $igres ) );
                    }
                    return;
                }
                
                $blogurl = $this->remove_http( site_url() );
                if ( isset( $referer ) ) {
                    $parsedurl = parse_url( $referer );
                }
                if ( isset( $parsedurl ) ) {
                    $domain = $parsedurl['host'];
                }
                // Filter out internal navigation by users - allowing internal searches
                if ( strpos( $referer, site_url() ) !== false && !isset( $parsedurl['query'] ) ) {
                    return;
                }
                /*
                if (get_option('seobooster_debug_logging') == 'on') {
                	$this->log(sprintf(__("Debug: checkreferrer(): Visitor on  <code>%s</code> from <code>%s</code>", 'seo-booster'), $this->remove_http($currurl), $this->remove_http($referer) ) );
                }
                */
                
                if ( isset( $parsedurl['query'] ) ) {
                    parse_str( $parsedurl['query'], $params );
                    // an internal search
                    
                    if ( isset( $params['s'] ) ) {
                        // came from internal search result so lets add the keyword etc..
                        $searchref = array();
                        $searchref['Query'] = $params['s'];
                        $searchingfor = $params['s'];
                        $searchref['sengine_name'] = 'Internal Search';
                        // Constant, dont translate.
                        
                        if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                            $this->log( 'Debug: checkreferrer(): Internal Search - ' . $searchingfor );
                            // @todo @debug
                        }
                    
                    } else {
                    }
                
                }
                
                // isset
                // Reintroduced in 2.4 - Ignore links from builtin list of domain referrers to ignore
                if ( $this->ignorelink( $referer ) ) {
                    // filter backlinks and unwanted links
                    // if (get_option('seobooster_debug_logging') == 'on') {
                    // 	$this->log("Debug: checkreferrer(): ignoring referrer '$referer'");
                    // }
                    return;
                }
                //We ignore number-only searches
                if ( is_numeric( $searchref['Query'] ) ) {
                    return;
                }
                //We ignore "site:"-searches
                $sitepos = strpos( $searchref['Query'], 'site:' );
                if ( !$sitepos === false ) {
                    return;
                }
                $sitepos = strpos( $searchref['Query'], 'related:' );
                //WE IGNORE "RELATED:"-SEARCHES
                if ( !$sitepos === false ) {
                    return;
                }
                $sitepos = strpos( $searchref['Query'], 'cache:' );
                //WE IGNORE "CACHE:"-visits
                
                if ( !$sitepos === false ) {
                    
                    if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        $this->log( "Debug: checkreferrer(): Ignoring 'cache:'" );
                        // @todo @i8n
                    }
                    
                    return;
                }
                
                
                if ( $searchref ) {
                    global  $wpdb, $wp_query ;
                    $table_kw = $wpdb->prefix . "sb2_kw";
                    $gquery = sanitize_text_field( $searchref['Query'] );
                    // Sanitized
                    $engine = sanitize_text_field( $searchref['sengine_name'] );
                    if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        
                        if ( $gquery ) {
                            $this->log( sprintf(
                                __( "Visitor from %s - Searching for <code>%s</code> <a href='%s' target='_blank'>%s</a>", 'seo-booster' ),
                                $engine,
                                $gquery,
                                $currurl,
                                str_replace( site_url(), '', $currurl )
                            ) );
                        } else {
                            $this->log( sprintf(
                                __( "Visitor from %s. <a href='%s' target='_blank'>%s</a>", 'seo-booster' ),
                                $engine,
                                $currurl,
                                $this->remove_http( str_replace( site_url(), '', $currurl ) )
                            ) );
                        }
                    
                    }
                    // sets to # indicating the keyword is unknown
                    if ( $gquery == '' ) {
                        $gquery = '#';
                    }
                    $tld = $parsedurl['host'];
                    // setting tld
                    $excistingentry = $wpdb->get_var( $wpdb->prepare(
                        "SELECT id\n\t\t\t\tFROM {$wpdb->prefix}sb2_kw\n\t\t\t\tWHERE kw = '%s'\n\t\t\t\tAND lp = '%s'\n\t\t\t\tAND engine = '%s'\n\t\t\t\tLIMIT 1;",
                        $gquery,
                        $currurl,
                        $engine
                    ) );
                    
                    if ( $excistingentry != 0 ) {
                        // not a NEW keyword entry, so lets update the visit count..
                        $query = "UPDATE {$wpdb->prefix}sb2_kw SET `visits` = `visits`+1,`lastvisit`='" . date( 'Y-m-d H:i:s' ) . "' WHERE `id` ='{$excistingentry}' LIMIT 1;";
                        $wpdb->query( $query );
                    } else {
                        // a NEW keyword and/or tld, insert into database...
                        
                        if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                            $this->log( "Debug: checkreferrer(): New search <code>{$gquery}</code>from {$engine} " );
                            // @todo @i8n
                        }
                        
                        $wpdb->insert( $table_kw, array(
                            'kw'        => esc_attr( $gquery ),
                            'lp'        => $currurl,
                            'engine'    => $engine,
                            'googletld' => $tld,
                            'visits'    => 1,
                            'lastvisit' => date( 'Y-m-d H:i:s' ),
                        ) );
                        $lastid = $wpdb->insert_id;
                    }
                    
                    // ********* Update daily tracking and avg. position
                    $table_kwdt = $wpdb->prefix . "sb2_kwdt";
                    $today = date( 'Y-m-d' );
                    if ( isset( $lastid ) ) {
                        $refid = $lastid;
                    }
                    if ( isset( $excistingentry ) && !isset( $refid ) ) {
                        $refid = $excistingentry;
                    }
                    
                    if ( $refid ) {
                        // the referring id in the kw table has been found...
                        $visits = $wpdb->get_var( "SELECT visits FROM `{$table_kwdt}` WHERE `refid` = '{$refid}' AND `daday` = '{$today}' limit 1;" );
                        
                        if ( $visits ) {
                            $wpdb->query( $wpdb->prepare( "UPDATE `{$table_kwdt}` SET `visits` = `visits`+1 WHERE `refid` ='%d' AND `daday` = '%s' LIMIT 1 ;", $refid, $today ) );
                            $kwtablerefid = $refid;
                        } else {
                            // new tracking of keyword daily visits...
                            $wpdb->insert( $table_kwdt, array(
                                'refid'  => $refid,
                                'daday'  => $today,
                                'visits' => '1',
                            ), array( '%s', '%s', '%d' ) );
                            $kwtablerefid = $wpdb->insert_id;
                        }
                    
                    }
                    
                    // Parse the URL into an array
                    $url_parts = parse_url( $referer );
                    if ( $url_parts['query'] ) {
                        parse_str( $url_parts['query'], $path_parts );
                    }
                }
                
                // if ($searchref)
                // Verifies its a valid URL or return
                if ( !$this->verifyurl( $referer ) ) {
                    
                    if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        $this->log( sprintf( __( "Ignored invalid URL <code>%s</code>", 'seo-booster' ), $referer ) );
                        return;
                    }
                
                }
                // BACKLINK logging logic
                
                if ( !$searchref && isset( $referer ) && !strstr( parse_url( $referer, PHP_URL_HOST ), $this->remove_http( site_url() ) ) ) {
                    global  $wpdb ;
                    // NOT a visit from a search engine, it might be a backlink then???
                    $table_bl = $wpdb->prefix . "sb2_bl";
                    $excisting = $wpdb->get_var( "SELECT id FROM `{$table_bl}` WHERE `ref` = '{$referer}' limit 1;" );
                    
                    if ( $excisting ) {
                        $query = "UPDATE `{$table_bl}` SET `visits` = `visits`+1,`lastvisit`=NOW(), `ig`='0'  WHERE `id` ='{$excisting}' LIMIT 1 ;";
                        $success = $wpdb->query( $query );
                        // todo - change to ->insert
                    } else {
                        // New backlink$, mark for later research.
                        $details = parse_url( $referer );
                        $wpdb->insert( $table_bl, array(
                            'domain'     => $details['host'],
                            'ref'        => $referer,
                            'lp'         => $currurl,
                            'anchor'     => '',
                            'firstvisit' => current_time( 'mysql' ),
                            'lastvisit'  => current_time( 'mysql' ),
                        ), array(
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s'
                        ) );
                        if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                            $this->log( sprintf( __( "First visit from backlink %s on page %s.", 'seo-booster' ), '<code>' . esc_url( $this->remove_http( $referer ) ) . '</code>', '<code>' . esc_url( $this->remove_http( $currurl ) ) . '</code>' ) );
                        }
                    }
                
                } else {
                    // Not a search engine, not a backlink visitor
                    //error_log('external '.$referer);
                }
                
                // DYNAMIC TAGGING SECTION...
                
                if ( $searchref and $gquery != '#' and !is_int( $gquery ) ) {
                    // if from a search engine, and the query is not an integer and is different from '#'
                    $dynamic_tagging = get_option( 'seobooster_dynamic_tagging' );
                    $dynamic_tagging_rel = get_option( 'seobooster_dynamic_tagging_related' );
                    $dynamic_tag_tax = get_option( 'seobooster_dynamic_tag_taxonomy' );
                    $dynamic_tag_max = get_option( 'seobooster_dynamic_tag_maximum' );
                    $dynamic_tag_minlength = intval( get_option( 'seobooster_dynamic_tag_minlength' ) );
                    $dynamic_tag_maxlength = intval( get_option( 'seobooster_dynamic_tag_maxlength' ) );
                    $dynamic_tag_assigncpts = get_option( 'seobooster_dynamic_tag_assigncpts' );
                    // debug position
                    $termlength = strlen( $gquery );
                    if ( !$dynamic_tag_minlength ) {
                        $dynamic_tag_minlength = 5;
                    }
                    // default setting
                    if ( !$dynamic_tag_maxlength ) {
                        $dynamic_tag_maxlength = 15;
                    }
                    
                    if ( $dynamic_tagging ) {
                        $postid = null;
                        global  $post ;
                        if ( $post ) {
                            $postid = $post->ID;
                        }
                        
                        if ( !isset( $postid ) ) {
                            $postid = url_to_postid( $currurl );
                            // Dont run too early, eg plugins_loaded
                        }
                        
                        
                        if ( !isset( $postid ) ) {
                            $page = get_page_by_path( $currurl );
                            if ( $page ) {
                                $postid = $page->ID;
                            }
                        }
                        
                        $termlength = strlen( $gquery );
                        if ( !$dynamic_tag_minlength ) {
                            $dynamic_tag_minlength = 5;
                        }
                        // default setting
                        if ( !$dynamic_tag_maxlength ) {
                            $dynamic_tag_maxlength = 15;
                        }
                        // default setting
                        
                        if ( $postid && $termlength > $dynamic_tag_minlength && $termlength < $dynamic_tag_maxlength ) {
                            // we figured out the post id!
                            $terms = get_the_terms( $postid, $dynamic_tag_tax );
                            $termcount = count( $terms );
                            
                            if ( $termcount < $dynamic_tag_max ) {
                                // if we are below the max count...
                                $term = term_exists( $gquery, $dynamic_tag_tax );
                                
                                if ( $term !== 0 && $term !== null ) {
                                    
                                    if ( is_array( $term ) ) {
                                        $term_id = intval( $term['term_id'] );
                                    } else {
                                        $term_id = intval( $term );
                                    }
                                
                                } else {
                                    // term does not exist
                                    $newterm = wp_insert_term(
                                        $gquery,
                                        // the term
                                        $dynamic_tag_tax,
                                        // the taxonomy
                                        array(
                                            'description' => $gquery,
                                            'slug'        => sanitize_title( $gquery ),
                                        )
                                    );
                                    $term_id = intval( $newterm['term_id'] );
                                    $term = $newterm;
                                    $posttitle = $wpdb->get_var( "SELECT post_title FROM `{$wpdb->posts}` where ID={$postid};" );
                                    $this->log( sprintf(
                                        __( "Created a new term <a href='%s'>%s</a> in the <code>%s</code> taxonomy. For <a href='%s' target='_blank'>%s</a> (ID %d)", 'seo-booster' ),
                                        admin_url( "edit-tags.php?action=edit&taxonomy={$dynamic_tag_tax}&tag_ID={$term_id}" ),
                                        $gquery,
                                        $dynamic_tag_tax,
                                        get_permalink( $postid ),
                                        $this->remove_http( get_permalink( $postid ) ),
                                        $postid
                                    ), 10 );
                                    // todo - change to ->insert
                                    $sqlstr = "UPDATE  `{$table_kw}` SET  `term_id` =  '" . $term_id . "' WHERE  `id` ={$postid};";
                                    // associate in database with the tag..
                                    $result = $wpdb->query( $sqlstr );
                                }
                                
                                $setresult = wp_set_object_terms(
                                    $postid,
                                    $term_id,
                                    $dynamic_tag_tax,
                                    true
                                );
                                
                                if ( is_wp_error( $setresult ) ) {
                                    $error_string = $setresult->get_error_message();
                                    $this->log( sprintf( __( 'Error tagging <code>%s</code>', 'seo-booster' ), $error_string ) );
                                } else {
                                    $this->log( sprintf(
                                        __( "Tagged <a href='%s'>%s</a> with <a href='%s'>%s</a>", 'seo-booster' ),
                                        get_permalink( $postid ),
                                        str_replace( site_url(), '', get_permalink( $postid ) ),
                                        admin_url( "edit-tags.php?action=edit&taxonomy={$dynamic_tag_tax}&tag_ID={$term_id}" ),
                                        $gquery
                                    ), 5 );
                                }
                                
                                // Automatic tagging related posts
                                
                                if ( $dynamic_tagging_rel == 'on' ) {
                                    $query_args = array(
                                        's'                   => $gquery,
                                        'posts_per_page'      => '25',
                                        'suppress_filters'    => '1',
                                        'ignore_sticky_posts' => true,
                                        'post_status'         => array( 'publish' ),
                                    );
                                    // todo - make a limit how many to automatically tag...
                                    if ( $dynamic_tag_assigncpts ) {
                                        $query_args['post_type'] = 'any';
                                    }
                                    // any is filtered and respects exclude_from_search
                                    $relatedposts = new WP_Query( $query_args );
                                    
                                    if ( $relatedposts->have_posts() ) {
                                        $collectedlist = array();
                                        while ( $relatedposts->have_posts() ) {
                                            $relatedposts->the_post();
                                            // todo - fails with plugins_loaded
                                            $relid = $post->ID;
                                            //$collectedlist[] = $relid;
                                            $relterms = get_the_terms( $relid, $dynamic_tag_tax );
                                            $reltermcount = count( $relterms );
                                            
                                            if ( $reltermcount < $dynamic_tag_max ) {
                                                $setrel = wp_set_object_terms(
                                                    $relid,
                                                    $gquery,
                                                    $dynamic_tag_tax,
                                                    true
                                                );
                                                $this->log( sprintf(
                                                    __( "Tagged related post <a href='%s'>%s</a> with <a href='%s'>%s</a>", 'seo-booster' ),
                                                    get_permalink( $relid ),
                                                    str_replace( site_url(), '', get_permalink( $relid ) ),
                                                    admin_url( "edit-tags.php?action=edit&taxonomy={$dynamic_tag_tax}&tag_ID={$term_id}" ),
                                                    $gquery
                                                ) );
                                            }
                                            
                                            // if ($reltermcount<$dynamic_tag_max)
                                        }
                                        // while ( $the_query->have_posts() )
                                    }
                                    
                                    //	if ( $relatedposts->have_posts() )
                                    //unset($relatedposts);
                                    wp_reset_postdata();
                                }
                                
                                // if ($dynamic_tagging_rel=='on')
                            }
                            
                            // if ($termcount<$dynamic_tag_max)
                        } else {
                            // no postid found, no idea what/where to tag!
                        }
                    
                    }
                
                }
            
            }
            
            /**
             *
             * do_seobooster_maintenance() -
             *
             **/
            function do_seobooster_maintenance()
            {
                global  $wpdb ;
                $changecount = 0;
                $bltable = $wpdb->prefix . "sb2_bl";
                $kwtable = $wpdb->prefix . "sb2_kw";
                $kwdttable = $wpdb->prefix . "sb2_kwdt";
                $logtable = $wpdb->prefix . "sb2_log";
                $urlstable = $wpdb->prefix . "sb2_urls";
                $urlsmetatable = $wpdb->prefix . "sb2_urls_meta";
                $sb2_maintenance_urls_table_stage = get_option( 'sb2_maintenance_urls_table_stage' );
                if ( !$sb2_maintenance_urls_table_stage ) {
                    $sb2_maintenance_urls_table_stage = 0;
                }
                // Get count of how many log entries there are.
                $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$logtable}`;" );
                // deleting old daily keyword tracking
                $oldkwdtcount = $wpdb->get_var( "SELECT count(refid) FROM {$kwdttable} WHERE refid NOT IN (SELECT id FROM {$kwtable});" );
                
                if ( $oldkwdtcount > 0 ) {
                    $wpdb->query( "DELETE FROM {$kwdttable} WHERE refid NOT IN (SELECT id FROM {$kwtable}) LIMIT 5000;" );
                    if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                        $this->log( sprintf( __( 'Removed %s old daily keywords tracking data.', 'seo-booster' ), number_format_i18n( $oldkwdtcount ) ) );
                    }
                }
                
                $maxlogentries = 2000;
                
                if ( $total > $maxlogentries * 2 ) {
                    $targettime = $wpdb->get_var( "SELECT `logtime` from `{$logtable}` order by `logtime` DESC limit {$maxlogentries},1;" );
                    // find timestamp for last insert
                    $query = "DELETE from `{$logtable}`  where `logtime` < '{$targettime}';";
                    $success = $wpdb->query( $query );
                    $this->log( sprintf( __( 'Log table has %s entries, trimming to %s.', 'seo-booster' ), number_format_i18n( $total ), number_format_i18n( $maxlogentries ) ) );
                }
                
                // Begin staged processing
                $sb2_maintenance_bl_table_stage = get_option( 'sb2_maintenance_bl_table_stage' );
                if ( !$sb2_maintenance_bl_table_stage ) {
                    $sb2_maintenance_bl_table_stage = 0;
                }
                $stepinterval = 5000;
                // @todo
                // testing for internal backlinks being falsely reported as external backlinks. Cleans up.
                $site_url = site_url();
                $site_url_no_http = $this->remove_http( $site_url );
                // @todo better query
                $ownrefsquery = "SELECT id,ref,lp FROM {$bltable} WHERE (ref LIKE '%{$site_url}%' OR ref LIKE '%{$site_url_no_http}%') AND id>{$sb2_maintenance_bl_table_stage} LIMIT {$stepinterval};";
                $ownresults = $wpdb->get_results( $ownrefsquery, ARRAY_A );
                if ( $ownresults ) {
                    foreach ( $ownresults as $res ) {
                        $resid = $res['id'];
                        $delresult = $wpdb->query( "DELETE FROM {$bltable} WHERE id={$resid} LIMIT 1;" );
                        // deleting permanently.
                        $changecount++;
                        if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                            $this->log( sprintf( __( "Maintenance - Removing URL <code>%s</code> Step %s", 'seo-booster' ), $res['ref'], $stepinterval ) );
                        }
                    }
                }
                // Filter url table from unwanted urls
                // @todo better query
                $query = "SELECT ID,url FROM `{$urlstable}` WHERE ID > {$sb2_maintenance_urls_table_stage} LIMIT {$stepinterval};";
                $results = $wpdb->get_results( $query, ARRAY_A );
                if ( $results ) {
                    foreach ( $results as $res ) {
                        $resid = $res['ID'];
                        
                        if ( $this->ignore_current_url( $res['url'] ) ) {
                            $changecount++;
                            $delresult = $wpdb->query( "DELETE FROM {$urlstable} WHERE ID={$resid} limit 1;" );
                            $delresult = $wpdb->query( "DELETE FROM {$urlsmetatable} WHERE refid={$resid};" );
                        }
                    
                    }
                }
                // Clean up urls from backlinks table
                // @todo - better query
                $query = "SELECT id,ref,lp,lastvisit FROM `{$bltable}` WHERE id > {$sb2_maintenance_bl_table_stage} LIMIT {$stepinterval};";
                $results = $wpdb->get_results( $query, ARRAY_A );
                
                if ( $results ) {
                    foreach ( $results as $res ) {
                        $resid = $res['id'];
                        $sb2_maintenance_bl_table_stage = $resid;
                        
                        if ( !$this->verifyurl( $res['ref'] ) ) {
                            $changecount++;
                            $delresult = $wpdb->query( "DELETE FROM {$bltable} WHERE id={$resid} limit 1;" );
                            // deleting backlink permanently.
                            if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                                $this->log( sprintf( __( "Maintenance - Removing URL <code>%s</code>", 'seo-booster' ), $res['ref'] ) );
                            }
                        }
                        
                        // Filtrer links fra vi -ikke- kan lide
                        
                        if ( $this->ignorelink( $res['ref'] ) ) {
                            $changecount++;
                            $delresult = $wpdb->query( "DELETE FROM {$bltable} WHERE id={$resid} limit 1;" );
                            // deleting backlink permanently.
                        }
                        
                        
                        if ( $this->ignore_current_url( $res['ref'] ) ) {
                            $changecount++;
                            $delresult = $wpdb->query( "DELETE FROM {$bltable} WHERE id={$resid} limit 1;" );
                            // deleting backlink permanently.
                        }
                        
                        // todo - check if valid referrer or remove
                        $searchref = $this->_isSearch( $res['ref'] );
                        
                        if ( $searchref ) {
                            // yir, we found an old referrer from a search engine
                            $engine = $searchref['sengine_name'];
                            $gquery = $searchref['Query'];
                            if ( $gquery == '' ) {
                                $gquery = '#';
                            }
                            $parsedurl = parse_url( $res['ref'] );
                            // 3.3.32 - missing
                            $tld = $parsedurl['host'];
                            if ( $engine != 'Google' ) {
                                $tld = '';
                            }
                            $this->log( sprintf( __( "Maintenance - Found a search engine visitor from <code>%s</code> - updating keyword records.", 'seo-booster' ), $searchref['sengine_name'] ) );
                            $wpdb->insert( $kwtable, array(
                                'kw'        => $gquery,
                                'lp'        => $res['lp'],
                                'engine'    => $searchref['sengine_name'],
                                'googletld' => $tld,
                                'visits'    => '1',
                                'lastvisit' => $res['lastvisit'],
                            ), array(
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%d',
                                '%s'
                            ) );
                            $lastid = $wpdb->insert_id;
                            
                            if ( $lastid ) {
                                $delresult = $wpdb->query( "DELETE FROM {$bltable} WHERE id={$resid} limit 1;" );
                                // deleting backlink permanently.
                            }
                            
                            $changecount++;
                        } else {
                            // checking to ignore this link or even remove.
                            
                            if ( $this->ignorelink( $res['ref'] ) ) {
                                $changecount++;
                                $delresult = $wpdb->query( "DELETE FROM {$bltable} WHERE id={$resid} limit 1;" );
                                // deleting backlink permanently.
                                // filter backlinks and unwanted links
                                if ( get_option( 'seobooster_debug_logging' ) == 'on' ) {
                                    $this->log( sprintf( __( "Maintenance - Removing a reference to <code>%s</code>.", 'seo-booster' ), $this->remove_http( $res['ref'] ) ) );
                                }
                            }
                        
                        }
                        
                        // if else ($searchref)
                    }
                    // foreach ($results as $res )
                }
                
                // if ($results)
                if ( $changecount > 0 ) {
                    $this->log( sprintf( __( "Maintenance routines made %d changes to the database.", 'seo-booster' ), number_format_i18n( $changecount ) ) );
                }
                // Setting current stage in cleaning up - Backlinks table
                //	$sb2_maintenance_bl_table_stage = $sb2_maintenance_bl_table_stage + $stepinterval;
                $highest_bltable = $wpdb->get_var( "SELECT id FROM {$bltable} ORDER BY id DESC LIMIT 1;" );
                
                if ( $sb2_maintenance_bl_table_stage >= $highest_bltable ) {
                    $sb2_maintenance_bl_table_stage = 0;
                } else {
                    $sb2_maintenance_bl_table_stage + $stepinterval;
                }
                
                update_option( 'sb2_maintenance_bl_table_stage', $sb2_maintenance_bl_table_stage );
                // Setting current stage in cleaning up - Urlstable
                $sb2_maintenance_urls_table_stage = $sb2_maintenance_urls_table_stage + $stepinterval;
                $highest_urlstable = $wpdb->get_var( "SELECT ID FROM {$urlstable} ORDER BY ID DESC LIMIT 1;" );
                if ( $sb2_maintenance_urls_table_stage >= $highest_urlstable ) {
                    $sb2_maintenance_urls_table_stage = 0;
                }
                update_option( 'sb2_maintenance_urls_table_stage', $sb2_maintenance_urls_table_stage );
                //$this->log('Cleaning urls: '.$sb2_maintenance_urls_table_stage.' ud af '.$highest_urlstable.' Backlinks:'.$sb2_maintenance_bl_table_stage.' ud af '.$highest_bltable); // @todo @debug
            }
            
            function check_page_for_url( $page_url, $check_url )
            {
                // Array to store results
                $result = array();
                /*
                		if ($check_www) {
                		// convert url to be checked so we can find www. part too
                			if (stripos($check_url, "http://www.") !== FALSE) {
                				$check_url = str_replace("http://www.", "http://(?:www.|)", $check_url);
                			} else {
                				$check_url = str_replace("http://", "http://(?:www.|)", $check_url);
                			}
                		}
                */
                // todo - make work with WP HTTP api
                // init curl
                $ch = curl_init( $page_url );
                curl_setopt( $ch, CURLOPT_HEADER, 0 );
                curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
                // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                $output = curl_exec( $ch );
                $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
                curl_close( $ch );
                // curl end
                // store the code in result
                $result["code"] = $http_code;
                // process data if code is 200
                
                if ( $http_code == 200 ) {
                    $dom = new DomDocument();
                    @$dom->loadHTML( $output );
                    $urls = $dom->getElementsByTagName( 'a' );
                    foreach ( $urls as $url ) {
                        
                        if ( !isset( $result['link_found'] ) ) {
                            // Ingen grund til at checke hvis vi -har- fundet linket
                            $daurl = $url->getAttribute( 'href' );
                            $pos = strpos( $daurl, $check_url );
                            
                            if ( $pos === false ) {
                            } else {
                                $result['link_found'] = '1';
                                $result['href'] = $daurl;
                                $result['anchor_text'] = (string) $url->nodeValue;
                            }
                        
                        }
                    
                    }
                }
                
                return $result;
            }
        
        }
        // end class seobooster2
        class seobooster_dyn_widget extends WP_Widget
        {
            public function __construct()
            {
                parent::__construct( 'sb2_dynwidget', __( 'SEO Booster - Dynamic Links', 'seo-booster' ), array(
                    'description' => __( 'List of links to other pages with anchor text detected from search engines.', 'seo-booster' ),
                ) );
            }
            
            public function widget( $args, $instance )
            {
                global  $seobooster2, $wpdb ;
                extract( $args );
                $title = apply_filters( 'widget_title', $instance['title'] );
                $listtype = $instance['listtype'];
                $limit = $instance['limit'];
                $showvisits = $instance['showvisits'];
                $output = $before_widget;
                if ( !empty($title) ) {
                    $output .= $before_title . $title . $after_title;
                }
                $kwtable = $wpdb->prefix . "sb2_kw";
                global  $wp_query ;
                $currurl = $seobooster2->seobooster_currenturl();
                if ( !is_int( $limit ) ) {
                    $limit = '10';
                }
                $ignorelist = get_option( 'seobooster_ignorelist' );
                $sqlignore = '';
                if ( $ignorelist ) {
                    $sqlignore = $seobooster2->seobooster_generateignorelist( $ignorelist );
                }
                if ( $listtype == 'hightraffic' ) {
                    // high traffic
                    $query = "SELECT DISTINCT `lp`,`kw`, (SELECT SUM(O.visits) FROM {$kwtable} O WHERE O.kw = M.kw ) AS totalvisits FROM {$kwtable} M WHERE {$sqlignore} ig='0' AND kw<>'#' AND lp<>'{$currurl}' AND engine<>'Internal Search' group by kw ORDER BY totalvisits DESC LIMIT {$limit};";
                }
                if ( $listtype == 'lowtraffic' ) {
                    // low traffic
                    $query = "SELECT DISTINCT `lp`,`kw`, (SELECT SUM(O.visits) FROM {$kwtable} O WHERE O.kw = M.kw ) AS totalvisits FROM {$kwtable} M WHERE {$sqlignore} ig='0' AND kw<>'#' AND lp<>'{$currurl}' AND engine<>'Internal Search' GROUP BY kw  ORDER BY totalvisits ASC LIMIT {$limit};";
                }
                $posthits = $wpdb->get_results( $query, ARRAY_A );
                
                if ( $posthits ) {
                    $output .= "<ul>";
                    foreach ( $posthits as $ph ) {
                        $permalink = '';
                        $visits = $ph['totalvisits'];
                        $permalink = $ph['lp'];
                        
                        if ( $permalink ) {
                            $output .= "<li><a href='" . $permalink . "'>" . ucfirst( stripslashes( $ph['kw'] ) ) . "</a>";
                            if ( $showvisits ) {
                                $output .= " ({$visits})";
                            }
                            $output .= "</li>";
                        }
                    
                    }
                    $output .= "</ul>";
                    $output .= $after_widget;
                } else {
                    // no hits - do not show widget
                    $output = '';
                }
                
                echo  $output ;
            }
            
            public function update( $new_instance, $old_instance )
            {
                $instance = array();
                $instance['title'] = strip_tags( $new_instance['title'] );
                $instance['listtype'] = strip_tags( $new_instance['listtype'] );
                $instance['limit'] = intval( strip_tags( $new_instance['limit'] ) );
                $instance['showvisits'] = strip_tags( $new_instance['showvisits'] );
                delete_transient( 'seobooster_dynlinkswidget_' . $widget_id );
                // resets the widget if you make changes.
                return $instance;
            }
            
            public function form( $instance )
            {
                
                if ( isset( $instance['title'] ) ) {
                    $title = $instance['title'];
                } else {
                    $title = __( 'Internal Links', 'seo-booster' );
                }
                
                
                if ( isset( $instance['listtype'] ) ) {
                    $listtype = $instance['listtype'];
                } else {
                    $listtype = 'hightraffic';
                }
                
                
                if ( isset( $instance['limit'] ) ) {
                    $limit = $instance['limit'];
                } else {
                    $limit = '10';
                }
                
                if ( $limit == '0' ) {
                    $limit = '10';
                }
                
                if ( isset( $instance['showvisits'] ) ) {
                    $showvisits = $instance['showvisits'];
                } else {
                    $showvisits = '';
                }
                
                ?>


		<p>
			<label for="<?php 
                echo  $this->get_field_id( 'title' ) ;
                ?>"><?php 
                _e( 'Title:', 'seo-booster' );
                ?></label>
			<input class="widefat" id="<?php 
                echo  $this->get_field_id( 'title' ) ;
                ?>" name="<?php 
                echo  $this->get_field_name( 'title' ) ;
                ?>" type="text" value="<?php 
                echo  esc_attr( $title ) ;
                ?>" />
			<br />
			<small><?php 
                _e( 'The Widget title.', 'seo-booster' );
                ?></small>
		</p>

		<p>
			<label for="<?php 
                echo  $this->get_field_id( 'limit' ) ;
                ?>"><?php 
                _e( 'Limit:', 'seo-booster' );
                ?></label>
			<input class="widefat" id="<?php 
                echo  $this->get_field_id( 'limit' ) ;
                ?>" name="<?php 
                echo  $this->get_field_name( 'limit' ) ;
                ?>" type="text" value="<?php 
                echo  $limit ;
                ?>" />
			<br />
			<small><?php 
                _e( 'The maximum amount of links. Defaults to 10.', 'seo-booster' );
                ?></small>
		</p>

		<p>
			<label for="<?php 
                echo  $this->get_field_id( 'listtype' ) ;
                ?>"><?php 
                _e( 'What to show:', 'seo-booster' );
                ?></label>
			<select class="widefat" id="<?php 
                echo  $this->get_field_id( 'listtype' ) ;
                ?>" name="<?php 
                echo  $this->get_field_name( 'listtype' ) ;
                ?>">
				<option value="hightraffic" <?php 
                if ( $listtype == 'hightraffic' ) {
                    echo  ' selected="selected"' ;
                }
                ?>><?php 
                _e( 'Pages with the most SEO traffic', 'seo-booster' );
                ?></option>
				<option value="lowtraffic" <?php 
                if ( $listtype == 'lowtraffic' ) {
                    echo  ' selected="selected"' ;
                }
                ?>><?php 
                _e( 'Pages with little SEO traffic', 'seo-booster' );
                ?></option>

			</select>

			<br />
			<small><?php 
                _e( 'Choosing to show links to most trafficked paged can boost them even further. If you want to help along low performing keywords, you should show the pages with little traffic.', 'seo-booster' );
                ?></small>
		</p>



		<p>
			<label for="<?php 
                echo  $this->get_field_id( 'showvisits' ) ;
                ?>"><?php 
                _e( 'Show number of visits:', 'seo-booster' );
                ?></label>
			<input class="widefat" id="<?php 
                echo  $this->get_field_id( 'showvisits' ) ;
                ?>" name="<?php 
                echo  $this->get_field_name( 'showvisits' ) ;
                ?>" type="checkbox" value="on" <?php 
                if ( $showvisits == 'on' ) {
                    echo  " checked" ;
                }
                ?> />

			<br />
			<small><?php 
                _e( 'Turn on showing number of visits after each keyword.', 'seo-booster' );
                ?></small>
		</p>


		<?php 
            }
        
        }
        // end class seobooster_dyn_widget
        class seobooster_keywords_widget extends WP_Widget
        {
            public function __construct()
            {
                parent::__construct( 'seobooster_keywords_widget', __( 'SEO Booster - Incoming Keywords', 'seo-booster' ), array(
                    'description' => __( 'Shows keywords used to find the current page. Widget does not display if no terms is found.', 'seo-booster' ),
                ) );
            }
            
            public function widget( $args, $instance )
            {
                global  $seobooster2, $wp_query, $wpdb ;
                extract( $args );
                $title = apply_filters( 'widget_title', $instance['title'] );
                $currurl = $seobooster2->seobooster_currenturl();
                $keywords = $seobooster2->list_keywords( 10, $currurl );
                // If no keywords found, back again, no need to show the widget then..
                if ( !$keywords ) {
                    return;
                }
                $output = '';
                $output .= $before_widget;
                if ( !empty($title) ) {
                    $output .= $before_title . $title . $after_title;
                }
                $output .= '<div style="padding:20px;">' . $keywords . '</div>';
                // todo - use builtin classes possible? .textwidget
                $output .= $after_widget;
                echo  $output ;
            }
            
            public function update( $new_instance, $old_instance )
            {
                $instance = array();
                $instance['title'] = strip_tags( $new_instance['title'] );
                $instance['limit'] = intval( strip_tags( $new_instance['limit'] ) );
                return $instance;
            }
            
            public function form( $instance )
            {
                global  $seobooster2 ;
                
                if ( isset( $instance['title'] ) ) {
                    $title = $instance['title'];
                } else {
                    $title = __( 'Tagged With', 'seo-booster' );
                }
                
                
                if ( isset( $instance['limit'] ) ) {
                    $limit = $instance['limit'];
                } else {
                    $limit = '10';
                }
                
                ?>
		<p>
			<label for="<?php 
                echo  $this->get_field_id( 'title' ) ;
                ?>"><?php 
                _e( 'Title:', 'seo-booster' );
                ?></label>
			<input class="widefat" id="<?php 
                echo  $this->get_field_id( 'title' ) ;
                ?>" name="<?php 
                echo  $this->get_field_name( 'title' ) ;
                ?>" type="text" value="<?php 
                echo  esc_attr( $title ) ;
                ?>" />
		</p>

		<p>
			<label for="<?php 
                echo  $this->get_field_id( 'limit' ) ;
                ?>"><?php 
                _e( 'Limit:', 'seo-booster' );
                ?></label>
			<input class="widefat" id="<?php 
                echo  $this->get_field_id( 'limit' ) ;
                ?>" name="<?php 
                echo  $this->get_field_name( 'limit' ) ;
                ?>" type="text" value="<?php 
                echo  $limit ;
                ?>" />
			<br />
			<small><?php 
                _e( 'The maximum amount of links. Defaults to 10.', 'seo-booster' );
                ?></small>
		</p>

		<?php 
            }
        
        }
        // end class seobooster_keywords_widget
    }
    
    if ( !function_exists( 'seoboosterpro_boostlist' ) ) {
        function seoboosterpro_boostlist(
            $before = '<ul>',
            $after = '</ul>',
            $beforeeach = '<li>',
            $aftereach = '</li>',
            $limit = 10
        )
        {
            global  $wpdb, $seobooster2 ;
            $currurl = $seobooster2->seobooster_currenturl();
            $kwtable = $wpdb->prefix . "sb2_kw";
            $ignorelist = get_option( 'seobooster_ignorelist' );
            //$ignorelistsize = sizeof($ignorelist);//removed 3.3.28
            
            if ( $ignorelist ) {
                $sqlignore = $seobooster2->seobooster_generateignorelist( $ignorelist );
            } else {
                $sqlignore = '';
            }
            
            $query = "SELECT * FROM `{$kwtable}` WHERE {$sqlignore} `lp` = '" . $currurl . "' AND kw<>'#' and kw<>'' ORDER BY `visits` DESC limit {$limit};";
            $posthits = $wpdb->get_results( $query, ARRAY_A );
            
            if ( $posthits ) {
                echo  $before ;
                foreach ( $posthits as $hits ) {
                    echo  $beforeeach ;
                    echo  $hits['kw'] ;
                    echo  $aftereach ;
                }
                echo  $after ;
            }
        
        }
    
    }
    // Alias for the old function, seoboosterpro_boostlist()
    if ( !function_exists( 'seobooster_kwlist' ) ) {
        function seobooster_kwlist(
            $before = '<ul>',
            $after = '</ul>',
            $beforeeach = '<li>',
            $aftereach = '</li>',
            $limit = 10
        )
        {
            if ( function_exists( 'seoboosterpro_boostlist' ) ) {
                // if the old function exists, reuse it.
                seoboosterpro_boostlist(
                    $before,
                    $after,
                    $beforeeach,
                    $aftereach,
                    $limit
                );
            }
        }
    
    }
    global  $seobooster2 ;
    if ( class_exists( "seobooster2" ) && !$seobooster2 ) {
        $seobooster2 = new seobooster2();
    }
}

// if ( ! function_exists( 'seobooster_fs' ) )