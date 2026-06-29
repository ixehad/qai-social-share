<?php
/**
 * Plugin Name:       Qai Social Share
 * Plugin URI:        https://github.com/ixehad/qai-social-share
 * Description:       Adds "Summarize with AI" buttons (ChatGPT, Claude, Perplexity, Grok, Google) and social share buttons (Facebook, X, WhatsApp, LinkedIn, Telegram, Pinterest) to blog posts. Fully configurable — no coding required.
 * Version:           1.2.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            Jehadul Islam
 * Author URI:        https://github.com/ixehad
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qai-social-share
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access.
}

define( 'KAS_VERSION', '1.2.0' );
define( 'KAS_PATH', plugin_dir_path( __FILE__ ) );
define( 'KAS_URL', plugin_dir_url( __FILE__ ) );

require_once KAS_PATH . 'includes/class-kas-settings.php';
require_once KAS_PATH . 'includes/class-kas-render.php';
require_once KAS_PATH . 'includes/class-kas-loader.php';

/**
 * Boot the plugin.
 */
function kas_init_plugin() {
    KAS_Settings::instance();
    KAS_Loader::instance();
}
add_action( 'plugins_loaded', 'kas_init_plugin' );

/**
 * Default options set on activation (only if not already present,
 * so re-activating, or upgrading from an earlier version of this
 * plugin, never wipes a site's existing configuration).
 */
function kas_activate_plugin() {
    if ( false === get_option( 'kas_settings' ) ) {
        update_option( 'kas_settings', KAS_Settings::defaults() );
    }
}
register_activation_hook( __FILE__, 'kas_activate_plugin' );
