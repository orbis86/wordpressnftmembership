<?php
/**
 * Handles Owner NFTs database tables & schema
 *
 * @package NFT_Memberships\Database\NFTs;
 * @subpackage Owner
 * @since 0.0.1
 */

namespace NFT_Memberships\Database\NFTs\Owner;

defined( 'ABSPATH' ) || exit;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use NFT_Memberships\Traits\Singleton;

class Owner_NFTs_Table {
	use Singleton;

	/**
	 * Class Initializer
	 */
	public function init() {
		// Create Facebook Pages Tables
		$this->create_owner_nfts_table();
	}

	/**
	 * Create Tables
	 */
	private function create_owner_nfts_table() {
		global $wpdb;

		if ( ! Capsule::schema()->hasTable( $wpdb->prefix . 'nft_memberships_owner_nfts' ) ) {
			Capsule::schema()->create( $wpdb->prefix . 'nft_memberships_owner_nfts', function ( Blueprint $table ) {
				$table->bigIncrements( 'id' );
				$table->char( 'contract_address', 200 );
				$table->char( 'owner_address', 200 );
				$table->char( 'token_id', 200 );
				$table->bigInteger( 'balance' )->default( 0 );
				$table->string( 'token_type' );
				$table->string( 'title' )->nullable();
				$table->longText( 'description' )->nullable();
				$table->longText( 'token_uri_raw' );
				$table->longText( 'token_uri_gateway', 200 );
				$table->longText( 'media' )->nullable();
				$table->longText( 'properties' )->nullable();
				$table->dateTime( 'last_updated' );
				$table->longText( 'contract_metadata' );
				$table->bigInteger( 'user_id' );
				$table->timestamps();
			} );
		}

	}

	/**
	 * Delete Tables
	 */
	private function delete_facebook_tables() {
		global $wpdb;

		Capsule::schema()->dropIfExists( $wpdb->prefix . 'nft_memberships_owner_nfts' );

	}

}