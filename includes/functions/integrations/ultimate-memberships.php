<?php
/**
 * NFT Memberships - Ultimate Memberships Pro Integration Functions
 */

/**
 * Check if the plugin is active
 *
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_ultimate_memberships_is_active( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( $blog_id != get_current_blog_id() && is_multisite() && is_plugin_active_for_network( NFT_MEMBERSHIPS_PLUGIN_BASENAME ) ) {
		switch_to_blog( $blog_id );
		$is_active = is_plugin_active( 'indeed-membership-pro/indeed-membership-pro.php' );
		restore_current_blog();
	} else {
		$is_active = is_plugin_active( 'indeed-membership-pro/indeed-membership-pro.php' );
	}

	return $is_active;
}

/**
 * Get Ultimate Memberships Pro Memberships / Levels
 *
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_ultimate_memberships_get_levels( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$ultimate_memberships_levels = array();

	if ( ! nft_memberships_ultimate_memberships_is_active( $blog_id ) ) {
		return $ultimate_memberships_levels;
	}

	$levels = \Indeed\Ihc\Db\Memberships::getAll();

	foreach ( $levels as $level ) {
		$ultimate_memberships_levels[ $level['id'] ] = $level['label'];
	}

	return $ultimate_memberships_levels;
}

/**
 * Add a user to a membership level
 *
 * @param int $level_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_ultimate_memberships_add_user_to_level( int $level_id, int $user_id = 0, int $blog_id = 0 ) {

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	// Assign membership to user
	$assigned = \Indeed\Ihc\UserSubscriptions::assign( $user_id, $level_id );
	if ( ! $assigned ) {
		return false;
	}

	// Activate membership assigned above
	$activated = \Indeed\Ihc\UserSubscriptions::makeComplete( $user_id, $level_id );
	if ( 1 === $activated ) {
		return true;
	}

	return false;

}

/**
 * Remove a user from a subscription level
 *
 * @param int $level_id
 * @param int $user_id
 * @param int $blog_id
 *
 * @return bool
 */
function nft_memberships_ultimate_memberships_remove_user_from_level( int $level_id, int $user_id = 0, int $blog_id = 0 ) {

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$removed = \Indeed\Ihc\UserSubscriptions::deleteOne( $user_id, $level_id );
	if ( 1 === $removed ) {
		return true;
	}

	return false;
}
