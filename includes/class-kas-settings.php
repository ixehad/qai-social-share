<?php
/**
 * Handles the admin settings page and the option schema.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KAS_Settings {

    private static $instance = null;
    private $page_hook = '';

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Master list of every AI tool and social network the plugin knows about,
     * along with the URL pattern used to build its share/summarize link.
     *
     * Placeholders available in url_pattern:
     *   {url}     - the post permalink, URL-encoded
     *   {url_raw} - the post permalink, NOT encoded (only for building the prompt text itself)
     *   {prompt}  - the URL-encoded prompt text (AI tools only)
     *   {title}   - the post title, URL-encoded
     */
    public static function get_network_definitions() {
        return array(
            // --- AI Summarize tools ---
            'chatgpt' => array(
                'group'       => 'ai',
                'label'       => 'ChatGPT',
                'url_pattern' => 'https://chatgpt.com/?prompt={prompt}',
                'color'       => '#74AA9C',
            ),
            'claude' => array(
                'group'       => 'ai',
                'label'       => 'Claude',
                'url_pattern' => 'https://claude.ai/new?q={prompt}',
                'color'       => '#DA7756',
            ),
            'perplexity' => array(
                'group'       => 'ai',
                'label'       => 'Perplexity',
                'url_pattern' => 'https://www.perplexity.ai/search/new?q={prompt}',
                'color'       => '#20B8CD',
            ),
            'grok' => array(
                'group'       => 'ai',
                'label'       => 'Grok',
                'url_pattern' => 'https://x.com/i/grok?text={prompt}',
                'color'       => '#000000',
            ),
            'google' => array(
                'group'       => 'ai',
                'label'       => 'Google',
                'url_pattern' => 'https://www.google.com/search?udm=50&q={prompt}',
                'color'       => '#4285F4',
            ),
            // --- Social share networks ---
            'facebook' => array(
                'group'       => 'social',
                'label'       => 'Facebook',
                'url_pattern' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
                'color'       => '#1877F2',
            ),
            'twitter' => array(
                'group'       => 'social',
                'label'       => 'X',
                'url_pattern' => 'https://twitter.com/intent/tweet?url={url}&text={title}',
                'color'       => '#000000',
            ),
            'whatsapp' => array(
                'group'       => 'social',
                'label'       => 'WhatsApp',
                'url_pattern' => 'https://api.whatsapp.com/send?text={title}%20{url}',
                'color'       => '#25D366',
            ),
            'linkedin' => array(
                'group'       => 'social',
                'label'       => 'LinkedIn',
                'url_pattern' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
                'color'       => '#0A66C2',
            ),
            'telegram' => array(
                'group'       => 'social',
                'label'       => 'Telegram',
                'url_pattern' => 'https://t.me/share/url?url={url}&text={title}',
                'color'       => '#26A5E4',
            ),
            'pinterest' => array(
                'group'       => 'social',
                'label'       => 'Pinterest',
                'url_pattern' => 'https://pinterest.com/pin/create/button/?url={url}&description={title}',
                'color'       => '#E60023',
            ),
        );
    }

    /**
     * Default option values. Every network is on by default.
     */
    public static function defaults() {
        $networks = array();
        foreach ( self::get_network_definitions() as $key => $def ) {
            $networks[ $key ] = 1;
        }

        return array(
            'enabled_networks'   => $networks,
            'ai_prompt_template' => 'Analyze and summarize the key insights from {url}',
            'show_ai'            => 1,
            'show_social'        => 1,
            'show_copy_link'     => 1,
            'ai_label'           => 'Summarize with',
            'social_label'       => 'Share this article',
            'position'           => 'after_meta', // after_meta | before_content | after_content | shortcode_only
            'open_new_tab'       => 1,
            'button_style'       => 'pill', // pill | rounded | square
        );
    }

    public function get_settings() {
        $saved    = get_option( 'kas_settings', array() );
        $defaults = self::defaults();

        // If the stored option is somehow not an array at all (corrupted data,
        // a conflicting plugin writing to the same option key, manual DB edit),
        // fall back to defaults entirely rather than feeding bad data downstream.
        if ( ! is_array( $saved ) ) {
            return $defaults;
        }

        $merged = wp_parse_args( $saved, $defaults );

        // wp_parse_args only merges top-level keys. If enabled_networks already
        // exists in saved data, manually backfill any network keys added in a
        // later plugin update so new networks default to "on" instead of vanishing.
        if ( isset( $saved['enabled_networks'] ) && is_array( $saved['enabled_networks'] ) ) {
            $merged['enabled_networks'] = wp_parse_args( $saved['enabled_networks'], $defaults['enabled_networks'] );
        } else {
            // Stored value exists but isn't an array (corrupted) -- use defaults for this key only.
            $merged['enabled_networks'] = $defaults['enabled_networks'];
        }

        // Guard every scalar setting against unexpected types (e.g. an array
        // landing where a string is expected) before it reaches str_replace(),
        // sprintf(), or esc_* calls elsewhere in the plugin.
        $string_keys = array( 'ai_prompt_template', 'ai_label', 'social_label', 'position', 'button_style' );
        foreach ( $string_keys as $key ) {
            if ( ! is_string( $merged[ $key ] ) ) {
                $merged[ $key ] = $defaults[ $key ];
            }
        }

        return $merged;
    }

    public function register_settings() {
        register_setting( 'kas_settings_group', 'kas_settings', array( $this, 'sanitize_settings' ) );
    }

    public function sanitize_settings( $input ) {
        $clean    = array();
        $defaults = self::defaults();
        $networks = self::get_network_definitions();

        $clean['enabled_networks'] = array();
        foreach ( $networks as $key => $def ) {
            $clean['enabled_networks'][ $key ] = isset( $input['enabled_networks'][ $key ] ) ? 1 : 0;
        }

        $clean['ai_prompt_template'] = isset( $input['ai_prompt_template'] ) && trim( $input['ai_prompt_template'] ) !== ''
            ? sanitize_text_field( $input['ai_prompt_template'] )
            : $defaults['ai_prompt_template'];

        // Make sure {url} is always present in the template, or the AI tools have nothing to read.
        if ( false === strpos( $clean['ai_prompt_template'], '{url}' ) ) {
            $clean['ai_prompt_template'] .= ' {url}';
        }

        $clean['show_ai']        = isset( $input['show_ai'] ) ? 1 : 0;
        $clean['show_social']    = isset( $input['show_social'] ) ? 1 : 0;
        $clean['show_copy_link'] = isset( $input['show_copy_link'] ) ? 1 : 0;
        $clean['open_new_tab']   = isset( $input['open_new_tab'] ) ? 1 : 0;

        $clean['ai_label']     = sanitize_text_field( isset( $input['ai_label'] ) ? $input['ai_label'] : $defaults['ai_label'] );
        $clean['social_label'] = sanitize_text_field( isset( $input['social_label'] ) ? $input['social_label'] : $defaults['social_label'] );

        $allowed_positions = array( 'after_meta', 'before_content', 'after_content', 'shortcode_only' );
        $position_input     = isset( $input['position'] ) ? $input['position'] : '';
        $clean['position']  = in_array( $position_input, $allowed_positions, true ) ? $position_input : $defaults['position'];

        $allowed_styles        = array( 'pill', 'rounded', 'square' );
        $style_input           = isset( $input['button_style'] ) ? $input['button_style'] : '';
        $clean['button_style'] = in_array( $style_input, $allowed_styles, true ) ? $style_input : $defaults['button_style'];

        return $clean;
    }

    public function add_settings_page() {
        $hook = add_menu_page(
            'Qai Social Share',
            'Qai Social Share',
            'manage_options',
            'qai-social-share',
            array( $this, 'render_settings_page' ),
            'dashicons-share',
            30 // Position in the main menu, just below Comments, easy to spot.
        );
        // Store the hook suffix so enqueue_admin_assets can target this exact page.
        $this->page_hook = $hook;
    }

    public function enqueue_admin_assets( $hook ) {
        if ( empty( $this->page_hook ) || $hook !== $this->page_hook ) {
            return;
        }
        wp_enqueue_style( 'kas-admin', KAS_URL . 'assets/admin.css', array(), KAS_VERSION );
        // The live preview reuses the exact same button markup/classes shown on
        // the live site, so load the front-end stylesheet here too.
        wp_enqueue_style( 'kas-front', KAS_URL . 'assets/front.css', array(), KAS_VERSION );
        wp_enqueue_script( 'kas-admin', KAS_URL . 'assets/admin.js', array(), KAS_VERSION, true );
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = $this->get_settings();
        $networks = self::get_network_definitions();
        ?>
        <div class="wrap kas-settings-wrap">
            <h1><span class="dashicons dashicons-share kas-title-icon"></span> Qai Social Share</h1>
            <p>Controls the "Summarize with AI" and "Share this article" buttons shown on your blog posts &mdash; no theme editing required.</p>

            <?php
            $settings_just_saved = isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) );
            if ( $settings_just_saved ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p>Settings saved. Changes are live on your site immediately.</p>
                </div>
            <?php endif; ?>

            <div class="kas-preview-box">
                <h2 class="title" style="margin-top:0;">Live Preview</h2>
                <p class="description">Updates instantly for layout changes below. Label and prompt text changes need a save to preview.</p>
                <div id="kas-preview-output"><?php echo KAS_Render::preview_rows( $settings ); ?></div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'kas_settings_group' ); ?>

                <h2 class="title">Display Options</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Show AI Summarize row</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kas_settings[show_ai]" value="1" <?php checked( $settings['show_ai'], 1 ); ?> />
                                Display the "Summarize with" row on posts
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Show Social Share row</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kas_settings[show_social]" value="1" <?php checked( $settings['show_social'], 1 ); ?> />
                                Display the "Share this article" row on posts
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Show "Copy Link" button</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kas_settings[show_copy_link]" value="1" <?php checked( $settings['show_copy_link'], 1 ); ?> />
                                Adds a button that copies the post URL to clipboard
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Open links in new tab</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kas_settings[open_new_tab]" value="1" <?php checked( $settings['open_new_tab'], 1 ); ?> />
                                Recommended &mdash; keeps readers on your site
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Placement on post</th>
                        <td>
                            <select name="kas_settings[position]">
                                <option value="after_meta" <?php selected( $settings['position'], 'after_meta' ); ?>>Right after post title/meta (top of post)</option>
                                <option value="before_content" <?php selected( $settings['position'], 'before_content' ); ?>>Before post content</option>
                                <option value="after_content" <?php selected( $settings['position'], 'after_content' ); ?>>After post content</option>
                                <option value="shortcode_only" <?php selected( $settings['position'], 'shortcode_only' ); ?>>Don't auto-insert &mdash; I'll use shortcodes</option>
                            </select>
                            <p class="description">
                                Want full manual control instead? Use the <code>[kas_ai_buttons]</code> and <code>[kas_social_buttons]</code> shortcodes anywhere inside a post's content. If a post already contains one of these shortcodes, this plugin automatically skips auto-inserting on that post &mdash; so you'll never see buttons twice.
                            </p>
                            <p class="description">Note: buttons only appear on regular blog Posts, not on Pages.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Button shape</th>
                        <td>
                            <select name="kas_settings[button_style]">
                                <option value="pill" <?php selected( $settings['button_style'], 'pill' ); ?>>Pill (fully rounded)</option>
                                <option value="rounded" <?php selected( $settings['button_style'], 'rounded' ); ?>>Rounded corners</option>
                                <option value="square" <?php selected( $settings['button_style'], 'square' ); ?>>Square corners</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2 class="title">Labels & Prompt Text</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">AI row label</th>
                        <td><input type="text" class="regular-text" name="kas_settings[ai_label]" value="<?php echo esc_attr( $settings['ai_label'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Social row label</th>
                        <td><input type="text" class="regular-text" name="kas_settings[social_label]" value="<?php echo esc_attr( $settings['social_label'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">AI prompt template</th>
                        <td>
                            <input type="text" class="large-text" name="kas_settings[ai_prompt_template]" value="<?php echo esc_attr( $settings['ai_prompt_template'] ); ?>" />
                            <p class="description">Use <code>{url}</code> as a placeholder for the post link. It must be included somewhere in the text. Example: <em>Analyze and summarize the key insights from {url}</em></p>
                        </td>
                    </tr>
                </table>

                <h2 class="title">AI Summarize Tools</h2>
                <table class="form-table" role="presentation">
                    <?php foreach ( $networks as $key => $def ) :
                        if ( 'ai' !== $def['group'] ) continue; ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $def['label'] ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="kas_settings[enabled_networks][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $settings['enabled_networks'][ $key ] ), true ); ?> />
                                    Enabled
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <h2 class="title">Social Share Networks</h2>
                <table class="form-table" role="presentation">
                    <?php foreach ( $networks as $key => $def ) :
                        if ( 'social' !== $def['group'] ) continue; ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $def['label'] ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="kas_settings[enabled_networks][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $settings['enabled_networks'][ $key ] ), true ); ?> />
                                    Enabled
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php submit_button( 'Save Settings' ); ?>
            </form>

            <hr />
            <h2 class="title">Shortcode Reference</h2>
            <p><code>[kas_ai_buttons]</code> &mdash; outputs the AI summarize row anywhere in post content.</p>
            <p><code>[kas_social_buttons]</code> &mdash; outputs the social share row anywhere in post content.</p>
        </div>
        <?php
    }
}
