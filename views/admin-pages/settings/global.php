<?php
/**
 * Plugin Settings Page
 *
 * Addons should hook to this page to render the settings
 */

$active_tab          = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
	</h1>

</div>

<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<?php do_action( 'nft_memberships_settings_tabs', $active_tab ); ?>
	</h2>

	<?php

	do_action( 'nft_memberships_settings_tabs_content', $active_tab );

	?>


</div> <!-- .wrap -->

<!-- Show/Hide password field - License Tab-->
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        let show_password_icon = jQuery('.dashicons-visibility');

        show_password_icon.on('click', function (e) {
            let password_field = jQuery(this).closest('.acf-input').find("input");
            // toggle the type attribute
            const type = password_field.attr('type') === 'password' ? 'text' : 'password';
            let removeClass,
                addClass;

            password_field.attr('type', type);

            if (type == 'text') {
                addClass = 'dashicons-hidden';
                removeClass = 'dashicons-visibility';
            } else {
                addClass = 'dashicons-visibility';
                removeClass = 'dashicons-hidden';
            }

            // toggle the eye / eye slash icon
            show_password_icon.addClass(addClass).removeClass(removeClass);
        });
    });
</script>