<?php
/**
 * Handles Token Owners database tables & schema
 *
 * @package NFT_Memberships\Database\Owners;
 * @subpackage Token
 * @since 0.0.1
 */

namespace NFT_Memberships\Database\Owners\Token;

defined( 'ABSPATH' ) || exit;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use NFT_Memberships\Traits\Singleton;

class Token_Owners_Table {
	use Singleton;

	/**
	 * Class Initializer
	 */
	public function init() {
		// Create Facebook Pages Tables
		$this->create_token_owners_table();
	}

	/**
	 * Create Tables
	 */
	private function create_token_owners_table() {
		global $wpdb;

		if ( ! Capsule::schema()->hasTable( $wpdb->prefix . 'nft_memberships_token_owners' ) ) {
			Capsule::schema()->create( $wpdb->prefix . 'nft_memberships_token_owners', function ( Blueprint $table ) {
				$table->bigIncrements( 'id' );
				$table->char( 'contract_address', 200 );
				$table->char( 'token_id', 200 );
				$table->longText( 'owners_addresses', 200 );
				$table->timestamps();
			} );
		}

	}

	/**
	 * Delete Tables
	 */
	private function delete_facebook_tables() {
		global $wpdb;

		Capsule::schema()->dropIfExists( $wpdb->prefix . 'nft_memberships_token_owners' );

	}

}