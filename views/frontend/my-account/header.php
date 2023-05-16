<?php
/**
 * My Account Header Template
 */

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

// Current User
$current_user = wp_get_current_user();

// User Memberships
$user_memberships = nft_memberships_get_memberships_for_user();

?>

<!-- My Account Header -->
<div class="row mt-2 py-5 d-flex align-items-center">

    <!-- Avatar -->
    <div class="col-4 text-center">
        <img src="
        <?php echo get_avatar_url( $current_user->ID, array('size' => '200') ) ? esc_url( get_avatar_url( $current_user->ID, array('size' => '200') ) ) : nft_memberships_get_asset( 'avatar.png' ); ?>
        " class="border rounded-circle shadow-4"
             alt="Avatar" style="background-color: #fFfFfF" />
    </div>

    <!-- Profile Details -->
    <div class="col-8">
        <?php do_action( 'nft_memberships_header_before_salutation' ); ?>

        <!-- Salutations & Registration Date-->
        <p> <span class="fw-bold">Hello</span>, <br/>
            You are logged in as <span class="fw-bold"><?php
                echo $current_user->display_name ?? $current_user->user_login ?></span>.
        <br>
            Member since <?php echo date( "M j, Y", strtotime( $current_user->user_registered ) ); ?>
        </p>
        <!-- End Salutations & Registration Date-->
	    <?php do_action( 'nft_memberships_header_after_salutation' ); ?>

	    <?php do_action( 'nft_memberships_header_before_memberships' ); ?>
        <!-- User Memberships -->
		<?php if( $user_memberships && is_array( $user_memberships ) ): ?>

            <div class="container px-0 mr-1 small">

				<?php foreach ( $user_memberships as $user_membership ): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm m-1">
						<?php echo $user_membership->post_title; ?>
                    </button>

				<?php endforeach; ?>
            </div>

		<?php endif; ?>
        <!-- End User Memberships -->
	    <?php do_action( 'nft_memberships_header_after_memberships' ); ?>

    </div>

</div>

<!-- Nav Bar-->
<div class="row border-top">
    <div class="col-12">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid pl-lg-0">
                <span class="navbar-brand mb-0 h2"><?php echo apply_filters('nft_memberships_dashboard_navbar_menu_title', 'Menu'); ?></span>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<?php do_action('nft_memberships_dashboard_navbar_tabs', $active_tab, $content_settings); ?>
                    </ul>
					<?php do_action( 'nft_memberships_dashboard_navbar_buttons', $active_tab, $content_settings ); ?>
                </div>
            </div>
        </nav>
    </div>
</div>

<hr/>
<!-- End Nav Bar-->
