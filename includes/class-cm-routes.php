<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class CM_Routes {

    protected static $instance;

    /**
     * Kick things off by calling this function.
     */
    public static function init() {
        $instance = self::get_instance();
        
        return $instance;
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {

        // add query vars
        add_filter( 'query_vars', function( $vars ) {
            $vars[] = 'cm-action';
            return $vars;
        }, 0 );

        add_rewrite_rule( 'client-home', 'index.php?cm-action=client-home', 'top' );

        /*
        // register endpoints
        add_action( 'init', function() {
            add_rewrite_rule( 'client-home', 'index.php?cm-action=client-home', 'top' );
        }, 0 );
        */
    }

}