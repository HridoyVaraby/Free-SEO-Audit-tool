<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_SEO_Audit_Admin {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register the administration menu for this plugin.
     */
    public function add_plugin_admin_menu() {
        add_options_page(
            'Varabit SEO Audit Settings',
            'Varabit SEO Audit',
            'manage_options',
            'varabit-seo-audit',
            array($this, 'display_plugin_admin_page')
        );
    }

    /**
     * Register settings for the plugin.
     */
    public function register_settings() {
        register_setting(
            'varabit_seo_audit_settings',
            'varabit_seo_audit_pagespeed_api_key',
            array('sanitize_callback' => 'sanitize_text_field')
        );
        
        register_setting(
            'varabit_seo_audit_settings',
            'varabit_seo_audit_newsletter_shortcode',
            array('sanitize_callback' => 'sanitize_text_field')
        );
    }

    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_admin_page() {
        ?>
        <div class="wrap">
            <h2>Varabit SEO Audit Settings</h2>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('varabit_seo_audit_settings');
                do_settings_sections('varabit_seo_audit_settings');
                ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Google PageSpeed Insights API Key</th>
                        <td>
                            <input type="text" name="varabit_seo_audit_pagespeed_api_key" value="<?php echo esc_attr(get_option('varabit_seo_audit_pagespeed_api_key')); ?>" class="regular-text" />
                            <p class="description">Enter your Google PageSpeed Insights API key. You can get one from the <a href="https://developers.google.com/speed/docs/insights/v5/get-started" target="_blank">Google Developers Console</a>.</p>
                        </td>
                    </tr>
                </table>
                
                <h3>Newsletter Form Settings</h3>
                <p>Add your newsletter form shortcode below (e.g. [wpforms id="138"]). This will be displayed in the audit results.</p>
                <table class="form-table">
                    <tr>
                        <th scope="row">Newsletter Form Shortcode</th>
                        <td>
                            <input type="text" name="varabit_seo_audit_newsletter_shortcode" value="<?php echo esc_attr(get_option('varabit_seo_audit_newsletter_shortcode')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            <?php
            if (isset($_POST['varabit_seo_audit_newsletter_shortcode'])) {
                $shortcode = trim($_POST['varabit_seo_audit_newsletter_shortcode']);
                if (!empty($shortcode)) {
                    update_option('varabit_seo_audit_newsletter_shortcode', sanitize_text_field($shortcode));
                } else {
                    delete_option('varabit_seo_audit_newsletter_shortcode');
                }
            }
            ?>
            <div class="varabit-seo-audit-info">
                <h3>How to Use the SEO Audit Tool</h3>
                <p>Use the shortcode <code>[varabit_seo_audit]</code> to display the SEO Audit tool on any page or post.</p>
                <p>You can customize the title by using the title attribute: <code>[varabit_seo_audit title="My Custom Title"]</code></p>
                
                <h3>About the API Key</h3>
                <p>While the Google PageSpeed Insights API can be used without an API key, there are rate limits. For production use, we recommend obtaining an API key.</p>
                
                <h3>Features</h3>
                <ul>
                    <li>Page Speed Analysis (Desktop & Mobile)</li>
                    <li>Meta Tags Analysis</li>
                    <li>Headings Structure Analysis</li>
                    <li>Image Alt Text Analysis</li>
                    <li>Mobile-Friendliness Check</li>
                    <li>Keyword Analysis</li>
                    <li>Downloadable PDF Reports</li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Get the Google PageSpeed Insights API key.
     *
     * @return string The API key.
     */
    public static function get_pagespeed_api_key() {
        return get_option('varabit_seo_audit_pagespeed_api_key', '');
    }
}

// Initialize the admin class
$varabit_seo_audit_admin = new Varabit_SEO_Audit_Admin();