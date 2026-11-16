<?php
defined('ABSPATH') || exit;

class TagConsent_API {
    private $endpoint;
    private $api_key;

    public function __construct() {
        $this->endpoint = rtrim(get_option('tagconsent_endpoint', TAGCONSENT_API_BASE), '/');
        $this->api_key = get_option('tagconsent_api_key', '');
    }

    public function get_endpoint() {
        return $this->endpoint;
    }

    public function get_api_key() {
        return $this->api_key;
    }

    public function get_domain_id() {
        return get_option('tagconsent_domain_id', '');
    }

    public function is_configured() {
        return !empty($this->api_key) && !empty($this->get_domain_id());
    }

    public function sync() {
        if (empty($this->api_key)) {
            return array('success' => false, 'error' => 'API key not configured');
        }

        $response = wp_remote_post($this->endpoint . '/api/dpdp/cms/sync', array(
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-TC-API-Key' => $this->api_key,
            ),
            'body' => wp_json_encode(array(
                'plugin_version' => TAGCONSENT_VERSION,
                'cms_version' => get_bloginfo('version'),
                'platform' => 'wordpress',
                'site_url' => home_url(),
                'php_version' => phpversion(),
            )),
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && isset($body['status']) && $body['status'] === 'ok') {
            update_option('tagconsent_last_sync', current_time('mysql'));
            if (isset($body['settings'])) {
                update_option('tagconsent_remote_settings', $body['settings']);
            }
            return array('success' => true, 'settings' => $body['settings'] ?? null);
        }

        return array('success' => false, 'error' => $body['error'] ?? 'Sync failed (HTTP ' . $code . ')');
    }

    public function test_connection() {
        if (empty($this->api_key)) {
            return array('success' => false, 'error' => 'API key not configured');
        }
        return $this->sync();
    }

    public function get_gcm_config() {
        $domain_id = $this->get_domain_id();
        if (empty($domain_id)) return null;

        $cache_key = 'tagconsent_gcm_' . md5($domain_id);
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;

        $response = wp_remote_get($this->endpoint . '/api/dpdp/gcm/config/' . $domain_id, array(
            'timeout' => 10,
        ));

        if (is_wp_error($response)) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if ($body && isset($body['enabled'])) {
            set_transient($cache_key, $body, 300);
            return $body;
        }
        return null;
    }

    public function get_preference_config() {
        $domain_id = $this->get_domain_id();
        if (empty($domain_id)) return null;

        $cache_key = 'tagconsent_pref_' . md5($domain_id);
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;

        $response = wp_remote_get($this->endpoint . '/api/dpdp/prefcentre/config/' . $domain_id, array(
            'timeout' => 10,
        ));

        if (is_wp_error($response)) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if ($body) {
            set_transient($cache_key, $body, 300);
            return $body;
        }
        return null;
    }
}
