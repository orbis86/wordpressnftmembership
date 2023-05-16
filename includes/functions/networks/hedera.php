<?php
/**
 * Hedera Hashgraph - NFT Memberships Helper Functions
 */

/**
 * Get networks
 *
 * @param string $network_name
 *
 * @return string|string[]
 */
function nft_membership_hedera_get_networks( string $network_name = '' ) {

	$hedera_networks = array(
		'hedera_mainnet'    => 'Hedera Mainnet',
		'hedera_testnet'    => 'Hedera Testnet',
		'hedera_previewnet' => 'Hedera Previewnet'
	);

	if ( '' == $network_name ) {
		return $hedera_networks;
	}

	return $hedera_networks[ $network_name ];
}

/**
 * Get Hedera memberships a user can subscribe to
 *
 * @param int $user_id
 * @param int $blog_id
 *
 * @return array|WP_Post[]
 */
function nft_memberships_hedera_get_memberships_user_can_subscribe( int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}


	// User's Memberships
	$user_memberships = array();

	/*
	 * Get the Hedera memberships
	 */
	$nft_level_memberships = nft_memberships_get_memberships_by( 'network_type', 'hedera', false, $blog_id );

	if ( is_array( $nft_level_memberships ) && 0 < count( $nft_level_memberships ) ) {
		foreach ( $nft_level_memberships as $nft_level_membership ) {
			if ( 'hedera' != nft_memberships_get_network_type( $nft_level_membership ) ) {
				continue;
			}

			$hedera_type = get_field( 'hedera_type', $nft_level_membership->ID );

			// Get owner's NFTs that have this membership's hedera type
			$user_nfts = nft_memberships_hedera_get_nfts_of_owner( $hedera_type, $user_id, $blog_id );

			if ( empty( $user_nfts ) ) {
				continue;
			}

			/*
			 * Get membership's NFTs
			 */
			$collection_nfts = explode( ',', get_field( 'collection_nfts', $nft_level_membership->ID ) ); // Array

			/*
			 * Get the operator used to check the NFT level membership - or / and
			 */
			$comparison = nft_memberships_get_membership_nft_comparison_name( get_field( 'nft_check_comparison', $nft_level_membership->ID ) );

			// Create array used in 'or' comparison
			$comparison_or_nfts = array();

			// Create array used in 'and' comparison
			$comparison_all_nfts = array();

			foreach ( $user_nfts as $user_nft ) {
				// Account for 'or' NFT check
				if ( 'or' == $comparison && in_array( $user_nft->token_id, $collection_nfts ) && empty( $comparison_or_nfts ) ) {
					$user_memberships[]   = $nft_level_membership;
					$comparison_or_nfts[] = $user_nft->token_id;

					// Get out of the loop since we have found at least one match
					break;
				}

				// Account for 'and' NFT check
				if ( 'and' == $comparison && in_array( $user_nft->token_id, $collection_nfts ) ) {
					$comparison_all_nfts[] = $user_nft->token_id;
				}

			}

			// Now do actual 'and' comparison
			sort( $comparison_all_nfts );
			sort( $collection_nfts );

			if ( 'and' == $comparison && $comparison_all_nfts === $collection_nfts ) {
				$user_memberships[] = $nft_level_membership;
				break;
			}
		}
	}

	return apply_filters( 'nft_memberships_hedera_user_can_subscribe', $user_memberships, $nft_level_memberships, $user_id, $blog_id );

}

/**
 * Get a network's endpoint
 *
 * @param string $hedera_type
 * @param int $blog_id
 *
 * @return false|string
 */
function nft_memberships_hedera_get_network_endpoint( string $hedera_type, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	switch ( $hedera_type ) {
		case 'hedera_mainnet':
			$url = 'https://mainnet-public.mirrornode.hedera.com/api/v1';
			break;
		case 'hedera_testnet':
			$url = 'https://testnet.mirrornode.hedera.com/api/v1';
			break;

		case 'hedera_previewnet':
			$url = 'https://previewnet.mirrornode.hedera.com/api/v1';
			break;

		default:
			$url = false;
	}

	return $url;

}

/**
 * Get Hedera NFTs of a user
 *
 * @param string $hedera_type
 * @param int $user_id
 * @param int $blog_id
 *
 * @todo - Store in database
 *
 * @return array|false
 */
function nft_memberships_hedera_get_nfts_of_owner( string $hedera_type, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	$owner_nfts = array();


	// Get user's address
	$hedera_account_id = get_user_meta( $user_id, 'nft_memberships_account_id', true );
	if ( empty( $hedera_account_id ) ) {
		return $owner_nfts;
	}

	$hedera_api_endpoint = nft_memberships_hedera_get_network_endpoint( $hedera_type, $blog_id );
	if ( ! $hedera_api_endpoint ) {
		return $owner_nfts;
	}

	$owner_nfts_request = wp_remote_get(
		$hedera_api_endpoint . '/accounts/' . $hedera_account_id . '/nfts'
	);

	if ( ! is_wp_error( $owner_nfts_request ) ) {

		$nfts = json_decode( wp_remote_retrieve_body( $owner_nfts_request ) );
		if ( ! isset( $nfts->nfts ) || ! is_array( $nfts->nfts ) ) {
			return $owner_nfts;
		}

		$owner_nfts = $nfts->nfts;
	}

	return $owner_nfts;
}