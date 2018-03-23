<?php

class CM_Cart66_Members {

    protected static $instance;

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register autoloader
        spl_autoload_register( array( $this, 'class_loader' ) );

        // Define constants
        $this->define_constants();

        // Check to see if Cart66 Cloud is installed
        $this->dependency_check();

        // Initialize plugin
        $this->initialize();
    }

    private function define_constants() {
        $plugin_dir = basename(dirname(__DIR__));

        define( 'CM_VERSION_NUMBER', '1.1.7' );
        define( 'CM_PATH', WP_PLUGIN_DIR . '/' . $plugin_dir . '/' );
        define( 'CM_URL',  WP_PLUGIN_URL . '/' . $plugin_dir . '/' );
        define( 'CM_DEBUG', true );
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
            CM_Log::write( 'WARNING: Cart66 Cloud is not loaded' );
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

            // Initialize routes and client page custom post type
            add_action( 'init', function() {
                CM_Client_Page::init();
                CM_Routes::init();
            });

            
            add_action( 'parse_query', ['CM_Route_Handler', 'run'] );

            do_action ( 'after_cart66_members_init' );
        }
    }

    public function register_actions() {

        // Initialize core classes
        add_action( 'activated_plugin', 'cm_save_activation_error' );

        if ( ! is_admin() ) {
            // Redirect to access denied page
            $monitor = new CM_Monitor();
            add_action( 'template_redirect', array( $monitor, 'access_denied_redirect' ) );

            // Remove content from restricted pages
            add_filter( 'the_content', array( $monitor, 'restrict_pages' ), 100 );

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

            // Use JS to fix caching of member content visibiltiy
            add_action( 'wp_enqueue_scripts', function() {
                CM_Log::write( 'Uhh.... enqueue scripts NOW please?');
                wp_enqueue_script( 'js-cookie', cm_url() . 'resources/js/js-cookie.js', ['jquery'] );
                wp_enqueue_script( 'content-visibility', cm_url() . 'resources/js/content-visibility.js', ['jquery', 'js-cookie'] );
            });

            // Check if current visitor is logged signed in to the cloud
            $visitor = CM_Visitor::get_instance();
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
