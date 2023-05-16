<?php
/**
 * Overview - My Account Template
 */

$subscriptions_link = get_permalink() . '/?memberships-action=subscriptions';
$wallet_link = get_permalink() . '/?memberships-action=wallet';

?>

<!-- Overview Template -->
<?php do_action('nft_memberships_dashboard_overview_tab_before_content'); ?>
<div class="row">
	<div class="col-12">
        <?php print $overview_settings['content'] ?? ''; ?>
    </div>
</div>

<?php do_action('nft_memberships_dashboard_overview_tab_after_content'); ?>
