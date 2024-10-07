<?php
/**
 * Plugin Name: Easy Signature
 * Plugin URI: https://www.chooomedia.de/easy-signature
 * Description: Ein Plugin zur Erstellung anpassbarer Mitarbeitersignaturen
 * Version: 1.0.0
 * Author: Christopher Matt
 * Author URI: https://www.chooomedia.de
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: easy-signature
 * Domain Path: /languages
 */

if (!defined('WPINC')) {
    die;
}

define('EASY_SIGNATURE_VERSION', '1.0.0');
define('EASY_SIGNATURE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EASY_SIGNATURE_PLUGIN_URL', plugin_dir_url(__FILE__));

function activate_easy_signature() {
    require_once EASY_SIGNATURE_PLUGIN_DIR . 'includes/class-easy-signature-activator.php';
    Easy_Signature_Activator::activate();
}

function deactivate_easy_signature() {
    require_once EASY_SIGNATURE_PLUGIN_DIR . 'includes/class-easy-signature-deactivator.php';
    Easy_Signature_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_easy_signature');
register_deactivation_hook(__FILE__, 'deactivate_easy_signature');

require EASY_SIGNATURE_PLUGIN_DIR . 'includes/class-easy-signature.php';

function run_easy_signature() {
    $plugin = new Easy_Signature();
    $plugin->run();
}

run_easy_signature();

function easy_signature_rewrite_rules() {
    add_rewrite_rule(
        '^signature/([0-9]+)/?$',
        'index.php?easy_signature_id=$matches[1]',
        'top'
    );
}
add_action('init', 'easy_signature_rewrite_rules');

function easy_signature_query_vars($query_vars) {
    $query_vars[] = 'easy_signature_id';
    return $query_vars;
}
add_filter('query_vars', 'easy_signature_query_vars');

function easy_signature_template_include($template) {
    if (get_query_var('easy_signature_id')) {
        return EASY_SIGNATURE_PLUGIN_DIR . 'public/signature-template.php';
    }
    return $template;
}
add_filter('template_include', 'easy_signature_template_include');