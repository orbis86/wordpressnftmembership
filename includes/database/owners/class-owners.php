<?php
/**
 * Handles collection and token database tables & schema
 *
 * @package NFT_Memberships\Database
 * @subpackage Platforms
 * @since 0.0.1
 */

namespace NFT_Memberships\Database\Owners;

defined( 'ABSPATH' ) || exit;

use NFT_Memberships\Database\Owners\Collection\Collection_Owners_Table;
use NFT_Memberships\Database\Owners\Token\Token_Owners_Table;
use NFT_Memberships\Traits\Singleton;

class Owners {
	use Singleton;

	/**
	 * Class Initializer
	 */
	public function init() {
		// Collection Owners
		Collection_Owners_Table::get_instance();

		// Token Owners
		Token_Owners_Table::get_instance();
	}

}
