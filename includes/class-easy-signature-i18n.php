<?php
class Easy_Signature_i18n {

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'easy-signature',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}