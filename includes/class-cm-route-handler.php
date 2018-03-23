<?php

class CM_Route_Handler {
   
    public static function run() {
        global $wp;

        CM_Log::write( 'cm_route_handler: starting' );

        // If the cm-action is not available forget about doing anything else here
        if ( ! isset( $wp->query_vars[ 'cm-action' ] ) ) {
            CM_Log::write( 'cm-action not set in WP query vars so bailing out of route handler.' );
            return;
        }

        $action = $wp->query_vars[ 'cm-action' ];
        CM_Log::write( "Route handler found action: $action" );

        // Unauthenticated requests
        switch ( $action ) {
            case 'client-home':
                $client_url = CM_Client_Page::get_url();
                wp_redirect( $client_url );
                exit();
                break;
        } 

    } // end cc_route_handler
}
