<?php
/**
 * Services - My Account Template
 */


?>

<!-- Overview Template -->
<?php do_action('nft_memberships_dashboard_services_tab_before_content'); ?>
<div class="row">
	<div class="col-12">
		<?php print $services_settings['content'] ?? ''; ?>

        <br/>
	</div>
</div>

<?php do_action('nft_memberships_dashboard_services_tab_after_content'); ?>
