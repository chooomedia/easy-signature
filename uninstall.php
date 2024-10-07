<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://www.chooomedia.de
 * @since      1.0.0
 *
 * @package    Easy_Signature
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all signature posts
$signatures = get_posts(array('post_type' => 'easy_signature', 'numberposts' => -1));
foreach ($signatures as $signature) {
    wp_delete_post($signature->ID, true);
}

// Delete all plugin options
delete_option('easy_signature_options');

// Remove the upload directory for signatures
$upload_dir = wp_upload_dir();
$signature_dir = $upload_dir['basedir'] . '/easy-signature/';
if (file_exists($signature_dir)) {
    $files = glob($signature_dir . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    rmdir($signature_dir);
}

// Clear any cached data that has been removed
wp_cache_flush();