<?php

class CM_Client_Page_Meta_Box {

    private $screens = array(
        'cm_client_page',
    );

    private $fields = array(
        array(
            'id' => 'email',
            'label' => 'Email',
            'type' => 'email',
        )
    );

    /**
     * Class construct method. Adds actions to their respective WordPress hooks.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_post' ) );
    }

    /**
     * Hooks into WordPress' add_meta_boxes function.
     * Goes through screens (post types) and adds the meta box.
     */
    public function add_meta_boxes() {
        foreach ( $this->screens as $screen ) {
            add_meta_box(
                'client-email',
                __( 'Client Email', 'cart66' ),
                array( $this, 'add_meta_box_callback' ),
                $screen,
                'side',
                'high'
            );
        }
    }

    /**
     * Generates the HTML for the meta box
     * 
     * @param object $post WordPress post object
     */
    public function add_meta_box_callback( $post ) {
        wp_nonce_field( 'client_email_data', 'client_email_nonce' );
        $this->generate_fields( $post );
    }

    /**
     * Generates the field's HTML for the meta box.
     */
    public function generate_fields( $post ) {
        $output = '';
        foreach ( $this->fields as $field ) {
            $label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
            $db_value = get_post_meta( $post->ID, 'cm_client_' . $field['id'], true );
            switch ( $field['type'] ) {
                case 'select':
                    CM_Log::write( 'Generating Select Box For Field ID: ' . $field['id'] . ' DBValue: ' . $db_value );
                    $input = sprintf(
                        '<select id="%s" name="%s">',
                        $field['id'],
                        $field['id']
                    );
                    foreach ( $field['options'] as $key => $value ) {
                        // $field_value = !is_numeric( $key ) ? $key : $value;
                        $field_value = $key;
                        $input .= sprintf(
                            '<option %s value="%s">%s</option>',
                            $db_value == $field_value ? 'selected' : '',
                            $field_value,
                            $value
                        );
                    }
                    $input .= '</select>';
                    break;
                default:
                    $input = sprintf(
                        '<input %s id="%s" name="%s" type="%s" value="%s">',
                        $field['type'] !== 'color' ? 'class="text"' : '',
                        $field['id'],
                        $field['id'],
                        $field['type'],
                        $db_value
                    );
            }
            $output .= $this->row_format( $label, $input );
        }
        
        echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
    }

    /**
     * Generates the HTML for table rows.
     */
    public function row_format( $label, $input ) {
        return sprintf(
            '<tr><th scope="row">%s</th><td>%s</td></tr>',
            $label,
            $input
        );
    }
    /**
     * Hooks into WordPress' save_post function
     */
    public function save_post( $post_id ) {
        if ( ! isset( $_POST['client_email_nonce'] ) )
            return $post_id;

        $nonce = $_POST['client_email_nonce'];
        if ( !wp_verify_nonce( $nonce, 'client_email_data' ) )
            return $post_id;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        foreach ( $this->fields as $field ) {
            if ( isset( $_POST[ $field['id'] ] ) ) {
                switch ( $field['type'] ) {
                    case 'email':
                        $_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
                        break;
                    case 'text':
                        $_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
                        break;
                }
                update_post_meta( $post_id, 'cm_client_' . $field['id'], $_POST[ $field['id'] ] );
            } else if ( $field['type'] === 'checkbox' ) {
                update_post_meta( $post_id, 'cm_client_' . $field['id'], '0' );
            }
        }
    }
}
