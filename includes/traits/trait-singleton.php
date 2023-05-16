<?php
/**
 * Singleton Trait
 *
 * @package NFT_Memberships
 * @subpackage Singleton
 * @since 0.0.1
 */

namespace NFT_Memberships\Traits;

trait Singleton {

	/**
	 * Makes sure we are only using one instance of the class
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Returns the instance of NFT_Memberships
	 *
	 * @return object
	 */
	public static function get_instance() {

		if ( ! static::$instance instanceof static ) {
			static::$instance = new static();

			static::$instance->init();
		} // end if;

		return static::$instance;
	} // end get_instance;

	/**
	 * Runs only once, at the first instantiation of the Singleton.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function init() {
	} // end init;
} // end trait Singleton;
