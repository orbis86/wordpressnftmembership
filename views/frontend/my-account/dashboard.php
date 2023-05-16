<?php
/**
 * Dashboard Shortcode Output
 */
if( ! is_user_logged_in() ){
	return;
}

/*
 * Content Settings
 */
$content_settings = array();
$settings = nft_memberships_get_settings();
if( isset( $settings['content'] ) ){
	$content_settings = $settings['content'];
}

/*
 * Active Navbar Tab
 */
$active_tab = $_GET['memberships-action'] ?? 'overview';

// Site Memberships
$site_memberships = nft_memberships_get_memberships();

// User Memberships
$memberships = nft_memberships_get_memberships_for_user();

$account_id = get_user_meta( get_current_user_id(), 'nft_memberships_account_id', true );
if( null == $account_id ){
    $account_id = 'account not connected';
}

$wallet = get_user_meta( get_current_user_id(), 'nft_memberships_owner_address', true );
if( null == $wallet ){
	$wallet = 'wallet not connected';
}

?>

<!-- My Account Header -->
<div class="container border border-2 my-5 nft-memberships-dashboard">

    <!-- Include Header -->
    <?php require_once __DIR__ . '/header.php'; ?>

    <!-- Include content depending on the tab -->
    <?php do_action( 'nft_memberships_dashboard_tab_content', $active_tab, $content_settings ); ?>

    <br/>

</div>

