<?php
/**
 * Create Token Owners Database Model
 */

namespace NFT_Memberships\Database\Owners\Token;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Token_Owners_Model extends Eloquent {
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
		'token_id',
		'owners_addresses',
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

		$this->table = $wpdb->prefix . 'nft_memberships_token_owners';

		parent::__construct( $attributes );

	}

	/**
	 * Class Initializer
	 */
	public function init() {
	}

}