<?php
/**
 * NFT Memberships Settings Page
 *
 * @package NFT_MEMBERSHIPS
 * @subpackage Admin_Pages
 * @since 0.0.1
 */

namespace NFT_Memberships\Admin_Pages;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use NFT_Memberships\Traits\Singleton;

class Plugin_Settings_Page {
	use Singleton;

	/**
	 * Repeater Row Filter Counter
	 *
	 * @link https://support.advancedcustomfields.com/forums/topic/get-row-index-inside-add_filter-acf-load_field/
	 *
	 * @var int
	 */
	private int $filter_row_index = 0;

	/**
	 * NFT Memberships
	 *
	 * @var array
	 */
	private array $memberships;

	/**
	 * Class initializer
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );

		/*
		 * Register tabs to settings page
		 */
        // General Tab
		add_action( 'nft_memberships_settings_tabs', array( $this, 'add_general_settings_tab' ), 10, 1 );
        // Content Tab
		add_action( 'nft_memberships_settings_tabs', array( $this, 'add_content_settings_tab' ), 10, 1 );

		/*
		 * Add content to registered tabs
		 */
        // General Tab
		add_action( 'nft_memberships_settings_tabs_content', array( $this, 'add_content_to_general_settings_tab' ) );
        // Content Tab
		add_action( 'nft_memberships_settings_tabs_content', array( $this, 'add_content_to_content_settings_tab' ) );

		/*
		 * Validate and Save General Settings
		 */
		if ( isset( $_POST['_acf_form'] ) && 'nft-memberships-plugin-settings-general' == $_POST['_acf_form'] ) {
			add_action( 'acf/validate_save_post', array( $this, 'validate_general_settings' ) );
			add_action( 'acf/pre_save_post', array( $this, 'save_general_settings' ), 5 );
		}

		/*
		 * Validate and Save Content Settings
		 */
		if ( isset( $_POST['_acf_form'] ) && 'nft-memberships-plugin-settings-content' == $_POST['_acf_form'] ) {
			add_action( 'acf/validate_save_post', array( $this, 'validate_content_settings' ) );
			add_action( 'acf/pre_save_post', array( $this, 'save_content_settings' ), 5 );
		}

	}

	/**
	 * Create subsite menu page
	 */
	public function register_page() {
		$hook = add_submenu_page(
			'edit.php?post_type=nft-memberships',
			'Settings - NFT Memberships',
			'Settings',
			'manage_options',
			'nft-memberships-settings',
			array( $this, 'render_page' ),
			null,
		);

		// Enqueue the scripts needed on this page
		add_action( "admin_print_scripts-$hook", array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Display the email accounts and forms
	 */
	public function render_page() {

		nft_memberships_get_template(
			'admin-pages/settings/global',
		);

	}

	/**
	 * Enqueue the required scripts and styles on this page
	 */
	public function enqueue_scripts() {
		//Show ACF Form
		acf_form_head();
	}

	/**
	 * Register General Settings Tab in Settings Page
	 */
	public function add_general_settings_tab( $active_tab ) {
		?>
        <a href="?post_type=nft-memberships&page=nft-memberships-settings&tab=general"
           class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
		<?php

	}

	/**
	 * Add Content to General Settings Tab in Settings Page
	 */
	public function add_content_to_general_settings_tab( $active_tab ) {
		if ( 'general' == $active_tab ) {
			nft_memberships_get_template(
				'admin-pages/settings/general',
			);
		}
	}

	/**
	 * Register Content Settings Tab in Settings Page
	 */
	public function add_content_settings_tab( $active_tab ) {
		?>
        <a href="?post_type=nft-memberships&page=nft-memberships-settings&tab=content"
           class="nav-tab <?php echo $active_tab == 'content' ? 'nav-tab-active' : ''; ?>">Tabs</a>
		<?php

	}

	/**
	 * Add Content to Content Settings Tab in Settings Page
	 */
	public function add_content_to_content_settings_tab( $active_tab ) {
		if ( 'content' == $active_tab ) {
			nft_memberships_get_template(
				'admin-pages/settings/content',
			);
		}
	}

	/**
	 * Validate General Settings
	 */
	public function validate_general_settings() {
	}

	/**
	 * Save General Settings
	 */
	public function save_general_settings() {
		$alchemy_api_key               = $_POST['acf'][ NFT_MEMBERSHIPS_GENERAL_SETTINGS_API_FIELD ] ?? '';
		$membership_plugin_integration = $_POST['acf'][ NFT_MEMBERSHIPS_GENERAL_SETTINGS_MEMBERSHIP_PLUGIN_INTEGRATION_FIELD ];

		$plugin_settings = nft_memberships_get_settings();
		if ( $plugin_settings ) {
			$plugin_settings['general']['alchemy_api_key'] = $alchemy_api_key;
			$plugin_settings['general']['integrations']    = array(
				'membership_plugin' => $membership_plugin_integration
			);

		} else {
			$plugin_settings = array(
				'general' => array(
					'alchemy_api_key' => $alchemy_api_key,
					'integrations'    => array(
						'membership_plugin' => $membership_plugin_integration
					)
				)
			);
		}

		if ( is_plugin_active_for_network( NFT_MEMBERSHIPS_PLUGIN_BASENAME ) ) {
			update_network_option( get_current_network_id(), 'nft_memberships_settings', $plugin_settings );
		} else {
			update_option( 'nft_memberships_settings', $plugin_settings );
		}

		if ( is_network_admin() ) {
			$admin_url = network_admin_url( 'edit.php?post_type=nft-memberships&page=nft-memberships-settings' );
		} else {
			$admin_url = admin_url( 'edit.php?post_type=nft-memberships&page=nft-memberships-settings' );
		}

		//No error so far
		wp_redirect( $admin_url . '&tab=general&error=false' );
		exit;
	}

	/**
	 * Validate Content Settings
	 */
	public function validate_content_settings() {
	}

	/**
	 * Save Content Settings
	 */
	public function save_content_settings() {
        $overview_group_settings = array(
            'name' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_OVERVIEW_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_OVERVIEW_TAB_SETTINGS_NAME_SUBFIELD ],
            'content' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_OVERVIEW_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_OVERVIEW_TAB_SETTINGS_CONTENT_SUBFIELD ],
            'order' => 0
        );

		$subscriptions_group_settings = array(
			'name' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_NAME_SUBFIELD ],
			'content' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_CONTENT_SUBFIELD ],
			'order' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_ORDER_SUBFIELD ],
		);

		$wallet_group_settings = array(
			'name' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_NAME_SUBFIELD ],
			'content' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_CONTENT_SUBFIELD ],
			'order' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_ORDER_SUBFIELD ],
		);

		$services_group_settings = array(
			'name' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_NAME_SUBFIELD ],
			'content' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_CONTENT_SUBFIELD ],
			'order' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_ORDER_SUBFIELD ],
			'allowed_memberships' => $_POST['acf'][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_FIELD_GROUP ][ NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_ALLOWED_MEMBERSHIPS_SUBFIELD ],

		);

		$plugin_settings = nft_memberships_get_settings();
		if ( $plugin_settings ) {
			$plugin_settings['content']['overview'] = $overview_group_settings;
			$plugin_settings['content']['subscriptions'] = $subscriptions_group_settings;
			$plugin_settings['content']['wallet'] = $wallet_group_settings;
			$plugin_settings['content']['services'] = $services_group_settings;

		} else {
			$plugin_settings = array(
				'content' => array(
					'overview' => $overview_group_settings,
					'subscriptions' => $subscriptions_group_settings,
                    'wallet' => $wallet_group_settings,
                    'services' => $services_group_settings
				)
			);
		}

		if ( is_plugin_active_for_network( NFT_MEMBERSHIPS_PLUGIN_BASENAME ) ) {
			update_network_option( get_current_network_id(), 'nft_memberships_settings', $plugin_settings );
		} else {
			update_option( 'nft_memberships_settings', $plugin_settings );
		}

		if ( is_network_admin() ) {
			$admin_url = network_admin_url( 'edit.php?post_type=nft-memberships&page=nft-memberships-settings' );
		} else {
			$admin_url = admin_url( 'edit.php?post_type=nft-memberships&page=nft-memberships-settings' );
		}

		//No error so far
		wp_redirect( $admin_url . '&tab=content&error=false' );
		exit;
	}
}


