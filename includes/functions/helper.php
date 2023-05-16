<?php
/**
 * NFT Memberships Helper Functions
 */

use NFT_Memberships\Cache;
use NFT_Memberships\Helper;

$helper = Helper::get_instance();

/*
 * AJAX functions
 */
require_once $helper->path( 'includes/functions/ajax.php' );

/*
 * Integration Functions
 */
require_once $helper->path( 'includes/functions/integrations/wishlist-member.php' );
require_once $helper->path( 'includes/functions/integrations/ultimate-memberships.php' );
require_once $helper->path( 'includes/functions/networks/alchemy.php' );
require_once $helper->path( 'includes/functions/networks/hedera.php' );

/**
 * Alias function to be used on the templates
 *
 * @param string $view Template to be get.
 * @param array $args Arguments to be parsed and made available inside the template file.
 * @param string|false $default_view View to be used if the view passed is not found. Used as fallback.
 *
 * @return void
 */
function nft_memberships_get_template( $view, $args = array(), $path = 'views', $default_view = false ) {
	nft_memberships()->helper->render( $view, $args, $path, $default_view );
} // end nft_memberships_get_template;

/**
 * Get license key
 *
 * @return mixed|null
 */
function nft_memberships_get_license_key() {
	$license_details = get_network_option( get_current_network_id(), 'nft_memberships_license' );
	if ( false != $license_details ) {
		return $license_details['key'];
	}

	return null;
}

/**
 * Get addon tabs used in the settings page
 *
 * @return mixed|void|null
 */
function nft_memberships_get_addon_setting_tabs() {
	$addon_tabs = array();

	return apply_filters( 'nft_memberships_addon_setting_tabs', $addon_tabs );

}

/**
 * Returns the URL for assets inside the assets' folder.
 *
 * @param string $asset Asset file name with the extension.
 * @param string $assets_dir Assets sub-directory. Defaults to 'img'.
 * @param string $base_dir Base dir. Defaults to 'assets'.
 *
 * @return string
 * @since 1.0.0
 *
 */
function nft_memberships_get_asset( $asset, $assets_dir = 'img', $base_dir = 'assets' ) {
	return nft_memberships()->helper->get_asset( $asset, $assets_dir, $base_dir );
} // end nft_memberships_get_asset;

/**
 * Returns the NFT Memberships version.
 *
 * @return string
 * @since 1.0.0
 */
function nft_memberships_get_version() {
	return nft_memberships()->get_version();
} // end nft_memberships_get_version;


/**
 * Check if plugin license is active
 * @return bool
 */
function nft_memberships_is_license_active() {

	$license_details = get_network_option( get_current_network_id(), 'nft_memberships_license' );

	if ( false != $license_details ) {
		if ( 'Active' == $license_details['status'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Gets the status of a license key
 *
 * @return false|string
 */
function nft_memberships_get_license_status() {
	$license_details = get_network_option( get_current_network_id(), 'nft_memberships_license' );

	if ( false != $license_details ) {
		return ucfirst( $license_details['status'] );
	}

	return false;
}

/**
 * WP AJAX behaves differently in network admin, so define that network admin is true when appropriate
 */
function nft_memberships_ajax_define_network_admin() {
	if ( ! defined( 'WP_NETWORK_ADMIN' ) && defined( 'DOING_AJAX' ) && DOING_AJAX && is_multisite() && preg_match( '#^' . network_admin_url() . '#i', $_SERVER['HTTP_REFERER'] ) ) {
		define( 'WP_NETWORK_ADMIN', true );
	}
}

/**
 * Get addon's settings
 *
 * @return false|mixed|void
 */
function nft_memberships_get_settings( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	//Check if there are any existing settings in db
	if ( is_plugin_active_for_network( NFT_MEMBERSHIPS_PLUGIN_BASENAME ) ) {
		$settings = get_network_option( get_current_network_id(), 'nft_memberships_settings', false );
	} else {
		if ( $blog_id != get_current_blog_id() && is_multisite() ) {
			switch_to_blog( $blog_id );
			$settings = get_option( 'nft_memberships_settings', false );
			restore_current_blog();
		} else {
			$settings = get_option( 'nft_memberships_settings', false );
		}
	}

	return $settings;
}

/**
 * Get Alchemy's API key
 *
 * @return string|false
 */
function nft_memberships_get_api_key( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return nft_memberships_alchemy_get_api_key( $blog_id );
}

/**
 * Get the enabled WordPress membership plugin integration
 *
 * @param int $blog_id
 *
 * @return false|mixed
 */
function nft_memberships_get_membership_plugin_integration( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$membership_plugin = false;
	$plugin_settings   = nft_memberships_get_settings( $blog_id );
	if ( isset( $plugin_settings['general']['integrations']['membership_plugin'] ) ) {
		$membership_plugin = $plugin_settings['general']['integrations']['membership_plugin'];
	}

	return $membership_plugin;
}

/**
 * Get the enabled WordPress membership plugin integration name
 *
 * @param int $blog_id
 *
 * @return false|string
 */
function nft_memberships_get_membership_plugin_integration_name( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$membership_plugin_name = nft_memberships_get_membership_plugin_integration( $blog_id );
	switch ( $membership_plugin_name ) {
		case 'wishlist_member':
			$membership_plugin_name = 'Wishlist Member';
			break;
		case 'ultimate_membership':
			$membership_plugin_name = 'Ultimate Membership Pro';
			break;
		default:
			$membership_plugin_name = '';
			break;
	}

	return $membership_plugin_name;
}

/**
 * Get Membership Plugin Integration's Levels/Memberships
 *
 * @param int $blog_id
 *
 * @return array // e.g. array('id' => 'name');
 */
function nft_memberships_get_membership_plugin_levels( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$membership_plugin_integration = nft_memberships_get_membership_plugin_integration( $blog_id );
	switch ( $membership_plugin_integration ) {
		case 'wishlist_member':
			$membership_levels = nft_memberships_wishlist_member_get_levels( $blog_id );
			break;
		case 'ultimate_membership':
			$membership_levels = nft_memberships_ultimate_memberships_get_levels( $blog_id );
			break;
		default:
			$membership_levels = array();
			break;
	}

	return $membership_levels;
}

/**
 * Get Membership Plugin Integration's Levels/Memberships Keys
 *
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_get_membership_plugin_levels_keys( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$plugin_level_keys = array();

	$plugin_levels = nft_memberships_get_membership_plugin_levels( $blog_id );
	foreach ( $plugin_levels as $plugin_level_key => $plugin_level_value ) {
		$plugin_level_keys[] = $plugin_level_key;
	}

	return $plugin_level_keys;

}

/**
 * Add a user to the current integration's specified membership level
 *
 * @param int $level_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_membership_integration_add_user( int $level_id, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	$membership_plugin = nft_memberships_get_membership_plugin_integration( $blog_id );
	switch ( $membership_plugin ) {
		case 'wishlist_member':
			$added = nft_memberships_wishlist_member_add_user_to_level( $level_id, 'nft-memberships', $user_id, $blog_id );
			break;
		case 'ultimate_membership':
			$added = nft_memberships_ultimate_memberships_add_user_to_level( $level_id, $user_id, $blog_id );
			break;
		default:
			$added = false;
	}

	do_action( 'nft_memberships_membership_add_user', $added, $membership_plugin, $level_id, $user_id, $blog_id );

	return $added;

}

/**
 * Subscribe a user to a membership
 *
 * @param int $membership_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_subscribe_user( int $membership_id, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	/*
	 * Current user's memberships
	 */
	$user_memberships = nft_memberships_get_memberships_for_user( $user_id );
	if ( $user_memberships ) {
		foreach ( $user_memberships as $user_membership ) {

			// Check if user is already subscribed to this membership
			if ( $membership_id == $user_membership->ID ) {
				return true;
			}

		}
	}


	$nft_membership = nft_memberships_get_memberships_by( 'ID', $membership_id, true, $blog_id );
	if ( $nft_membership ) {
		/*
		 * Check if the user meets the memberships requirements
		 */
		$can_subscribe = nft_memberships_check_user_can_subscribe( $nft_membership->ID, $user_id, $blog_id );
		if ( ! $can_subscribe ) {
			return false;
		}

		/*
		 * Membership Meta Data
		 */
		$membership_data             = new StdClass;
		$membership_data->id         = $nft_membership->ID;
		$membership_data->user_id    = $user_id;
		$membership_data->registered = strtotime( 'now' );

		/*
		 * Subscribe user to membership
		 */
		$existing_memberships = get_field( 'nft_user_memberships', 'user_' . $user_id );
		if ( is_array( $existing_memberships ) ) {
			$existing_memberships[ $nft_membership->ID ] = $membership_data;

			update_user_meta( $user_id, 'nft_user_memberships', $existing_memberships );
		} else {
			$new_memberships = array();

			$new_memberships[ $nft_membership->ID ] = $membership_data;

			update_user_meta( $user_id, 'nft_user_memberships', $new_memberships );
		}

		// Membership Levels
		$membership_levels = get_field( 'membership', $nft_membership->ID );

		if ( is_array( $membership_levels ) && 0 < count( $membership_levels ) ) {
			foreach ( $membership_levels as $membership_level_id ) {
				$added = nft_memberships_membership_integration_add_user( $membership_level_id, $user_id, $blog_id );

				if ( $added ) {
					do_action( 'nft_memberships_membership_subscribe', $membership_id, $membership_level_id, $user_id, $blog_id );
				}

			}

			return true;
		}
	}

	return false;
}

/**
 * Unsubscribe a user from a membership
 *
 * @param int $membership_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_unsubscribe_user( int $membership_id, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	$nft_membership = nft_memberships_get_memberships_by( 'ID', $membership_id, true, $blog_id );
	if ( $nft_membership ) {
		/*
		 * Unsubscribe user from membership
		 */
		$existing_memberships = get_field( 'nft_user_memberships', 'user_' . $user_id );

		if ( is_array( $existing_memberships ) ) {
			foreach ( $existing_memberships as $existing_membership_key => $existing_membership_value ) {
				if ( $existing_membership_key == $nft_membership->ID ) {
					unset( $existing_memberships[ $existing_membership_key ] );
				}
			}

			update_user_meta( $user_id, 'nft_user_memberships', $existing_memberships );
		}

		$membership_levels = get_field( 'membership', $nft_membership->ID );

		if ( is_array( $membership_levels ) && 0 < count( $membership_levels ) ) {
			foreach ( $membership_levels as $membership_level_id ) {
				$added = nft_memberships_membership_integration_remove_user( $membership_level_id, $user_id, $blog_id );

				if ( $added ) {
					do_action( 'nft_memberships_membership_unsubscribe', $membership_id, $membership_level_id, $user_id, $blog_id );
				}

			}

			return true;
		}
	}

	return false;
}

/**
 * Remove a user from the current integration's specified membership level
 *
 * @param int $level_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_membership_integration_remove_user( int $level_id, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	$membership_plugin = nft_memberships_get_membership_plugin_integration( $blog_id );
	switch ( $membership_plugin ) {
		case 'wishlist_member':
			$removed = nft_memberships_wishlist_member_remove_user_from_level( $level_id, $user_id, $blog_id );
			break;
		case 'ultimate_membership':
			$removed = nft_memberships_ultimate_memberships_remove_user_from_level( $level_id, $user_id, $blog_id );
			break;
		default:
			$removed = false;
	}

	do_action( 'nft_memberships_membership_remove_user', $removed, $membership_plugin, $level_id, $user_id, $blog_id );

	return $removed;

}

/**
 * Get the memberships configured in the settings page
 *
 * @param string $status
 * @param int $blog_id
 *
 * @return int[]|WP_Post[]
 */
function nft_memberships_get_memberships( string $status = 'publish', int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return get_posts(
		array(
			'post_type'      => 'nft-memberships',
			'posts_per_page' => - 1,
			'post_status'    => $status
		)
	);
}

/**
 * Get Memberships for all Users
 *
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_get_memberships_for_users( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return nft_memberships_alchemy_get_memberships_for_users( $blog_id );
}

/**
 * Get users of membership
 *
 * @param string $network_type e.g. blockchain or hedera
 * @param mixed $contract_address
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_get_users_of_membership( string $network_type, $contract_address = '', int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 'blockchain' == $network_type && ! empty( $contract_address ) ) {
		return nft_memberships_alchemy_get_users_of_membership( $contract_address, $blog_id );
	}

	return array();

}

/**
 * Get Memberships for all Users by a specific parameter
 *
 * @param string $parameter_name
 * @param $parameter_value
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_get_memberships_for_users_by( string $parameter_name, $parameter_value, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$memberships = array();

	// Get memberships for all users
	$all_memberships = nft_memberships_get_memberships_for_users( $blog_id );
	if ( 0 < count( $all_memberships ) ) {
		foreach ( $all_memberships as $all_memberships_key => $all_memberships_value ) {

			foreach ( $all_memberships_value as $item ) {

				if ( $parameter_value == get_field( $parameter_name, $item->ID ) ) {
					$memberships[] = $item;
				}
			}


		}
	}

	return $memberships;
}

/**
 * Get memberships by a particular value
 *
 * @param string $parameter_name
 * @param $parameter_value
 * @param bool $single
 * @param int $blog_id
 *
 * @return array|int[]|WP_Post[]|WP_Post
 */
function nft_memberships_get_memberships_by( string $parameter_name, $parameter_value, bool $single = false, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	// Filtered Arrays
	$filtered_memberships = array();

	$memberships = nft_memberships_get_memberships( 'publish', $blog_id );
	if ( empty( $memberships ) ) {
		return $filtered_memberships;
	}

	foreach ( $memberships as $membership ) {
		if ( ( $parameter_value == get_field( $parameter_name, $membership->ID ) ) || ( isset( $membership->$parameter_name ) && $membership->$parameter_name == $parameter_value ) ) {
			$filtered_memberships[] = $membership;

			if ( $single ) {
				return $membership;
			}
		}
	}

	return $filtered_memberships;
}

/**
 * Get networks
 *
 * @param string $network_type e.g. blockchain, hedera
 * @param string $network_name
 *
 * @return string|string[]
 */
function nft_membership_get_networks( string $network_type, string $network_name = '' ) {

	if ( 'blockchain' == $network_type ) {
		return nft_membership_alchemy_get_networks( $network_name );
	}

	return nft_membership_hedera_get_networks( $network_name );

}


/**
 * Get a membership's network type
 *
 * @param WP_Post $membership
 *
 * @return mixed e.g. blockchain, hedera etc
 */
function nft_memberships_get_network_type( WP_Post $membership ) {

	return get_field( 'network_type', $membership->ID );

}

/**
 * Get membership check name
 *
 * @param bool $membership_check
 *
 * @return string
 */
function nft_memberships_get_membership_check_name( bool $membership_check ) {

	switch ( $membership_check ) {
		case false:
			$membership_check_name = 'Collection Level';
			break;
		case true:
			$membership_check_name = 'NFT Level';
			break;
		default:
			$membership_check_name = '-';
	}

	return $membership_check_name;
}

/**
 * Get NFT comparison check name
 *
 * @param bool $nft_comparison_check
 *
 * @return string
 */
function nft_memberships_get_membership_nft_comparison_name( bool $nft_comparison_check ) {

	switch ( $nft_comparison_check ) {
		case false:
			$nft_comparison_check_name = 'or';
			break;
		case true:
			$nft_comparison_check_name = 'and';
			break;
		default:
			$nft_comparison_check_name = '-';
	}

	return $nft_comparison_check_name;
}

/**
 * Get all memberships a user can subscribe to (i.e. meets all the requirements)
 *
 * @param int $user_id
 * @param int $blog_id
 *
 * @return array|int[]|WP_Post[]|false
 */
function nft_memberships_get_memberships_user_can_subscribe( int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	$alchemy_memberships = nft_memberships_alchemy_get_memberships_user_can_subscribe( $user_id, $blog_id );
	$hedera_memberships  = nft_memberships_hedera_get_memberships_user_can_subscribe( $user_id, $blog_id );


	return array_merge( $alchemy_memberships, $hedera_memberships );
}

/**
 * Get all memberships a user can't subscribe to (i.e. does not meet all the requirements e.g. user sold NFT) and was not added manually
 * from edit-user.php or profile.php admin page/
 *
 * @param int $user_id
 * @param int $blog_id
 *
 * @return array|int[]|WP_Post[]|false
 */
function nft_memberships_get_memberships_user_cant_subscribe( int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	/*
	 * Get all memberships that a user has access to (approved manually or automatically).
	 */
	$all_memberships = nft_memberships_get_memberships_for_user( $user_id, $blog_id );

	// Get manually approved memberships
	$filters_class = \NFT_Memberships\Filters::get_instance();
	$manually_approved_memberships = $filters_class->filter_user_memberships(  $all_memberships, $user_id, $blog_id );

	// Get automatically approved memberships
	return array_udiff(
		$all_memberships,
		$manually_approved_memberships,
		function ( $obj_a, $obj_b ){
			return $obj_a->ID - $obj_b->ID;
		}
	);

}

/**
 * Get a user's memberships
 *
 * @param int $user_id
 * @param int $blog_id
 *
 * @return array|false|int[]|WP_Post[]
 */
function nft_memberships_get_memberships_for_user( int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	$all_memberships_user_can_subscribe = nft_memberships_get_memberships_user_can_subscribe( $user_id, $blog_id );

	return apply_filters( 'nft_memberships_for_user', $all_memberships_user_can_subscribe, $user_id, $blog_id );
}

/**
 * Check if user has a specific membership
 *
 * @param $membership
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_user_has_membership( int $membership, int $user_id = 0, int $blog_id = 0 ){
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}


	if( is_int( $membership ) ){
		$membership = nft_memberships_get_memberships_by( 'ID', $membership, true, $blog_id );
	}

	if( ! $membership ){
		return false;
	}

	$user_memberships_ids = array();
	$user_memberships = nft_memberships_get_memberships_for_user( $user_id );
	if( $user_memberships && is_array( $user_memberships ) ){
		foreach ( $user_memberships as $user_membership ){
			$user_memberships_ids[] = $user_membership->ID;
		}
	}

	if( in_array( $membership->ID, $user_memberships_ids ) ){
		return true;
	}

	return false;

}

/**
 * Check if a user can subscribe to a membership
 *
 * @param int $membership_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_check_user_can_subscribe( int $membership_id, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	// All memberships a user can subscribe to (i.e. meets requirements)
	$all_memberships_user_can_subscribe = nft_memberships_get_memberships_user_can_subscribe( $user_id, $blog_id );

	if ( is_array( $all_memberships_user_can_subscribe ) && 0 < count( $all_memberships_user_can_subscribe ) ) {
		foreach ( $all_memberships_user_can_subscribe as $membership_user_can_subscribe ) {
			if ( $membership_id === $membership_user_can_subscribe->ID ) {
				return true;
			}
		}
	}

	return false;

}

/**
 * Get NFTs that have a specific attribute
 *
 * @param array $nfts
 * @param string $column_name
 * @param $column_value
 *
 * @return array
 */
function nft_memberships_get_nfts_by( array $nfts, string $column_name, $column_value ) {
	// Filtered NFTs
	$filtered_nfts = array();

	if ( empty( $nfts ) ) {
		return $filtered_nfts;
	}

	// Filter the NFTs
	foreach ( $nfts as $nft ) {
		if ( array_key_exists( $column_name, $nft ) && $column_value == $nft[ $column_value ] ) {
			$filtered_nfts[] = $nft;
		}
	}

	return $filtered_nfts;
}

/**
 * Set Cache
 *
 * @param string $cache_key
 * @param $cache_data
 * @param int $blog_id
 * @param int $expire_time
 *
 * @return mixed
 */
function nft_memberships_set_cache( string $cache_key, $cache_data, int $blog_id = 0, int $expire_time = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = $_POST['nft_blog_id'] ?? $_GET['blog-id'] ?? get_current_blog_id();
	}

	$cache_class = Cache::get_instance();

	return $cache_class->set_cache( $cache_key, $cache_data, $blog_id, $expire_time );

}

/**
 * Get Cache
 *
 * @param string $cache_key
 * @param int $blog_id
 *
 * @return false|mixed
 */
function nft_memberships_get_cache( string $cache_key, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = $_POST['nft_blog_id'] ?? $_GET['blog-id'] ?? get_current_blog_id();
	}

	$cache_class = Cache::get_instance();

	return $cache_class->get_cache( $cache_key, $blog_id );
}

/**
 * Set transient
 *
 * @param string $transient_name The part that should appear after 'nft_memberships_'
 * @param $transient_data
 * @param int $expiry_time in seconds
 * @param int $blog_id
 * @param bool $is_network
 *
 * @return bool
 */
function nft_memberships_set_transient( string $transient_name, $transient_data, int $expiry_time = 0, int $blog_id = 0, bool $is_network = false ) {
	if ( 0 == $blog_id ) {
		$blog_id = $_POST['nft_blog_id'] ?? $_GET['blog-id'] ?? get_current_blog_id();
	}

	$cache_class = Cache::get_instance();

	/*
	 * Setting transient does not always work, so first delete the transient then update it
	 */
	nft_memberships_delete_transient( $transient_name, $blog_id );

	return $cache_class->set_transient( $transient_name, $transient_data, $expiry_time, $blog_id, $is_network );
}

/**
 * Delete Transient
 *
 * @param string $transient_name
 * @param int $blog_id
 * @param bool $is_network
 *
 * @return bool
 */
function nft_memberships_delete_transient( string $transient_name, int $blog_id = 0, bool $is_network = false ) {
	if ( 0 == $blog_id ) {
		$blog_id = $_POST['nft_blog_id'] ?? $_GET['blog-id'] ?? get_current_blog_id();
	}

	$cache_class = Cache::get_instance();

	return $cache_class->delete_transient( $transient_name, $blog_id, $is_network );

}

/**
 * @param string $transient_name
 * @param int $blog_id
 * @param bool $is_network
 *
 * @return mixed
 */
function nft_memberships_get_transient( string $transient_name, int $blog_id = 0, bool $is_network = false ) {
	if ( 0 == $blog_id ) {
		$blog_id = $_POST['nft_blog_id'] ?? $_GET['blog-id'] ?? get_current_blog_id();
	}

	$cache_class = Cache::get_instance();

	return $cache_class->get_transient( $transient_name, $blog_id, $is_network );
}

