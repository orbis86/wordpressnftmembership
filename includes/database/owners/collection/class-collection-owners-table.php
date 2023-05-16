<?php
/**
 * Handles Collection Owners database tables & schema
 *
 * @package NFT_Memberships\Database\Owners;
 * @subpackage Collection
 * @since 0.0.1
 */

namespace NFT_Memberships\Database\Owners\Collection;

defined( 'ABSPATH' ) || exit;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use NFT_Memberships\Traits\Singleton;

class Collection_Owners_Table {
	use Singleton;

	/**
	 * Class Initializer
	 */
	public function init() {
		// Create Facebook Pages Tables
		$this->create_collection_owners_table();
	}

	/**
	 * Create Tables
	 */
	private function create_collection_owners_table() {
		global $wpdb;

		if ( ! Capsule::schema()->hasTable( $wpdb->prefix . 'nft_memberships_collection_owners' ) ) {
			Capsule::schema()->create( $wpdb->prefix . 'nft_memberships_collection_owners', function ( Blueprint $table ) {
				$table->bigIncrements( 'id' );
				$table->char( 'contract_address', 200 );
				$table->char( 'owner_address', 200 );
				$table->longText( 'token_balances' );
				$table->timestamps();
			} );
		}

	}

	/**
	 * Delete Tables
	 */
	private function delete_facebook_tables() {
		global $wpdb;

		Capsule::schema()->dropIfExists( $wpdb->prefix . 'nft_memberships_collection_owners' );

	}

}