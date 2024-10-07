<?php
// Verhindere direkten Zugriff auf diese Datei
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" data-tab="general"><?php _e('Allgemeine Einstellungen', 'easy-signature'); ?></a>
        <a href="#" class="nav-tab" data-tab="signatures"><?php _e('Signaturen', 'easy-signature'); ?></a>
        <a href="#" class="nav-tab" data-tab="new-signature"><?php _e('Neue Signatur', 'easy-signature'); ?></a>
    </nav>

    <div class="tab-content">
        <div id="general" class="tab-pane active">
            <form method="post" action="options.php">
                <?php
                settings_fields('easy_signature_options');
                do_settings_sections('easy_signature_options');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Standard-Schriftart', 'easy-signature'); ?></th>
                        <td>
                            <select name="easy_signature_options[default_font]">
                                <option value="Arial" <?php selected(get_option('easy_signature_options')['default_font'], 'Arial'); ?>>Arial</option>
                                <option value="Helvetica" <?php selected(get_option('easy_signature_options')['default_font'], 'Helvetica'); ?>>Helvetica</option>
                                <option value="Times New Roman" <?php selected(get_option('easy_signature_options')['default_font'], 'Times New Roman'); ?>>Times New Roman</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Standard-Farbe', 'easy-signature'); ?></th>
                        <td>
                            <input type="color" name="easy_signature_options[default_color]" value="<?php echo esc_attr(get_option('easy_signature_options')['default_color']); ?>">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>

        <div id="signatures" class="tab-pane">
            <h2><?php _e('Vorhandene Signaturen', 'easy-signature'); ?></h2>
            <?php
            $signatures = get_posts(array('post_type' => 'easy_signature', 'posts_per_page' => -1));
            if ($signatures) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'easy-signature'); ?></th>
                            <th><?php _e('Position', 'easy-signature'); ?></th>
                            <th><?php _e('E-Mail', 'easy-signature'); ?></th>
                            <th><?php _e('Aktionen', 'easy-signature'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($signatures as $signature) : 
                            $signature_data = get_post_meta($signature->ID, '_easy_signature_data', true);
                        ?>
                            <tr>
                                <td><?php echo esc_html($signature_data['name']); ?></td>
                                <td><?php echo esc_html($signature_data['position']); ?></td>
                                <td><?php echo esc_html($signature_data['email']); ?></td>
                                <td>
                                    <a href="<?php echo get_edit_post_link($signature->ID); ?>" class="button"><?php _e('Bearbeiten', 'easy-signature'); ?></a>
                                    <a href="<?php echo get_delete_post_link($signature->ID); ?>" class="button"><?php _e('Löschen', 'easy-signature'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('Keine Signaturen gefunden.', 'easy-signature'); ?></p>
            <?php endif; ?>
        </div>

        <div id="new-signature" class="tab-pane">
            <h2><?php _e('Neue Signatur erstellen', 'easy-signature'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="easy_signature_create_signature">
                <?php wp_nonce_field('easy_signature_create_signature', 'easy_signature_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Name', 'easy-signature'); ?></th>
                        <td><input type="text" name="easy_signature_name" required></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Position', 'easy-signature'); ?></th>
                        <td><input type="text" name="easy_signature_position"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('E-Mail', 'easy-signature'); ?></th>
                        <td><input type="email" name="easy_signature_email" required></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Telefon', 'easy-signature'); ?></th>
                        <td><input type="tel" name="easy_signature_phone"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Avatar', 'easy-signature'); ?></th>
                        <td>
                            <input type="hidden" name="easy_signature_avatar" id="easy_signature_avatar">
                            <button type="button" class="button" id="upload_avatar_button"><?php _e('Avatar auswählen', 'easy-signature'); ?></button>
                            <div id="avatar_preview"></div>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Signatur erstellen', 'easy-signature')); ?>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab-Funktionalität
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('tab');
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-pane').removeClass('active');
        $('#' + target).addClass('active');
    });

    // Media Uploader für Avatar
    var file_frame;
    $('#upload_avatar_button').on('click', function(e) {
        e.preventDefault();
        if (file_frame) {
            file_frame.open();
            return;
        }
        file_frame = wp.media.frames.file_frame = wp.media({
            title: '<?php _e('Avatar auswählen', 'easy-signature'); ?>',
            button: {
                text: '<?php _e('Avatar verwenden', 'easy-signature'); ?>'
            },
            multiple: false
        });
        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#easy_signature_avatar').val(attachment.url);
            $('#avatar_preview').html('<img src="' + attachment.url + '" style="max-width: 100px; max-height: 100px;">');
        });
        file_frame.open();
    });
});
</script>