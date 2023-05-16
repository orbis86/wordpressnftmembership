<?php

/**
 * Class NFT Memberships List Table
 *
 * @see https://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 */

namespace NFT_Memberships\List_Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use NFT_Memberships\Traits\Singleton;

class List_Table extends Base_List_Table {
	use Singleton;

	/**
	 * Memberships
	 */
	private $memberships;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Membership', 'nft-memberships' ),
			'plural'   => __( 'Memberships', 'nft-memberships' ),
			'ajax'     => true,
		) );

	}

	/**
	 * Class Initializer
	 */
	public function init() {
		$this->get_memberships();

	}

	/**
	 * Get Memberships
	 */
	public function get_memberships() {
		$list_table_memberships = array();

		$memberships = nft_memberships_get_memberships();
		if ( 0 < count( $memberships ) ) {
			foreach ( $memberships as $membership ) {

				// Show values in human-readable format
				$membership['membership_check'] = nft_memberships_get_membership_check_name( get_field( 'membership_check', $membership->ID ) );

				$membership_users = nft_memberships_get_users_of_membership( get_field( 'network_type', $membership->ID ), get_field( 'contract_address', $membership->ID ) );

				$membership['users'] = count( $membership_users );

				$list_table_memberships[] = $membership;
			}
		}

		$this->memberships = $list_table_memberships;

	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here.
	 *
	 * @global wpdb $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 */
	function prepare_items() {
		global $wpdb; //This is used only if making any database queries

		/*
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->get_items_per_page( 'nft_memberships_per_page', 10 );

		/*
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		/*
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * three other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		/*
		 * GET THE DATA!
		 *
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our dummy data.
		 *
		 * In a real-world situation, this is probably where you would want to
		 * make your actual database query. Likewise, you will probably want to
		 * use any posted sort or pagination data to build a custom query instead,
		 * as you'll then be able to use the returned query data immediately.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 */
		//Search/filter data
		$data = $this->memberships;

		/*
		 * This checks for sorting input and sorts the data in our array of dummy
		 * data accordingly (using a custom usort_reorder() function). It's for
		 * example purposes only.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary. In other words: remove this when
		 * you implement your own query.
		 */
		if ( null != $data ) {
			//usort( $data, array( $this, 'usort_reorder' ) );
		}

		/*
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/*
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		if ( null != $data ) {
			$total_items = count( $data );
		} else {
			$total_items                 = 0;
			$this->total_nft_memberships = 0;
		}

		/*
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to do that.
		 */
		if ( null != $data ) {
			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		}


		/*
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items != null ? $total_items : 0,
			// WE have to calculate the total number of items.
			'per_page'    => $per_page,
			// WE have to determine how many items to show on a page.
			'total_pages' => $total_items != null ? ceil( $total_items / $per_page ) : 0,
			// WE have to calculate the total number of pages.
		) );
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a `column_cb()` method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @return array An associative array containing column information.
	 * @see WP_List_Table::::single_row_columns()
	 */
	public function get_columns() {
		return array(
			//'cb'             => '<input type="checkbox" />', // Render a checkbox instead of text.
			'membership_name'  => 'Membership Name',
			'collection_name'  => 'Collection Name',
			'network_type'     => 'Network Type',
			'membership_check' => 'Membership Check',
			'users'            => 'Users'
		);
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within `prepare_items()` and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable.
	 */
	protected function get_sortable_columns() {
		return array(
			'membership_name'  => array( 'membership_name', true ),
			'collection_name'  => array( 'collection_name', true ),
			'membership_check' => array( 'membership_check', true ),
			'users'            => array( 'users', false )
		);
	}

	/**
	 * Handle bulk actions.
	 *
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 */
	protected function process_bulk_action() {

	}

	/**
	 * Return Email Count
	 */
	public function record_count() {
		return count( $this->memberships );
	}

	/**
	 * Error message when no items found, overriding parent method
	 */
	public function no_items() {
		_e( 'No active memberships available.', 'nft-memberships' );
	}

	function get_views() {
		$views   = array();
		$current = ( isset( $_REQUEST['network-type'] ) && ( $_REQUEST['network-type'] == 'blockchain' || $_REQUEST['network-type'] == 'hedera' ) ? $_REQUEST['network-type'] : 'all' );

		//All link
		$class = ( $current == 'all' ? ' class="current"' : '' );
		if ( null != $this->memberships ) {
			$all_count = count( $this->memberships );
		} else {
			$all_count = 0;
		}
		$all_url      = admin_url( 'admin.php?page=nft-memberships' );
		$views['all'] = "<a href='{$all_url }' {$class} >All (" . $all_count . ")</a>";

		return $views;
	}


	/**
	 * Callback for filtering data
	 */
	public function filter_nft_memberships( $nft_memberships, $filter ) {
		$filtered_accounts = array();
		foreach ( $nft_memberships as $key => $val ) {
			if ( $val['suspended'] == ( $filter == 1 ? 'Yes' : 'No' ) ) {
				$filtered_accounts[] = $nft_memberships[ $key ];
			}
		}

		return $filtered_accounts;
	}

	/**
	 * Table navigation/dropdown filters & other info
	 */
	function extra_tablenav( $which ) {

	}

	/**
	 * Get default column value.
	 *
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_login() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 * @param string $column_name The name/slug of the column to be processed.
	 *
	 * @return string Text or HTML to be placed inside the column <td>.
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'membership_name':
			case 'collection_name':
			case 'network_type':
			case 'membership_check':
			case 'users':
				return ucfirst( $item[ $column_name ] );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Get value for checkbox column.
	 *
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 *
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],  // Let's simply repurpose the table's singular label ("email").
			$item['title']                // The value of the checkbox should be the email's user.
		);
	}

	/**
	 * Get title column value.
	 *
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links are
	 * secured with wp_nonce_url(), as an expected security measure.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 *
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_title( $item ) {
		$page = wp_unslash( $_REQUEST['page'] ); // WPCS: Input var ok.

		// Build edit row action.
		$edit_url       = admin_url( 'admin.php?page=nft-memberships/?action=update&email=' . $item['login'] );
		$edit_nonce_url = add_query_arg( '_wpnonce', wp_create_nonce( 'manage-email-account' . $item['login'] ), $edit_url );

		$actions['edit'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			$edit_nonce_url,
			_x( 'Manage Account', 'List table row action', 'nft-memberships' ),
		);


		// Return the title contents.
		return sprintf(
			'%1$s %2$s',
			$item['title'],
			$this->row_actions( $actions )
		);
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions.
	 */
	protected function get_bulk_actions() {

		return array();
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
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( $_REQUEST['orderby'] == 'humandiskused' ) {
				$_REQUEST['orderby'] = '_diskused';
			} elseif ( $_REQUEST['orderby'] == 'humandiskquota' ) {
				$_REQUEST['orderby'] = '_diskquota';
			} elseif ( $_REQUEST['orderby'] == 'domain' ) {
				$_REQUEST['orderby'] = 'domain';
			}
		}

		// If no sort, default to title.
		$orderby = ! empty( $_REQUEST['orderby'] ) ? wp_unslash( $_REQUEST['orderby'] ) : 'title'; // WPCS: Input var ok.

		// If no order, default to asc.
		$order = ! empty( $_REQUEST['order'] ) ? wp_unslash( $_REQUEST['order'] ) : 'asc'; // WPCS: Input var ok.

		// Determine sort order.
		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		return ( 'asc' === $order ) ? $result : - $result;
	}
}
