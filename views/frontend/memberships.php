<?php
/**
 * Memberships Output Template
 *
 * It displays the memberships in a format similar to pricing tables (3 columns)
 *
 * One can only subscribe to the following memberships if they meet their respective requirements.
 */

$site_memberships = nft_memberships_get_memberships();

?>

<div class="container my-2">
	<div class="row mb-3">
		<?php if( is_array( $site_memberships) && ! empty( $site_memberships) ): ?>
			<?php foreach ( $site_memberships as $site_membership ): ?>
				<div class="col">
					<div class="card rounded-3 shadow-sm">
						<div class="card-header py-3">
							<h4 class="my-0 fw-normal text-center"><?php echo $site_membership->post_title ?></h4>
						</div>
						<div class="card-body">
							<?php echo get_field( 'membership_description', $site_membership->ID ) ?>
							<span class="subscribe-nft-membership" data-membership-id="<?php echo $site_membership->ID ?>">
                                <button type="button" class="w-100 btn btn-lg btn-outline-primary text-center">Sign Up</button>
                            </span>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<p>No memberships configured.</p>
		<?php endif; ?>
	</div>
</div>
