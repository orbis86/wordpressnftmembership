<?php

/**
 * This class loads ACF
 *
 * @link       https://wpemailmanager.com
 * @since      1.0.0
 *
 * @package    NFT_Memberships
 * @subpackage NFT_Memberships/includes
 */

namespace NFT_Memberships;

use NFT_Memberships\Traits\Singleton;
use stdClass;

class ACF {
	use Singleton;

	/**
	 * Holds an instance of the helper functions layer.
	 *
	 * @since 1.0.0
	 * @var Helper
	 */
	public $helper;

	/**
	 * Determine if we are using our own ACF
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      bool $is_our_acf
	 */
	protected $is_our_acf;

	/**
	 * Load the class
	 */
	public function init() {
		/*
		* Loads the NFT_Memberships\Helper class.
		*/
		$this->helper = Helper::get_instance();

		$this->load_acf_pro();

		/**
		 * Register the forms
		 */
		add_action( 'wp_loaded', array( $this, 'plugin_general_settings_form' ) );
		add_action( 'wp_loaded', array( $this, 'plugin_content_settings_form' ) );
		add_action( 'wp_loaded', array( $this, 'plugin_membership_settings_form' ) );
		add_action( 'wp_loaded', array( $this, 'user_membership_settings_form' ) );

		// Filter the data saved when a user's membership is adjusted from the edit-user.php or profile.php admin page
		add_filter( 'acf/update_value/key=' . NFT_MEMBERSHIPS_USER_MEMBERSHIPS_MEMBERSHIPS_FIELD, array(
			$this,
			'filter_user_membership_settings_values'
		), 10, 4 );

		// Filter how the above values are displayed in the user memberships settings field
		add_filter( 'acf/prepare_field/key=' . NFT_MEMBERSHIPS_USER_MEMBERSHIPS_MEMBERSHIPS_FIELD, array(
			$this,
			'filter_user_membership_settings_field'
		), 10, 1 );

	} // end init;

	/**
	 * Load ACF PRO if not installed
	 *
	 * @since   1.0.0
	 */
	public function load_acf_pro() {
		// Makes sure the plugin is defined before trying to use it
		if ( ! class_exists( 'ACF' ) ) {
			// Define path and URL to the ACF plugin.
			define( 'NFT_MEMBERSHIPS_ACF_PATH', $this->helper->path( 'dependencies/acf/' ) );
			define( 'NFT_MEMBERSHIPS_ACF_URL', $this->helper->url( 'dependencies/acf/' ) );

			// Include the ACF plugin.
			require_once( NFT_MEMBERSHIPS_ACF_PATH . 'acf.php' );

			$this->is_our_acf = true;

			add_filter( 'acf/settings/url', array( $this, 'settings_url' ) );
			add_filter( 'acf/settings/show_admin', array( $this, 'show_admin' ) );
		} else {
			$this->is_our_acf = false;
		}
	}

	/**
	 * Get settings url used by bundled ACF Plugin
	 *
	 * @param $url
	 *
	 * @return string
	 */
	function settings_url( $url ) {
		if ( $this->is_our_acf ) {
			return NFT_MEMBERSHIPS_ACF_URL;
		}

		return $url;
	}

	/**
	 * Whether we should show bundled ACF's admin menu
	 *
	 * @param $show_admin
	 *
	 * @return bool
	 */
	function show_admin( $show_admin ) {
		if ( $this->is_our_acf ) {
			return apply_filters( 'nft_memberships_acf_show_admin', false );
		}

		return $show_admin;
	}

	/**
	 * Create 'General Plugin Settings' Form
	 */
	function plugin_general_settings_form() {

		/*
		 * Set Default API Key
		 */
		$default_api_key = '';
		$api_key         = nft_memberships_get_api_key();
		if ( $api_key ) {
			$default_api_key = $api_key;
		}

		/*
		 * Set Default Membership Plugin Integration
		 */
		$default_membership_plugin_integration = nft_memberships_get_membership_plugin_integration();

		if ( function_exists( 'acf_add_local_field_group' ) ):

			acf_add_local_field_group( array(
				'key'                   => NFT_MEMBERSHIPS_GENERAL_SETTINGS_GROUP,
				'title'                 => 'General Settings',
				'fields'                => array(
					array(
						'key'               => NFT_MEMBERSHIPS_GENERAL_SETTINGS_API_FIELD,
						'label'             => 'Alchemy API Key',
						'name'              => 'alchemy_api_key',
						'type'              => 'password',
						'instructions'      => 'The NFT Memberships plugin uses Alchemy to interact with NFTs. Learn how to create an API key <a href="https://docs.alchemy.com/docs/alchemy-quickstart-guide" target="_blank" rel="noopener">here</a>.',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'default_value'     => $default_api_key,
						'placeholder'       => '',
						'prepend'           => '',
						'append'            => '',
						'maxlength'         => '',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_GENERAL_SETTINGS_MEMBERSHIP_PLUGIN_INTEGRATION_FIELD,
						'label'             => 'Membership Plugin Integration',
						'name'              => 'membership_plugin_integration',
						'type'              => 'select',
						'instructions'      => 'Which WordPress membership plugin should NFT Memberships integrate with?',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => array(
							'wishlist_member' => 'Wishlist Member',
							//'ultimate_membership' => 'Ultimate Membership Pro',
						),
						'default_value'     => $default_membership_plugin_integration,
						'allow_null'        => 0,
						'multiple'          => 0,
						'ui'                => 1,
						'ajax'              => 0,
						'return_format'     => 'value',
						'placeholder'       => '',
					),
				),
				'location'              => array(),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => 0,
			) );

		endif;

		if ( function_exists( 'acf_register_form' ) ) {
			// Register form.
			acf_register_form( array(
				'id'                 => 'nft-memberships-plugin-settings-general',
				'post_id'            => 'options',
				'field_groups'       => array( NFT_MEMBERSHIPS_GENERAL_SETTINGS_GROUP ),
				'submit_value'       => __( "Save Settings", 'acf' ),
				'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
			) );
		}

	}

	/**
	 * Create 'Content Plugin Settings' Form
	 */
	function plugin_content_settings_form() {
		
		/*
		 * Default Values
		 */
		$overview_tab_name = 'Overview';
		$overview_tab_content = '';
		$overview_tab_order = 0;
		
		$subscriptions_tab_name = 'Subscriptions';
		$subscriptions_tab_content = '';
		$subscriptions_tab_order = 1;

		$wallet_tab_name = 'Wallet';
		$wallet_tab_content = '';
		$wallet_tab_order = 2;

		$services_tab_name = 'Services';
		$services_tab_content = '';
		$services_tab_order = 3;
		$services_tab_allowed_memberships = array();

		// Current Settings
		$current_settings = nft_memberships_get_settings();
		if( isset( $current_settings['content'] ) ){
			$content_settings = $current_settings['content'];
			
			// Overview Tab Settings
			if( isset( $content_settings['overview'] ) ){
				$overview_tab_name = $content_settings['overview']['name'];
				$overview_tab_content = $content_settings['overview']['content'];
				$overview_tab_order = $content_settings['overview']['order'];
			}
			
			// Subscriptions Tab Settings
			if( isset( $content_settings['subscriptions'] ) ){
				$subscriptions_tab_name = $content_settings['subscriptions']['name'];
				$subscriptions_tab_content = $content_settings['subscriptions']['content'];
				$subscriptions_tab_order = $content_settings['subscriptions']['order'];
			}

			// Wallet Tab Settings
			if( isset( $content_settings['wallet'] ) ){
				$wallet_tab_name = $content_settings['wallet']['name'];
				$wallet_tab_content = $content_settings['wallet']['content'];
				$wallet_tab_order = $content_settings['wallet']['order'];
			}

			// Services Tab Settings
			if( isset( $content_settings['services'] ) ){
				$services_tab_name = $content_settings['services']['name'];
				$services_tab_content = $content_settings['services']['content'];
				$services_tab_order = $content_settings['services']['order'];
				$services_tab_allowed_memberships = $content_settings['services']['allowed_memberships'];
			}
		}
		
		

		// Currently, chosen memberships
		$chosen_membership_default_values = array();
		if ( is_array( $services_tab_allowed_memberships ) && 0 < count( $services_tab_allowed_memberships ) ) {
			foreach ( $services_tab_allowed_memberships as $services_tab_allowed_membership ) {
				$chosen_membership_default_values[] = $services_tab_allowed_membership;
			}
		}

		// All memberships
		$all_memberships         = nft_memberships_get_memberships();
		$all_memberships_choices = array();

		if ( is_array( $all_memberships ) && 0 < count( $all_memberships ) ) {
			foreach ( $all_memberships as $membership ) {
				$all_memberships_choices[ $membership->ID ] = $membership->post_title;
			}
		}

		if( function_exists('acf_add_local_field_group') ):

			acf_add_local_field_group(array(
				'key' => NFT_MEMBERSHIPS_CONTENT_SETTINGS_GROUP,
				'title' => 'Content Tab Settings',
				'fields' => array(
					// Overview Tab
					array(
						'key' => NFT_MEMBERSHIPS_CONTENT_OVERVIEW_TAB_SETTINGS_FIELD_GROUP,
						'label' => 'Overview Tab',
						'name' => 'overview_tab',
						'aria-label' => '',
						'type' => 'group',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'layout' => 'block',
						'sub_fields' => array(
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_OVERVIEW_TAB_SETTINGS_NAME_SUBFIELD,
								'label' => 'Tab Name',
								'name' => 'tab_name',
								'aria-label' => '',
								'type' => 'text',
								'instructions' => "Enter the tab's title.",
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $overview_tab_name,
								'maxlength' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
							),
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_OVERVIEW_TAB_SETTINGS_CONTENT_SUBFIELD,
								'label' => 'Tab Content',
								'name' => 'tab_content',
								'aria-label' => '',
								'type' => 'wysiwyg',
								'instructions' => 'Enter the tab\'s content. ',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $overview_tab_content,
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
								'delay' => 0,
							),
						),
					),

					// Subscriptions Tab
					array(
						'key' => NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_FIELD_GROUP,
						'label' => 'Subscriptions Tab',
						'name' => 'subscriptions_tab',
						'aria-label' => '',
						'type' => 'group',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'layout' => 'block',
						'sub_fields' => array(
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_NAME_SUBFIELD,
								'label' => 'Tab Name',
								'name' => 'tab_name',
								'aria-label' => '',
								'type' => 'text',
								'instructions' => "Enter the tab's title.",
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $subscriptions_tab_name,
								'maxlength' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
							),
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_CONTENT_SUBFIELD,
								'label' => 'Tab Content',
								'name' => 'tab_content',
								'aria-label' => '',
								'type' => 'wysiwyg',
								'instructions' => 'Enter the tab\'s content. ',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $subscriptions_tab_content,
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
								'delay' => 0,
							),
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_SUBSCRIPTIONS_TAB_SETTINGS_ORDER_SUBFIELD,
								'label' => 'Tab Order',
								'name' => 'tab_order',
								'aria-label' => '',
								'type' => 'number',
								'instructions' => "Enter the tab's position. The overview tab always starts at 0.",
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $subscriptions_tab_order,
								'min' => '',
								'max' => '',
								'placeholder' => '',
								'step' => '',
								'prepend' => '',
								'append' => '',
							),
						),
					),

					// Wallet Tab
					array(
						'key' => NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_FIELD_GROUP,
						'label' => 'Wallet Tab',
						'name' => 'wallet_tab',
						'aria-label' => '',
						'type' => 'group',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'layout' => 'block',
						'sub_fields' => array(
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_NAME_SUBFIELD,
								'label' => 'Tab Name',
								'name' => 'tab_name',
								'aria-label' => '',
								'type' => 'text',
								'instructions' => "Enter the tab's title.",
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $wallet_tab_name,
								'maxlength' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
							),
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_CONTENT_SUBFIELD,
								'label' => 'Tab Content',
								'name' => 'tab_content',
								'aria-label' => '',
								'type' => 'wysiwyg',
								'instructions' => 'Enter the tab\'s content. ',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $wallet_tab_content,
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
								'delay' => 0,
							),
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_WALLET_TAB_SETTINGS_ORDER_SUBFIELD,
								'label' => 'Tab Order',
								'name' => 'tab_order',
								'aria-label' => '',
								'type' => 'number',
								'instructions' => "Enter the tab's position. The overview tab always starts at 0.",
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $wallet_tab_order,
								'min' => '',
								'max' => '',
								'placeholder' => '',
								'step' => '',
								'prepend' => '',
								'append' => '',
							),
						),
					),

					// Services Tab
					array(
						'key' => NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_FIELD_GROUP,
						'label' => 'Services Tab',
						'name' => 'services_tab',
						'aria-label' => '',
						'type' => 'group',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'layout' => 'block',
						'sub_fields' => array(
							// Name
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_NAME_SUBFIELD,
								'label' => 'Tab Name',
								'name' => 'tab_name',
								'aria-label' => '',
								'type' => 'text',
								'instructions' => "Enter the tab's title.",
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $services_tab_name,
								'maxlength' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
							),

							// Content
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_CONTENT_SUBFIELD,
								'label' => 'Tab Content',
								'name' => 'tab_content',
								'aria-label' => '',
								'type' => 'wysiwyg',
								'instructions' => 'Enter the tab\'s content. ',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $services_tab_content,
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
								'delay' => 0,
							),

							// Order
							array(
								'key' => NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_ORDER_SUBFIELD,
								'label' => 'Tab Order',
								'name' => 'tab_order',
								'aria-label' => '',
								'type' => 'number',
								'instructions' => "Enter the tab's position. The overview tab always starts at 0.",
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => $services_tab_order,
								'min' => '',
								'max' => '',
								'placeholder' => '',
								'step' => '',
								'prepend' => '',
								'append' => '',
							),

							// Allowed Memberships
							array(
								'key'               => NFT_MEMBERSHIPS_CONTENT_SERVICES_TAB_SETTINGS_ALLOWED_MEMBERSHIPS_SUBFIELD,
								'label'             => 'User Memberships',
								'name'              => 'nft_user_memberships',
								'aria-label'        => '',
								'type'              => 'select',
								'instructions'      => 'To which memberships should this tab be visible to? Leave it empty to disable the tab.',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'choices'           => $all_memberships_choices,
								'default_value'     => $services_tab_allowed_memberships,
								'return_format'     => 'value',
								'multiple'          => 1,
								'allow_null'        => 0,
								'ui'                => 1,
								'ajax'              => 0,
								'placeholder'       => '',
							),
						),
					),

				),
				'location' => array(),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
				'show_in_rest' => 0,
			));

		endif;

		if ( function_exists( 'acf_register_form' ) ) {
			// Register form.
			acf_register_form( array(
				'id'                 => 'nft-memberships-plugin-settings-content',
				'post_id'            => 'options',
				'field_groups'       => array( NFT_MEMBERSHIPS_CONTENT_SETTINGS_GROUP ),
				'submit_value'       => __( "Save Settings", 'acf' ),
				'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
			) );
		}

	}
	
	/**
	 * Create 'Memberships Plugin Settings' Form
	 */
	function plugin_membership_settings_form() {
		if ( function_exists( 'acf_add_local_field_group' ) ):

			acf_add_local_field_group( array(
				'key'                   => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_GROUP,
				'title'                 => 'Membership Settings',
				'fields'                => array(
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_COLLECTION_NAME_FIELD,
						'label'             => 'Collection Name',
						'name'              => 'collection_name',
						'type'              => 'text',
						'instructions'      => 'Enter the collection\'s name.',
						'required'          => 1,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'default_value'     => '',
						'placeholder'       => 'NFT Collection Name',
						'prepend'           => '',
						'append'            => '',
						'maxlength'         => '',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_PRIVATE_NOTE_FIELD,
						'label'             => 'Private Note',
						'name'              => 'private_note',
						'type'              => 'textarea',
						'instructions'      => 'Enter a private note that describes why this membership is being created. It will not be displayed elsewhere.',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'default_value'     => '',
						'placeholder'       => '',
						'maxlength'         => '',
						'rows'              => 4,
						'new_lines'         => '',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_MEMBERSHIP_DESCRIPTION_FIELD,
						'label'             => 'Membership Description',
						'name'              => 'membership_description',
						'aria-label'        => '',
						'type'              => 'wysiwyg',
						'instructions'      => 'Enter the plan\'s description and features.',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'default_value'     => '',
						'tabs'              => 'all',
						'toolbar'           => 'full',
						'media_upload'      => 1,
						'delay'             => 0,
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NETWORK_TYPE_FIELD,
						'label'             => 'Network Type',
						'name'              => 'network_type',
						'aria-label'        => '',
						'type'              => 'select',
						'instructions'      => '',
						'required'          => 1,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => array(
							'blockchain' => 'Blockchain',
							'hedera'     => 'Hedera Hashgraph',
						),
						'default_value'     => false,
						'return_format'     => '',
						'multiple'          => 0,
						'allow_null'        => 0,
						'ui'                => 1,
						'ajax'              => 0,
						'placeholder'       => ''
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_BLOCKCHAIN_TYPE_FIELD,
						'label'             => 'Blockchain Type',
						'name'              => 'blockchain_type',
						'type'              => 'select',
						'instructions'      => 'To which blockchain does this collection belong to?',
						'required'          => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NETWORK_TYPE_FIELD,
									'operator' => '==',
									'value'    => 'blockchain',
								),
							),
						),
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => nft_membership_get_networks( 'blockchain' ),
						'default_value'     => 'ethereum',
						'allow_null'        => 0,
						'multiple'          => 0,
						'ui'                => 1,
						'ajax'              => 0,
						'return_format'     => 'value',
						'placeholder'       => '',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_HEDERA_TYPE_FIELD,
						'label'             => 'Hedera Type',
						'name'              => 'hedera_type',
						'type'              => 'select',
						'instructions'      => 'To which Hedera network does this collection belong to?',
						'required'          => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NETWORK_TYPE_FIELD,
									'operator' => '==',
									'value'    => 'hedera',
								),
							),
						),
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => nft_membership_get_networks( 'hedera' ),
						'default_value'     => 'hedera_mainnet',
						'allow_null'        => 0,
						'multiple'          => 0,
						'ui'                => 1,
						'ajax'              => 0,
						'return_format'     => 'value',
						'placeholder'       => '',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_CONTRACT_ADDRESS_FIELD,
						'label'             => 'Contract Address',
						'name'              => 'contract_address',
						'type'              => 'text',
						'instructions'      => 'Collection\'s contract address.',
						'required'          => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NETWORK_TYPE_FIELD,
									'operator' => '==',
									'value'    => 'blockchain',
								),
							),
						),
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'default_value'     => '',
						'placeholder'       => '0x495....',
						'prepend'           => '',
						'append'            => '',
						'maxlength'         => '',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_MEMBERSHIP_CHECK_FIELD,
						'label'             => 'Membership Check',
						'name'              => 'membership_check',
						'type'              => 'true_false',
						'instructions'      => 'Select the level at which a user\'s membership is checked. 

If set to a \'Collection Level\', a user will be assigned to specified membership level provided that they own any specific NFT in that collection.	

If set to a \'NFT Level\', a user will be assigned to specified membership level provided that they own any of the specified NFT(s) in that collection.',
						'required'          => 0,
						'conditional_logic' => array(
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NETWORK_TYPE_FIELD,
									'operator' => '==',
									'value'    => 'blockchain',
								),
							),
						),
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'message'           => '',
						'default_value'     => 0,
						'ui'                => 1,
						'ui_on_text'        => 'NFT Level',
						'ui_off_text'       => 'Collection Level',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_COLLECTION_NFTS_FIELD,
						'label'             => 'Collection\'s NFTs',
						'name'              => 'collection_nfts',
						'type'              => 'textarea',
						'instructions'      => 'Type the NFT token ids, separated by a comma, that will be used to check a user\'s membership status.',
						'required'          => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_MEMBERSHIP_CHECK_FIELD,
									'operator' => '==',
									'value'    => '1',
								),
							),
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NETWORK_TYPE_FIELD,
									'operator' => '==',
									'value'    => 'hedera',
								),
							),
						),
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => array(),
						'default_value'     => array(),
						'allow_null'        => 0,
						'multiple'          => 1,
						'ui'                => 1,
						'ajax'              => 0,
						'return_format'     => 'value',
						'placeholder'       => '',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NFT_CHECK_COMPARISON_FIELD,
						'label'             => 'NFT Check Comparison',
						'name'              => 'nft_check_comparison',
						'type'              => 'true_false',
						'instructions'      => 'Which criteria should be used to check the NFT(s)? When set to "Or" comparison, a user has to own any of the NFT(s) specified above and when to "And" comparison, a user has to own all of the NFT(s) specified above.',
						'required'          => 0,
						'conditional_logic' => array(
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_MEMBERSHIP_CHECK_FIELD,
									'operator' => '==',
									'value'    => '1',
								),
							),
							array(
								array(
									'field'    => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_NETWORK_TYPE_FIELD,
									'operator' => '==',
									'value'    => 'hedera',
								),
							),
						),
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'message'           => '',
						'default_value'     => 0,
						'ui'                => 1,
						'ui_on_text'        => '\'And\' Comparison',
						'ui_off_text'       => '\'Or\' Comparison',
					),
					array(
						'key'               => NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_MEMBERSHIP_FIELD,
						'label'             => 'Membership',
						'name'              => 'membership',
						'type'              => 'select',
						'instructions'      => 'To which membership level created using ' . nft_memberships_get_membership_plugin_integration_name() . ' should a user be assigned to?',
						'required'          => 1,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => nft_memberships_get_membership_plugin_levels(),
						'default_value'     => array(),
						'allow_null'        => 0,
						'multiple'          => 1,
						'ui'                => 1,
						'ajax'              => 0,
						'return_format'     => 'value',
						'placeholder'       => '',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'nft-memberships',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => 0,
			) );

		endif;

		if ( function_exists( 'acf_register_form' ) ) {
			// Register form.
			acf_register_form( array(
				'id'                 => 'nft-memberships-plugin-settings-memberships',
				'post_id'            => 'options',
				'field_groups'       => array( NFT_MEMBERSHIPS_MEMBERSHIPS_SETTINGS_GROUP ),
				'submit_value'       => __( "Save Settings", 'acf' ),
				'html_submit_button' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
			) );
		}
	}

	/**
	 * Create 'User Memberships' form
	 *
	 * It is shown in the user-edit.php page and is used to manage a user's memberships
	 *
	 */
	function user_membership_settings_form() {

		global $pagenow;

		// apply only to user profile or user edit pages
		if ( $pagenow !== 'profile.php' && $pagenow !== 'user-edit.php' || ! current_user_can( 'edit_users' ) ) {
			return;
		}

		// User ID
		$user_id = get_current_user_id();
		if ( 'user-edit.php' == $pagenow && isset( $_GET['user_id'] ) ) {
			$user_id = $_GET['user_id'];
		}

		// User's memberships
		$user_memberships               = nft_memberships_get_memberships_for_user( $user_id );
		$user_membership_default_values = array();

		if ( is_array( $user_memberships ) && 0 < count( $user_memberships ) ) {
			foreach ( $user_memberships as $user_membership_key => $user_membership_value ) {
				$user_membership_default_values[] = $user_membership_value->ID;
			}
		}

		// All memberships
		$all_memberships         = nft_memberships_get_memberships();
		$all_memberships_choices = array();

		if ( is_array( $all_memberships ) && 0 < count( $all_memberships ) ) {
			foreach ( $all_memberships as $membership ) {
				$all_memberships_choices[ $membership->ID ] = $membership->post_title;
			}
		}

		/*
		 * User wallet address and account id
		 */
		$wallet_address = get_user_meta( $user_id, 'nft_memberships_owner_address', true );
		if( empty( $wallet_address ) ){
			$wallet_address = '';
		}

		$account_id = get_user_meta( $user_id, 'nft_memberships_account_id', true );
		if( empty( $account_id ) ){
			$account_id = '';
		}

		if ( function_exists( 'acf_add_local_field_group' ) ):

			acf_add_local_field_group( array(
				'key'                   => NFT_MEMBERSHIPS_USER_MEMBERSHIPS_SETTINGS_GROUP,
				'title'                 => 'NFT Memberships',
				'fields'                => array(
					array(
						'key'               => NFT_MEMBERSHIPS_USER_MEMBERSHIPS_MEMBERSHIPS_FIELD,
						'label'             => 'User Memberships',
						'name'              => 'nft_user_memberships',
						'aria-label'        => '',
						'type'              => 'select',
						'instructions'      => 'Which memberships should the user have access to?',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'choices'           => $all_memberships_choices,
						'default_value'     => $user_membership_default_values,
						'return_format'     => 'value',
						'multiple'          => 1,
						'allow_null'        => 0,
						'ui'                => 1,
						'ajax'              => 0,
						'placeholder'       => '',
					),
					array(
						'key' => NFT_MEMBERSHIPS_USER_MEMBERSHIPS_WALLET_ADDRESS_FIELD,
						'label' => 'Wallet Address',
						'name' => 'nft_memberships_owner_address',
						'aria-label' => '',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => $wallet_address,
						'maxlength' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
					),
					array(
						'key' => NFT_MEMBERSHIPS_USER_MEMBERSHIPS_ACCOUNT_ID_FIELD,
						'label' => 'Hedera Account ID',
						'name' => 'nft_memberships_account_id',
						'aria-label' => '',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => $account_id,
						'maxlength' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'user_form',
							'operator' => '==',
							'value'    => 'edit',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => 0,
			) );

		endif;

	}

	/**
	 * Filter the values saved when a user's membership data is edited
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 * @param $original
	 *
	 * @return array|mixed
	 */
	public function filter_user_membership_settings_values( $value, $post_id, $field, $original ) {
		global $pagenow;

		// apply only to user profile or user edit pages
		if ( $pagenow !== 'profile.php' && $pagenow !== 'user-edit.php' || ! current_user_can( 'edit_users' ) ) {
			return $value;
		}

		// User ID
		$user_id = get_current_user_id();
		if ( 'user-edit.php' == $pagenow && isset( $_GET['user_id'] ) ) {
			$user_id = $_GET['user_id'];
		}

		if ( $post_id != 'user_' . $user_id ) {
			return $value;
		}

		/*
		 * Save the whole membership, not only the ID
		 */
		$new_memberships = array();

		/*
		 * This field always returns an array
		 */
		if ( is_array( $value ) && 0 < count( $value ) ) {
			foreach ( $value as $membership_id ) {
				$membership = nft_memberships_get_memberships_by( 'ID', $membership_id, true );
				if ( $membership ) {
					/*
					 * Membership Meta Data
					 */
					$membership_data             = new StdClass;
					$membership_data->id         = $membership_id;
					$membership_data->user_id    = $user_id;
					$membership_data->registered = strtotime( 'now' );

					$new_memberships[ $membership_id ] = $membership_data;
				}
			}

		}

		return $new_memberships;


	}

	/**
	 * Filter how the above values are displayed in the user memberships settings field
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function filter_user_membership_settings_field( $field ) {

		if ( isset( $field['value'] ) && is_array( $field['value'] ) ) {
			$membership_ids = array();

			foreach ( $field['value'] as $value ) {
				$membership_ids[] = $value->id;
			}

			$field['value'] = $membership_ids;
		}

		return $field;

	}

}
