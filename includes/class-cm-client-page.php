<?php

class CM_Client_Page {

    protected static $instance;
    protected static $slugs;

    /**
     * Kick things off by calling this function.
     */
    public static function init() {
        $instance = self::get_instance();
        
        return $instance;
    }

    /**
     * Return the client page url 
     *
     * If the email address is found, return the url to the client page.
     * Otherwise, return the site homepage.
     * 
     * @return string 
     */
    public static function get_url() {
        global $wpdb;

        $visitor_email = CC::visitor_email();
        CM_Log::write("Got visitor email: $visitor_email");

        $prepared_query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta where meta_key ='cm_client_email' and meta_value = %s", $visitor_email );
        $post_ids = $wpdb->get_col( $prepared_query );
        CM_Log::write("CM Client Email Query: " . $wpdb->last_query);
        $post_id = array_shift( $post_ids );
        if ( $post_id ) {
            $permalink = get_permalink( $post_id );
            CM_Log::write("Found permalink: $permalink");
        }
        else {
            $permalink = get_site_url();
            CM_Log::write("Could not find client page, returning site url: $permalink");
        }

        return $permalink;
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->create_post_type();
            self::$instance->manage_custom_columns();
            // self::$instance->custom_bulk_actions();
        }

        return self::$instance;
    }

    public function manage_custom_columns() {
        add_filter( 'manage_cm_client_page_posts_columns', function( $columns ) {
            unset( $columns['date'] );

            $columns['email'] = __('Email', 'cart66');

            return $columns;
        });

        add_action( 
            'manage_cm_client_page_posts_custom_column',
            function( $column, $post_id ) {
                $value = get_post_meta( $post_id, 'cm_client_' . $column, true );
                echo $value;
            },
            10,
            2
        );
    }

    public function create_post_type() {

        $labels = array(
            'name'               => __( 'Client', 'cart66' ),
            'singular_name'      => __( 'Client', 'cart66' ),
            'menu_name'          => __( 'Clients', 'cart66' ),
            'all_items'          => __( 'All Clients', 'cart66' ),
            'view_item'          => __( 'View Client', 'cart66' ),
            'add_new_item'       => __( 'Add New Client', 'cart66' ),
            'add_new'            => __( 'Add New', 'cart66' ),
            'edit_item'          => __( 'Edit Client', 'cart66' ),
            'update_item'        => __( 'Update Client', 'cart66' ),
            'search_items'       => __( 'Search Clients', 'cart66' ),
            'not_found'          => __( 'Not Found', 'cart66' ),
            'not_found_in_trash' => __( 'Not found in Trash', 'cart66' ),
        );

        $options = array (
            'description' => 'Client Pages',
            'hierarchical' => true,     
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true, 
            'show_in_menu' => true, 
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'page',
            'has_archive' => false, 
            'menu_position' => 30,
            'rewrite' => array( 'slug' => 'client-page' ),
            'menu_icon' => 'dashicons-groups',
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' )
        );

        register_post_type( 'cm_client_page', $options );
        add_post_type_support( 'cm_client_page', 'make-builder' );

        // Register cliient page meta box
        new CM_Client_Page_Meta_Box();
    }

}
