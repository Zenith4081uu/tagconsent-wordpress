<?php
defined('ABSPATH') || exit;

class TagConsent_Banner {
    private $api;

    public function __construct($api) {
        $this->api = $api;
    }

    public function init() {
        if (!is_admin() && get_option('tagconsent_banner_enabled', '1') === '1') {
            add_action('wp_head', array($this, 'inject_gcm_defaults'), 1);
            add_action('wp_head', array($this, 'inject_loader'), 2);
            add_action('wp_footer', array($this, 'inject_banner'), 99);
        }
        add_shortcode('tagconsent_preferences', array($this, 'preferences_shortcode'));
        add_shortcode('tagconsent_cookie_declaration', array($this, 'cookie_declaration_shortcode'));
    }

    public function inject_gcm_defaults() {
        if (get_option('tagconsent_gcm_enabled', '1') !== '1') return;

        $gcm = $this->api->get_gcm_config();
        if (!$gcm || empty($gcm['enabled'])) {
            echo "<!-- TagConsent: GCM defaults (deny-all) -->\n";
            echo "<script>\n";
            echo "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}\n";
            echo "gtag('consent','default',{'ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied','analytics_storage':'denied','functionality_storage':'granted','personalization_storage':'denied','security_storage':'granted','wait_for_update':500});\n";
            echo "</script>\n";
            return;
        }

        $defaults = $gcm['defaults'] ?? array();
        echo "<!-- TagConsent: Google Consent Mode v2 -->\n";
        echo "<script>\n";
        echo "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}\n";
        echo "gtag('consent','default',{\n";
        echo "  'ad_storage':'" . esc_js($defaults['ad_storage'] ?? 'denied') . "',\n";
        echo "  'ad_user_data':'" . esc_js($defaults['ad_user_data'] ?? 'denied') . "',\n";
        echo "  'ad_personalization':'" . esc_js($defaults['ad_personalization'] ?? 'denied') . "',\n";
        echo "  'analytics_storage':'" . esc_js($defaults['analytics_storage'] ?? 'denied') . "',\n";
        echo "  'functionality_storage':'" . esc_js($defaults['functionality_storage'] ?? 'granted') . "',\n";
        echo "  'personalization_storage':'" . esc_js($defaults['personalization_storage'] ?? 'denied') . "',\n";
        echo "  'security_storage':'" . esc_js($defaults['security_storage'] ?? 'granted') . "',\n";
        echo "  'wait_for_update':" . intval($gcm['wait_for_update'] ?? 500) . "\n";
        echo "});\n";
        if (!empty($gcm['url_passthrough'])) echo "gtag('set','url_passthrough',true);\n";
        if (!empty($gcm['ads_data_redaction'])) echo "gtag('set','ads_data_redaction',true);\n";
        echo "</script>\n";
    }

    public function inject_loader() {
        if (!$this->api->is_configured()) return;

        $endpoint = esc_url($this->api->get_endpoint());
        $api_key = esc_js($this->api->get_api_key());
        $domain_id = esc_js($this->api->get_domain_id());
        $position = esc_js(get_option('tagconsent_banner_position', 'bottom'));
        $theme = esc_js(get_option('tagconsent_banner_theme', 'light'));
        $auto_block = get_option('tagconsent_auto_block', '1') === '1' ? 'true' : 'false';

        echo "<!-- TagConsent DPDP Consent Management -->\n";
        echo "<script>\n";
        echo "window.tagconsentConfig={\n";
        echo "  apiKey:'{$api_key}',\n";
        echo "  endpoint:'{$endpoint}',\n";
        echo "  domainId:'{$domain_id}',\n";
        echo "  platform:'wordpress',\n";
        echo "  bannerPosition:'{$position}',\n";
        echo "  bannerTheme:'{$theme}',\n";
        echo "  autoBlock:{$auto_block},\n";
        echo "  scanCookies:" . (get_option('tagconsent_scan_cookies', '1') === '1' ? 'true' : 'false') . "\n";
        echo "};\n";
        echo "</script>\n";
        echo "<script src=\"{$endpoint}/api/dpdp/cms/loader.js\" async></script>\n";
    }

    public function inject_banner() {
        if (!$this->api->is_configured()) return;

        $domain_id = esc_attr($this->api->get_domain_id());
        $endpoint = esc_url($this->api->get_endpoint());
        echo "<div id=\"tagconsent-banner-root\" data-domain=\"{$domain_id}\" data-endpoint=\"{$endpoint}\"></div>\n";
    }

    public function preferences_shortcode($atts) {
        if (!$this->api->is_configured()) {
            return '<p>TagConsent is not configured. Please set up your API key in Settings &gt; TagConsent.</p>';
        }
        $domain_id = esc_attr($this->api->get_domain_id());
        $endpoint = esc_url($this->api->get_endpoint());
        return '<div id="tagconsent-preferences" data-domain="' . $domain_id . '" data-endpoint="' . $endpoint . '"></div>'
            . '<script src="' . $endpoint . '/api/dpdp/prefcentre/embed/' . $domain_id . '" async></script>';
    }

    public function cookie_declaration_shortcode($atts) {
        if (!$this->api->is_configured()) {
            return '<p>TagConsent is not configured.</p>';
        }
        $domain_id = esc_attr($this->api->get_domain_id());
        $endpoint = esc_url($this->api->get_endpoint());
        return '<div id="tagconsent-cookie-declaration" data-domain="' . $domain_id . '" data-endpoint="' . $endpoint . '"></div>';
    }
}
