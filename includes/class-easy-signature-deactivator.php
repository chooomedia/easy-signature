<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://www.chooomedia.de
 * @since      1.0.0
 *
 * @package    Easy_Signature
 * @subpackage Easy_Signature/includes
 */

class Easy_Signature_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Unregister the post type, so the rules are no longer in memory
        unregister_post_type('easy_signature');

        // Clear the permalinks to remove our post type's rules from the database
        flush_rewrite_rules();
    }
}