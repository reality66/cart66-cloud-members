<?php

class CM_Admin_Settings_Notifications extends CC_Admin_Setting {

    public static function init() {
        $page = 'cart66_members';
        $option_group = 'cart66_members_notifications';
        $setting = new CM_Admin_Settings_Notifications( $page, $option_group );
        return $setting;
    }

    public function render_section() {
        _e( 'Members Settings For Notifications', 'cart66_members' );
    }

    /**
     * admin_init hooked in by parent class constructor
     */
    public function register_settings() {
        $this->register_notification_section();
        $this->register();
    }

    public function register_notification_section() {
        // Set the name for the options in this section and load any stored values
        $option_values = self::get_options( $this->option_name );

        // Create the section for the cart66_main_settings section
        $title = __( 'Access Notifications', 'cart66_members' );
        $section = new CC_Admin_Settings_Section( $title, $this->option_name );

        // Add member home select box
        $home_title = __( 'Member Home Page', 'cart66_members');
        $home = new CC_Admin_Settings_Select_Box( $home_title, $this->option_name, 'member_home' );
        $home->new_option( 'Secure Order History', 'order_history', false );
        $home->description = __( 'The page where members will be directed after logging in', 'cart66_members' );
        $this->build_member_homepage_list( $home, $option_values['member_home'] );
        $section->add_field( $home );

        // Add the settings sections for the page and register the settings
        $this->add_section( $section );

    }

    /**
     * Add all of the published pages to the select box
     *
     * The selected option is the one where the page ID matches the given $value
     *
     * @param CC_Admin_Settings_Select_Box $home
     * @param string The page ID of the selected value
     */
    public function build_member_homepage_list( $home, $value ) {
        
        foreach($this->get_page_list() as $page) {
            $selected = ($value == $page->ID);
            $title = str_repeat('&ndash; ', count($page->ancestors)) . $page->post_title;
            $home->new_option( $title, $page->ID, $selected );
        }

    }

    public function get_page_list() {
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ); 

        return get_pages($args); 
    }


}
