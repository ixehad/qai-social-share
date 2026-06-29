<?php
/**
 * Settings: two-tab admin UI (AI Summarize + Social Share).
 * Option key stays "kas_settings" so existing saves carry forward.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class KAS_Settings {

    private static $instance = null;
    private $page_hook        = '';
    private $ai_hook          = '';
    private $social_hook      = '';

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu',            array( $this, 'add_menu_pages' ) );
        add_action( 'admin_init',            array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /* -----------------------------------------------------------------------
     * Network definitions
     * -------------------------------------------------------------------- */

    public static function get_network_definitions() {
        return array(
            // AI tools
            'chatgpt'    => array( 'group' => 'ai',     'label' => 'ChatGPT',    'color' => '#74AA9C', 'url_pattern' => 'https://chatgpt.com/?prompt={prompt}',                              'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.032.067L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0L4.3 14.11A4.5 4.5 0 0 1 2.34 7.896zm16.597 3.855l-5.843-3.387L15.114 7.2a.076.076 0 0 1 .071 0l4.518 2.606a4.504 4.504 0 0 1-.676 8.137v-5.678a.79.79 0 0 0-.39-.514zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.512-2.602a4.495 4.495 0 0 1 6.59 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.495 4.495 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>' ),
            'claude'     => array( 'group' => 'ai',     'label' => 'Claude',     'color' => '#DA7756', 'url_pattern' => 'https://claude.ai/new?q={prompt}',                                   'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.709 15.955l4.72-2.647.08-.23-.08-.128H9.2l-.79-.048-2.698-.031-2.339-.047-1.089-.048L2 12.409l.067-.416.398-.215 1.912.017 2.279.065 2.007.048.624.033-.014-.192-.624-.784-1.84-2.36-1.317-1.71-.78-1.018-.338-.705.148-.476.494-.191.68.305.783.773.897 1.486 1.762 1.52 1.762.726.883.096-.032.065-.128-.048-.226L8.787 7.7l-.495-2.247-.24-1.297-.062-.878.51-.575.608-.117.435.133.28.378.117.592.24 1.519.378 2.185.238 1.405.117.544.128-.017.096-.16.034-.483.31-2.438.166-1.244.148-.926.31-.69.592-.29.623.205.31.605-.055.64-.28 1.488-.48 2.37-.207 1.242.033.017.145-.13.354-.43 1.08-1.306 1.293-1.54.94-1.165.447-.48.74-.178.524.338.07.686-.32.534-.908 1.005-1.434 1.502-.81.877-.432.57.048.08.176.065h.065l1.261-.37 2.627-.626 1.664-.367.802-.096.737.303.19.606-.27.544-.649.222-1.695.338-2.624.545-1.34.32-.16.096.033.145.49.576 1.44 1.512.9 1.04.43.686-.19.703-.51.336-.727-.16-.61-.48-.932-1.01-1.166-1.128-.98-.91-.48-.56h-.097l-.08.048-.048.096.016.162.29.85.606 2.006.398 1.553.16.877-.19.625-.51.35-.608-.128-.398-.43-.24-.768-.35-1.552-.51-2.022-.274-1.152-.08-.256h-.064l-.1.14-.932 1.375-1.2 1.664-.88 1.12-.72.784-.672.415-.768-.19-.366-.51.14-.703.495-.672.768-.975 1.232-1.712.704-1.04.607-.975.095-.19-.063-.098z"/></svg>' ),
            'perplexity' => array( 'group' => 'ai',     'label' => 'Perplexity', 'color' => '#20B8CD', 'url_pattern' => 'https://www.perplexity.ai/search/new?q={prompt}',                    'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.197 11.207l-4.012-3.86V3.242L13.438 7.91l-4.438-4.27v4.033L4.937 11.8l3.925 3.887v1.033l.677-.643v-3.683l2.9 2.87v3.553l.65.617.65-.617v-3.55l2.9-2.874v3.614l.677.643v-1.067l3.88-3.82zm-4.012 2.47v-4.043l1.882-1.903-1.882 1.814v-.627l-1.688-1.625 1.688 1.625v3.803l-1.688 1.687 1.688-1.731zM12 8.19L9.36 10.77 12 13.394l2.64-2.623L12 8.19zm-5.197 5.487v-3.743L5.113 11.8l1.69 1.877zm5.197 5.04l-2.84-2.693v-2.31L12 16.33l2.84-2.617v2.31L12 18.716z"/></svg>' ),
            'grok'       => array( 'group' => 'ai',     'label' => 'Grok',       'color' => '#1a1a1a', 'url_pattern' => 'https://x.com/i/grok?text={prompt}',                                  'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>' ),
            'google'     => array( 'group' => 'ai',     'label' => 'Google',     'color' => '#4285F4', 'url_pattern' => 'https://www.google.com/search?udm=50&q={prompt}',                    'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>' ),
            // Social networks
            'facebook'   => array( 'group' => 'social', 'label' => 'Facebook',   'color' => '#1877F2', 'url_pattern' => 'https://www.facebook.com/sharer/sharer.php?u={url}',                 'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>' ),
            'twitter'    => array( 'group' => 'social', 'label' => 'X',          'color' => '#000000', 'url_pattern' => 'https://twitter.com/intent/tweet?url={url}&text={title}',            'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>' ),
            'whatsapp'   => array( 'group' => 'social', 'label' => 'WhatsApp',   'color' => '#25D366', 'url_pattern' => 'https://api.whatsapp.com/send?text={title}%20{url}',                  'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>' ),
            'linkedin'   => array( 'group' => 'social', 'label' => 'LinkedIn',   'color' => '#0A66C2', 'url_pattern' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}',           'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>' ),
            'telegram'   => array( 'group' => 'social', 'label' => 'Telegram',   'color' => '#26A5E4', 'url_pattern' => 'https://t.me/share/url?url={url}&text={title}',                       'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>' ),
            'pinterest'  => array( 'group' => 'social', 'label' => 'Pinterest',  'color' => '#E60023', 'url_pattern' => 'https://pinterest.com/pin/create/button/?url={url}&description={title}', 'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>' ),
        );
    }

    /* -----------------------------------------------------------------------
     * Defaults
     * -------------------------------------------------------------------- */

    public static function defaults() {
        // Default ON: ChatGPT, Claude, Perplexity, Google (AI) + Facebook, X, WhatsApp, LinkedIn (Social)
        // Everything else defaults to OFF so new installs aren't overwhelming
        $default_on = array( 'chatgpt', 'claude', 'perplexity', 'google', 'facebook', 'twitter', 'whatsapp', 'linkedin' );
        $networks   = array();
        foreach ( self::get_network_definitions() as $key => $def ) {
            $networks[ $key ] = in_array( $key, $default_on, true ) ? 1 : 0;
        }
        return array(
            'enabled_networks'     => $networks,
            // AI row
            'ai_enabled'           => 1,
            'ai_position'          => 'before_content',
            'ai_button_style'      => 'text',        // text | icon | icon_text
            'ai_label'             => 'Summarize with',
            'ai_prompt_template'   => 'Analyze and summarize the key insights from {url}',
            // Social row
            'social_enabled'       => 1,
            'social_position'      => 'after_content',
            'social_button_style'  => 'icon_text',   // text | icon | icon_text
            'social_label'         => 'Share this article',
            'show_copy_link'       => 1,
            // Shared
            'open_new_tab'         => 1,
            'button_shape'         => 'pill',        // pill | rounded | square
        );
    }

    /* -----------------------------------------------------------------------
     * get_settings: safe merge with type guards
     * -------------------------------------------------------------------- */

    public function get_settings() {
        $saved    = get_option( 'kas_settings', array() );
        $defaults = self::defaults();

        if ( ! is_array( $saved ) ) return $defaults;

        $merged = wp_parse_args( $saved, $defaults );

        if ( isset( $saved['enabled_networks'] ) && is_array( $saved['enabled_networks'] ) ) {
            $merged['enabled_networks'] = wp_parse_args( $saved['enabled_networks'], $defaults['enabled_networks'] );
        } else {
            $merged['enabled_networks'] = $defaults['enabled_networks'];
        }

        $string_keys = array( 'ai_position', 'ai_button_style', 'ai_label', 'ai_prompt_template',
                              'social_position', 'social_button_style', 'social_label', 'button_shape' );
        foreach ( $string_keys as $k ) {
            if ( ! is_string( $merged[ $k ] ) ) $merged[ $k ] = $defaults[ $k ];
        }

        return $merged;
    }

    /* -----------------------------------------------------------------------
     * Admin menu
     * -------------------------------------------------------------------- */

    public function add_menu_pages() {
        // Parent menu (no content of its own — redirects to AI tab)
        $this->page_hook = add_menu_page(
            'Qai Social Share',
            'Qai Social Share',
            'manage_options',
            'qai-social-share',
            array( $this, 'render_ai_page' ),
            'dashicons-share',
            30
        );

        add_submenu_page(
            'qai-social-share',
            'AI Summarize — Qai Social Share',
            'AI Summarize',
            'manage_options',
            'qai-social-share',
            array( $this, 'render_ai_page' )
        );

        $this->social_hook = add_submenu_page(
            'qai-social-share',
            'Social Share — Qai Social Share',
            'Social Share',
            'manage_options',
            'qai-social-share-social',
            array( $this, 'render_social_page' )
        );
    }

    public function register_settings() {
        register_setting( 'qai_ai_group',     'kas_settings', array( $this, 'sanitize' ) );
        register_setting( 'qai_social_group', 'kas_settings', array( $this, 'sanitize' ) );
    }

    public function enqueue_admin_assets( $hook ) {
        $our_hooks = array( $this->page_hook, $this->social_hook );
        if ( ! in_array( $hook, $our_hooks, true ) ) return;
        wp_enqueue_style(  'kas-admin', KAS_URL . 'assets/admin.css',  array(), KAS_VERSION );
        wp_enqueue_style(  'kas-front', KAS_URL . 'assets/front.css',  array(), KAS_VERSION );
        wp_enqueue_script( 'kas-admin', KAS_URL . 'assets/admin.js',   array(), KAS_VERSION, true );
    }

    /* -----------------------------------------------------------------------
     * Sanitize
     * -------------------------------------------------------------------- */

    public function sanitize( $input ) {
        // Merge with existing saved settings so submitting one tab
        // doesn't wipe the other tab's values.
        $existing = get_option( 'kas_settings', array() );
        if ( ! is_array( $existing ) ) $existing = array();
        $input = is_array( $input ) ? $input : array();
        $merged = array_merge( $existing, $input );

        $d = self::defaults();
        $clean = array();

        // Networks
        $clean['enabled_networks'] = array();
        foreach ( self::get_network_definitions() as $key => $def ) {
            $clean['enabled_networks'][ $key ] = ! empty( $merged['enabled_networks'][ $key ] ) ? 1 : 0;
        }

        // AI
        $clean['ai_enabled']         = ! empty( $merged['ai_enabled'] ) ? 1 : 0;
        $clean['ai_label']           = sanitize_text_field( isset( $merged['ai_label'] ) ? $merged['ai_label'] : $d['ai_label'] );
        $clean['ai_prompt_template'] = sanitize_text_field( isset( $merged['ai_prompt_template'] ) ? $merged['ai_prompt_template'] : $d['ai_prompt_template'] );
        if ( false === strpos( $clean['ai_prompt_template'], '{url}' ) ) {
            $clean['ai_prompt_template'] .= ' {url}';
        }
        $ai_pos_input             = isset( $merged['ai_position'] ) ? $merged['ai_position'] : '';
        $clean['ai_position']     = in_array( $ai_pos_input, array( 'before_content', 'after_content', 'after_meta', 'shortcode_only' ), true ) ? $ai_pos_input : $d['ai_position'];
        $ai_style_input           = isset( $merged['ai_button_style'] ) ? $merged['ai_button_style'] : '';
        $clean['ai_button_style'] = in_array( $ai_style_input, array( 'text', 'icon', 'icon_text' ), true ) ? $ai_style_input : $d['ai_button_style'];

        // Social
        $clean['social_enabled']      = ! empty( $merged['social_enabled'] ) ? 1 : 0;
        $clean['social_label']        = sanitize_text_field( isset( $merged['social_label'] ) ? $merged['social_label'] : $d['social_label'] );
        $social_pos_input             = isset( $merged['social_position'] ) ? $merged['social_position'] : '';
        $clean['social_position']     = in_array( $social_pos_input, array( 'before_content', 'after_content', 'after_meta', 'shortcode_only' ), true ) ? $social_pos_input : $d['social_position'];
        $social_style_input           = isset( $merged['social_button_style'] ) ? $merged['social_button_style'] : '';
        $clean['social_button_style'] = in_array( $social_style_input, array( 'text', 'icon', 'icon_text' ), true ) ? $social_style_input : $d['social_button_style'];
        $clean['show_copy_link']      = ! empty( $merged['show_copy_link'] ) ? 1 : 0;

        // Shared
        $clean['open_new_tab']  = ! empty( $merged['open_new_tab'] ) ? 1 : 0;
        $shape_input            = isset( $merged['button_shape'] ) ? $merged['button_shape'] : '';
        $clean['button_shape']  = in_array( $shape_input, array( 'pill', 'rounded', 'square' ), true ) ? $shape_input : $d['button_shape'];

        return $clean;
    }

    /* -----------------------------------------------------------------------
     * Shared page header
     * -------------------------------------------------------------------- */

    private function page_header( $active_tab ) {
        // settings_errors() is the WP-approved way to show save confirmations
        // after options.php processes the form. No direct $_GET access needed.
        $saved = isset( $_GET['settings-updated'] ) && '1' === $_GET['settings-updated']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag set by WP options.php after nonce-verified save
        ?>
        <div class="wrap kas-settings-wrap">
        <h1><span class="dashicons dashicons-share kas-title-icon"></span> Qai Social Share</h1>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>
        <?php endif; ?>

        <nav class="nav-tab-wrapper kas-tabs">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=qai-social-share' ) ); ?>"
               class="nav-tab <?php echo 'ai' === $active_tab ? 'nav-tab-active' : ''; ?>">
                🤖 AI Summarize
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=qai-social-share-social' ) ); ?>"
               class="nav-tab <?php echo 'social' === $active_tab ? 'nav-tab-active' : ''; ?>">
                📣 Social Share
            </a>
        </nav>
        <?php
    }

    private function page_footer() {
        echo '</div><!-- .kas-settings-wrap -->';
    }

    private function position_field( $name, $current_value, $group ) {
        $options = array(
            'before_content' => 'Before post content',
            'after_meta'     => 'Right after post title / meta (top of post)',
            'after_content'  => 'After post content (bottom of post)',
            'shortcode_only' => 'Don\'t auto-insert — I\'ll place it manually with a shortcode',
        );
        echo '<select name="kas_settings[' . esc_attr( $name ) . ']">';
        foreach ( $options as $val => $label ) {
            echo '<option value="' . esc_attr( $val ) . '"' . selected( $current_value, $val, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    private function button_style_field( $name, $current_value, $context = 'social' ) {
        if ( 'ai' === $context ) {
            $options = array(
                'text'      => 'Text only — e.g. "ChatGPT"',
                'icon'      => 'Icon only — compact, no label',
                'icon_text' => 'Icon + Text — shows both',
            );
        } else {
            $options = array(
                'text'      => 'Text only — e.g. "Facebook"',
                'icon'      => 'Icon only — compact, no label',
                'icon_text' => 'Icon + Text — shows both',
            );
        }
        echo '<select name="kas_settings[' . esc_attr( $name ) . ']">';
        foreach ( $options as $val => $label ) {
            echo '<option value="' . esc_attr( $val ) . '"' . selected( $current_value, $val, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    private function shortcode_notice( $shortcode ) {
        ?>
        <div class="kas-notice kas-notice-warn">
            <strong>⚠️ Avoid duplicates:</strong>
            If this row is set to auto-insert (any option above except "shortcode only"), do <strong>not</strong> also paste
            <code><?php echo esc_html( $shortcode ); ?></code> into the same post.
            Using both at once will show the buttons twice. Pick one method per post.
        </div>
        <?php
    }

    /* -----------------------------------------------------------------------
     * AI Summarize settings page
     * -------------------------------------------------------------------- */

    public function render_ai_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $s = $this->get_settings();
        $networks = self::get_network_definitions();
        $this->page_header( 'ai' );
        ?>

        <!-- Live preview -->
        <div class="kas-preview-box">
            <h3 style="margin:0 0 8px;">Live Preview</h3>
            <p class="description" style="margin-bottom:12px;">Updates when you change options on this page.</p>
            <div id="kas-preview-ai"><?php
                $preview_settings = $s;
                $preview_settings['social_enabled'] = 0; // AI tab: show AI row only
                echo wp_kses( KAS_Render::preview_rows( $preview_settings ), KAS_Render::preview_kses() );
            ?></div>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields( 'qai_ai_group' ); ?>

            <!-- Enable/disable -->
            <h2 class="kas-section-title">AI Summarize Row</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th>Enable AI Summarize</th>
                    <td>
                        <label class="kas-toggle-label">
                            <input type="checkbox" name="kas_settings[ai_enabled]" value="1" id="kas-ai-enabled" <?php checked( $s['ai_enabled'], 1 ); ?> />
                            <span>Show the "Summarize with" row on every post</span>
                        </label>
                        <p class="description">Turn this off if you don't want AI summarize buttons anywhere on your site.</p>
                    </td>
                </tr>
            </table>

            <div id="kas-ai-fields" <?php echo $s['ai_enabled'] ? '' : 'style="opacity:.45;pointer-events:none;"'; ?>>

                <!-- Position -->
                <h2 class="kas-section-title">Placement</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th>Position on post</th>
                        <td>
                            <?php $this->position_field( 'ai_position', $s['ai_position'], 'ai' ); ?>
                            <p class="description">
                                Choose where the AI Summarize row appears on each post.<br>
                                "Before post content" is the default — readers see it before they start reading, which is ideal for summarizing.
                            </p>
                            <?php $this->shortcode_notice( '[qai_ai_buttons]' ); ?>
                        </td>
                    </tr>
                </table>

                <!-- Appearance -->
                <h2 class="kas-section-title">Appearance</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th>Button style</th>
                        <td>
                            <?php $this->button_style_field( 'ai_button_style', $s['ai_button_style'], 'ai' ); ?>
                            <p class="description">AI tools are best with text labels — most people don't recognise AI logos at a glance.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Button shape</th>
                        <td>
                            <select name="kas_settings[button_shape]">
                                <option value="pill"    <?php selected( $s['button_shape'], 'pill' ); ?>>Pill (fully rounded)</option>
                                <option value="rounded" <?php selected( $s['button_shape'], 'rounded' ); ?>>Rounded corners</option>
                                <option value="square"  <?php selected( $s['button_shape'], 'square' ); ?>>Square corners</option>
                            </select>
                            <p class="description">Applies to both AI and Social rows.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Row label</th>
                        <td>
                            <input type="text" class="regular-text" name="kas_settings[ai_label]" value="<?php echo esc_attr( $s['ai_label'] ); ?>" />
                            <p class="description">The small heading shown above the buttons. Default: "Summarize with"</p>
                        </td>
                    </tr>
                    <tr>
                        <th>AI prompt text</th>
                        <td>
                            <input type="text" class="large-text" name="kas_settings[ai_prompt_template]" value="<?php echo esc_attr( $s['ai_prompt_template'] ); ?>" />
                            <p class="description">
                                The instruction sent to each AI tool when a reader clicks a button.<br>
                                <strong>Must include <code>{url}</code></strong> — this is replaced with the post's link automatically.<br>
                                Example: <em>Analyze and summarize the key insights from {url}</em>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>Open in new tab</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kas_settings[open_new_tab]" value="1" <?php checked( $s['open_new_tab'], 1 ); ?> />
                                Open all links in a new browser tab
                            </label>
                            <p class="description">Recommended — keeps your readers on your site while they use the AI tool.</p>
                        </td>
                    </tr>
                </table>

                <!-- AI tool toggles -->
                <h2 class="kas-section-title">AI Tools</h2>
                <p class="description" style="margin-bottom:12px;">Choose which AI tools to show. Uncheck any you don't want to display.</p>
                <table class="form-table" role="presentation">
                    <?php foreach ( $networks as $key => $def ) :
                        if ( 'ai' !== $def['group'] ) continue; ?>
                    <tr>
                        <th><?php echo esc_html( $def['label'] ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                    name="kas_settings[enabled_networks][<?php echo esc_attr( $key ); ?>]"
                                    value="1"
                                    <?php checked( ! empty( $s['enabled_networks'][ $key ] ), true ); ?> />
                                Show <?php echo esc_html( $def['label'] ); ?> button
                            </label>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

            </div><!-- #kas-ai-fields -->

            <!-- Shortcode reference -->
            <div class="kas-shortcode-box">
                <h3>Shortcode</h3>
                <p>To place the AI Summarize row at a specific spot inside a post, use:</p>
                <code>[qai_ai_buttons]</code>
                <p class="description">Paste this inside the post editor wherever you want the row to appear. <strong>Remove the position setting above to "shortcode only" first</strong> to avoid showing buttons twice.</p>
            </div>

            <?php submit_button( 'Save AI Settings' ); ?>
        </form>

        <?php
        $this->page_footer();
    }

    /* -----------------------------------------------------------------------
     * Social Share settings page
     * -------------------------------------------------------------------- */

    public function render_social_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $s = $this->get_settings();
        $networks = self::get_network_definitions();
        $this->page_header( 'social' );
        ?>

        <!-- Live preview -->
        <div class="kas-preview-box">
            <h3 style="margin:0 0 8px;">Live Preview</h3>
            <p class="description" style="margin-bottom:12px;">Updates when you change options on this page.</p>
            <div id="kas-preview-social"><?php
                $preview_settings = $s;
                $preview_settings['ai_enabled'] = 0; // Social tab: show social row only
                echo wp_kses( KAS_Render::preview_rows( $preview_settings ), KAS_Render::preview_kses() );
            ?></div>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields( 'qai_social_group' ); ?>

            <!-- Enable/disable -->
            <h2 class="kas-section-title">Social Share Row</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th>Enable Social Share</th>
                    <td>
                        <label class="kas-toggle-label">
                            <input type="checkbox" name="kas_settings[social_enabled]" value="1" id="kas-social-enabled" <?php checked( $s['social_enabled'], 1 ); ?> />
                            <span>Show the "Share this article" row on every post</span>
                        </label>
                        <p class="description">Turn this off if you don't want social share buttons anywhere on your site.</p>
                    </td>
                </tr>
            </table>

            <div id="kas-social-fields" <?php echo $s['social_enabled'] ? '' : 'style="opacity:.45;pointer-events:none;"'; ?>>

                <!-- Position -->
                <h2 class="kas-section-title">Placement</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th>Position on post</th>
                        <td>
                            <?php $this->position_field( 'social_position', $s['social_position'], 'social' ); ?>
                            <p class="description">
                                Choose where the Social Share row appears on each post.<br>
                                "After post content" is the default — readers share after finishing the article, which gets better click-through.
                            </p>
                            <?php $this->shortcode_notice( '[qai_social_buttons]' ); ?>
                        </td>
                    </tr>
                </table>

                <!-- Appearance -->
                <h2 class="kas-section-title">Appearance</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th>Button style</th>
                        <td>
                            <?php $this->button_style_field( 'social_button_style', $s['social_button_style'], 'social' ); ?>
                            <p class="description">Social networks have well-known icons — "Icon only" saves space on mobile; "Icon + Text" is clearest for all users.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Row label</th>
                        <td>
                            <input type="text" class="regular-text" name="kas_settings[social_label]" value="<?php echo esc_attr( $s['social_label'] ); ?>" />
                            <p class="description">The small heading shown above the share buttons. Default: "Share this article"</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Copy Link button</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kas_settings[show_copy_link]" value="1" <?php checked( $s['show_copy_link'], 1 ); ?> />
                                Show a "Copy Link" button alongside the share buttons
                            </label>
                            <p class="description">Lets readers copy the post URL to their clipboard with one click. Useful for messaging apps not listed above.</p>
                        </td>
                    </tr>
                </table>

                <!-- Social network toggles -->
                <h2 class="kas-section-title">Social Networks</h2>
                <p class="description" style="margin-bottom:12px;">Choose which platforms to show. Uncheck any that don't suit your audience.</p>
                <table class="form-table" role="presentation">
                    <?php foreach ( $networks as $key => $def ) :
                        if ( 'social' !== $def['group'] ) continue;
                        $allowed_svg = array(
                            'svg'  => array( 'viewbox' => true, 'fill' => true, 'xmlns' => true, 'aria-hidden' => true ),
                            'path' => array( 'd' => true ),
                        );
                        ?>
                    <tr>
                        <th>
                            <span style="display:inline-flex;align-items:center;gap:6px;">
                                <span class="kas-network-icon" style="color:<?php echo esc_attr( $def['color'] ); ?>;width:18px;height:18px;display:inline-flex;">
                                    <?php echo wp_kses( $def['icon'], $allowed_svg ); ?>
                                </span>
                                <?php echo esc_html( $def['label'] ); ?>
                            </span>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox"
                                    name="kas_settings[enabled_networks][<?php echo esc_attr( $key ); ?>]"
                                    value="1"
                                    <?php checked( ! empty( $s['enabled_networks'][ $key ] ), true ); ?> />
                                Show <?php echo esc_html( $def['label'] ); ?> button
                            </label>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

            </div><!-- #kas-social-fields -->

            <!-- Shortcode reference -->
            <div class="kas-shortcode-box">
                <h3>Shortcode</h3>
                <p>To place the Social Share row at a specific spot inside a post, use:</p>
                <code>[qai_social_buttons]</code>
                <p class="description">Paste this inside the post editor wherever you want the share row to appear. <strong>Set the position above to "shortcode only" first</strong> to avoid showing buttons twice.</p>
            </div>

            <?php submit_button( 'Save Social Settings' ); ?>
        </form>

        <?php
        $this->page_footer();
    }
}
