<?php
/**
 * Memberships List Table
 */
?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>

        <a href="admin.php?page=nft-memberships-settings&tab=memberships" id="wp-em-add-account" class="add-new-h2">Manage Memberships</a>
	</h1>

	<form method="post">
		<input type="hidden" name="page" value="wp-email-manager"/>
		<?php $memberships_list_table->search_box( 'Search', 'search' ); ?>
	</form>
	<?php $memberships_list_table->views(); ?>

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="accounts-filters" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
		<!-- Now we can render the completed list table -->
		<?php $memberships_list_table->display() ?>
	</form>


</div>
