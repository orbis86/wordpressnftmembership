<?php
/**
 * NFT Memberships Filters
 *
 *
 * @package NFT_Memberships
 * @subpackage Hooks
 * @since 0.0.1
 */

namespace NFT_Memberships;

use NFT_Memberships\Traits\Singleton;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Filters {

	use Singleton;

	/**
	 * Constructor.
	 */
	public function __construct() {
	} // end __construct;

	/**
	 * Register the filters
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function init() {

		/*
		 * Filter a user's memberships if any
		 */
		add_filter( 'nft_memberships_for_user', array( $this, 'filter_user_memberships' ), 10, 3 );

	}

	/**
	 * Filter a user's memberships
	 *
	 * @param $user_memberships
	 * @param $user_id
	 * @param $blog_id
	 *
	 * @return array
	 */
	public function filter_user_memberships( $user_memberships, $user_id, $blog_id ) {

		$memberships = get_field( 'nft_user_memberships', 'user_' . $user_id );

		if ( ! is_array( $memberships ) ) {
			return array();
		}

		$user_memberships = array();
		foreach ( $memberships as $membership_key => $membership_value ) {

			$membership = nft_memberships_get_memberships_by( 'ID', $membership_key, true, $blog_id );
			if ( $membership ) {
				$membership->registered = $membership_value->registered; // strtotime value

				$user_memberships[] = $membership;
			}

		}

		return $user_memberships;
	}

}