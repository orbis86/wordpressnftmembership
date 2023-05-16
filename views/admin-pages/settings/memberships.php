<?php
/**
 * Memberships Settings Page
 */
?>
<style>
    .select2-container{
        width: 100% !important;
    }
</style>
<div id="poststuff">

	<div id="post-body" class="metabox-holder columns-2">

		<!-- main content -->
		<div id="post-body-content">

			<div class="meta-box-sortables ui-sortable">

				<div class="postbox">

					<div class="inside">
						<?php
						//Show plugin sidebar content settings form
						acf_form('nft-memberships-plugin-settings-memberships');
						?>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables .ui-sortable -->

		</div>
		<!-- post-body-content -->


	</div>
	<!-- #post-body .metabox-holder .columns-2 -->

	<br class="clear">
</div>

<!--Load Collection's NFTs via AJAX-->
<script type="text/javascript">
    /*
    (function($) {

        // Runs when a repeater row is added
        acf.add_action('append', function( $el ){
           let nft_membership_checkbox = $el.find('[data-key="field_630e0279109d1"] .acf-input input[type="checkbox"]');

           // Contract Address
           let contract_address_field = $el.find('[data-key="field_630e04e763abc"] .acf-input input');
           let contract_address = '';
           contract_address_field.change(function() {
               contract_address = contract_address_field.val();
           });

           // Blockchain Type
            let blockchain_type_field = $el.find('[data-key="field_630e006694927"] .acf-input select');
            // Set default value of null
            blockchain_type_field.val(null).trigger('change');
            let blockchain_type = '';
            blockchain_type_field .on('select2:select', function (e) {
                let data = e.params.data;
                blockchain_type = data.id;
            });

            // Check membership level check
            nft_membership_checkbox.change(function() {
                // NFT Membership level check is enabled
                if (jQuery(this).is(':checked') ) {

                    // Load NFTs via AJAX
                    $el.find('[data-key="field_630e018f1fa3a"] .acf-input select').select2({
                        width: 'resolve',
                        ajax: {
                            url: ajaxurl,
                            type: "post",
                            dataType: 'json',
                            delay: 0,
                            data: function (params) {
                                return {
                                    nonce: '<?php echo wp_create_nonce('get-collection-nfts')?>',
                                    action: 'nft_memberships_get_collection_nfts',
                                    contract_address : contract_address,
                                    blockchain_type : blockchain_type
                                }
                            },
                            processResults: function (response) {
                                return {
                                    results: response.data
                                };
                            },
                            cache: true
                        },
                        placeholder: 'Select NFT(s)',
                    });

                } else {
                    // console.log('not checked');
                }

            });

        });

        // Runs when all fields have loaded (excluding the append action)
        acf.add_action('load', function( $el ){
            console.log('inside load');
            let nft_membership_checkboxes = $el.find('[data-key="field_630e0279109d1"] .acf-input input[type="checkbox"]');

            jQuery.each(nft_membership_checkboxes, function(index, value) {
                console.log('inside loop');
                console.log(index);

                // Contract Address
                let contract_address_field = $el.find('[data-key="field_630e04e763abc"] .acf-input input');
                console.log(contract_address_field);
                contract_address_field.change(function() {
                    let contract_address = contract_address_field.val();
                    console.log(contract_address);
                });

                /*
                // Blockchain Type
                let blockchain_type_field = $el.find('[data-key="field_630e006694927"] .acf-input select');
                // Set default value of null
                blockchain_type_field.val(null).trigger('change');
                let blockchain_type = '';
                blockchain_type_field .on('select2:select', function (e) {
                    let data = e.params.data;
                    blockchain_type = data.id;
                });

                // Check membership level check
                nft_membership_checkboxes.change(function() {
                    // NFT Membership level check is enabled
                    if (jQuery(this).is(':checked') ) {

                        // Load NFTs via AJAX
                        $el.find('[data-key="field_630e018f1fa3a"] .acf-input select').select2({
                            width: 'resolve',
                            ajax: {
                                url: ajaxurl,
                                type: "post",
                                dataType: 'json',
                                delay: 0,
                                data: function (params) {
                                    return {
                                        nonce: '<?php echo wp_create_nonce('get-collection-nfts')?>',
                                        action: 'nft_memberships_get_collection_nfts',
                                        contract_address : contract_address,
                                        blockchain_type : blockchain_type
                                    }
                                },
                                processResults: function (response) {
                                    return {
                                        results: response.data
                                    };
                                },
                                cache: true
                            },
                            placeholder: 'Select NFT(s)',
                        });

                    } else {
                        // console.log('not checked');
                    }

                });
                */
       //     });




      //  });

   // })( jQuery );
  //  */
</script>



