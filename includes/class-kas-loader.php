<?php
/**
 * Wires shortcodes, the auto-insert hook, and front-end assets together.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KAS_Loader {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'kas_ai_buttons', array( $this, 'shortcode_ai' ) );
        add_shortcode( 'kas_social_buttons', array( $this, 'shortcode_social' ) );
        add_shortcode( 'kas_buttons', array( $this, 'shortcode_both' ) );

        add_filter( 'the_content', array( $this, 'maybe_inject' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ) );
    }

    public function shortcode_ai() {
        return KAS_Render::ai_row();
    }

    public function shortcode_social() {
        return KAS_Render::social_row();
    }

    public function shortcode_both() {
        return KAS_Render::both_rows();
    }

    /**
     * Tracks which post IDs have already had rows injected during this
     * single page request, so a theme or plugin calling apply_filters(
     * 'the_content', ... ) more than once on the same post (common with
     * AMP plugins, REST API rendering, some page builders, related-post
     * widgets that re-run the loop) can never produce duplicate rows.
     */
    private static $injected_post_ids = array();

    /**
     * Auto-inject the rows into post content based on the configured position.
     * Skipped if:
     *  - position = shortcode_only
     *  - this exact post was already injected once in this request
     *  - the post body already contains one of our shortcodes (the user has
     *    manually placed it, so auto-inserting too would duplicate it)
     *  - we're outside the main singular post view
     */
    public function maybe_inject( $content ) {
        if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $post_id = get_the_ID();
        if ( ! $post_id || in_array( $post_id, self::$injected_post_ids, true ) ) {
            return $content;
        }

        $settings = KAS_Settings::instance()->get_settings();
        $position = $settings['position'];

        if ( 'shortcode_only' === $position ) {
            return $content;
        }

        // If the user already dropped a shortcode into this post, respect that
        // placement and don't also auto-inject — otherwise the buttons show twice.
        if ( $this->content_has_shortcode( $content ) ) {
            self::$injected_post_ids[] = $post_id;
            return $content;
        }

        $rows = KAS_Render::both_rows( $post_id );

        // Mark this post handled now, regardless of outcome, so a second firing
        // of the_content within the same request (AMP, related-post widgets,
        // some page builders) never repeats this work or risks duplicate output.
        self::$injected_post_ids[] = $post_id;

        if ( '' === trim( $rows ) ) {
            return $content;
        }

        if ( 'before_content' === $position || 'after_meta' === $position ) {
            // "after_meta" (right under title/date) requires a template edit for pixel-perfect
            // placement above the content; as a content filter we place it at the top of the
            // content area, which visually sits directly under the post meta in most themes.
            return $rows . $content;
        }

        // after_content
        return $content . $rows;
    }

    /**
     * Checks the raw (pre-shortcode-rendering) post content for any of this
     * plugin's shortcodes, so auto-inject can step aside if found.
     */
    private function content_has_shortcode( $content ) {
        return has_shortcode( $content, 'kas_ai_buttons' )
            || has_shortcode( $content, 'kas_social_buttons' )
            || has_shortcode( $content, 'kas_buttons' );
    }

    public function enqueue_front_assets() {
        // Always load on Posts (where auto-inject normally fires), and on any
        // other singular content (Pages, custom post types) since the
        // shortcodes work everywhere — without this, a shortcode pasted into
        // a Page would render as unstyled raw links.
        if ( ! is_singular() ) {
            return;
        }
        wp_enqueue_style( 'kas-front', KAS_URL . 'assets/front.css', array(), KAS_VERSION );
        wp_enqueue_script( 'kas-front', KAS_URL . 'assets/front.js', array(), KAS_VERSION, true );
    }
}
