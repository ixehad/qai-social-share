<?php
/**
 * Plugin Name: Qai Social Share
 * Plugin URI:  https://kahfkids.com
 * Description: Adds "Summarize with AI" buttons (ChatGPT, Claude, Perplexity, Grok, Google AI Mode) and social share buttons (Facebook, X, WhatsApp, LinkedIn, Telegram, Pinterest) to blog posts. Fully configurable from the Qai Social Share dashboard menu.
 * Version:     1.1.0
 * Author:      KahfKids
 * Text Domain: qai-social-share
 * License:     GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access.
}

define( 'KAS_VERSION', '1.1.0' );
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
