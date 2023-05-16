<?php
/**
 * Subscriptions - My Account Template
 */

// User Memberships
$user_memberships = nft_memberships_get_memberships_for_user();

?>

<!-- Subscriptions Template -->
<?php do_action('nft_memberships_dashboard_subscriptions_tab_before_content'); ?>

<div class="row">
	<div class="col-12">
		<?php print $subscriptions_settings['content'] ?? ''; ?>
        <br/>


        <br/>
		<h4>Your Memberships</h4>

        <!-- User Memberships -->
		<?php if( $user_memberships && is_array( $user_memberships ) && 0 < count( $user_memberships ) ): ?>

        <table class="table mx-2">
            <thead>
            <tr>
                <th scope="col">Membership</th>
                <th scope="col">Network</th>
                <th scope="col">Registered On</th>
                <th scope="col">Actions</th>
            </tr>
            </thead>

            <tbody>
				<?php foreach ( $user_memberships as $user_membership ): ?>
                    <tr>
                        <th scope="row"><?php echo $user_membership->post_title; ?></th>
                        <td><?php echo ucfirst( nft_memberships_get_network_type( $user_membership ) ); ?></td>
                        <td><?php echo date( 'M j, Y', $user_membership->registered ); ?></td>
                        <td>
                            <span class="unsubscribe-nft-membership" data-membership-id="<?php echo $user_membership->ID ?>">
                                <button type="button" class="w-100 btn btn-lg btn-outline-danger text-center btn-sm">Unsubscribe</button>
                            </span>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </tbody>

        </table>

		<?php else: ?>
            <p>You are not subscribed to any memberships.</p>

		<?php endif; ?>
        <!-- End User Memberships -->
	</div>

</div>

<?php do_action('nft_memberships_dashboard_subscriptions_tab_after_content'); ?>

<div class="row">
    <div class="col-12">
        <hr/>
        <p><?php echo do_shortcode( '[nft_memberships_list]'); ?></p>
    </div>
</div>


