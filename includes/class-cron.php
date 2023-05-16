<?php
/**
 * NFT Memberships Cron Jobs
 *
 * Also allows running functions attached to specified hooks at specific intervals.
 *
 * @see https://developer.wordpress.org/plugins/cron/scheduling-wp-cron-events/
 *
 * @package NFT_Memberships
 * @subpackage Hooks
 * @since 0.0.1
 */

namespace NFT_Memberships;

// Exit if accessed directly
use NFT_Memberships\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

class Cron {

	use Singleton;

	/**
	 * Class initializer
	 */
	public function init() {

		// Create hook
		add_action( 'nft_memberships_cron_check_memberships', array( $this, 'check_membership_requirements' ) );

	}

	/**
	 * Gets all users and checks if each user has met the NFT memberships requirements.
	 *
	 * If not, they are unsubscribed from the membership.
	 */
	public function check_membership_requirements(){

		/*
		 * Get all users
		 */
		$all_users = get_users();
		foreach ( $all_users as $user ){
			/*
			 * Get all memberships a user has access to that were approved earlier automatically, but now the user should
			 * not have access to due to reasons such as selling an NFT
			 *
			 * This does not factor manually approved memberships
			 */
			$memberships = nft_memberships_get_memberships_user_cant_subscribe( $user->ID );
			foreach ( $memberships as $membership ){

				// Unsubscribe the user
				nft_memberships_unsubscribe_user( $membership->ID, $user->ID );
			}

		}


	}

}