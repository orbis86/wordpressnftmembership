<?php
/**
 * Helper class to handle global registering of scripts and styles.
 *
 * @package NFT_Memberships
 * @subpackage Scripts
 * @since 1.0.0
 */

namespace NFT_Memberships;

// Exit if accessed directly
use NFT_Memberships\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * NFT Memberships helper class to handle global registering of scripts and styles.
 *
 * @since 1.0.0
 */
class Scripts {

	use Singleton;

	/**
	 * Runs when the instantiation first occurs.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {

		add_action( 'init', array( $this, 'register_default_scripts' ) );
		add_action( 'init', array( $this, 'register_default_styles' ) );

		add_action( 'wp_head', array( $this, 'enqueue_default_frontend_styles' ) );
		add_action( 'wp_head', array( $this, 'enqueue_default_frontend_scripts' ) );

		add_action( 'admin_init', array( $this, 'enqueue_default_admin_styles' ) );
		add_action( 'admin_init', array( $this, 'enqueue_default_admin_scripts' ) );
	} // end init;

	/**
	 * Registers the default NFT Memberships scripts.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register_default_scripts() {
		/*
		 * Add web3.js dependency
		 */
		$this->register_script(
			'nft-memberships-web3',
			nft_memberships_get_asset( 'web3.min.js', 'vendors/js' ),
			array(),
			true
		);

		/*
		 * Add Web3 Wallet Modal JS
		 */
		$this->register_script(
			'nft-memberships-web3-modal',
			nft_memberships_get_asset( 'web3modal.min.js', 'vendors/js' ),
			array( 'nft-memberships-web3' ),
			true
		);

		/*
		 * Bootstrap JS
		 */
		$this->register_script(
			'nft-memberships-bootstrap',
			nft_memberships_get_asset( 'bootstrap.bundle.min.js', 'vendors/js' ),
			array( 'jquery' ),
			true
		);


		/*
		 * Toastr JS
		 */
		$this->register_script(
			'nft-memberships-toastr',
			nft_memberships_get_asset( 'toastr.min.js', 'vendors/js' ),
			array( 'jquery' ),
			true
		);

		/*
		 * Add MetaMask JS
		 */
		$this->register_script(
			'nft-memberships-web3-connection',
			nft_memberships_get_asset( 'web3-connection.js', 'js' ),
			array( 'nft-memberships-web3', 'nft-memberships-toastr' ),
			true
		);

		/*
		 * Add Wallet Connect JS
		 */
		$this->register_script(
			'nft-memberships-wallet-connect',
			nft_memberships_get_asset( 'web3-modal.js', 'js' ),
			array( 'nft-memberships-web3' ),
			true
		);

		/*
		 * Add HashConnect - Runtime JS
		 */
		$this->register_script(
			'nft-memberships-hashconnect-runtime',
			nft_memberships_get_asset( 'runtime.js', 'js/hashconnect' ),
			array(),
			true
		);

		/*
		 * Add HashConnect - Polyfills JS
		 */
		$this->register_script(
			'nft-memberships-hashconnect-polyfills',
			nft_memberships_get_asset( 'polyfills.js', 'js/hashconnect' ),
			array(),
			true
		);

		/*
		 * Add HashConnect - Main JS
		 */
		$this->register_script(
			'nft-memberships-hashconnect-main',
			nft_memberships_get_asset( 'main.js', 'js/hashconnect' ),
			array(),
			true
		);


	} // end register_script;

	/**
	 * Wrapper for the register scripts function.
	 *
	 * @param string $handle The script handle. Used to enqueue the script.
	 * @param string $src URL to the file.
	 * @param array $deps List of dependency scripts.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function register_script( $handle, $src, $deps = array(), $in_footer = false ) {
		wp_register_script( $handle, $src, $deps, nft_memberships_get_version(), $in_footer );
	} // end register_style;

	/**
	 * Registers the default NFT Memberships styles.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function register_default_styles() {
		/*
		 * Boostrap CSS
		 */
		$this->register_style(
			'nft-memberships-bootstrap',
			nft_memberships_get_asset( 'bootstrap.min.css', 'vendors/css' ),
		);

		/*
		 * Toastr CSS
		 */
		$this->register_style(
			'nft-memberships-toastr',
			nft_memberships_get_asset( 'toastr.min.css', 'vendors/css' ),
		);

	} // end register_default_scripts;

	/**
	 * Wrapper for the register styles function.
	 *
	 * @param string $handle The script handle. Used to enqueue the script.
	 * @param string $src URL to the file.
	 * @param array $deps List of dependency scripts.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function register_style( $handle, $src, $deps = array() ) {
		wp_register_style( $handle, $src, $deps, nft_memberships_get_version() );
	} // end register_default_styles;

	/**
	 * Loads the default admin styles.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_default_admin_styles() {

	} // end enqueue_default_admin_styles;

	/**
	 * Loads the default frontend styles.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_default_frontend_styles() {

	}

	/**
	 * Loads the default frontend scripts.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_default_frontend_scripts() {

	} // end enqueue_default_admin_scripts;

	/**
	 * Loads the default admin scripts.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_default_admin_scripts() {

	}
} // end class Scripts;
