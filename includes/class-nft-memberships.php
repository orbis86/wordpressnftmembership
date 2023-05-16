<?php

namespace NFT_Memberships;

use NFT_Memberships\Admin_Pages\Memberships_List_Page;
use NFT_Memberships\Admin_Pages\Plugin_License_Page;
use NFT_Memberships\Admin_Pages\Plugin_Settings_Page;
use NFT_Memberships\Database\Database;
use NFT_Memberships\Frontend\Shortcodes;
use NFT_Memberships\Traits\Singleton;

class NFT_MEMBERSHIPS {
	use Singleton;

	/**
	 * Helper Class
	 */
	public $helper;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Class Initializer
	 */
	public function init() {

		if ( defined( 'NFT_MEMBERSHIPS_VERSION' ) ) {
			$this->version = NFT_MEMBERSHIPS_VERSION;
		} else {
			$this->version = '0.0.1';
		}
		$this->plugin_name = 'nft-memberships';

		/*
		 * Loads Cron class
		 */
		Cron::get_instance();

		/*
         * Loads the Helper class.
         */
		$this->helper = Helper::get_instance();

		/*
		 * Loads Database Class
		 */
		Database::get_instance();

		/*
		 * Load Scripts
		 */
		Scripts::get_instance();

		/*
		 * Load Shortcodes
		 */
		add_action('wp_head', array( $this, 'register_shortcodes' ), 9 );


		/*
		 * Helper Functions
		 */
		require_once $this->helper->path( 'includes/functions/helper.php' );

		/*
		 * Load dependencies need to run the plugin
		 */
		add_action( 'plugins_loaded', array( $this, 'load_dependencies' ) );

		/*
		 * Load Admin Pages
		 */
		add_action( 'wp_loaded', array( $this, 'load_admin_pages' ), 40, 1 );

		/*
		 * Register Custom Post Type
		 */
		add_action( 'init', array( $this, 'register_post_type' ) );

	}

	/**
	 * Register Shortcodes
	 */
	public function register_shortcodes(){
		Shortcodes::get_instance();
	}

	/**
	 * Register Custom Post Type
	 */
	public function register_post_type() {
		Post_Type::get_instance();
	}

	/**
	 * Load all components required by this plugin
	 */
	public function load_dependencies() {
		/*
		 * Load ACF Forms
		 */
		ACF::get_instance();

		/*
		 * Load Admin Notices
		 */
		Admin_Notices::get_instance();

	}

	/**
	 * Load admin pages
	 */
	public function load_admin_pages() {
		/*
		 * Memberships List Page
		 */
		Memberships_List_Page::get_instance();

		/*
         * License Fields
         */
		Plugin_License_Page::get_instance();

		/*
		 * Addon Settings Page
		 */
		Plugin_Settings_Page::get_instance();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}
}