<?php
/*
Plugin Name: Cart66 Cloud - Members
Plugin URI: http://cart66.com
Description: Membership functionality for Cart66 Cloud
Version: 1.0
Author: Reality66
Author URI: http://www.reality66.com

-------------------------------------------------------------------------
Cart66 Cloud
Copyright 2015  Reality66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('Cart66_Members') ) {

    $cm_requirements_met = true;
    $plugin_file = __FILE__;
    if(isset($plugin)) { $plugin_file = $plugin; }
    elseif (isset($mu_plugin)) { $plugin_file = $mu_plugin; }
    elseif (isset($network_plugin)) { $plugin_file = $network_plugin; }

    define( 'CM_PLUGIN_FILE', $plugin_file );
    define( 'CM_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'CM_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'CM_DEBUG', true );

    include_once CM_PATH . 'includes/cm-functions.php';

    // Cart66 Members requires Cart66 Cloud
    if ( ! class_exists( 'Cart66_Cloud' ) ) {
        add_action( 'admin_notices', 'cart66_cloud_required_notice' );
        $cm_requirements_met = false;
    }

    /**
     * Cart66 Members main class
     *
     * The main Cart66 class should not be extended
     */
    final class Cart66_Members {

        protected static $instance;

        /**
         * Cart66 should only be loaded one time
         *
         * @since 2.0
         * @static
         * @return Cart66 instance
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            // Define constants
            define( 'CC_VERSION_NUMBER', $this->version_number() );

            // Register autoloader
            spl_autoload_register( array( $this, 'class_loader' ) );

            // Register action hooks
            $this->register_actions();
        }

        public function register_actions() {
            // Initialize core classes
            add_action( 'init', array( $this, 'init' ), 0 );

        }

        public function init() {
            do_action( 'before_cart66_members_init' );

            if( is_admin() ) {
                CM_Admin::init();
            }

            do_action ( 'after_cart66_members_init' );
        }

        public static function class_loader($class) {
            if(cc_starts_with($class, 'CM_')) {
                $class = strtolower($class);
                $file = 'class-' . str_replace( '_', '-', $class ) . '.php';
                $root = CM_PATH;

                if(cc_starts_with($class, 'cm_exception')) {
                    include_once $root . 'includes/exception-library.php';
                } elseif ( cc_starts_with( $class, 'cm_admin' ) ) {
                    include_once $root . 'includes/admin/' . $file;
                } else {
                    include_once $root . 'includes/' . $file;
                }
            }
        }


        /**
         * Get the plugin version number from the header comments
         *
         * @return string
         */
        public function version_number() {
            if(!function_exists('get_plugin_data')) {
              require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            $plugin_data = get_plugin_data(CC_PLUGIN_FILE);
            return $plugin_data['Version'];
        }

    }

}

if( $cm_requirements_met ) {
    Cart66_Members::instance();
}

