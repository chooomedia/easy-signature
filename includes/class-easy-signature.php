<?php
class Easy_Signature {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        if (defined('EASY_SIGNATURE_VERSION')) {
            $this->version = EASY_SIGNATURE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'easy-signature';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-easy-signature-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-easy-signature-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-easy-signature-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-easy-signature-public.php';

        $this->loader = new Easy_Signature_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new Easy_Signature_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {
        $plugin_admin = new Easy_Signature_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        $this->loader->add_action('init', $plugin_admin, 'register_signature_post_type');
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_signature_meta_boxes');
        $this->loader->add_action('save_post_easy_signature', $plugin_admin, 'save_signature_meta', 10, 2);
        $this->loader->add_action('admin_post_easy_signature_create_signature', $plugin_admin, 'create_signature');
    }

    private function define_public_hooks() {
        $plugin_public = new Easy_Signature_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_shortcode('easy_signature', $plugin_public, 'shortcode_signature');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}