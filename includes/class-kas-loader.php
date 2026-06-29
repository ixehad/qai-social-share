<?php
/**
 * Registers shortcodes, handles per-row auto-injection, enqueues assets.
 *
 * Shortcode names: [qai_ai_buttons], [qai_social_buttons], [qai_buttons]
 *
 * Auto-inject logic — per row, independent:
 *   - If [qai_ai_buttons] OR [qai_buttons] found in post → skip AI auto-inject
 *   - If [qai_social_buttons] OR [qai_buttons] found in post → skip Social auto-inject
 *   - Each row uses its OWN position setting independently
 *   - A static per-request flag prevents double-injection even if the_content fires more than once
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class KAS_Loader {

    private static $instance = null;

    /** Tracks which post IDs have already had each row injected this request. */
    private static $ai_injected     = array();
    private static $social_injected = array();

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        // New shortcodes
        add_shortcode( 'qai_ai_buttons',     array( $this, 'sc_ai' ) );
        add_shortcode( 'qai_social_buttons', array( $this, 'sc_social' ) );
        add_shortcode( 'qai_buttons',        array( $this, 'sc_both' ) );

        add_filter( 'the_content',       array( $this, 'maybe_inject' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ) );
    }

    /* -----------------------------------------------------------------------
     * Shortcode callbacks
     * -------------------------------------------------------------------- */

    public function sc_ai()     { return KAS_Render::ai_row(); }
    public function sc_social() { return KAS_Render::social_row(); }
    public function sc_both()   { return KAS_Render::both_rows(); }

    /* -----------------------------------------------------------------------
     * Per-row auto-inject
     * -------------------------------------------------------------------- */

    public function maybe_inject( $content ) {
        if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $post_id  = get_the_ID();
        if ( ! $post_id ) return $content;

        $settings = KAS_Settings::instance()->get_settings();

        // --- Determine which rows should auto-inject for this post ---
        $has_ai_sc     = has_shortcode( $content, 'qai_ai_buttons' )
                      || has_shortcode( $content, 'qai_buttons' );
        $has_social_sc = has_shortcode( $content, 'qai_social_buttons' )
                      || has_shortcode( $content, 'qai_buttons' );

        // Row is eligible for auto-inject only if:
        //  1. Enabled in settings
        //  2. Position is not shortcode_only
        //  3. No matching shortcode already in post content
        //  4. Not already injected in this request for this post
        $inject_ai = ! empty( $settings['ai_enabled'] )
            && 'shortcode_only' !== $settings['ai_position']
            && ! $has_ai_sc
            && ! in_array( $post_id, self::$ai_injected, true );

        $inject_social = ! empty( $settings['social_enabled'] )
            && 'shortcode_only' !== $settings['social_position']
            && ! $has_social_sc
            && ! in_array( $post_id, self::$social_injected, true );

        // Mark immediately (before rendering) so re-entrant calls can't double-inject
        if ( $inject_ai )     self::$ai_injected[]     = $post_id;
        if ( $inject_social ) self::$social_injected[]  = $post_id;

        if ( ! $inject_ai && ! $inject_social ) {
            return $content;
        }

        // Build each row and inject at its own configured position
        $ai_row     = $inject_ai     ? KAS_Render::ai_row( $post_id )     : '';
        $social_row = $inject_social ? KAS_Render::social_row( $post_id ) : '';

        // Determine splice positions
        $ai_pos     = $settings['ai_position'];     // before_content | after_meta | after_content
        $social_pos = $settings['social_position'];

        // Group rows by position so we don't touch $content more than needed
        $prepend = '';  // before_content / after_meta go here (top of content)
        $append  = '';  // after_content goes here (bottom of content)

        $top_positions = array( 'before_content', 'after_meta' );

        if ( $inject_ai ) {
            if ( in_array( $ai_pos, $top_positions, true ) ) {
                $prepend .= $ai_row;
            } else {
                $append .= $ai_row;
            }
        }

        if ( $inject_social ) {
            if ( in_array( $social_pos, $top_positions, true ) ) {
                $prepend .= $social_row;
            } else {
                $append .= $social_row;
            }
        }

        return $prepend . $content . $append;
    }

    /* -----------------------------------------------------------------------
     * Front-end assets
     * -------------------------------------------------------------------- */

    public function enqueue_front_assets() {
        if ( ! is_singular() ) return;
        wp_enqueue_style(  'kas-front', KAS_URL . 'assets/front.css', array(), KAS_VERSION );
        wp_enqueue_script( 'kas-front', KAS_URL . 'assets/front.js',  array(), KAS_VERSION, true );
    }
}
