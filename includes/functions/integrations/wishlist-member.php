<?php
/**
 * NFT Memberships - Wishlist Member Integration Functions
 */

/**
 * Check if the plugin is active
 *
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_wishlist_member_is_active( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( $blog_id != get_current_blog_id() && is_multisite() && is_plugin_active_for_network( NFT_MEMBERSHIPS_PLUGIN_BASENAME ) ) {
		switch_to_blog( $blog_id );
		$is_active = is_plugin_active( 'wishlist-member-x/wpm.php' );
		restore_current_blog();
	} else {
		$is_active = is_plugin_active( 'wishlist-member-x/wpm.php' );
	}

	return $is_active;
}

/**
 * Get Wishlist Member Membership Levels
 *
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_wishlist_member_get_levels( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$wishlist_levels = array();

	if ( ! nft_memberships_wishlist_member_is_active( $blog_id ) ) {
		return $wishlist_levels;
	}

	$levels = \WishListMember\Level::get_all_levels( true );

	foreach ( $levels as $level ) {
		$wishlist_levels[ $level->get_data()['id'] ] = $level->get_data()['name'];
	}

	return $wishlist_levels;
}

/**
 * Add a user to a WishList Membership Level
 *
 * @param int $level_id
 * @param string $transaction_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_wishlist_member_add_user_to_level( int $level_id, string $transaction_id = 'nft-memberships', int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	// Get Wishlist Member User Object
	$user = new \WishListMember\User( $user_id, false );

	// Check if the user is already subscribed to the level
	if ( in_array( $level_id, $user->active_levels ) ) {
		return true;
	}

	// Add user to level
	$user->AddLevel( $level_id, $transaction_id );

	// Confirm that user is actually added to level
	if ( in_array( $level_id, $user->active_levels ) ) {
		return true;
	}

	return false;
}

/**
 * Remove a user from a WishList Membership Level
 *
 * @param int $level_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_wishlist_member_remove_user_from_level( int $level_id, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	// Get Wishlist Member User Object
	$user = new \WishListMember\User( $user_id, false );

	// Check if the user is already unsubscribed from the level
	if ( ! in_array( $level_id, $user->active_levels ) ) {
		return true;
	}

	// Add user to level
	$user->RemoveLevel( $level_id );

	return true;
}

