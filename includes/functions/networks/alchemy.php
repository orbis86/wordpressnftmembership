<?php
/**
 * Alchemy - NFT Memberships Helper Functions
 */

use NFT_Memberships\Database\NFTs\Collection\Collection_NFTs_Model;
use NFT_Memberships\Database\NFTs\Owner\Owner_NFTs_Model;
use NFT_Memberships\Database\Owners\Token\Token_Owners_Model;

/**
 * Get Alchemy's API key
 *
 * @return string|false
 */
function nft_memberships_alchemy_get_api_key( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$api_key         = false;
	$plugin_settings = nft_memberships_get_settings( $blog_id );
	if ( isset( $plugin_settings['general']['alchemy_api_key'] ) ) {
		$api_key = $plugin_settings['general']['alchemy_api_key'];
	}

	return $api_key;
}

/**
 * Get Memberships for all Users
 *
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_alchemy_get_memberships_for_users( int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	/*
	 * Get from Cache
	 */
	$cache_key    = 'memberships_for_users';
	$cached_users = nft_memberships_get_cache( $cache_key, $blog_id );
	if ( $cached_users ) {
		return $cached_users;
	}

	$users_with_contract_address = array();

	/*
	 * Get all users that have a contract address
	 */
	$users = get_users();
	foreach ( $users as $user ) {
		if ( ! metadata_exists( 'user', $user->ID, 'nft_memberships_owner_address' ) ) {
			continue;
		}

		$users_with_contract_address[] = $user;
	}

	$user_memberships = array();

	/*
	 * Get User Membership
	 */
	foreach ( $users_with_contract_address as $user_with_contract_address ) {

		$memberships_for_user = nft_memberships_get_memberships_for_user( $user_with_contract_address->ID, $blog_id );
		if ( $memberships_for_user ) {
			$user_memberships[ $user_with_contract_address->ID ] = $memberships_for_user;
		}

	}

	/*
	 * Set Cache
	 */
	nft_memberships_set_cache( $cache_key, $user_memberships, $blog_id );

	return $user_memberships;

}

/**
 * Get users of membership
 *
 * @param string $contract_address
 * @param int $blog_id
 *
 * @return array
 */
function nft_memberships_alchemy_get_users_of_membership( string $contract_address, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$membership_users = array();

	$memberships_for_users = nft_memberships_get_memberships_for_users_by( 'contract_address', $contract_address, $blog_id );
	if ( ! empty( $memberships_for_users ) ) {
		foreach ( $memberships_for_users as $user_id => $user_memberships ) {
			$membership_users[] = $user_id;
		}
	}

	return $membership_users;

}

/**
 * Get networks
 *
 * @param string $network_name
 *
 * @return string|string[]
 */
function nft_membership_alchemy_get_networks( string $network_name = '' ) {

	$ethereum_networks = array(
		'ethereum_mainnet' => 'Ethereum Mainnet',
		'ethereum_rinkbey' => 'Ethereum Rinkbey',
		'ethereum_goerli'  => 'Ethereum Goerli',
		'polygon_mainnet'  => 'Polygon Mainnet',
		'polygon_mumbai'   => 'Polygon Mumbai',
	);

	if ( '' == $network_name ) {
		return $ethereum_networks;
	}

	return $ethereum_networks[ $network_name ];
}

/**
 * Get all memberships a user can subscribe to (i.e. meets all the requirements)
 *
 * @param int $user_id
 * @param int $blog_id
 *
 * @return array|WP_Post[]
 */
function nft_memberships_alchemy_get_memberships_user_can_subscribe( int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	// Get user's address
	$user_address = get_user_meta( $user_id, 'nft_memberships_owner_address', true );
	if ( empty( $user_address ) ) {
		return array();
	}

	// User's Memberships
	$user_memberships = array();

	// Whether we should get the NFTs from the API even if they exist in the database
	$force_fetch = false;
	if( wp_doing_cron() ){
		$force_fetch = true;
	}

	/*
	 * Get the memberships that are checked at a collection level
	 */
	$collection_level_memberships = nft_memberships_get_memberships_by( 'membership_check', false, false, $blog_id );
	if ( 0 < count( $collection_level_memberships ) ) {
		foreach ( $collection_level_memberships as $collection_level_membership ) {
			if ( 'blockchain' != nft_memberships_get_network_type( $collection_level_membership ) ) {
				continue;
			}

			// Get owner's NFTs that have this membership's contract address
			$user_nfts = nft_memberships_alchemy_get_nfts_of_owner_address(
				$user_address,
				array( get_field( 'contract_address', $collection_level_membership->ID ) ),
				get_field( 'blockchain_type', $collection_level_membership->ID ),
				array(),
				array(),
				$force_fetch,
				$user_id,
				$blog_id
			);

			if ( empty( $user_nfts ) ) {
				continue;
			}

			$user_memberships[] = $collection_level_membership;

		}
	}

	/*
	 * Get the memberships that are checked at a NFT level
	 */
	$nft_level_memberships = nft_memberships_get_memberships_by( 'membership_check', true, false, $blog_id );

	if ( is_array( $nft_level_memberships ) && 0 < count( $nft_level_memberships ) ) {
		foreach ( $nft_level_memberships as $nft_level_membership ) {

			// Get owner's NFTs that have this membership's contract address
			$user_nfts = nft_memberships_alchemy_get_nfts_of_owner_address(
				$user_address,
				array( get_field( 'contract_address', $nft_level_membership->ID ) ),
				get_field( 'blockchain_type', $nft_level_membership->ID ),
				array(),
				array(),
				$force_fetch,
				$user_id,
				$blog_id
			);

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
				if ( 'or' == $comparison && in_array( $user_nft['token_id'], $collection_nfts ) && empty( $comparison_or_nfts ) ) {
					$user_memberships[]   = $nft_level_membership;
					$comparison_or_nfts[] = $user_nft['token_id'];

					// Get out of the loop since we have found at least one match
					break;
				}

				// Account for 'and' NFT check
				if ( 'and' == $comparison && in_array( $user_nft['token_id'], $collection_nfts ) ) {
					$comparison_all_nfts[] = $user_nft['token_id'];
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

	return apply_filters( 'nft_memberships_alchemy_user_can_subscribe', $user_memberships, $collection_level_memberships, $nft_level_memberships, $user_id, $blog_id );

}

/**
 * Get a contract address' network endpoint
 *
 * @param string $blockchain_type
 * @param int $blog_id
 *
 * @return false|string
 */
function nft_memberships_alchemy_get_contract_address_endpoint( string $blockchain_type, int $blog_id ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$api_key = nft_memberships_get_api_key( $blog_id );
	if ( ! $api_key ) {
		return false;
	}

	switch ( $blockchain_type ) {
		case 'ethereum_mainnet':
			$url = 'https://eth-mainnet.g.alchemy.com/nft/v2/' . $api_key;
			break;
		case 'polygon_mainnet':
			$url = 'https://polygon-mainnet.g.alchemyapi.io/nft/v2/' . $api_key;
			break;

		case 'ethereum_rinkbey':
			$url = 'https://eth-rinkeby.g.alchemy.com/nft/v2/' . $api_key;
			break;
		case 'ethereum_goerli':
			$url = 'https://eth-goerli.g.alchemy.com/nft/v2/' . $api_key;
			break;
		case 'polygon_mumbai':
			$url = 'https://polygon-mumbai.g.alchemy.com/nft/v2/' . $api_key;
			break;
		default:
			$url = false;
	}

	return $url;

}

/**
 * Get NFTs of a Collection / Contract Address
 *
 * @param string $contract_address // Also referred to as collection
 * @param string $contract_address_network
 * @param array $args
 * @param bool $force_fetch // whether to get from the API even though the resource exists in the database
 * @param int $blog_id
 * @param array $nfts // Used only in the recursive call
 *
 * @return array|false
 * @link https://docs.alchemy.com/reference/getnftsforcollection
 */
function nft_memberships_alchemy_get_nfts_of_contract_address( string $contract_address, string $contract_address_network = 'ethereum_mainnet', array $args = array(), bool $force_fetch = false, int $blog_id = 0, array $nfts = array() ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	/*
	 * Get data from database
	 */
	$collection_nfts_query = Collection_NFTs_Model::query();
	if ( ! $force_fetch ) {
		$collection_nfts = $collection_nfts_query->where( 'contract_address', $contract_address )->get();

		if ( 0 < count( $collection_nfts ) ) {
			return $collection_nfts;
		}
	}

	// Get API Key
	$api_key = nft_memberships_get_api_key( $blog_id );
	if ( ! $api_key ) {
		return false;
	}

	// Get Contract Address Endpoint
	$contract_address_endpoint = nft_memberships_alchemy_get_contract_address_endpoint( $contract_address_network, $blog_id );
	if ( ! $contract_address_endpoint ) {
		return false;
	}

	/*
	 * Create API Request URL
	 */
	$contract_address_endpoint = $contract_address_endpoint . '/getNFTsForCollection/?contractAddress=' . $contract_address;

	if ( ! empty( $args ) ) {
		$contract_address_endpoint = $contract_address_endpoint . '&' . http_build_query( $args );
	}

	// Make API request
	$response = wp_remote_get(
		$contract_address_endpoint,
		array(
			'headers' => array(
				'Content-Type' => 'application/json',
			)
		)
	);

	if ( ! is_wp_error( $response ) && is_array( $response ) && array_key_exists( 'body', $response ) ) {
		$body = json_decode( $response['body'] );

		/*
		 * Save to Database
		 */
		foreach ( $body->nfts as $nft ) {
			$token_id = nft_memberships_alchemy_get_nft_token_id( $nft );

			// Check if item is in database and update it instead
			$collection_nfts_query = Collection_NFTs_Model::query();
			$existing_nft          = $collection_nfts_query->where(
				array(
					'contract_address' => $nft->contract->address,
					'token_id'         => $token_id,
				)
			)->get()->first();

			if ( $existing_nft ) {
				$existing_nft->save();

				$nfts[] = $existing_nft;

				continue;
			}

			// Add new resource to database
			$nft_resource                    = new Collection_NFTs_Model();
			$nft_resource->contract_address  = $nft->contract->address;
			$nft_resource->token_id          = $token_id;
			$nft_resource->token_type        = $nft->id->tokenMetadata->tokenType;
			$nft_resource->title             = $nft->title == '' ? $nft->contractMetadata->name . ' #...' . substr( $nft->id->tokenId, - 4 ) : $nft->title;
			$nft_resource->description       = $nft->description;
			$nft_resource->token_uri_raw     = $nft->tokenUri->raw;
			$nft_resource->token_uri_gateway = $nft->tokenUri->gateway;
			$nft_resource->media             = json_encode( $nft->media );
			$nft_resource->properties        = json_encode( $nft->metadata->attributes );
			$nft_resource->last_updated      = date_create( $nft->timeLastUpdated );
			$nft_resource->contract_metadata = json_encode( $nft->contractMetadata );
			$nft_resource->save();

			$nfts[] = $nft_resource;

		}

		/*
		 * Check if there is a next token, and if so, remake request
		 *
		 * A single API request returns 100 items
		 */
		if ( $args['total'] > count( $nfts ) ) {
			$args['startToken'] = absint( count( $nfts ) + 1 );
			$args['limit']      = absint( $args['total'] - count( $nfts ) );
			$nfts               = nft_memberships_alchemy_get_nfts_of_contract_address( $contract_address, $contract_address_network, $args, $force_fetch, $blog_id, $nfts );
		}

		return $nfts;

	}

	return false;
}

/**
 * Get owners of a token id in a contract address
 *
 * @param string $token_id // In hex or decimal format
 * @param string $contract_address
 * @param string $network
 * @param array $args
 * @param array $token_owners // Used in recursion
 * @param bool $force_fetch // whether to get from the API even though the resource exists in the database
 * @param int $blog_id
 *
 * @return Token_Owners_Model|false
 * @link https://docs.alchemy.com/reference/getownersfortoken
 */
function nft_memberships_alchemy_get_owners_of_token_id( string $token_id, string $contract_address, string $network, array $args = array(), array $token_owners = array(), bool $force_fetch = false, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	/*
	 * Get data from database
	 */
	$token_owners_query = Token_Owners_Model::query();
	if ( ! $force_fetch ) {
		$token_owners = $token_owners_query->where(
			array(
				'contract_address' => $contract_address,
				'token_id'         => $token_id
			)
		)->get()->first();

		if ( $token_owners ) {
			return $token_owners;
		}
	}

	// Get API Key
	$api_key = nft_memberships_get_api_key( $blog_id );
	if ( ! $api_key ) {
		return false;
	}

	// Get Contract Address Endpoint
	$contract_address_endpoint = nft_memberships_alchemy_get_contract_address_endpoint( $network, $blog_id );
	if ( ! $contract_address_endpoint ) {
		return false;
	}

	// Append to endpoint
	$contract_address_endpoint = $contract_address_endpoint . '/getOwnersForToken/?contractAddress=' . $contract_address . '&tokenId=' . $token_id;

	// Create query parameters from passed arguments
	if ( ! empty( $args ) ) {
		$contract_address_endpoint = $contract_address_endpoint . '&' . http_build_query( $args );
	}

	// Make API request
	$response = wp_remote_get(
		$contract_address_endpoint,
		array(
			'headers' => array(
				'Content-Type' => 'application/json',
			)
		)
	);

	if ( ! is_wp_error( $response ) && is_array( $response ) && array_key_exists( 'body', $response ) ) {
		$body = json_decode( $response['body'] );

		/*
		 * Save to Database
		 */
		if ( $body->owners ) {

			// Check if item is in database and update it instead
			$token_owners_query = Token_Owners_Model::query();
			$existing_owners    = $token_owners_query->where(
				array(
					'contract_address' => $contract_address,
					'token_id'         => $token_id
				)
			)->get()->first();

			if ( $existing_owners ) {
				$existing_owners->owners_adresses = json_encode( $body->owners );
				$existing_owners->save();

				return $existing_owners;
			}
		}

		// Add New Resource to Database
		$new_token_owners                   = new Token_Owners_Model();
		$new_token_owners->contract_address = $contract_address;
		$new_token_owners->token_id         = $token_id;
		$new_token_owners->owners_addresses = json_encode( $body->owners );
		$new_token_owners->save();


		return $new_token_owners;

	}

	return false;

}


/**
 * Get owners of a collection / contract address
 *
 * @param string $contract_address
 * @param string $network
 * @param array $args
 * @param array $collection_owners // Used in recursion
 * @param bool $force_fetch // whether to get from the API even though the resource exists in the database
 * @param int $blog_id
 *
 * @return array|false
 * @link https://docs.alchemy.com/reference/getownersforcollection
 */
function nft_memberships_alchemy_get_owners_of_contract_address( string $contract_address, string $network, array $args = array(), array $collection_owners = array(), bool $force_fetch = false, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	/*
	 * Get data from database
	 */
	$collection_owners_query = Collection_Owners_Model::query();
	if ( ! $force_fetch ) {
		$collection_owners = $collection_owners_query->where( 'contract_address', $contract_address )->get();

		if ( 0 < count( $collection_owners ) ) {
			return $collection_owners;
		}
	}

	// Get API Key
	$api_key = nft_memberships_get_api_key( $blog_id );
	if ( ! $api_key ) {
		return false;
	}

	// Get Contract Address Endpoint
	$contract_address_endpoint = nft_memberships_alchemy_get_contract_address_endpoint( $network, $blog_id );
	if ( ! $contract_address_endpoint ) {
		return false;
	}

	// Append to endpoint
	$contract_address_endpoint = $contract_address_endpoint . '/getOwnersForCollection/?withTokenBalances=true&contractAddress=' . $contract_address;

	// Create query parameters from passed arguments
	if ( ! empty( $args ) ) {
		$contract_address_endpoint = $contract_address_endpoint . '&' . http_build_query( $args );
	}

	// Make API request
	$response = wp_remote_get(
		$contract_address_endpoint,
		array(
			'headers' => array(
				'Content-Type' => 'application/json',
			)
		)
	);

	if ( ! is_wp_error( $response ) && is_array( $response ) && array_key_exists( 'body', $response ) ) {
		$body = json_decode( $response['body'] );

		/*
		 * Save to Database
		 */
		foreach ( $body->ownerAddresses as $collection_owner ) {
			// Check if item is in database and update it instead
			$collection_owners_query = Collection_Owners_Model::query();
			$existing_owner          = $collection_owners_query->where(
				array(
					'contract_address' => $contract_address,
					'owner_address'    => $collection_owner->ownerAddress,
				)
			)->get()->first();

			if ( $existing_owner ) {
				$existing_owner->token_balances = json_encode( $collection_owner->tokenBalances );
				$existing_owner->save();

				$collection_owners[] = $existing_owner;

				continue;
			}

			// Add New Resource to Database
			$new_collection_owner                   = new Collection_Owners_Model();
			$new_collection_owner->contract_address = $contract_address;
			$new_collection_owner->owner_address    = $collection_owner->ownerAddress;
			$new_collection_owner->token_balances   = json_encode( $collection_owner->tokenBalances );
			$new_collection_owner->save();

			$collection_owners[] = $new_collection_owner;

		}

		/*
		 * Check if there is pagination, and if so, remake request
		 */
		if ( isset( $body->pageKey ) && count( $collection_owners ) < $body->totalCount ) {
			$collection_owners = nft_memberships_alchemy_get_owners_of_contract_address( $contract_address, $network, $args, $collection_owners, $blog_id );
		}

		return $collection_owners;

	}

	return false;

}

/**
 * Get an NFT's token id.
 *
 * Various methods are used to get it as not all NFTs use the same parameter as the token id e.g. from Opensea
 *
 * @param object $nft
 *
 * @return int|string
 */
function nft_memberships_alchemy_get_nft_token_id( object $nft ) {
	$token_id = '';

	$token_id = $nft->metadata->edition ?? ltrim( $nft->id->tokenId, '0x' );

	// First check from the title
	if ( isset( $nft->title ) && strpos( $nft->title, '#' ) !== false ) {
		$parts    = explode( '#', $nft->title );
		$token_id = $parts[1];
	}

	// Check from the edition
	if ( isset( $nft->metadata->edition ) && '' == $token_id ) {
		$token_id = $nft->metadata->edition;
	}

	// Get from actual token id
	if ( isset( $nft->id->tokenId ) && '' == $token_id ) {
		$token_id = ltrim( $nft->id->tokenId, '0x' );
	}

	// If token id still empty, assign it to 0
	return $token_id == '' ? 0 : $token_id;

}

/**
 * Get NFTs owned by a user
 *
 * @param string $owner_address
 * @param array $contract_addresses // Also referred to as collection
 * @param string $network
 * @param array $args
 * @param array $nfts // Used in recursion
 * @param bool $force_fetch // whether to get from the API even though the resource exists in the database
 * @param int $user_id
 * @param int $blog_id
 *
 * @return false|mixed
 * @link https://docs.alchemy.com/reference/getnfts
 */
function nft_memberships_alchemy_get_nfts_of_owner_address( string $owner_address, array $contract_addresses, string $network, array $args = array(), array $nfts = array(), bool $force_fetch = false, int $user_id = 0, int $blog_id = 0 ) {
	if ( 0 == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $user_id ) {
		$user_id = get_current_user_id();
	}

	/*
	 * Get data from database
	 */
	$owner_nfts_query = Owner_NFTs_Model::query();
	if ( ! $force_fetch ) {
		$owner_nfts = $owner_nfts_query->where( 'owner_address', $owner_address );

		if ( ! empty( $contract_addresses ) ) {
			$owner_nfts = $owner_nfts->whereIn( 'contract_address', $contract_addresses );
		}

		$owner_nfts = $owner_nfts->get();
		if ( 0 < count( $owner_nfts ) ) {
			return $owner_nfts;
		}
	}

	// Get API Key
	$api_key = nft_memberships_get_api_key( $blog_id );
	if ( ! $api_key ) {
		return false;
	}

	// Get Contract Address Endpoint
	$contract_address_endpoint = nft_memberships_alchemy_get_contract_address_endpoint( $network, $blog_id );
	if ( ! $contract_address_endpoint ) {
		return false;
	}

	// Append to endpoint
	$contract_address_endpoint = $contract_address_endpoint . '/getNFTs/?owner=' . $owner_address;

	/*
	 * Add attributes to query parameters
	 */
	$args['withMetadata'] = 'true';
	$args['pageSize']     = '100';
	// Create query parameters from passed arguments
	if ( ! empty( $args ) ) {
		$contract_address_endpoint = $contract_address_endpoint . '&' . http_build_query( $args );
	}

	// Create query parameters from passed contract addresses
	if ( ! empty( $contract_addresses ) ) {
		$contract_addresses_url_params = '&';
		foreach ( $contract_addresses as $contract_address ) {
			$contract_addresses_url_params = $contract_addresses_url_params . 'contractAddresses[]=' . $contract_address . '&';
		}

		$contract_address_endpoint = $contract_address_endpoint . $contract_addresses_url_params;
	}

	// Make API request
	$response = wp_remote_get(
		$contract_address_endpoint,
		array(
			'headers' => array(
				'Content-Type' => 'application/json',
			)
		)
	);

	if ( ! is_wp_error( $response ) && is_array( $response ) && array_key_exists( 'body', $response ) ) {
		$body = json_decode( $response['body'] );

		/*
		 * Save to Database
		 */
		foreach ( $body->ownedNfts as $nft ) {
			$token_id = nft_memberships_alchemy_get_nft_token_id( $nft );

			// Check if item is in database and update it instead
			$owner_nfts_query = Owner_NFTs_Model::query();
			$existing_nft     = $owner_nfts_query->where(
				array(
					'contract_address' => $nft->contract->address,
					'token_id'         => $token_id,
				)
			)->get()->first();

			if ( $existing_nft ) {
				$existing_nft->save();

				$nfts[] = $existing_nft;

				continue;
			}

			// Add new resource to database
			$nft_resource                    = new Owner_NFTs_Model();
			$nft_resource->contract_address  = $nft->contract->address;
			$nft_resource->owner_address     = $owner_address;
			$nft_resource->token_id          = $token_id;
			$nft_resource->token_type        = $nft->id->tokenMetadata->tokenType;
			$nft_resource->balance           = $nft->balance;
			$nft_resource->title             = $nft->title;
			$nft_resource->description       = $nft->description;
			$nft_resource->token_uri_raw     = $nft->tokenUri->raw;
			$nft_resource->token_uri_gateway = $nft->tokenUri->gateway;
			$nft_resource->media             = json_encode( $nft->media );
			$nft_resource->properties        = json_encode( $nft->metadata->attributes );
			$nft_resource->last_updated      = date_create( $nft->timeLastUpdated );
			$nft_resource->contract_metadata = json_encode( $nft->contractMetadata );
			$nft_resource->user_id           = $user_id;
			$nft_resource->save();

			$nfts[] = $nft_resource;

		}

		/*
		 * Check if there is pagination, and if so, remake request
		 */
		if ( isset( $body->pageKey ) && count( $nfts ) < $body->totalCount ) {
			$args['pageKey'] = $body->pageKey;
			$nfts            = nft_memberships_alchemy_get_nfts_of_owner_address( $owner_address, $contract_addresses, $network, $args, $nfts, $user_id, $blog_id );
		}

		return $nfts;

	}

	return false;
}


