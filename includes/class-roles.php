<?php
/**
 * NFT Memberships Roles
 *
 * Also allows running functions attached to specified hooks
 *
 * @package NFT_Memberships_User_Roles
 * @subpackage Roles
 * @since 0.0.1
 */

namespace NFT_Memberships;

use NFT_Memberships\Traits\Singleton;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Roles {

	use Singleton;

	/**
	 * Class Initializer
	 */
	public function init() {

	}

	/**
	 * Get WordPress and Custom Roles
	 *
	 * @return string[]
	 */
	public function get_roles() {
		global $wp_roles;

		return $wp_roles->get_names();

	}

	/**
	 * Add a capability to a user role
	 *
	 * @param string $role
	 * @param string $capability
	 */
	public function add_role_capability( string $role, string $capability = 'manage_email_accounts' ) {
		// Get the role object
		$role_object = get_role( $role );

		if ( $role_object ) {
			$role_object->add_cap( $capability );
		}

	}

	/**
	 * Remove a capability from a user role
	 *
	 * @param string $role
	 * @param string $capability
	 */
	public function remove_role_capability( string $role, string $capability = 'manage_email_accounts' ) {
		// Get the role object
		$role_object = get_role( strtolower( $role ) );

		if ( $role_object ) {
			$role_object->remove_cap( $capability );
		}

	}

	/**
	 * Add capability to a user
	 *
	 * @param int $user_id
	 * @param string $capability
	 */
	public function add_user_capability( int $user_id, string $capability = 'manage_email_accounts' ) {
		$user = new \WP_User( $user_id );

		// Check if user exists and doesn't have capability
		if ( $user->exists() && ! $user->has_cap( $capability ) ) {
			$user->add_cap( $capability, true );
		}
	}

	/**
	 * Remove capability from a user
	 *
	 * @param int $user_id
	 * @param string $capability
	 */
	public function remove_user_capability( int $user_id, string $capability = 'manage_email_accounts' ) {
		$user = new \WP_User( $user_id );

		// Check if user exists and has capability
		if ( $user->exists() && $user->has_cap( $capability ) ) {
			$user->remove_cap( $capability );
		}
	}


}