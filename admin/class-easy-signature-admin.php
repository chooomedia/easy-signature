<?php
/**
 * Die Admin-spezifische Funktionalität des Plugins.
 *
 * @link       https://www.chooomedia.de
 * @since      1.0.0
 *
 * @package    Easy_Signature
 * @subpackage Easy_Signature/admin
 */

class Easy_Signature_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_easy_signature_create_signature', array($this, 'create_signature'));
        add_action('add_meta_boxes', array($this, 'add_signature_meta_boxes'));
        add_action('save_post_easy_signature', array($this, 'save_signature_meta'), 10, 2);
        add_action('wp_ajax_easy_signature_generate_preview', array($this, 'ajax_generate_preview'));
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/easy-signature-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/easy-signature-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_media();
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'Easy Signature Settings',
            'Easy Signature',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-id-alt',
            100
        );
    }

    public function display_plugin_admin_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/easy-signature-admin-display.php';
    }

    public function register_settings() {
        register_setting('easy_signature_options', 'easy_signature_options', array($this, 'validate_options'));

        add_settings_section(
            'easy_signature_general_settings',
            __('Allgemeine Einstellungen', 'easy-signature'),
            array($this, 'general_settings_section_callback'),
            'easy_signature_options'
        );

        add_settings_field(
            'default_font',
            __('Standard-Schriftart', 'easy-signature'),
            array($this, 'default_font_callback'),
            'easy_signature_options',
            'easy_signature_general_settings'
        );

        add_settings_field(
            'default_color',
            __('Standard-Farbe', 'easy-signature'),
            array($this, 'default_color_callback'),
            'easy_signature_options',
            'easy_signature_general_settings'
        );
    }

    public function general_settings_section_callback() {
        echo '<p>' . __('Hier können Sie die allgemeinen Einstellungen für Easy Signature festlegen.', 'easy-signature') . '</p>';
    }

    public function default_font_callback() {
        $options = get_option('easy_signature_options');
        $font = isset($options['default_font']) ? $options['default_font'] : 'Arial';
        echo '<select name="easy_signature_options[default_font]">
            <option value="Arial" ' . selected($font, 'Arial', false) . '>Arial</option>
            <option value="Helvetica" ' . selected($font, 'Helvetica', false) . '>Helvetica</option>
            <option value="Times New Roman" ' . selected($font, 'Times New Roman', false) . '>Times New Roman</option>
        </select>';
    }

    public function default_color_callback() {
        $options = get_option('easy_signature_options');
        $color = isset($options['default_color']) ? $options['default_color'] : '#000000';
        echo '<input type="color" name="easy_signature_options[default_color]" value="' . esc_attr($color) . '">';
    }

    public function validate_options($input) {
        $valid = array();
        $valid['default_font'] = sanitize_text_field($input['default_font']);
        $valid['default_color'] = sanitize_hex_color($input['default_color']);
        return $valid;
    }

    public function create_signature() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sie haben nicht die erforderlichen Berechtigungen, um diese Aktion durchzuführen.', 'easy-signature'));
        }

        check_admin_referer('easy_signature_create_signature', 'easy_signature_nonce');

        $signature_data = array(
            'post_title'   => sanitize_text_field($_POST['easy_signature_name']),
            'post_status'  => 'publish',
            'post_type'    => 'easy_signature',
        );

        $signature_id = wp_insert_post($signature_data);

        if (!is_wp_error($signature_id)) {
            $meta_data = array(
                'name'     => sanitize_text_field($_POST['easy_signature_name']),
                'position' => sanitize_text_field($_POST['easy_signature_position']),
                'email'    => sanitize_email($_POST['easy_signature_email']),
                'phone'    => sanitize_text_field($_POST['easy_signature_phone']),
                'avatar'   => esc_url_raw($_POST['easy_signature_avatar']),
            );

            update_post_meta($signature_id, '_easy_signature_data', $meta_data);
            $this->generate_signature_html($signature_id);
            $this->generate_signature_url($signature_id);

            wp_redirect(admin_url('admin.php?page=' . $this->plugin_name . '&message=created'));
            exit;
        } else {
            wp_die(__('Bei der Erstellung der Signatur ist ein Fehler aufgetreten.', 'easy-signature'));
        }
    }

    public function add_signature_meta_boxes() {
        add_meta_box(
            'easy_signature_details',
            __('Signatur-Details', 'easy-signature'),
            array($this, 'render_signature_meta_box'),
            'easy_signature',
            'normal',
            'high'
        );

        add_meta_box(
            'easy_signature_url',
            __('Signatur-URL', 'easy-signature'),
            array($this, 'render_signature_url_box'),
            'easy_signature',
            'side',
            'high'
        );
    }

    public function render_signature_meta_box($post) {
        wp_nonce_field('easy_signature_save_meta', 'easy_signature_meta_nonce');

        $signature_data = get_post_meta($post->ID, '_easy_signature_data', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="easy_signature_name"><?php _e('Name', 'easy-signature'); ?></label></th>
                <td><input type="text" id="easy_signature_name" name="easy_signature_name" value="<?php echo esc_attr($signature_data['name']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="easy_signature_position"><?php _e('Position', 'easy-signature'); ?></label></th>
                <td><input type="text" id="easy_signature_position" name="easy_signature_position" value="<?php echo esc_attr($signature_data['position']); ?>"></td>
            </tr>
            <tr>
                <th><label for="easy_signature_email"><?php _e('E-Mail', 'easy-signature'); ?></label></th>
                <td><input type="email" id="easy_signature_email" name="easy_signature_email" value="<?php echo esc_attr($signature_data['email']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="easy_signature_phone"><?php _e('Telefon', 'easy-signature'); ?></label></th>
                <td><input type="tel" id="easy_signature_phone" name="easy_signature_phone" value="<?php echo esc_attr($signature_data['phone']); ?>"></td>
            </tr>
            <tr>
                <th><label for="easy_signature_avatar"><?php _e('Avatar', 'easy-signature'); ?></label></th>
                <td>
                    <input type="hidden" id="easy_signature_avatar" name="easy_signature_avatar" value="<?php echo esc_attr($signature_data['avatar']); ?>">
                    <button type="button" class="button" id="upload_avatar_button"><?php _e('Avatar auswählen', 'easy-signature'); ?></button>
                    <div id="avatar_preview">
                        <?php if (!empty($signature_data['avatar'])) : ?>
                            <img src="<?php echo esc_url($signature_data['avatar']); ?>" style="max-width: 100px; max-height: 100px;">
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_signature_url_box($post) {
        $url = get_post_meta($post->ID, '_easy_signature_url', true);
        if ($url) {
            echo '<p>' . __('Verwenden Sie diese URL in Ihrem E-Mail-Client:', 'easy-signature') . '</p>';
            echo '<input type="text" class="widefat" value="' . esc_url($url) . '" readonly>';
        } else {
            echo '<p>' . __('Die URL wird nach dem Speichern generiert.', 'easy-signature') . '</p>';
        }
    }

    public function save_signature_meta($post_id, $post) {
        if (!isset($_POST['easy_signature_meta_nonce']) || !wp_verify_nonce($_POST['easy_signature_meta_nonce'], 'easy_signature_save_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $meta_data = array(
            'name'     => sanitize_text_field($_POST['easy_signature_name']),
            'position' => sanitize_text_field($_POST['easy_signature_position']),
            'email'    => sanitize_email($_POST['easy_signature_email']),
            'phone'    => sanitize_text_field($_POST['easy_signature_phone']),
            'avatar'   => esc_url_raw($_POST['easy_signature_avatar']),
        );

        update_post_meta($post_id, '_easy_signature_data', $meta_data);
        $this->generate_signature_html($post_id);
        $this->generate_signature_url($post_id);
    }

    private function generate_signature_html($post_id) {
        $signature_data = get_post_meta($post_id, '_easy_signature_data', true);
        $options = get_option('easy_signature_options');
        
        ob_start();
        include(plugin_dir_path(dirname(__FILE__)) . 'public/signature-template.php');
        $html = ob_get_clean();

        $upload_dir = wp_upload_dir();
        $signature_dir = $upload_dir['basedir'] . '/easy-signature/';
        wp_mkdir_p($signature_dir);

        $filename = 'signature-' . $post_id . '.html';
        file_put_contents($signature_dir . $filename, $html);

        update_post_meta($post_id, '_easy_signature_file', $filename);
    }

    private function generate_signature_url($post_id) {
        $filename = get_post_meta($post_id, '_easy_signature_file', true);
        if (!$filename) return;

        $url = home_url('signature/' . $post_id);
        update_post_meta($post_id, '_easy_signature_url', $url);
    }

    public function ajax_generate_preview() {
        check_ajax_referer('easy_signature_preview', 'nonce');

        $signature_data = array(
            'name'     => sanitize_text_field($_POST['name']),
            'position' => sanitize_text_field($_POST['position']),
            'email'    => sanitize_email($_POST['email']),
            'phone'    => sanitize_text_field($_POST['phone']),
            'avatar'   => esc_url_raw($_POST['avatar']),
        );

        $options = get_option('easy_signature_options');

        ob_start();
        include(plugin_dir_path(dirname(__FILE__)) . 'public/signature-template.php');
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }
}