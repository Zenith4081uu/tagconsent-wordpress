<?php
defined('ABSPATH') || exit;

class TagConsent_Admin {
    private $api;

    public function __construct($api) {
        $this->api = $api;
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_filter('plugin_action_links_tagconsent/tagconsent.php', array($this, 'settings_link'));
    }

    public function settings_link($links) {
        $url = admin_url('options-general.php?page=tagconsent');
        array_unshift($links, '<a href="' . esc_url($url) . '">Settings</a>');
        return $links;
    }

    public function add_menu() {
        add_menu_page(
            'TagConsent',
            'TagConsent',
            'manage_options',
            'tagconsent',
            array($this, 'render_dashboard'),
            'dashicons-shield',
            80
        );
        add_submenu_page('tagconsent', 'Dashboard', 'Dashboard', 'manage_options', 'tagconsent', array($this, 'render_dashboard'));
        add_submenu_page('tagconsent', 'Settings', 'Settings', 'manage_options', 'tagconsent-settings', array($this, 'render_settings'));
        add_submenu_page('tagconsent', 'Banner', 'Banner Config', 'manage_options', 'tagconsent-banner', array($this, 'render_banner'));
        add_submenu_page('tagconsent', 'Consent Log', 'Consent Log', 'manage_options', 'tagconsent-logs', array($this, 'render_logs'));
    }

    public function register_settings() {
        register_setting('tagconsent_settings', 'tagconsent_api_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('tagconsent_settings', 'tagconsent_domain_id', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('tagconsent_settings', 'tagconsent_endpoint', array('sanitize_callback' => 'esc_url_raw'));
        register_setting('tagconsent_settings', 'tagconsent_banner_enabled', array('sanitize_callback' => 'absint'));
        register_setting('tagconsent_settings', 'tagconsent_gcm_enabled', array('sanitize_callback' => 'absint'));
        register_setting('tagconsent_settings', 'tagconsent_auto_block', array('sanitize_callback' => 'absint'));
        register_setting('tagconsent_settings', 'tagconsent_banner_position', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('tagconsent_settings', 'tagconsent_banner_theme', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('tagconsent_settings', 'tagconsent_scan_cookies', array('sanitize_callback' => 'absint'));
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'tagconsent') === false) return;
        wp_enqueue_style('tagconsent-admin', TAGCONSENT_PLUGIN_URL . 'admin/css/admin.css', array(), TAGCONSENT_VERSION);
        wp_enqueue_script('tagconsent-admin', TAGCONSENT_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), TAGCONSENT_VERSION, true);
        wp_localize_script('tagconsent-admin', 'tagconsentAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tagconsent_nonce'),
            'endpoint' => get_option('tagconsent_endpoint', TAGCONSENT_API_BASE),
            'domainId' => get_option('tagconsent_domain_id', ''),
        ));
    }

    public function render_dashboard() {
        $configured = $this->api->is_configured();
        $last_sync = get_option('tagconsent_last_sync', '');
        ?>
        <div class="wrap tagconsent-wrap">
            <h1><span class="dashicons dashicons-shield" style="font-size:28px;margin-right:8px"></span> TagConsent Dashboard</h1>

            <?php if (!$configured): ?>
            <div class="notice notice-warning">
                <p><strong>Setup required:</strong> Enter your API Key and Domain ID in <a href="<?php echo esc_url(admin_url('admin-menu.php?page=tagconsent-settings')); ?>">Settings</a> to activate TagConsent.</p>
            </div>
            <?php endif; ?>

            <div class="tagconsent-cards">
                <div class="tagconsent-card">
                    <h3>Connection Status</h3>
                    <div id="tc-connection-status">
                        <?php if ($configured): ?>
                            <span class="tc-status tc-status-ok">Connected</span>
                            <?php if ($last_sync): ?>
                                <p class="description">Last sync: <?php echo esc_html($last_sync); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="tc-status tc-status-error">Not Configured</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($configured): ?>
                        <button class="button" id="tc-btn-sync" onclick="tagconsentSync()">Sync Now</button>
                        <button class="button" id="tc-btn-test" onclick="tagconsentTest()">Test Connection</button>
                    <?php endif; ?>
                </div>

                <div class="tagconsent-card">
                    <h3>Banner Status</h3>
                    <p>
                        <strong>Enabled:</strong> <?php echo get_option('tagconsent_banner_enabled', '1') === '1' ? '&#x2705; Yes' : '&#x274c; No'; ?><br>
                        <strong>Position:</strong> <?php echo esc_html(ucfirst(get_option('tagconsent_banner_position', 'bottom'))); ?><br>
                        <strong>Theme:</strong> <?php echo esc_html(ucfirst(get_option('tagconsent_banner_theme', 'light'))); ?><br>
                        <strong>Auto-block cookies:</strong> <?php echo get_option('tagconsent_auto_block', '1') === '1' ? 'Yes' : 'No'; ?>
                    </p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tagconsent-banner')); ?>" class="button">Configure Banner</a>
                </div>

                <div class="tagconsent-card">
                    <h3>Google Consent Mode v2</h3>
                    <p>
                        <strong>GCM Enabled:</strong> <?php echo get_option('tagconsent_gcm_enabled', '1') === '1' ? '&#x2705; Yes' : '&#x274c; No'; ?><br>
                        <strong>Default:</strong> Deny all (until consent)
                    </p>
                    <p class="description">GCM v2 defaults are injected before Google Tag Manager, ensuring compliance with DPDP Act §6 (Consent).</p>
                </div>

                <div class="tagconsent-card">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo esc_url(get_option('tagconsent_endpoint', TAGCONSENT_API_BASE)); ?>/app" target="_blank">Open TagConsent Dashboard &#x2197;</a></li>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=tagconsent-settings')); ?>">Plugin Settings</a></li>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=tagconsent-logs')); ?>">View Consent Logs</a></li>
                    </ul>
                    <h4>Shortcodes</h4>
                    <code>[tagconsent_preferences]</code> — Preference Centre<br>
                    <code>[tagconsent_cookie_declaration]</code> — Cookie Declaration
                </div>
            </div>

            <div class="tagconsent-card tagconsent-card-wide">
                <h3>DPDP Act 2023 Compliance Summary</h3>
                <table class="widefat">
                    <thead><tr><th>Obligation</th><th>Section</th><th>Plugin Coverage</th></tr></thead>
                    <tbody>
                        <tr><td>Obtain informed consent before processing</td><td>§6</td><td>&#x2705; Cookie banner with granular categories</td></tr>
                        <tr><td>Provide notice with purpose of processing</td><td>§5</td><td>&#x2705; Configurable notice text in banner</td></tr>
                        <tr><td>Allow withdrawal of consent</td><td>§6(4)</td><td>&#x2705; Preference centre shortcode</td></tr>
                        <tr><td>Consent for children's data</td><td>§9</td><td>&#x2705; Age gate support via TagConsent dashboard</td></tr>
                        <tr><td>Cookie declaration / transparency</td><td>§5, §6</td><td>&#x2705; Cookie declaration shortcode</td></tr>
                        <tr><td>Google Consent Mode v2</td><td>—</td><td>&#x2705; Automatic GCM defaults (deny-all)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public function render_settings() {
        ?>
        <div class="wrap tagconsent-wrap">
            <h1>TagConsent Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('tagconsent_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="tagconsent_api_key">API Key</label></th>
                        <td>
                            <input type="text" id="tagconsent_api_key" name="tagconsent_api_key" value="<?php echo esc_attr(get_option('tagconsent_api_key')); ?>" class="regular-text" placeholder="tc_cms_...">
                            <p class="description">Get your API key from <a href="<?php echo esc_url(get_option('tagconsent_endpoint', TAGCONSENT_API_BASE)); ?>/app" target="_blank">TagConsent Dashboard</a> → CMS Integrations → Create Installation.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tagconsent_domain_id">Domain ID</label></th>
                        <td>
                            <input type="text" id="tagconsent_domain_id" name="tagconsent_domain_id" value="<?php echo esc_attr(get_option('tagconsent_domain_id')); ?>" class="regular-text" placeholder="UUID from TagConsent">
                            <p class="description">Found in TagConsent Dashboard → Domains → your domain's ID.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tagconsent_endpoint">API Endpoint</label></th>
                        <td>
                            <input type="url" id="tagconsent_endpoint" name="tagconsent_endpoint" value="<?php echo esc_attr(get_option('tagconsent_endpoint', TAGCONSENT_API_BASE)); ?>" class="regular-text">
                            <p class="description">Default: https://tagconsent.com. Change only for self-hosted instances.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function render_banner() {
        ?>
        <div class="wrap tagconsent-wrap">
            <h1>Banner Configuration</h1>
            <form method="post" action="options.php">
                <?php settings_fields('tagconsent_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Banner</th>
                        <td><label><input type="checkbox" name="tagconsent_banner_enabled" value="1" <?php checked(get_option('tagconsent_banner_enabled', '1'), '1'); ?>> Show consent banner on the frontend</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Google Consent Mode v2</th>
                        <td><label><input type="checkbox" name="tagconsent_gcm_enabled" value="1" <?php checked(get_option('tagconsent_gcm_enabled', '1'), '1'); ?>> Inject GCM v2 consent defaults (deny-all until consent)</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Auto-block Cookies</th>
                        <td><label><input type="checkbox" name="tagconsent_auto_block" value="1" <?php checked(get_option('tagconsent_auto_block', '1'), '1'); ?>> Block non-essential cookies until consent is given</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Scan Cookies</th>
                        <td><label><input type="checkbox" name="tagconsent_scan_cookies" value="1" <?php checked(get_option('tagconsent_scan_cookies', '1'), '1'); ?>> Automatically scan and categorise cookies</label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tagconsent_banner_position">Banner Position</label></th>
                        <td>
                            <select name="tagconsent_banner_position" id="tagconsent_banner_position">
                                <option value="bottom" <?php selected(get_option('tagconsent_banner_position'), 'bottom'); ?>>Bottom</option>
                                <option value="top" <?php selected(get_option('tagconsent_banner_position'), 'top'); ?>>Top</option>
                                <option value="bottom-left" <?php selected(get_option('tagconsent_banner_position'), 'bottom-left'); ?>>Bottom Left (Popup)</option>
                                <option value="bottom-right" <?php selected(get_option('tagconsent_banner_position'), 'bottom-right'); ?>>Bottom Right (Popup)</option>
                                <option value="center" <?php selected(get_option('tagconsent_banner_position'), 'center'); ?>>Centre (Modal)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tagconsent_banner_theme">Banner Theme</label></th>
                        <td>
                            <select name="tagconsent_banner_theme" id="tagconsent_banner_theme">
                                <option value="light" <?php selected(get_option('tagconsent_banner_theme'), 'light'); ?>>Light</option>
                                <option value="dark" <?php selected(get_option('tagconsent_banner_theme'), 'dark'); ?>>Dark</option>
                                <option value="auto" <?php selected(get_option('tagconsent_banner_theme'), 'auto'); ?>>Auto (match site)</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Banner Settings'); ?>
            </form>
        </div>
        <?php
    }

    public function render_logs() {
        ?>
        <div class="wrap tagconsent-wrap">
            <h1>Consent Logs</h1>
            <p>View detailed consent logs and analytics in your <a href="<?php echo esc_url(get_option('tagconsent_endpoint', TAGCONSENT_API_BASE)); ?>/app" target="_blank">TagConsent Dashboard</a>.</p>
            <div id="tc-logs-iframe-wrap">
                <?php if ($this->api->is_configured()): ?>
                <p>Recent sync events will appear below after your first plugin sync.</p>
                <div id="tc-sync-events"></div>
                <?php else: ?>
                <div class="notice notice-warning"><p>Configure your API key first to see consent logs.</p></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
