<?php
/**
 * Create Owner NFTs Database Model
 */

namespace NFT_Memberships\Database\NFTs\Owner;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Owner_NFTs_Model extends Eloquent {
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
		'token_id',
		'token_type',
		'balance',
		'title',
		'description',
		'token_uri_raw',
		'token_uri_gateway',
		'media',
		'properties',
		'last_updated',
		'contract_metadata',
		'user_id',
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

		$this->table = $wpdb->prefix . 'nft_memberships_owner_nfts';

		parent::__construct( $attributes );

	}

	/**
	 * Class Initializer
	 */
	public function init() {
	}

}