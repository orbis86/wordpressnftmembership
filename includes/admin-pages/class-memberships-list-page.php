<?php
/**
 * NFT Memberships List Page
 *
 * @package NFT_MEMBERSHIPS
 * @subpackage Admin_Pages
 * @since 0.0.1
 */

namespace NFT_Memberships\Admin_Pages;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use NFT_Memberships\Traits\Singleton;

class Memberships_List_Page {
	use Singleton;

	/**
	 * Class initializer
	 */
	public function init() {

		/*
		 * Add custom columns to list table
		 */
		add_action( 'load-edit.php', array( $this, 'check_current_screen' ) );
	}

	/*
	 * Add manage columns filter to our post type
	 */
	public function check_current_screen() {
		$screen = get_current_screen();

		if ( ! isset( $screen->post_type ) || 'nft-memberships' != $screen->post_type ) {
			return;
		}

		add_filter( "manage_{$screen->id}_columns", array( $this, 'add_columns' ) );
		add_action( "manage_{$screen->post_type}_posts_custom_column", array( $this, 'add_data_to_columns' ), 10, 2 );
	}

	/*
	 * Add custom columns to list table
	 */
	public function add_columns( $columns ) {

		/*
		 * Date column to come after custom columns
		 */
		unset( $columns['date'] );

		$columns['collection_name']  = 'Collection Name';
		$columns['network_type']     = 'Network Type';
		$columns['membership_check'] = 'Membership Check';
		$columns['users']            = 'Users';
		$columns['date']             = 'Date';

		return $columns;
	}

	/**
	 * Add data to custom columns
	 *
	 * @param $column
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function add_data_to_columns( $column, $post_id ) {

		switch ( $column ) {
			case 'collection_name':
				echo esc_html( get_field( $column, $post_id ) );
				break;

			case 'network_type':
				echo ucfirst( get_field( $column, $post_id ) );
				break;

			case 'membership_check':
				echo get_field( $column, $post_id ) === false ? 'Collection Level' : 'NFT Level';
				break;

			case 'users':
				echo count( nft_memberships_get_users_of_membership( get_field( 'network_type', $post_id ), get_field( 'contract_address', $post_id ) ) );
				break;
		}

		return $column;
	}


}