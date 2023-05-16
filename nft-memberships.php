<?php
/**
 * Plugin Name:       NFT Memberships
 * Plugin URI:        https://orbis86.com
 * Description:       Assign memberships to users with specific NFTs in their wallets. Integrates with WishList Member and Divi.
 * Version:           0.0.1
 * Author:            Orbis86
 * Author URI:        https://orbis86.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nft-memberships
 * Domain Path:       /languages
 * Network:           false
 * Requires WP:       5.4
 * Requires at least: 5.4
 * Requires PHP:       7.4
 * Tested up to:       6.2
 */

// If this file is called directly, abort.
use NFT_Memberships\NFT_Memberships;

if (! defined('WPINC')) {
	exit;
}

if (!defined('NFT_MEMBERSHIPS_PLUGIN_FILE')) {
	define('NFT_MEMBERSHIPS_PLUGIN_FILE', __FILE__);
} // end if;


/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('NFT_MEMBERSHIPS_VERSION', '0.0.1');

/**
 * Require core file dependencies
 */
require_once __DIR__ . '/constants.php';

require_once __DIR__ . '/includes/class-autoloader.php';

require_once __DIR__ . '/dependencies/autoload.php';

require_once __DIR__ . '/includes/traits/trait-singleton.php';

//Add custom links under the plugin's row
add_filter('network_admin_plugin_action_links_'.plugin_basename(__FILE__), 'nft_memberships_plugin_links' );
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'nft_memberships_plugin_links' );
function nft_memberships_plugin_links( $links ) {
	// $links[] = '<a href="https://orbis86.com" target="_blank" rel="noopener">' . __('Documentation') . '</a>';
	return $links;
}


/**
 * Setup autoloader
 */
\NFT_Memberships\Autoloader::init();

/**
 * Setup activation/deactivation hooks
 */
\NFT_Memberships\Hooks::get_instance();

/**
 * Setup Filters
 */
\NFT_Memberships\Filters::get_instance();

/**
 * Setup Actions
 */
\NFT_Memberships\Actions::get_instance();

/**
 * Initializes the NFT Memberships class
 *
 * This function returns the NFT_Memberships class singleton, and
 * should be used to avoid declaring globals.
 *
 * @return object
 * @since 0.0.1
 */
function nft_memberships()
{
	return NFT_MEMBERSHIPS::get_instance();
} // end NFT_Memberships;

/* Initialize the plugin */
nft_memberships();