<?php

class CM_Admin_Settings_Restrictions extends CC_Admin_Setting {

    public static function init() {
        $page = 'cart66_members_restriction_settings';
        $option_group = 'cart66_members_restrictions';
        $setting = new CM_Admin_Settings_Restrictions( $page, $option_group );
        return $setting;
    }

    /**
     * admin_init hooked in by parent class constructor
     */
    public function register_settings() {
        $this->register_category_restriction_settings();
        $this->register();
    }

    public function register_category_restriction_settings() {
        // Set the name for the options in this section and load any stored values
        $option_values = self::get_options( $this->option_name );

        // Create the section for the cart66_main_settings section
        $title = __( 'Restrict access to Post categories', 'cart66_members' );
        $description = __( 'Select the memberships that are required in order to access posts for the listed categories. Do not select any memberships for categories open to the public', 'cart66_members' );
        $section = new CC_Admin_Settings_Section( $title, $this->option_name );
        $section->description = $description;

        // Add text field
        $title = __( 'Color', 'cart66_members');
        $value = esc_attr( $option_values[ 'color' ] );
        $color = new CC_Admin_Settings_Text_Field( $title, 'color', $value );
        $color->description = __( 'The color you like most', 'cart66_members' );
        $section->add_field( $color );

        // Add the settings sections for the page and register the settings
        $this->add_section( $section );
    }

}
