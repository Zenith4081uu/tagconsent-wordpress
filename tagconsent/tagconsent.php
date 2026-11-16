<?php
/**
 * Plugin Name: TagConsent - DPDP Consent Management
 * Plugin URI: https://tagconsent.com
 * Description: India's Digital Personal Data Protection (DPDP) Act 2023 compliant consent management platform. Cookie consent banner, Google Consent Mode v2, preference centre, consent logging, and compliance dashboard — all integrated with your TagConsent account.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: TagConsent / TecstaqOps
 * Author URI: https://tagconsent.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tagconsent
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

define('TAGCONSENT_VERSION', '1.0.0');
define('TAGCONSENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TAGCONSENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TAGCONSENT_API_BASE', 'https://tagconsent.com');

require_once TAGCONSENT_PLUGIN_DIR . 'includes/class-tagconsent.php';
require_once TAGCONSENT_PLUGIN_DIR . 'includes/class-tagconsent-api.php';
require_once TAGCONSENT_PLUGIN_DIR . 'includes/class-tagconsent-banner.php';
require_once TAGCONSENT_PLUGIN_DIR . 'admin/class-tagconsent-admin.php';

function tagconsent_init() {
    $plugin = new TagConsent();
    $plugin->run();
}
add_action('plugins_loaded', 'tagconsent_init');

register_activation_hook(__FILE__, 'tagconsent_activate');
register_deactivation_hook(__FILE__, 'tagconsent_deactivate');

function tagconsent_activate() {
    add_option('tagconsent_api_key', '');
    add_option('tagconsent_domain_id', '');
    add_option('tagconsent_endpoint', TAGCONSENT_API_BASE);
    add_option('tagconsent_banner_enabled', '1');
    add_option('tagconsent_gcm_enabled', '1');
    add_option('tagconsent_auto_block', '1');
    add_option('tagconsent_banner_position', 'bottom');
    add_option('tagconsent_banner_theme', 'light');
    add_option('tagconsent_scan_cookies', '1');
    add_option('tagconsent_version', TAGCONSENT_VERSION);
    flush_rewrite_rules();
}

function tagconsent_deactivate() {
    flush_rewrite_rules();
}
