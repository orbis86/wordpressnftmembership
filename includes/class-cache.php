<?php
/**
 * Handles all Caching Logic
 */

namespace NFT_Memberships;

use NFT_Memberships\Traits\Singleton;

class Cache {
	use Singleton;

	/**
	 * Cache group key
	 *
	 * @var string
	 */
	public $cache_group_key = 'nft_memberships';

	/**
	 * Class initializer
	 */
	public function init() {
		//$this->add_global_cache_group();
		$this->add_non_persistent_groups();
	}

	/**
	 * Add our cache group to non-persistent groups
	 */
	public function add_non_persistent_groups() {
		wp_cache_add_non_persistent_groups( $this->cache_group_key );
	}

	/**
	 * Add our cache group to global groups so that the cache can be accessed across all sites in a multisite
	 */
	public function add_global_cache_group() {
		wp_cache_add_global_groups( $this->cache_group_key );
	}

	/**
	 * Set Cache
	 *
	 * @param string $cache_key
	 * @param $cache_data
	 * @param int $blog_id
	 * @param int $expire_time
	 *
	 * @return bool
	 */
	public function set_cache( string $cache_key, $cache_data, int $blog_id, int $expire_time = 0 ) {
		if ( $blog_id != get_current_blog_id() && is_multisite() ) {
			switch_to_blog( $blog_id );
			$is_cache_set = wp_cache_set( $cache_key, $cache_data, $this->cache_group_key, $expire_time );
			restore_current_blog();
		} else {
			$is_cache_set = wp_cache_set( $cache_key, $cache_data, $this->cache_group_key, $expire_time );
		}

		return $is_cache_set;
	}

	/**
	 * Get Cache
	 *
	 * @param string $cache_key
	 * @param int $blog_id
	 *
	 * @return false|mixed
	 */
	public function get_cache( string $cache_key, int $blog_id ) {
		if ( $blog_id != get_current_blog_id() && is_multisite() ) {
			switch_to_blog( $blog_id );
			$cache_data = wp_cache_get( $cache_key, $this->cache_group_key );
			restore_current_blog();
		} else {
			$cache_data = wp_cache_get( $cache_key, $this->cache_group_key );
		}

		return $cache_data;
	}

	/**
	 * Delete Cache
	 *
	 * @param string $cache_key
	 * @param int $blog_id
	 *
	 * @return bool
	 */
	public function delete_cache( string $cache_key, int $blog_id ) {
		if ( $blog_id != get_current_blog_id() && is_multisite() ) {
			switch_to_blog( $blog_id );
			$is_deleted = wp_cache_delete( $cache_key, $this->cache_group_key );
			restore_current_blog();
		} else {
			$is_deleted = wp_cache_delete( $cache_key, $this->cache_group_key );
		}

		return $is_deleted;
	}

	/**
	 * Set transient
	 *
	 * @param string $transient_name The part that should appear after 'nft_memberships_'
	 * @param $transient_data
	 * @param int $expiry_time in seconds
	 * @param int $blog_id
	 * @param bool $is_network
	 *
	 * @return bool
	 */
	public function set_transient( string $transient_name, $transient_data, int $expiry_time, int $blog_id, bool $is_network ) {
		$transient_name = $this->cache_group_key . '_' . $transient_name;

		if ( $is_network && is_multisite() ) {
			$is_transient_set = set_site_transient( $transient_name, $transient_data, $expiry_time );
		} else {
			if ( $blog_id != get_current_blog_id() && is_multisite() ) {
				switch_to_blog( $blog_id );
				$is_transient_set = set_transient( $transient_name, $transient_data, $expiry_time );
				restore_current_blog();
			} else {
				$is_transient_set = set_transient( $transient_name, $transient_data, $expiry_time );
			}
		}

		return $is_transient_set;
	}

	/**
	 * Get transient
	 *
	 * @param string $transient_name The part that should appear after 'nft_memberships_'
	 * @param int $blog_id
	 * @param bool $is_network
	 *
	 * @return mixed
	 */
	public function get_transient( string $transient_name, int $blog_id, bool $is_network ) {
		$transient_name = $this->cache_group_key . '_' . $transient_name;

		if ( $is_network && is_multisite() ) {
			$transient_data = get_site_transient( $transient_name );
		} else {
			if ( $blog_id != get_current_blog_id() && is_multisite() ) {
				switch_to_blog( $blog_id );
				$transient_data = get_transient( $transient_name );
				restore_current_blog();
			} else {
				$transient_data = get_transient( $transient_name );
			}
		}

		return $transient_data;
	}

	/**
	 * Delete Transient
	 *
	 * @param string $transient_name
	 * @param int $blog_id
	 * @param bool $is_network
	 *
	 * @return bool
	 */
	public function delete_transient( string $transient_name, int $blog_id, bool $is_network ) {
		$transient_name = $this->cache_group_key . '_' . $transient_name;

		if ( $is_network && is_multisite() ) {
			$is_transient_deleted = delete_site_transient( $transient_name );
		} else {
			if ( $blog_id != get_current_blog_id() && is_multisite() ) {
				switch_to_blog( $blog_id );
				$is_transient_deleted = delete_transient( $transient_name );
				restore_current_blog();
			} else {
				$is_transient_deleted = delete_transient( $transient_name );
			}
		}

		return $is_transient_deleted;
	}

}