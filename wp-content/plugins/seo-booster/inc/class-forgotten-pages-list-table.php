<?php

// don't load directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class SB_Forgotten_List_Table extends WP_List_Table
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
        $columns = array(
            'lp'        => _x( 'Landing Page', 'Column label', 'seo-booster' ),
            'lastvisit' => _x( 'Latest SE Visitor', 'Column label', 'seo-booster' ),
            'kws'       => _x( 'Keywords', 'Column label', 'seo-booster' ),
        );
        return $columns;
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
    
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'lp'        => array( 'lp', false ),
            'lastvisit' => array( 'lastvisit', false ),
        );
        return $sortable_columns;
    }
    
    protected function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'lastvisit':
                return $item[$column_name];
            case 'kws':
                return $item[$column_name];
            default:
                return print_r( $item, true );
                // Show the whole array for troubleshooting purposes.
        }
    }
    
    protected function column_lp( $item )
    {
        $page = wp_unslash( $_REQUEST['page'] );
        return sprintf(
            '<a href="%1$s" target="_blank">%2$s</a> <span style="color:silver;">(Visitors:%3$s)</span>',
            site_url( $item['lp'] ),
            $item['lp'],
            $item['visits']
        );
    }
    
    function prepare_items()
    {
        global  $wpdb ;
        $per_page = 15;
        // todo - screen settings?
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->process_bulk_action();
        $kwtable = $wpdb->prefix . "sb2_kw";
        $paged = ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
        $offset = $paged * $per_page - $per_page;
        // TODO - optional range - 30, 60, 90 days?
        /*
         */
        $daquery = "SELECT id,kw,lp,visits,firstvisit,lastvisit,min(firstvisit)\n\t\tFROM {$kwtable}\n\t\tWHERE engine<>'Internal Search'\n\t\tAND visits>1\n\t\tAND kw<>'#'\n\t\tAND ( (lastvisit < DATE_SUB(NOW(), INTERVAL 30 DAY) ) or (firstvisit < DATE_SUB(NOW(), INTERVAL 30 DAY) ) )\n\t\tGROUP BY lp\n\t\tORDER BY lastvisit ASC, visits DESC\n\t\tLIMIT {$offset}, {$per_page};";
        // @todo - lav query der henter data på sideniveua, dvs. man kunne have fået besøg af "blue widget" før, men efterfølgende er der blot "#" - det skal der tages højde for.
        $data = $wpdb->get_results( $daquery, ARRAY_A );
        $newarr = array();
        // Parse each
        foreach ( $data as $dat ) {
            $newdat = $dat;
            $lp = $dat['lp'];
            $lastvisit = $newdat['lastvisit'];
            if ( '0000-00-00 00:00:00' == $lastvisit ) {
                $lastvisit = $newdat['firstvisit'];
            }
            $newdat['firstvisit'] = "<span class='ago'>" . human_time_diff( strtotime( $dat['firstvisit'] ) ) . " " . __( 'ago', 'seo-booster' ) . "</span><span class='date'>" . $dat['firstvisit'] . "</span>";
            $newdat['lastvisit'] = "<div class='visitdata'><span class='ago'>" . human_time_diff( strtotime( $lastvisit ) ) . " " . __( 'ago', 'seo-booster' ) . "</span><span class='date'>" . $lastvisit . "</span></div>";
            $newdat['lastvisit'] .= "<div class='visitdata first'>" . __( 'First visit:', 'seo-booster' ) . " <span class='ago'>" . human_time_diff( strtotime( $dat['firstvisit'] ) ) . " " . __( 'ago', 'seo-booster' ) . "</span><span class='date'>" . $dat['firstvisit'] . "</span>";
            $kwlist = $wpdb->get_results( "SELECT kw FROM {$kwtable} WHERE lp='{$lp}' AND kw<>'#' ORDER by visits DESC limit 25;", ARRAY_A );
            $kwstring = '';
            if ( $kwlist ) {
                foreach ( $kwlist as $kw ) {
                    $kwstring .= '<span>' . $kw['kw'] . '</span>';
                }
            }
            $newdat['kws'] = $kwstring;
            $newarr[] = $newdat;
        }
        $data = $newarr;
        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var( "SELECT count(*) FROM {$kwtable} WHERE engine<>'Internal Search' AND visits>1 AND kw<>'#' AND lastvisit < DATE_SUB(NOW(), INTERVAL 30 DAY);" );
        $data = array_slice( $data, ($current_page - 1) * $per_page, $per_page );
        $this->items = $data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ) );
    }

}