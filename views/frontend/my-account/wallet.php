<?php
/**
 * Wallet - My Account Template
 */

/*
 * Hedera Account ID
 */
$account_id = get_user_meta( get_current_user_id(), 'nft_memberships_account_id', true );
if( null == $account_id ){
	$account_id = false;
}

/*
 * Blockchain Wallet
 */
$wallet = get_user_meta( get_current_user_id(), 'nft_memberships_owner_address', true );
if( null == $wallet ){
	$wallet = false;
}

?>

<!-- Wallet Template-->
<?php do_action('nft_memberships_dashboard_wallet_tab_before_content'); ?>
<div class="row">
    <div class="col-12">
	    <?php print $wallet_settings['content'] ?? ''; ?>

        <br/>
        <p>Your connected wallets:</p>

        <table class="table mx-2">
            <thead>
            <tr>
                <th scope="col">Wallet Type</th>
                <th scope="col">Wallet Address / Account ID</th>
                <th scope="col">Actions</th>
            </tr>
            </thead>

            <tbody>
                <!-- Ethereum Wallet -->
                <tr>
                    <th scope="row">Ethereum Wallet</th>
                    <td>
	                    <?php if( $wallet ): ?>
		                    <?php echo $wallet; ?>
	                    <?php else: ?>
                            <p>Wallet not connected.</p>
	                    <?php endif; ?>
                    </td>
                    <td>
	                    <?php if( $wallet ): ?>
                            <span id="resetAddressButton" >
                                <button id="reset-wallet" class="btn btn-outline-danger" type="button">Reset Wallet</button>
                            </span>
	                    <?php else: ?>
                            <span id="walletConnect" >
                                <button id="btn-connect" class="btn btn-outline-primary" type="button">Connect Wallet</button>
                            </span>
	                    <?php endif; ?>
                    </td>
                </tr>
                <!-- End Ethereum Wallet -->

                <!-- Hedera Hashgraph Account -->
                <tr>
                    <th scope="row">Hedera Hashgraph Wallet</th>
                    <td>
	                    <?php if( $account_id ): ?>
		                    <?php echo $account_id; ?>
	                    <?php else: ?>
                            <p>Wallet not connected.</p>
	                    <?php endif; ?>
                    </td>
                    <td>
                        <?php if( $account_id ): ?>
                            <span id="resetAccountButton" >
                                <button id="reset-wallet" class="btn btn-outline-danger" type="button" >Reset Account ID</button>
                            </span>

                        <?php else: ?>
                            <span id="hederaConnect" >
                                <app-root>
                                    <button _ngcontent-xru-c88="" id="btn-connect" class="btn btn-outline-primary" type="button">Connect Hashpack</button>
                                </app-root>
                            </span>

                        <?php endif; ?>
                    </td>
                </tr>
            <!-- End Hedera Hashgraph Account -->

            </tbody>

        </table>
    </div>
</div>
<?php do_action('nft_memberships_dashboard_wallet_tab_after_content'); ?>

<script type="text/javascript" src="https://unpkg.com/web3@1.2.11/dist/web3.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/web3modal@1.9.0/dist/index.js"></script>
<script type="text/javascript" src="https://unpkg.com/evm-chains@0.2.0/dist/umd/index.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/@walletconnect/web3-provider@1.2.1/dist/umd/index.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/fortmatic@2.0.6/dist/fortmatic.js"></script>


