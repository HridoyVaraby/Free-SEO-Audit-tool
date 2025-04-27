<?php
/**
 * The main plugin class.
 *
 * @since      1.0.0
 */
class Varabit_SEO_Audit {

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Include the class responsible for defining the shortcode
        require_once VARABIT_SEO_AUDIT_PLUGIN_DIR . 'public/class-varabit-seo-audit-shortcode.php';
        
        // Include the class responsible for API calls
        require_once VARABIT_SEO_AUDIT_PLUGIN_DIR . 'includes/class-varabit-seo-audit-api.php';
        
        // Include admin functions
        require_once VARABIT_SEO_AUDIT_PLUGIN_DIR . 'admin/class-varabit-seo-audit-admin.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        // Enqueue public scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        
        // Register shortcode
        $shortcode = new Varabit_SEO_Audit_Shortcode();
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style('varabit-seo-audit-admin', VARABIT_SEO_AUDIT_PLUGIN_URL . 'admin/css/varabit-seo-audit-admin.css', array(), VARABIT_SEO_AUDIT_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_script('varabit-seo-audit-admin', VARABIT_SEO_AUDIT_PLUGIN_URL . 'admin/js/varabit-seo-audit-admin.js', array('jquery'), VARABIT_SEO_AUDIT_VERSION, false);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_public_styles() {
        wp_enqueue_style('varabit-seo-audit-public', VARABIT_SEO_AUDIT_PLUGIN_URL . 'public/css/varabit-seo-audit-public.css', array(), VARABIT_SEO_AUDIT_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_public_scripts() {
        wp_enqueue_script('varabit-seo-audit-public', VARABIT_SEO_AUDIT_PLUGIN_URL . 'public/js/varabit-seo-audit-public.js', array('jquery'), VARABIT_SEO_AUDIT_VERSION, false);
        
        // Localize the script with new data
        wp_localize_script('varabit-seo-audit-public', 'varabit_seo_audit', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('varabit_seo_audit_nonce'),
        ));
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Nothing to do here for now
    }
}