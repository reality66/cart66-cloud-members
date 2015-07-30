<?php
/*
Plugin Name: Cart66 Cloud - Members
Plugin URI: http://cart66.com
Description: Membership functionality for Cart66 Cloud
Version: 1.1.2
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

    $plugin_file = __FILE__;
    if(isset($plugin)) { $plugin_file = $plugin; }
    elseif (isset($mu_plugin)) { $plugin_file = $mu_plugin; }
    elseif (isset($network_plugin)) { $plugin_file = $network_plugin; }

    // Define constants
    define( 'CM_VERSION_NUMBER', '1.1.2' );
    define( 'CM_PLUGIN_FILE', $plugin_file );
    define( 'CM_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'CM_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'CM_DEBUG', true );

    // Include Cart66 Cloud Members helper functions
    include_once CM_PATH . 'includes/cm-functions.php';

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
            // Register autoloader
            spl_autoload_register( array( $this, 'class_loader' ) );

            // Check to see if Cart66 Cloud is installed
            add_action( 'plugins_loaded', array( $this, 'dependency_check' ) );

            // Initialize plugin
            add_action( 'init', array( $this, 'initialize' ) );

        }

        public function dependency_check() {
            $check = true;

            if ( class_exists('Cart66_Cloud') ) {
                // If Cart66 Cloud is loaded register the account widget
                add_action('widgets_init', create_function('', 'return register_widget("CM_Account_Widget");'));
            } else {
                // If Cart66 Cloud is not loaded show and admin notice
                add_action( 'admin_notices', 'cart66_cloud_required_notice' );
                $check = false;
            }

            CM_Flash_Data::set( 'dependency_check', $check );
        }

        public function initialize() {
            if ( CM_Flash_Data::get('dependency_check') ) {
                do_action( 'before_cart66_members_init' );
                CM_Log::write('Initializing Cart66 Cloud Members plugin');

                // Register action hooks
                $this->register_actions();

                // Initialize admin
                if( is_admin() ) {
                    CM_Admin::init();
                }

                // Initialize shortcodes for managing access to content
                CM_Shortcode_Manager::init();

                do_action ( 'after_cart66_members_init' );
            }
        }

        public function register_actions() {

            // Initialize core classes
            add_action( 'init', array( $this, 'init' ), 0 );
            add_action( 'activated_plugin', 'cm_save_activation_error' );

            if ( ! is_admin() ) {
                // Redirect to access denied page
                $monitor = new CM_Monitor();
                add_action( 'template_redirect', array( $monitor, 'access_denied_redirect' ) );

                // Remove content from restricted pages
                add_filter( 'the_content', array( $monitor, 'restrict_pages' ) );

                $post_filter = CC_Admin_Setting::get_option( 'cart66_members_notifications', 'post_filter' );
                CM_Log::write( 'Post filter value: ' . $post_filter );

                if ( 'remove' == $post_filter ) {
                    // Remove unauthorized posts from ever being displayed
                    add_filter( 'the_posts',   array( $monitor, 'filter_posts' ) );
                    
                    // Remove restricted categores from the category widget
                    add_filter( 'widget_categories_args', array( $monitor, 'filter_category_widget' ), 10, 2 );
                }

                // Filter restricted pages that are not part of nav menus
                add_filter( 'get_pages',          array( $monitor, 'filter_pages' ) );
                add_filter( 'nav_menu_css_class', array( $monitor, 'filter_menus' ), 10, 2 );
                add_action( 'wp_enqueue_scripts', array( $monitor, 'enqueue_css' ) );

                // Check if current visitor is logged signed in to the cloud
                $visitor = new CM_Visitor();
                add_action( 'wp_loaded', array( $visitor, 'check_remote_login' ) );

            }
        
        }

        public static function class_loader($class) {
            if(cm_starts_with($class, 'CM_')) {
                $class = strtolower($class);
                $file = 'class-' . str_replace( '_', '-', $class ) . '.php';
                $root = CM_PATH;

                if(cm_starts_with($class, 'cm_exception')) {
                    include_once $root . 'includes/exception-library.php';
                } elseif ( cm_starts_with( $class, 'cm_admin' ) ) {
                    include_once $root . 'includes/admin/' . $file;
                } elseif ( cm_starts_with( $class, 'cm_cloud' ) ) {
                    include_once $root . 'includes/cloud/' . $file;
                } else {
                    include_once $root . 'includes/' . $file;
                }
            }
        }

    }

}

Cart66_Members::instance();
