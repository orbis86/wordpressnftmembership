<?php
/**
 * NFT Memberships Shortcodes
 *
 * @package NFT_MEMBERSHIPS
 * @subpackage Admin_Pages
 * @since 0.0.1
 */

namespace NFT_Memberships\Frontend;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use NFT_Memberships\Traits\Singleton;

class Shortcodes {
	use Singleton;

	/**
     * Class Initializer
     *
	 * @return void
	 */
	public function init() {
        if( ! function_exists( 'nft_memberships_get_settings' ) ){
            require_once ABSPATH . 'wp-content/plugins/nft-memberships/includes/functions/helper.php';
        }

		// Dashboard Shortcode
		add_shortcode( 'nft_memberships_dashboard', array( $this, 'nft_memberships_dashboard_shortcode' ) );

		// Memberships List Shortcode
		add_shortcode( 'nft_memberships_list', array( $this, 'nft_memberships_list_shortcode' ) );

		/**
		 * Output template parts on dashboard template
		 */
		$overview_tab_order = 0;
		$subscriptions_tab_order = 1;
		$wallet_tab_order = 2;
		$services_tab_order = 3;

		// Current Settings
		$current_settings = nft_memberships_get_settings();
		if( isset( $current_settings['content'] ) ){
			$content_settings = $current_settings['content'];

			// Overview Tab Settings
			if( isset( $content_settings['overview'] ) ){
				$overview_tab_order = $content_settings['overview']['order'];
			}

			// Subscriptions Tab Settings
			if( isset( $content_settings['subscriptions'] ) ){
				$subscriptions_tab_order = $content_settings['subscriptions']['order'];
			}

			// Wallet Tab Settings
			if( isset( $content_settings['wallet'] ) ){
				$wallet_tab_order = $content_settings['wallet']['order'];
			}

			// Services Tab Settings
			if( isset( $content_settings['services'] ) ){
				$services_tab_order = $content_settings['services']['order'];
			}
		}


		/*
		 * Overview Tab and Content
		 */
		// Add Overview Tab
		add_action( 'nft_memberships_dashboard_navbar_tabs', array( $this, 'overview_tab' ), $overview_tab_order, 2 );

		// Add Overview Tab Content
		add_action( 'nft_memberships_dashboard_tab_content', array( $this, 'overview_tab_content' ), 10, 2 );

		/*
		 * Subscriptions Tab and Content
		 */
		// Add Subscriptions Tab
		add_action( 'nft_memberships_dashboard_navbar_tabs', array( $this, 'subscriptions_tab' ), $subscriptions_tab_order, 2 );

		// Add Subscriptions Tab Content
		add_action( 'nft_memberships_dashboard_tab_content', array( $this, 'subscriptions_tab_content' ), 10, 2 );

		/*
		 * Wallet Tab and Content
		 */
		// Add Wallet Tab
		add_action( 'nft_memberships_dashboard_navbar_tabs', array( $this, 'wallet_tab' ), $wallet_tab_order, 2 );

		// Add Wallet Tab Content
		add_action( 'nft_memberships_dashboard_tab_content', array( $this, 'wallet_tab_content' ), 10, 2 );
        
        /*
         * Services Tab and Content
         */
		if( isset( $current_settings['content'] ) ){
			$user_has_allowed_membership = false;

			$allowed_memberships = $current_settings['content']['services']['allowed_memberships'];

			if( isset( $allowed_memberships ) ){
				foreach ( $allowed_memberships as $allowed_membership ){
					if( nft_memberships_user_has_membership( absint( $allowed_membership ) ) ){
						$user_has_allowed_membership = true;

						break;
					}
				}
			}

			if( $user_has_allowed_membership ){
				// Add Services Tab
				add_action( 'nft_memberships_dashboard_navbar_tabs', array( $this, 'services_tab' ), $services_tab_order, 2 );

				// Add Services Tab Content
				add_action( 'nft_memberships_dashboard_tab_content', array( $this, 'services_tab_content' ), 10, 2 );
			}
		}


	}

	/**
	 * Dashboard Shortcode
	 *
	 * @return false|string|void
	 */
	public function nft_memberships_dashboard_shortcode() {
		if ( ! is_admin() ) {

			// Current URL
			$current_url = get_permalink();
			if ( isset( $_GET['memberships-action'] ) ) {
				$current_url = $current_url . '?memberships-action=' . $_GET['memberships-action'];
			}

			//Show ACF Form
			acf_form_head();

			wp_enqueue_style( 'nft-memberships-bootstrap' );
			wp_enqueue_style( 'nft-memberships-toastr' );

			wp_enqueue_script( 'nft-memberships-web3' );
			wp_enqueue_script( 'nft-memberships-web3-modal' );
			wp_enqueue_script( 'nft-memberships-bootstrap' );
			wp_enqueue_script( 'nft-memberships-toastr' );

			wp_enqueue_script( 'nft-memberships-web3-connection' );
			wp_enqueue_script( 'nft-memberships-wallet-connect' );
			wp_enqueue_script( 'nft-memberships-hashconnect-runtime' );
			wp_enqueue_script( 'nft-memberships-hashconnect-polyfills' );
			wp_enqueue_script( 'nft-memberships-hashconnect-main' );

			wp_localize_script( 'nft-memberships-web3-connection', 'web3_connection_js_object',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'add_owner_nonce'        => wp_create_nonce( 'add-owner-address' ),
					'add_account_nonce'      => wp_create_nonce( 'add-account-id' ),
					'subscribe_user_nonce'   => wp_create_nonce( 'subscribe-user' ),
					'unsubscribe_user_nonce' => wp_create_nonce( 'unsubscribe-user' ),
					'current_url'            => $current_url,
				) );

			/*
			 * External Scripts
			 */
			wp_enqueue_script( 'nft-memberships-web3-package', 'https://unpkg.com/web3@1.2.11/dist/web3.min.js', array(), '1.2.11', true );
			wp_enqueue_script( 'nft-memberships-web3-modal-package', 'https://unpkg.com/web3modal@1.9.0/dist/index.js', array(), '1.9.0', true );
			wp_enqueue_script( 'nft-memberships-evm-chains-package', 'https://unpkg.com/evm-chains@0.2.0/dist/umd/index.min.js', array(), '0.2.0', true );
			wp_enqueue_script( 'nft-memberships-web3-provider-package', 'https://unpkg.com/@walletconnect/web3-provider@1.2.1/dist/umd/index.min.js', array(), '1.2.1', true );
			wp_enqueue_script( 'nft-memberships-fortmatic-package', 'https://unpkg.com/fortmatic@2.0.6/dist/fortmatic.js', array(), '2.0.6', true );

			nft_memberships_get_template( 'dashboard', array(), 'views/frontend/my-account' );
		}
	}

	/**
	 * Memberships List Shortcode
	 *
	 * @return false|string|void
	 */
	public function nft_memberships_list_shortcode() {
		if ( ! is_admin() ) {

			// Current URL
			$current_url = get_permalink();
			if ( isset( $_GET['memberships-action'] ) ) {
				$current_url = $current_url . '?memberships-action=' . $_GET['memberships-action'];

			}

			//Show ACF Form
			acf_form_head();

			wp_enqueue_style( 'nft-memberships-bootstrap' );
			wp_enqueue_style( 'nft-memberships-toastr' );

			wp_enqueue_script( 'nft-memberships-web3' );
			wp_enqueue_script( 'nft-memberships-web3-modal' );
			wp_enqueue_script( 'nft-memberships-bootstrap' );
			wp_enqueue_script( 'nft-memberships-toastr' );

			wp_enqueue_script( 'nft-memberships-web3-connection' );
			wp_enqueue_script( 'nft-memberships-wallet-connect' );
			wp_enqueue_script( 'nft-memberships-hashconnect-runtime' );
			wp_enqueue_script( 'nft-memberships-hashconnect-polyfills' );
			wp_enqueue_script( 'nft-memberships-hashconnect-main' );

			wp_localize_script( 'nft-memberships-web3-connection', 'web3_connection_js_object',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'add_owner_nonce'        => wp_create_nonce( 'add-owner-address' ),
					'add_account_nonce'      => wp_create_nonce( 'add-account-id' ),
					'subscribe_user_nonce'   => wp_create_nonce( 'subscribe-user' ),
					'unsubscribe_user_nonce' => wp_create_nonce( 'unsubscribe-user' ),
					'current_url'            => $current_url,
				) );

			/*
			 * External Scripts
			 */
			wp_enqueue_script( 'nft-memberships-web3-package', 'https://unpkg.com/web3@1.2.11/dist/web3.min.js', array(), '1.2.11', true );
			wp_enqueue_script( 'nft-memberships-web3-modal-package', 'https://unpkg.com/web3modal@1.9.0/dist/index.js', array(), '1.9.0', true );
			wp_enqueue_script( 'nft-memberships-evm-chains-package', 'https://unpkg.com/evm-chains@0.2.0/dist/umd/index.min.js', array(), '0.2.0', true );
			wp_enqueue_script( 'nft-memberships-web3-provider-package', 'https://unpkg.com/@walletconnect/web3-provider@1.2.1/dist/umd/index.min.js', array(), '1.2.1', true );
			wp_enqueue_script( 'nft-memberships-fortmatic-package', 'https://unpkg.com/fortmatic@2.0.6/dist/fortmatic.js', array(), '2.0.6', true );


			nft_memberships_get_template( 'memberships', array(), 'views/frontend' );
		}
	}


	/**
	 * Output Overview Tab
	 *
	 * @param $active_tab
	 * @param $content_settings
	 */
	public function overview_tab( $active_tab, $content_settings ) {
		?>
        <li class="nav-item">
            <a class="nav-link <?php if ( $active_tab == 'overview' ) {
				echo 'active';
			} ?>" aria-current="page" href="<?php echo get_permalink(); ?>"><?php echo $content_settings['overview']['name']; ?></a>
        </li>
		<?php
	}

	/**
	 * Output Overview Tab Content
	 *
	 * @param $active_tab
	 * @param $content_settings
	 */
	public function overview_tab_content( $active_tab, $content_settings ) {
		if ( 'overview' == $active_tab ) {
            $overview_settings = $content_settings['overview'] ?? '';

			nft_memberships_get_template( 'overview', array( 'overview_settings' => $overview_settings ), 'views/frontend/my-account/' );
		}
	}

	/**
	 * Output Subscriptions Tab
	 *
	 * @param $active_tab
	 * @param $content_settings
	 */
	public function subscriptions_tab( $active_tab, $content_settings ) {
		?>
        <li class="nav-item">
            <a class="nav-link <?php if ( $active_tab == 'subscriptions' ) {
				echo 'active';
			} ?>" aria-current="page" href="<?php echo get_permalink() . '?memberships-action=subscriptions'; ?>"><?php echo $content_settings['subscriptions']['name']; ?></a>
        </li>
		<?php
	}

	/**
	 * Output Subscriptions Tab Content
	 *
	 * @param $active_tab
	 * @param $content_settings
	 */
	public function subscriptions_tab_content( $active_tab, $content_settings ) {
		if ( 'subscriptions' == $active_tab ) {
			$subscriptions_settings = $content_settings['subscriptions'] ?? '';

			nft_memberships_get_template( 'subscriptions', array( 'subscriptions_settings' => $subscriptions_settings ), 'views/frontend/my-account/' );
		}
	}

	/**
	 * Output Wallet Tab
	 *
	 * @param $active_tab
	 * @param $content_settings
	 */
	public function wallet_tab( $active_tab, $content_settings ) {
		?>
        <li class="nav-item">
            <a class="nav-link <?php if ( $active_tab == 'wallet' ) {
				echo 'active';
			} ?>" aria-current="page" href="<?php echo get_permalink() . '?memberships-action=wallet'; ?>"><?php echo $content_settings['wallet']['name']; ?></a>
        </li>
		<?php
	}

	/**
	 * Output Wallet Tab Content
	 *
	 * @param $active_tab
	 * @param $content_settings
	 */
	public function wallet_tab_content( $active_tab, $content_settings ) {
		if ( 'wallet' == $active_tab ) {
			$wallet_settings = $content_settings['wallet'] ?? array();

			nft_memberships_get_template( 'wallet', array( 'wallet_settings' => $wallet_settings ), 'views/frontend/my-account/' );
		}
	}

	/**
	 * Outputs Services Tab
	 *
	 * @param $active_tab
	 * @param $content_settings
	 */
	function services_tab( $active_tab, $content_settings ){
		?>
        <li class="nav-item">
            <a class="nav-link <?php if ( $active_tab == 'services' ) {
				echo 'active';
			} ?>" aria-current="page" href="<?php echo get_permalink() . '?memberships-action=services'; ?>"><?php echo $content_settings['services']['name']; ?></a>
        </li>
        <?php
	}

	/**
     * Outputs Services Tab Content
     *
	 * @param $active_tab
	 * @param $content_settings
	 */
	function services_tab_content( $active_tab, $content_settings ){
		if ( 'services' == $active_tab ) {
			$services_settings = $content_settings['services'] ?? array();

			nft_memberships_get_template( 'services', array( 'services_settings' => $services_settings ), 'views/frontend/my-account/' );
		}
	}

}