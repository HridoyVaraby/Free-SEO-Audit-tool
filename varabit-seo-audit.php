<?php
/**
 * Plugin Name: Varabit SEO Audit
 * Plugin URI: https://varabit.com/
 * Description: A simple SEO audit tool that analyzes websites for common SEO issues and provides recommendations.
 * Version: 1.0.0
 * Author: Varabit
 * Author URI: https://varabit.com/
 * Text Domain: varabit-seo-audit
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('VARABIT_SEO_AUDIT_VERSION', '1.0.0');
define('VARABIT_SEO_AUDIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VARABIT_SEO_AUDIT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once VARABIT_SEO_AUDIT_PLUGIN_DIR . 'includes/class-varabit-seo-audit.php';

/**
 * Begins execution of the plugin.
 */
function run_varabit_seo_audit() {
    $plugin = new Varabit_SEO_Audit();
    $plugin->run();
}
run_varabit_seo_audit();