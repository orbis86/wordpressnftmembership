<?php
/**
 * Handles database tables & schema
 *
 * @package NFT_Memberships
 * @subpackage Database
 * @since 0.0.1
 */

namespace NFT_Memberships\Database;

defined( 'ABSPATH' ) || exit;

use Illuminate\Database\Capsule\Manager as Capsule;
use NFT_Memberships\Database\NFTs\NFTs;
use NFT_Memberships\Database\Owners\Owners;
use NFT_Memberships\Traits\Singleton;

class Database {
	use Singleton;

	/**
	 * Class Initializer
	 */
	public function init() {
		// Connect to Database
		$this->connect_database();

		// NFTs Database Tables
		NFTs::get_instance();

		// Owners Database Tables
		Owners::get_instance();
	}

	/**
	 * Connect to WordPress Database
	 */
	private function connect_database() {
		$capsule = new Capsule;
		$capsule->addConnection( [
			"driver"   => "mysql",
			"host"     => DB_HOST,
			"database" => DB_NAME,
			"username" => DB_USER,
			"password" => DB_PASSWORD
		] );
		$capsule->setAsGlobal();
		$capsule->bootEloquent();
	}

}