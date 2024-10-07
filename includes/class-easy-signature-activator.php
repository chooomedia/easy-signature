<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.chooomedia.de
 * @since      1.0.0
 *
 * @package    Easy_Signature
 * @subpackage Easy_Signature/includes
 */

class Easy_Signature_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create the upload directory for signatures if it doesn't exist
        $upload_dir = wp_upload_dir();
        $signature_dir = $upload_dir['basedir'] . '/easy-signature/';
        if (!file_exists($signature_dir)) {
            wp_mkdir_p($signature_dir);
        }

        // Register the custom post type
        self::register_signature_post_type();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        $default_options = array(
            'default_font' => 'Arial',
            'default_color' => '#000000',
        );
        add_option('easy_signature_options', $default_options);
    }

    private static function register_signature_post_type() {
        $args = array(
            'public' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'signature'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'thumbnail'),
            'menu_icon' => 'dashicons-id-alt',
        );
        register_post_type('easy_signature', $args);
    }
}