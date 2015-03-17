<?php

function cart66_cloud_required_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'Cart66 Members requires the Cart66 Cloud plugin to be installed and activated.', 'cart66_members' ); ?></p>
    </div>
    <?php
}

function cm_save_activation_error() {
    CC_Log::write( 'Activation error information for Cart66 Members: ' . ob_get_contents() );
}

function cm_updater_init() {

	/* Load Plugin Updater */
	require_once CM_PATH . 'includes/plugin-updater.php';

	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( CM_PLUGIN_FILE ), //required
		'dashboard' => false,
		'username'  => false,
		'key'       => '',
		'repo_uri'  => 'http://cart66.com',  //required
		'repo_slug' => 'cart66-members',  //required
	);

	/* Load Updater Class */
	new Cart66_Members_Plugin_Updater( $config );
}
