<?php
/**
 * NFT Memberships User Accounts activation and deactivation hooks
 *
 * Also allows running functions attached to specified hooks
 *
 * @package NFT_Memberships
 * @subpackage Hooks
 * @since 0.0.1
 */

namespace NFT_Memberships;

use NFT_Memberships\Traits\Singleton;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Hooks {

	use Singleton;

	/**
	 * Constructor.
	 */
	public function __construct() {
	} // end __construct;

	/**
	 * Register the activation and deactivation hooks
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function init() {

		/**
		 * Runs on NFT Memberships activation
		 */
		register_activation_hook( NFT_MEMBERSHIPS_PLUGIN_FILE, array( $this, 'on_activation' ) );

		/**
		 * Runs on NFT Memberships deactivation
		 */
		register_deactivation_hook( NFT_MEMBERSHIPS_PLUGIN_FILE, array( $this, 'on_deactivation' ) );

		/**
		 * Runs the activation hook.
		 */
		add_action( 'plugins_loaded', array( $this, 'on_activation_do' ), 1 );

		/**
		 * Plugin Updater
		 */
		//Checks plugin version and if it needs updating
		add_action( 'init', array( $this, 'plugin_updater' ) );

		//Run some actions after our plugin is updated
		add_action( 'upgrader_process_complete', array( $this, 'run_after_upgrade' ), 10, 2 );


		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}


	} // end init;

	/**
	 *  Runs when NFT Memberships is activated
	 *
	 * @since 0.0.1 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 0.0.1
	 */
	public static function on_activation() {
		/*
		 * Add cron scheduled events
		 */
		if ( ! wp_next_scheduled ( 'nft_memberships_cron_check_memberships' ) ) {
			wp_schedule_event( strtotime('00:00:00'), 'daily', 'nft_memberships_cron_check_memberships');
		}

	} // end on_activation;

	/**
	 * Runs whenever the activation flag is set.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public static function on_activation_do() {

		if ( get_network_option( null, 'nft_memberships_activation' ) === 'yes' ) {
			// Removes the flag
			delete_network_option( null, 'nft_memberships_activation' );

			/**
			 * Let other parts of the plugin attach their routines for activation
			 *
			 * @return void
			 * @since 0.0.1
			 */
			do_action( 'nft_memberships_activation' );
		} // end if;
	} // end on_activation_do;

	/**
	 * Runs when NFT Memberships is deactivated
	 *
	 * @since 0.0.1 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 0.0.1
	 */
	public static function on_deactivation() {
		/*
		 * Disable cron scheduled events
		 */
		$timestamp = wp_next_scheduled( 'nft_memberships_cron_check_memberships' );
		wp_unschedule_event( $timestamp, 'nft_memberships_cron_check_memberships' );


		/**
		 * Let other parts of the plugin attach their routines for deactivation
		 *
		 * @return void
		 * @since 0.0.1
		 */
		do_action( 'nft_memberships_deactivation' );
	} // end on_deactivation;

	/**
	 * Initialize the updater. Hooked into `init` to work with the
	 * wp_version_check cron job, which allows auto-updates.
	 */
	public function plugin_updater() {
		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		// retrieve our license key from the DB
		$license_key     = '';
		$license_details = get_network_option( get_current_network_id(), 'nft_memberships_license' );
		if ( false != $license_details ) {
			$license_key = $license_details['key'];
		}

		// setup the updater
		$updater = new Updater(
			NFT_MEMBERSHIPS_PLUGIN_URL,
			NFT_MEMBERSHIPS_PLUGIN_FILE,
			array(
				'version' => NFT_MEMBERSHIPS_VERSION,                    // current version number
				'license' => $license_key,             // license key (used get_option above to retrieve from DB)
				'item_id' => NFT_MEMBERSHIPS_UPDATER_PRODUCT_ID,       // ID of the product
				'author'  => NFT_MEMBERSHIPS_PLUGIN_NAME, // author of this plugin
				'beta'    => false,
			)
		);
	}

	/**
	 * Run some actions after our plugin is updated
	 */
	public function run_after_upgrade( $upgrader_object, $options ) {
		//If you successfully upgraded, then your license must be valid
		//At times, the license status changes to 'site_inactive' so this rectifies it
		$license = nft_memberships_get_license_key();
		if ( null == $license ) {
			return;
		}

		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == NFT_MEMBERSHIPS_PLUGIN_BASENAME ) {
					$license_option = get_network_option( get_current_network_id(), 'nft_memberships_license', 'false' );
					if ( 'false' != $license_option ) {
						$license_option['status'] = 'Active';
						update_network_option( get_current_network_id(), 'nft_memberships_license', $license_option );
					}
				}
			}
		}
	}

} // end class Hooks;
