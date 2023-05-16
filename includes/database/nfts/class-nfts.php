<?php
/**
 * Handles nfts database tables & schema
 *
 * @package NFT_Memberships\Database
 * @subpackage Platforms
 * @since 0.0.1
 */

namespace NFT_Memberships\Database\NFTs;

defined( 'ABSPATH' ) || exit;

use NFT_Memberships\Database\NFTs\Collection\Collection_NFTs_Table;
use NFT_Memberships\Database\NFTs\Owner\Owner_NFTs_Table;
use NFT_Memberships\Traits\Singleton;

class NFTs {
	use Singleton;

	/**
	 * Class Initializer
	 */
	public function init() {
		// Collection NFTs
		Collection_NFTs_Table::get_instance();

		// Owner NFTs
		Owner_NFTs_Table::get_instance();
	}

}
