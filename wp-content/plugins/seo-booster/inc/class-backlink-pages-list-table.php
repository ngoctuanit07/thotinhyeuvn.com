<?php

// don't load directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class SB_Backlinks_List_Table extends WP_List_Table
{
    public function __construct()
    {
        // Set parent defaults.
        parent::__construct( array(
            'singular' => 'url',
            'plural'   => 'urls',
            'ajax'     => false,
        ) );
    }
    
    public function get_columns()
    {
        $columns['cb'] = '<input type="checkbox" />';
        $columns['ref'] = _x( 'Link From', 'Column label', 'seo-booster' );
        $columns['screenshot'] = _x( 'Screenshot', 'Column label', 'seo-booster' );
        $columns['lp'] = _x( 'Landing Page', 'Column label', 'seo-booster' );
        $columns['visits'] = _x( 'Visitors', 'Column label', 'seo-booster' );
        $columns['firstvisit'] = _x( 'First Visit', 'Column label', 'seo-booster' );
        return $columns;
    }
    
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'verified'   => array( 'verified', false ),
            'ref'        => array( 'ref', false ),
            'lp'         => array( 'lp', false ),
            'visits'     => array( 'visits', false ),
            'lastcheck'  => array( 'lastcheck', false ),
            'firstvisit' => array( 'firstvisit', false ),
        );
        return $sortable_columns;
    }
    
    protected function column_verified( $item )
    {
        if ( $item['verified'] === '1' ) {
            return '<span class="dashicons dashicons-yes"></span> ' . __( 'Verified', 'seo-booster' );
        }
        if ( $item['verified'] === '0' ) {
            return '';
        }
        if ( $item['verified'] === '-1' ) {
            return '<span class="dashicons dashicons-no"></span> ' . __( 'Not found', 'seo-booster' );
        }
    }
    
    protected function column_anchor( $item )
    {
        return $item['anchor'];
    }
    
    protected function column_lastcheck( $item )
    {
        if ( '0000-00-00 00:00:00' === $item['lastcheck'] ) {
            return __( 'Not checked yet', 'seo-booster' );
        }
        return "<span class='ago'>" . human_time_diff( strtotime( $item['lastcheck'] ), current_time( 'timestamp' ) ) . "<br/> " . __( 'ago', 'seo-booster' ) . "</span><span class='date'>" . $item['lastcheck'] . "</span>";
    }
    
    protected function column_default( $item, $column_name )
    {
        global  $seobooster2 ;
        switch ( $column_name ) {
            case 'ref':
                $img = '';
                include 'engine_meta.php';
                $domain = $item['domain'];
                $domain = explode( ".", $domain );
                // create an array of the bits
                $number = count( $domain );
                // find out how many there are
                $tld = $domain[$number - 1];
                // tld is last element
                // Check if we have a match
                
                if ( isset( $engine_meta[$tld] ) ) {
                    $datld = $tld;
                } else {
                    $secondld = $domain[$number - 2];
                    $datld = $secondld . '.' . $tld;
                }
                
                
                if ( isset( $engine_meta[$datld] ) ) {
                    $imgurl = SEOBOOSTER_PLUGINURL . '/images/flags/' . $engine_meta[$datld]['flag'];
                    $imgalt = $engine_meta[$datld]['label'];
                } else {
                    $imgurl = SEOBOOSTER_PLUGINURL . '/images/flags/Unknown.png';
                    $imgalt = __( 'Unknown', 'seo-booster' );
                }
                
                $img = '<img src="' . $imgurl . '" class="flag" alt="' . $imgalt . '">';
                return $img . '<a href="' . $item['ref'] . '" target="_blank">' . $seobooster2->truncatestring( $seobooster2->remove_http( $item['ref'] ), 35 ) . '</a>';
            case 'httpstatus':
                return $item[$column_name];
            case 'lp':
                return $seobooster2->truncatestring( $seobooster2->remove_http( $item['lp'] ), 55 );
            case 'visits':
                return $item[$column_name];
            case 'firstvisit':
                return $item[$column_name];
            case 'screenshot':
                $imgurl = add_query_arg( array(
                    'w' => '250',
                ), "//s.wordpress.com/mshots/v1/" . urlencode( $item['ref'] ) );
                return "<a href='" . $item['ref'] . "' target='_blank'><img data-original='{$imgurl}' src='" . SEOBOOSTER_PLUGINURL . "images/blplaceholder.png' width='125' height='93' class='lazy'></a>";
        }
    }
    
    protected function column_cb( $item )
    {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], urlencode( $item['id'] ) );
    }
    
    protected function get_bulk_actions()
    {
        $actions = array(
            'delete' => _x( 'Delete', 'List table bulk action', 'seo-booster' ),
        );
        return $actions;
    }
    
    protected function process_bulk_action()
    {
        
        if ( 'delete' === $this->current_action() ) {
            global  $wpdb ;
            
            if ( isset( $_GET['url'] ) ) {
                $bltable = $wpdb->prefix . "sb2_bl";
                $count = 0;
                if ( is_array( $_GET['url'] ) ) {
                    foreach ( $_GET['url'] as $urlid ) {
                        $delquery = "DELETE FROM `{$bltable}` WHERE id={$urlid} limit 1;";
                        $wpdb->query( $delquery );
                        $count++;
                    }
                }
            }
        
        }
    
    }
    
    function prepare_items()
    {
        global  $wpdb ;
        $per_page = 35;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->process_bulk_action();
        $paged = ( isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1 );
        $offset = $paged * $per_page - $per_page;
        $bltable = $wpdb->prefix . "sb2_bl";
        $search = ( isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : false );
        $do_search = ( $search ? $wpdb->prepare(
            " AND (lp LIKE '%%%s%%' OR ref LIKE '%%%s%%' OR anchor LIKE '%%%s%%') ",
            $search,
            $search,
            $search
        ) : '' );
        $order = 'desc';
        if ( isset( $_GET['order'] ) ) {
            $order = sanitize_text_field( $_GET['order'] );
        }
        $orderby = 'visits';
        if ( isset( $_GET['orderby'] ) ) {
            $orderby = sanitize_text_field( $_GET['orderby'] );
        }
        $orderby = 'firstvisit';
        if ( isset( $_GET['orderby'] ) ) {
            $orderby = sanitize_text_field( $_GET['orderby'] );
        }
        $daquery = "SELECT * FROM `{$bltable}` WHERE 1 = 1 {$do_search} ORDER BY {$orderby} {$order} LIMIT {$offset}, {$per_page};";
        $data = $wpdb->get_results( $daquery, ARRAY_A );
        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var( "SELECT count(lp) FROM `{$bltable}` WHERE 1=1 {$do_search};" );
        $this->items = $data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ) );
    }
    
    protected function usort_reorder( $a, $b )
    {
        $orderby = ( !empty($_REQUEST['orderby']) ? wp_unslash( $_REQUEST['orderby'] ) : 'firstvisit' );
        $order = ( !empty($_REQUEST['order']) ? wp_unslash( $_REQUEST['order'] ) : 'asc' );
        $result = strcmp( $a[$orderby], $b[$orderby] );
        return ( 'asc' === $order ? $result : -$result );
    }

}