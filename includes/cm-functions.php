<?php

function cart66_cloud_required_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'Cart66 Members requires the Cart66 Cloud plugin to be installed and activated.', 'cart66-members' ); ?></p>
    </div>
    <?php
}

function cm_starts_with( $haystack, $needle ) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function cm_save_activation_error() {
    CM_Log::write( 'Activation error information for Cart66 Members: ' . ob_get_contents() );
}
