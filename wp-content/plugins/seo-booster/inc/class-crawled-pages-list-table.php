<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SB_Crawled_List_Table extends WP_List_Table {


	public function __construct() {
		parent::__construct( array(
			'singular' => 'url',
			'plural'   => 'urls',
			'ajax'     => false,
		) );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
	  _e( 'No crawl data available.', 'seo-booster');
	}


	public function get_columns() {
		$columns = array(
			'url'    => _x( 'URL', 'Column label', 'seo-booster'),
			'visits'    => _x( 'Robot Crawls', 'Column label', 'seo-booster'),
			'lastcrawl'    => _x( 'Last Crawled', 'Column label', 'seo-booster'),
			'crawlers' => _x( 'Crawled by', 'Column label', 'seo-booster'),
		);

		return $columns;
	}

	protected function get_sortable_columns() {
		$sortable_columns = array(
			'url'    => array( 'url', false ),
			'visits'    => array( 'ttlvisits', false ),
			'lastcrawl'    => array( 'lastcrawl', false ),
		);

		return $sortable_columns;
	}


	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'url':
			return $item[ $column_name ];
			case 'lastcrawl':
			return $item[ $column_name ];
			case 'crawlers':
			return $item[ $column_name ];
			case 'visits':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
			}
		}


	protected function column_url( $item ) {

		$page = wp_unslash( $_REQUEST['page'] ); // WPCS: Input var ok.

		// Return the title contents.
		return sprintf( '<a href="%1$s" target="_blank">%2$s</a>',
			site_url($item['url']),
			$item['url'],
			urlencode($item['url'])
		);
	}

	protected function process_bulk_action() {

		if ( 'deleteall' === $this->current_action() ) {
			global $seobooster2, $wpdb;
			$seobooster2->log( __('Resetting Crawl Data','seo-booster') );
			$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sb2_crawl;");
		}

		if ( 'delete' === $this->current_action() ) {
			// Todo


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

		$do_search = ( $search ) ? $wpdb->prepare(" AND url LIKE '%%%s%%' ", $search ) : '';

		$order = 'desc';
		if (isset($_GET['order'])) $order=$_GET['order'];

		$orderby = 'ttlvisits';
		if (isset($_GET['orderby'])) $orderby=$_GET['orderby'];

		$daquery = "SELECT url,
		lastcrawl,
		visits,
		SUM(visits) as ttlvisits
			FROM {$wpdb->prefix}sb2_crawl
			WHERE 1 = 1
			$do_search
			GROUP BY url
			ORDER BY $orderby $order
			LIMIT $offset, $per_page;";


		$data = $wpdb->get_results($daquery,ARRAY_A);

		if ($data) {
			$newdat = array();
			foreach ($data as $da) {
				$daurl = $da['url'];
				$crawlers = $wpdb->get_results("SELECT id,engine,SUM(visits) as visits,lastcrawl FROM {$wpdb->prefix}sb2_crawl WHERE url='$daurl' GROUP BY engine;");

				//$crawlout = '##todo##';
				if ($crawlers) {
					$crawlout = '<ul class="crawllist">';
					$tv = 0;
					foreach ($crawlers as $cr) {
						$tv = $tv + $cr->visits;
						$crawlout .= '<li>'.ucfirst($cr->engine).' <span>'. sprintf( esc_html( _n( '%d visit', '%d visits', $cr->visits, 'seo-booster' ) ), $cr->visits ).'. Last: '.$cr->lastcrawl.'</span></li>';
					}

					$da['visits'] = $tv;
					$crawlout .= '</ul><!-- .crawllist -->';
				}
				$da['crawlers'] = $crawlout;
				$newdat[] = $da;
			} // foreach
			$data = $newdat;
		} // if ($data)

		$current_page = $this->get_pagenum();

		$total_items = $wpdb->get_var("SELECT count(DISTINCT(url)) FROM {$wpdb->prefix}sb2_crawl
			WHERE 1=1
			$do_search
			;");

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}

	/**
	 * Callback to allow sorting of example data.
	 *
	 * @param string $a First value.
	 * @param string $b Second value.
	 *
	 * @return int
	 */
	protected function usort_reorder( $a, $b ) {
		$orderby = ! empty( $_REQUEST['orderby'] ) ? wp_unslash( $_REQUEST['orderby'] ) : 'lastcrawl';

		$order = ! empty( $_REQUEST['order'] ) ? wp_unslash( $_REQUEST['order'] ) : 'desc';

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		return ( 'asc' === $order ) ? $result : - $result;
	}
}