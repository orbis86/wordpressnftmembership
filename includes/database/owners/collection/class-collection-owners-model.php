<?php
/**
 * Create Collection Owners Database Model
 */

namespace NFT_Memberships\Database\Owners\Collection;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Collection_Owners_Model extends Eloquent {
	use \NFT_Memberships\Traits\Singleton;

	/**
	 * Set the table name
	 */
	public $table;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'id',
		'contract_address',
		'owner_address',
		'token_balances',
		'created_at',
		'updated_at',
	);

	/**
	 * Class Constructor
	 *
	 * @param array $attributes
	 */
	public function __construct( array $attributes = [] ) {
		global $wpdb;

		$this->table = $wpdb->prefix . 'nft_memberships_collection_owners';

		parent::__construct( $attributes );

	}

	/**
	 * Class Initializer
	 */
	public function init() {
	}

}