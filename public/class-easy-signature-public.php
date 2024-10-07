<?php
class Easy_Signature_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/easy-signature-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/easy-signature-public.js', array('jquery'), $this->version, false);
    }

    public function shortcode_signature($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
        ), $atts, 'easy_signature');

        if (empty($atts['id'])) {
            return __('Bitte geben Sie eine Signatur-ID an.', 'easy-signature');
        }

        $signature_data = get_post_meta($atts['id'], '_easy_signature_data', true);
        if (!$signature_data) {
            return __('Signatur nicht gefunden.', 'easy-signature');
        }

        ob_start();
        include 'partials/easy-signature-public-display.php';
        return ob_get_clean();
    }
}