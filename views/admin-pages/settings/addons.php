<?php
/**
 * Plugin Settings Page
 *
 * Addons should hook to this page to render the settings only when the main plugin is network active and the addon
 * is active on a subsite only
 */

$addon_tabs = nft_memberships_get_addon_setting_tabs();
$active_tab = $_GET['tab'] ?? $addon_tabs[0];

?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
	</h1>

</div>

<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<?php do_action( 'nft_memberships_addon_settings_tabs', $active_tab ); ?>
	</h2>

	<?php

	do_action( 'nft_memberships_addon_settings_tabs_content', $active_tab );

	?>


</div> <!-- .wrap -->