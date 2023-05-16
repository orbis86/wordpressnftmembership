<?php
/**
 * NFT Memberships User Accounts License Fields
 *
 * @package NFT_MEMBERSHIPS
 * @subpackage Admin_Pages
 * @since 0.0.1
 */

namespace NFT_Memberships\Admin_Pages;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use NFT_Memberships\Traits\Singleton;

class Plugin_License_Page {
	use Singleton;

	public function init() {
		// Add license fields
		add_action( 'nft_memberships_license_fields', array( $this, 'add_license_fields' ), 40, 1 );

		// License Activation Processes
		if (
			isset( $_POST['_acf_form'] ) &&
			'wp-email-manager-ua-plugin-activate-license' == $_POST['_acf_form'] &&
			nft_memberships_user_permitted()
		) {
			add_action( 'acf/validate_save_post', array( $this, 'validate_activate_license' ) );
			add_action( 'acf/pre_save_post', array( $this, 'activate_license' ), 5 );
		}

		// License Deactivation Processes
		if (
			isset( $_POST['_acf_form'] ) &&
			'wp-email-manager-ua-plugin-deactivate-license' == $_POST['_acf_form'] &&
			nft_memberships_user_permitted()
		) {
			add_action( 'acf/validate_save_post', array( $this, 'validate_deactivate_license' ) );
			add_action( 'acf/pre_save_post', array( $this, 'deactivate_license' ), 5 );
		}

	}

	/**
	 * Add License Fields
	 */
	public function add_license_fields() {
		nft_memberships_get_template( 'plugin-license', array(), 'views/admin-pages' );
	}

	/**
	 * Validate activate plugin license
	 */
	public function validate_activate_license() {
		$license = $_POST['acf']['field_610f49de8mk46'];

		//Activate
		$api_params = array(
			'edd_action'  => 'activate_license',
			'license'     => $license,
			'item_id'     => NFT_MEMBERSHIPS_UPDATER_PRODUCT_ID,
			'url'         => get_site_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post( NFT_MEMBERSHIPS_UPDATER_URL, [
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		] );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				acf_add_validation_error( 'acf[field_610f49de8mk46]', $response->get_error_message() );
			} else {
				acf_add_validation_error( 'acf[field_610f49de8mk46]', 'An error occurred, please try again.' );
			}
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {
				switch ( $license_data->error ) {
					case 'expired':
						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						acf_add_validation_error( 'acf[field_610f49de8mk46]', $message );
						break;

					case 'disabled':
					case 'revoked':
						$message = __( 'Your license key has been disabled.' );
						acf_add_validation_error( 'acf[field_610f49de8mk46]', $message );
						break;

					case 'missing':
						$message = __( 'Invalid license.' );
						acf_add_validation_error( 'acf[field_610f49de8mk46]', $message );
						break;

					case 'invalid':
					case 'site_inactive':
						$message = __( 'Your license is not active for this URL.' );
						acf_add_validation_error( 'acf[field_610f49de8mk46]', $message );
						break;

					case 'item_name_mismatch':
						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), NFT_MEMBERSHIPS_PLUGIN_NAME );
						acf_add_validation_error( 'acf[field_610f49de8mk46]', $message );
						break;

					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.' );
						acf_add_validation_error( 'acf[field_610f49de8mk46]', $message );
						break;

					default:
						$message = __( 'An error occurred, please try again.' );
						acf_add_validation_error( 'acf[field_610f49de8mk46]', $message );
						break;
				}
			}
		}

		// $license_data->license will be either "valid" or "invalid"
		$license_network_option = array(
			'key'    => $license,
			'status' => ( $license_data->license == 'valid' ? 'Active' : ucfirst( $license_data->license ) )
		);

		update_network_option( get_current_network_id(), 'nft_memberships_license', $license_network_option );
	}

	/**
	 * Activate license
	 */
	public function activate_license() {
		// No errors so far
		if ( is_network_admin() ) {
			$admin_url = network_admin_url( 'admin.php?page=wp-email-manager/?action=settings' );
		} else {
			$admin_url = admin_url( 'admin.php?page=wp-email-manager/?action=settings' );
		}

		wp_redirect( $admin_url . '&tab=license&status=activate&error=false' );
		exit;
	}

	/**
	 * Validate deactivate plugin license
	 */
	public function validate_deactivate_license() {
		//$license = $_POST['acf']['field_610f49de83747'];
		$license = nft_memberships_get_license_key();

		//Activate
		$api_params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $license,
			'item_id'     => NFT_MEMBERSHIPS_UPDATER_PRODUCT_ID,
			'url'         => get_site_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post( NFT_MEMBERSHIPS_UPDATER_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				acf_add_validation_error( 'acf[field_610f49de83747]', $response->get_error_message() );
			} else {
				acf_add_validation_error( 'acf[field_610f49de83747]', 'An error occurred, please try again.' );
			}
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'failed' ) {
				//acf_add_validation_error('acf[field_610f49de83747]', 'An error occurred, please try again.');
			} else {
				delete_network_option( get_current_network_id(), 'nft_memberships_license' );
			}
		}
	}

	/**
	 * Deactivate license
	 */
	public function deactivate_license() {
		// No errors so far
		if ( is_network_admin() ) {
			$admin_url = network_admin_url( 'admin.php?page=wp-email-manager/?action=settings' );
		} else {
			$admin_url = admin_url( 'admin.php?page=wp-email-manager/?action=settings' );
		}

		wp_redirect( $admin_url . '&tab=license&status=deactivate&error=false' );
		exit;
	}
}