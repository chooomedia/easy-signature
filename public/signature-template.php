<?php
/**
 * Template für die Anzeige einer einzelnen Signatur
 *
 * @package Easy_Signature
 */

// Verhindere direkten Zugriff auf diese Datei
if (!defined('ABSPATH')) {
    exit;
}

// Hole die Signatur-ID aus der Query
$signature_id = get_query_var('easy_signature_id');
if (!$signature_id) {
    wp_die(__('Keine Signatur-ID angegeben.', 'easy-signature'));
}

// Hole die Signatur-Daten
$signature_data = get_post_meta($signature_id, '_easy_signature_data', true);
if (!$signature_data) {
    wp_die(__('Signatur nicht gefunden.', 'easy-signature'));
}

// Verhindere WordPress-Weiterleitungen und Header
remove_action('template_redirect', 'redirect_canonical');
nocache_headers();

// Setze den Content-Type Header
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($signature_data['name']); ?> - Signatur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .signature-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        .signature-info {
            padding-left: 20px;
        }
        .signature-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .signature-position {
            font-style: italic;
            margin: 0 0 10px 0;
        }
        .signature-contact {
            margin: 0;
        }
        .signature-social {
            margin-top: 10px;
        }
        .signature-social a {
            display: inline-block;
            margin-right: 10px;
        }
        .signature-logo {
            margin-top: 20px;
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    <?php if (!empty($signature_data['avatar'])) : ?>
                        <img src="<?php echo esc_url($signature_data['avatar']); ?>" alt="<?php echo esc_attr($signature_data['name']); ?>" class="signature-avatar">
                    <?php endif; ?>
                </td>
                <td class="signature-info">
                    <h2 class="signature-name"><?php echo esc_html($signature_data['name']); ?></h2>
                    <p class="signature-position"><?php echo esc_html($signature_data['position']); ?></p>
                    <p class="signature-contact">
                        Email: <a href="mailto:<?php echo esc_attr($signature_data['email']); ?>"><?php echo esc_html($signature_data['email']); ?></a><br>
                        Tel: <?php echo esc_html($signature_data['phone']); ?>
                    </p>
                    <div class="signature-social">
                        <?php if (!empty($signature_data['facebook'])) : ?>
                            <a href="<?php echo esc_url($signature_data['facebook']); ?>" target="_blank">Facebook</a>
                        <?php endif; ?>
                        <?php if (!empty($signature_data['linkedin'])) : ?>
                            <a href="<?php echo esc_url($signature_data['linkedin']); ?>" target="_blank">LinkedIn</a>
                        <?php endif; ?>
                        <?php if (!empty($signature_data['twitter'])) : ?>
                            <a href="<?php echo esc_url($signature_data['twitter']); ?>" target="_blank">Twitter</a>
                        <?php endif; ?>
                        <!-- Fügen Sie hier weitere Social-Media-Links hinzu -->
                    </div>
                </td>
            </tr>
        </table>
        <?php if (!empty($signature_data['company_logo'])) : ?>
            <img src="<?php echo esc_url($signature_data['company_logo']); ?>" alt="Company Logo" class="signature-logo">
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// Beende die Ausführung, um zu verhindern, dass WordPress weiteren Content ausgibt
die();
?>