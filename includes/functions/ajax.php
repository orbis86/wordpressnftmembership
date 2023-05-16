<?php
/**
 * Handles AJAX requests for NFT Memberships
 */

/**
 * ==============================================================================================================================
 * BLOCKCHAIN AJAX
 * ==============================================================================================================================
 */

/**
 * Add user's wallet address
 */
add_action( 'wp_ajax_nft_memberships_add_owner_address', 'wp_ajax_nft_memberships_add_owner_address' );
function wp_ajax_nft_memberships_add_owner_address() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'add-owner-address' ) ) {
		wp_send_json_error( 'You are not authorized to do that.', 403 );
		wp_die();
	}

	if ( isset( $_POST['owner_address'] ) ) {
		update_user_meta( get_current_user_id(), 'nft_memberships_owner_address', sanitize_text_field( $_POST['owner_address'] ) );

		wp_send_json_success( array( 'added' => true ) );
	} else {
		wp_send_json_error();
	}

	wp_die(); //Required to end ajax request
}

/**
 * No Priv - Add user's wallet address
 */
add_action( 'wp_ajax_nopriv_nft_memberships_add_owner_address', 'wp_ajax_nopriv_nft_memberships_add_owner_address' );
function wp_ajax_nopriv_nft_memberships_add_owner_address() {
	wp_send_json_error( 'Please log in', 403 );

	wp_die(); //Required to end ajax request
}

/**
 * Reset user's wallet address
 */
add_action( 'wp_ajax_nft_memberships_reset_owner_address', 'wp_ajax_nft_memberships_reset_owner_address' );
function wp_ajax_nft_memberships_reset_owner_address() {

	delete_user_meta( get_current_user_id(), 'nft_memberships_owner_address' );
	delete_user_meta( get_current_user_id(), 'nft_user_memberships' );

	wp_send_json_success( array( 'reset' => true ) );

	wp_die(); //Required to end ajax request
}

/**
 * No Priv - Reset user's wallet address
 */
add_action( 'wp_ajax_nopriv_nft_memberships_reset_owner_address', 'wp_ajax_nopriv_nft_memberships_reset_owner_address' );
function wp_ajax_nopriv_nft_memberships_reset_owner_address() {
	wp_send_json_error( 'Please log in', 403 );

	wp_die(); //Required to end ajax request
}

/**
 * ==============================================================================================================================
 * END BLOCKCHAIN AJAX
 * ==============================================================================================================================
 */

/**
 * ==============================================================================================================================
 * HEDERA AJAX
 * ==============================================================================================================================
 */


/**
 * Add user's wallet address
 */
add_action( 'wp_ajax_nft_memberships_add_account_id', 'wp_ajax_nft_memberships_add_account_id' );
function wp_ajax_nft_memberships_add_account_id() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'add-account-id' ) ) {
		wp_send_json_error( 'You are not authorized to do that.', 403 );
		wp_die();
	}

	if ( isset( $_POST['account_id'] ) ) {
		update_user_meta( get_current_user_id(), 'nft_memberships_account_id', sanitize_text_field( $_POST['account_id'] ) );

		wp_send_json_success( array( 'added' => true ) );
	} else {
		wp_send_json_error();
	}

	wp_die(); //Required to end ajax request
}

/**
 * No Priv - Add user's wallet address
 */
add_action( 'wp_ajax_nopriv_nft_memberships_add_account_id', 'wp_ajax_nopriv_nft_memberships_add_account_id' );
function wp_ajax_nopriv_nft_memberships_add_account_id() {
	wp_send_json_error( 'Please log in', 403 );

	wp_die(); //Required to end ajax request
}

/**
 * Reset user's wallet address
 */
add_action( 'wp_ajax_nft_memberships_reset_account_id', 'wp_ajax_nft_memberships_reset_account_id' );
function wp_ajax_nft_memberships_reset_account_id() {

	delete_user_meta( get_current_user_id(), 'nft_memberships_account_id' );
	delete_user_meta( get_current_user_id(), 'nft_user_memberships' );

	wp_send_json_success( array( 'reset' => true ) );

	wp_die(); //Required to end ajax request
}

/**
 * No Priv - Reset user's wallet address
 */
add_action( 'wp_ajax_nopriv_nft_memberships_reset_account_id', 'wp_ajax_nopriv_nft_memberships_reset_account_id' );
function wp_ajax_nopriv_nft_memberships_reset_account_id() {
	wp_send_json_error( 'Please log in', 403 );

	wp_die(); //Required to end ajax request
}

/**
 * ==============================================================================================================================
 * END HEDERA AJAX
 * ==============================================================================================================================
 */


/**
 * Subscribe user to membership
 */
add_action( 'wp_ajax_nft_memberships_subscribe_user', 'wp_ajax_nft_memberships_subscribe_user' );
function wp_ajax_nft_memberships_subscribe_user() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'subscribe-user' ) ) {
		wp_send_json_error( 'You are not authorized to do that.', 403 );
		wp_die();
	}

	if ( isset( $_POST['membership_id'] ) ) {
		$subscribed = nft_memberships_subscribe_user( absint( $_POST['membership_id'] ) );

		if ( $subscribed ) {
			wp_send_json_success( array( 'subscribed' => true ) );
		} else {
			wp_send_json_error( array( 'subscribed' => false ) );
		}
	} else {
		wp_send_json_error();
	}


	wp_die(); //Required to end ajax request
}

/**
 * No Priv - Subscribe user to membership
 */
add_action( 'wp_ajax_nopriv_nft_memberships_subscribe_user', 'wp_ajax_nopriv_nft_memberships_subscribe_user' );
function wp_ajax_nopriv_nft_memberships_subscribe_user() {
	wp_send_json_error( 'Please log in', 403 );

	wp_die(); //Required to end ajax request
}

/**
 * Unsubscribe user to membership
 */
add_action( 'wp_ajax_nft_memberships_unsubscribe_user', 'wp_ajax_nft_memberships_unsubscribe_user' );
function wp_ajax_nft_memberships_unsubscribe_user() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'unsubscribe-user' ) ) {
		wp_send_json_error( 'You are not authorized to do that.', 403 );
		wp_die();
	}

	if ( isset( $_POST['membership_id'] ) ) {
		$subscribed = nft_memberships_unsubscribe_user( absint( $_POST['membership_id'] ) );

		if ( $subscribed ) {
			wp_send_json_success( array( 'unsubscribed' => true ) );
		} else {
			wp_send_json_error( array( 'unsubscribed' => false ) );
		}
	} else {
		wp_send_json_error();
	}


	wp_die(); //Required to end ajax request
}

/**
 * No Priv - Unsubscribe user to membership
 */
add_action( 'wp_ajax_nopriv_nft_memberships_unsubscribe_user', 'wp_ajax_nopriv_nft_memberships_unsubscribe_user' );
function wp_ajax_nopriv_nft_memberships_unsubscribe_user() {
	wp_send_json_error( 'Please log in', 403 );

	wp_die(); //Required to end ajax request
}

/**
 * ===================================================================================================================================
 */


/**
 * Get NFTs of a collection
 */
add_action( 'wp_ajax_nft_memberships_get_collection_nfts', 'wp_ajax_nft_memberships_get_collection_nfts_callback' );
function wp_ajax_nft_memberships_get_collection_nfts_callback() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'get-collection-nfts' ) ) {
		wp_send_json_error( 'You are not authorized to do that.', 403 );
		wp_die();
	}

	if ( isset( $_POST['contract_address'] ) && isset( $_POST['blockchain_type'] ) ) {
		$nfts = nft_memberships_alchemy_get_nfts_of_contract_address( $_POST['contract_address'], 'ethereum_mainnet', array(
			'withMetadata' => 'true',
			'total'        => '1500'
		) );
		if ( $nfts ) {
			$response = array();
			foreach ( $nfts as $nft ) {
				$response[] = array(
					'id' => $nft['token_id'],
					'text' => $nft['title'],
				);
			}

			wp_send_json_success( $response );
		} else {
			wp_send_json_error();
		}
	} else {
		wp_send_json_error();
	}

	wp_die(); //Required to end ajax request
}

/**
 * No Privileges - Get NFTs of a Collection
 */
add_action( 'wp_ajax_nopriv_nft_memberships_get_collection_nfts', 'wp_ajax_nopriv_nft_memberships_get_collection_nfts_callback' );
function wp_ajax_nopriv_nft_memberships_get_collection_nfts_callback() {
	wp_send_json_error( 'Please log in', 403 );

	wp_die(); //Required to end ajax request
}

