<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.chooomedia.de
 * @since      1.0.0
 *
 * @package    Easy_Signature
 * @subpackage Easy_Signature/public/partials
 */
?>

<div class="easy-signature-container">
    <table class="easy-signature-table">
        <tr>
            <td class="easy-signature-avatar">
                <?php if (!empty($signature_data['avatar'])) : ?>
                    <img src="<?php echo esc_url($signature_data['avatar']); ?>" alt="<?php echo esc_attr($signature_data['name']); ?>">
                <?php endif; ?>
            </td>
            <td class="easy-signature-info">
                <h3 class="easy-signature-name"><?php echo esc_html($signature_data['name']); ?></h3>
                <p class="easy-signature-position"><?php echo esc_html($signature_data['position']); ?></p>
                <p class="easy-signature-contact">
                    Email: <a href="mailto:<?php echo esc_attr($signature_data['email']); ?>"><?php echo esc_html($signature_data['email']); ?></a><br>
                    Tel: <?php echo esc_html($signature_data['phone']); ?>
                </p>
            </td>
        </tr>
    </table>
</div>