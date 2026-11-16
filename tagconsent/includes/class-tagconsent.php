<?php
defined('ABSPATH') || exit;

class TagConsent {
    private $admin;
    private $banner;
    private $api;

    public function __construct() {
        $this->api = new TagConsent_API();
        $this->banner = new TagConsent_Banner($this->api);
        if (is_admin()) {
            $this->admin = new TagConsent_Admin($this->api);
        }
    }

    public function run() {
        if (is_admin()) {
            $this->admin->init();
        }
        $this->banner->init();
        add_action('wp_ajax_tagconsent_sync', array($this, 'ajax_sync'));
        add_action('wp_ajax_tagconsent_test_connection', array($this, 'ajax_test_connection'));
    }

    public function ajax_sync() {
        check_ajax_referer('tagconsent_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $result = $this->api->sync();
        wp_send_json($result);
    }

    public function ajax_test_connection() {
        check_ajax_referer('tagconsent_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $result = $this->api->test_connection();
        wp_send_json($result);
    }
}
