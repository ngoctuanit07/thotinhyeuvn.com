<?php
/**
 * WP List Table Example class
 *
 * @package   WPListTableExample
 * @author    Matt van Andel
 * @copyright 2016 Matthew van Andel
 * @license   GPL-2.0+
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SB_FOF_List_Table extends WP_List_Table {

	public function __construct() {
		// Set parent defaults.
		parent::__construct( array(
			'singular' => 'fof',     // Singular name of the listed records.
			'plural'   => 'fofs',    // Plural name of the listed records.
			'ajax'     => false,       // Does this table support ajax?
		) );
	}

	public function no_items() {
		_e( 'Nothing found.', 'seo-booster');
	}


	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />', // Render a checkbox instead of text.
			'lp'    => _x( 'Landing Page', 'Column label', 'seo-booster' ),
			'visits'    => _x( 'Visitors', 'Column label', 'seo-booster' ),
			'lastseen' => _x( 'Latest Visit', 'Column label', 'seo-booster' ),
			'referer' => _x( 'Referrer', 'Column label', 'seo-booster' ),


		);

		return $columns;
	}

	protected function get_sortable_columns() {
		$sortable_columns = array(
			'lp'    => array( 'lp', false ),
			'visits'    => array( 'visits', false ),
			'lastseen'    => array( 'lastseen', false ),
		);

		return $sortable_columns;
	}


	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'visits':
			return $item[ $column_name ];
			case 'lastseen':
			return $item[ $column_name ];
			case 'referer':
			return $item[ $column_name ];

			default:
			return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
		}
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],  // Let's simply repurpose the table's singular label ("movie").
			$item['id']                // The value of the checkbox should be the record's ID.
		);
	}


	protected function column_lp( $item ) {

		$page = wp_unslash( $_REQUEST['page'] ); // WPCS: Input var ok.

		// Build delete row action.
		$delete_query_args = array(
			'page'   => $page,
			'action' => 'delete',
			'fof'  => $item['id'],
		);
		$actions['delete'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $delete_query_args, 'admin.php' ), 'deletemovie_' . $item['id'] ) ),
			_x( 'Delete', 'List table row action', 'seo-booster' )
		);

		// Return the title contents.
		return sprintf( '<a href="%1$s" target="_blank">%2$s</a> <span style="color:silver;">(#%3$s)</span>%4$s',
			site_url($item['lp']),
			$item['lp'],
			$item['id'],
			$this->row_actions( $actions )
		);
	}

	protected function get_bulk_actions() {

		$actions = array(
			'delete' => _x( 'Delete', 'List table bulk action', 'seo-booster' ),
		);
		return $actions;
	}

	protected function process_bulk_action() {

		if ( 'deleteall' === $this->current_action() ) {
			global $seobooster2, $wpdb;
			$seobooster2->log( __('Resetting 404 Errors','seo-booster') );
			$wpdb->query("TRUNCATE TABLE `{$wpdb->prefix}sb2_404`;");
		}

		if ( 'delete' === $this->current_action() ) {
			global $wpdb;
			if (isset($_GET['fof'])) {
				if (is_array($_GET['fof'])) {
					foreach($_GET['fof'] as $fofid) {
						$wpdb->query("DELETE FROM {$wpdb->prefix}sb2_404 WHERE id=$fofid limit 1;");
					}
				}
				else {
					$fofid = intval($_GET['fof']);
					$wpdb->query("DELETE FROM {$wpdb->prefix}sb2_404 WHERE id=$fofid limit 1;");
				}
			}
		}
	}


	function prepare_items() {
		global $wpdb;
		$per_page = 50;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$paged = ( isset($_GET['paged']) ) ? $_GET['paged'] : 1;

		$offset = ( $paged * $per_page ) - $per_page;

		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;

		$do_search = ( $search ) ? $wpdb->prepare(" AND lp LIKE '%%%s%%' ", $search ) : '';

		$order = 'desc';
		if (isset($_GET['order'])) $order=sanitize_text_field($_GET['order']);

		$orderby = 'visits';
		if (isset($_GET['orderby'])) $orderby=sanitize_text_field($_GET['orderby']);

		$daquery = "SELECT *
		FROM {$wpdb->prefix}sb2_404
		WHERE 1=1
		$do_search
		ORDER BY $orderby $order
		LIMIT $offset, $per_page;";

		$data = $wpdb->get_results($daquery,ARRAY_A);

		$current_page = $this->get_pagenum();

		$total_items = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}sb2_404;");

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,                     // WE have to calculate the total number of items.
			'per_page'    => $per_page,                        // WE have to determine how many items to show on a page.
			'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
		) );
	}
}