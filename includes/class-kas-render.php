<?php
/**
 * Builds HTML output for AI Summarize and Social Share rows.
 * Supports three button styles: text | icon | icon_text
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class KAS_Render {

    /* -----------------------------------------------------------------------
     * URL builder
     * -------------------------------------------------------------------- */

    private static function build_url( $pattern, $url_raw, $title_raw, $prompt_raw ) {
        return strtr( $pattern, array(
            '{url}'    => rawurlencode( $url_raw ),
            '{title}'  => rawurlencode( $title_raw ),
            '{prompt}' => rawurlencode( $prompt_raw ),
        ) );
    }

    /* -----------------------------------------------------------------------
     * Single button HTML
     * -------------------------------------------------------------------- */

    /**
     * Allowed HTML tags/attributes for inline SVG icons.
     * Icons come from our own hardcoded definitions, never user input,
     * but we still pass through wp_kses() to satisfy PHPCS and WP.org review.
     */
    private static function svg_kses() {
        return array(
            'svg'  => array( 'viewbox' => true, 'fill' => true, 'xmlns' => true, 'aria-hidden' => true, 'width' => true, 'height' => true ),
            'path' => array( 'd' => true, 'fill' => true ),
        );
    }

    /**
     * Full allowlist for the admin live-preview output.
     * wp_kses_post() strips SVG, so we need our own allowlist that
     * covers every tag/attribute the preview rows can produce.
     */
    public static function preview_kses() {
        $base = array(
            'div'    => array( 'class' => true, 'id' => true, 'style' => true ),
            'span'   => array( 'class' => true, 'style' => true, 'aria-hidden' => true ),
            'a'      => array( 'href' => true, 'class' => true, 'style' => true, 'target' => true, 'rel' => true, 'aria-label' => true ),
            'button' => array( 'type' => true, 'class' => true, 'style' => true, 'disabled' => true, 'title' => true, 'data-kas-copy-url' => true, 'aria-label' => true ),
            'p'      => array( 'class' => true, 'style' => true ),
        );
        return array_merge( $base, self::svg_kses() );
    }

    private static function button_html( $href, $label, $icon_svg, $style, $color, $target, $extra_class = '' ) {
        $btn_class = 'kas-btn kas-btn-style-' . esc_attr( $style );
        if ( $extra_class ) $btn_class .= ' ' . $extra_class;

        $inner = '';
        if ( 'icon' === $style || 'icon_text' === $style ) {
            $inner .= '<span class="kas-btn-icon" aria-hidden="true">' . wp_kses( $icon_svg, self::svg_kses() ) . '</span>';
        }
        if ( 'text' === $style || 'icon_text' === $style ) {
            $inner .= '<span class="kas-btn-label">' . esc_html( $label ) . '</span>';
        }
        // Fallback: if somehow style is unknown, show text
        if ( '' === $inner ) {
            $inner = '<span class="kas-btn-label">' . esc_html( $label ) . '</span>';
        }

        return sprintf(
            '<a href="%s" class="%s"%s style="background:%s;" aria-label="%s">%s</a>',
            esc_url( $href ),
            esc_attr( $btn_class ),
            $target,
            esc_attr( $color ),
            esc_attr( $label ),
            $inner
        );
    }

    /* -----------------------------------------------------------------------
     * AI Summarize row
     * -------------------------------------------------------------------- */

    public static function ai_row( $post_id = null ) {
        $settings = KAS_Settings::instance()->get_settings();
        if ( empty( $settings['ai_enabled'] ) ) return '';

        $post_id   = $post_id ? $post_id : get_the_ID();
        if ( ! $post_id ) return '';

        $url_raw   = get_permalink( $post_id );
        $title_raw = get_the_title( $post_id );
        $prompt    = str_replace( '{url}', $url_raw, $settings['ai_prompt_template'] );
        $networks  = KAS_Settings::get_network_definitions();
        $target    = ! empty( $settings['open_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $style     = $settings['ai_button_style'];
        $shape     = 'kas-shape-' . sanitize_html_class( $settings['button_shape'] );

        $buttons = '';
        foreach ( $networks as $key => $def ) {
            if ( 'ai' !== $def['group'] )                              continue;
            if ( empty( $settings['enabled_networks'][ $key ] ) )     continue;

            $href     = self::build_url( $def['url_pattern'], $url_raw, $title_raw, $prompt );
            $buttons .= self::button_html( $href, $def['label'], $def['icon'], $style, $def['color'], $target, 'kas-btn-' . esc_attr( $key ) );
        }

        if ( '' === $buttons ) return '';

        return sprintf(
            '<div class="kas-row kas-ai-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
            esc_attr( $shape ),
            esc_html( $settings['ai_label'] ),
            $buttons
        );
    }

    /* -----------------------------------------------------------------------
     * Social Share row
     * -------------------------------------------------------------------- */

    public static function social_row( $post_id = null ) {
        $settings = KAS_Settings::instance()->get_settings();
        if ( empty( $settings['social_enabled'] ) ) return '';

        $post_id   = $post_id ? $post_id : get_the_ID();
        if ( ! $post_id ) return '';

        $url_raw   = get_permalink( $post_id );
        $title_raw = get_the_title( $post_id );
        $networks  = KAS_Settings::get_network_definitions();
        $target    = ! empty( $settings['open_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $style     = $settings['social_button_style'];
        $shape     = 'kas-shape-' . sanitize_html_class( $settings['button_shape'] );

        $buttons = '';
        foreach ( $networks as $key => $def ) {
            if ( 'social' !== $def['group'] )                         continue;
            if ( empty( $settings['enabled_networks'][ $key ] ) )    continue;

            $href     = self::build_url( $def['url_pattern'], $url_raw, $title_raw, '' );
            $buttons .= self::button_html( $href, $def['label'], $def['icon'], $style, $def['color'], $target, 'kas-btn-' . esc_attr( $key ) );
        }

        if ( ! empty( $settings['show_copy_link'] ) ) {
            $copy_icon  = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>';
            $icon_part  = ( 'icon' === $style || 'icon_text' === $style ) ? '<span class="kas-btn-icon" aria-hidden="true">' . wp_kses( $copy_icon, self::svg_kses() ) . '</span>' : '';
            $text_part  = ( 'text' === $style || 'icon_text' === $style ) ? '<span class="kas-btn-label">Copy Link</span>' : '';
            $buttons .= sprintf(
                '<button type="button" class="kas-btn kas-btn-style-%s %s kas-btn-copy" data-kas-copy-url="%s" aria-label="Copy link to this post">%s%s</button>',
                esc_attr( $style ),
                esc_attr( $shape ),
                esc_url( $url_raw ),
                $icon_part,
                $text_part
            );
        }

        if ( '' === $buttons ) return '';

        return sprintf(
            '<div class="kas-row kas-social-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
            esc_attr( $shape ),
            esc_html( $settings['social_label'] ),
            $buttons
        );
    }

    /* -----------------------------------------------------------------------
     * Both rows together (used by [qai_buttons] shortcode)
     * -------------------------------------------------------------------- */

    public static function both_rows( $post_id = null ) {
        return self::ai_row( $post_id ) . self::social_row( $post_id );
    }

    /* -----------------------------------------------------------------------
     * Admin live preview (no real post context — uses sample data)
     * -------------------------------------------------------------------- */

    public static function preview_rows( $settings = null ) {
        if ( null === $settings ) {
            $settings = KAS_Settings::instance()->get_settings();
        }
        $sample_url   = home_url( '/sample-post/' );
        $sample_title = 'Sample Post Title';
        $networks     = KAS_Settings::get_network_definitions();
        $target       = ! empty( $settings['open_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $shape        = 'kas-shape-' . sanitize_html_class( $settings['button_shape'] );
        $output       = '';

        if ( ! empty( $settings['ai_enabled'] ) ) {
            $prompt  = str_replace( '{url}', $sample_url, $settings['ai_prompt_template'] );
            $style   = $settings['ai_button_style'];
            $buttons = '';
            foreach ( $networks as $key => $def ) {
                if ( 'ai' !== $def['group'] || empty( $settings['enabled_networks'][ $key ] ) ) continue;
                $href     = KAS_Render::build_url( $def['url_pattern'], $sample_url, $sample_title, $prompt );
                $buttons .= self::button_html( $href, $def['label'], $def['icon'], $style, $def['color'], $target, 'kas-btn-' . esc_attr( $key ) );
            }
            if ( '' !== $buttons ) {
                $output .= sprintf(
                    '<div class="kas-row kas-ai-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
                    esc_attr( $shape ), esc_html( $settings['ai_label'] ), $buttons
                );
            }
        }

        if ( ! empty( $settings['social_enabled'] ) ) {
            $style   = $settings['social_button_style'];
            $buttons = '';
            foreach ( $networks as $key => $def ) {
                if ( 'social' !== $def['group'] || empty( $settings['enabled_networks'][ $key ] ) ) continue;
                $href     = self::build_url( $def['url_pattern'], $sample_url, $sample_title, '' );
                $buttons .= self::button_html( $href, $def['label'], $def['icon'], $style, $def['color'], $target, 'kas-btn-' . esc_attr( $key ) );
            }
            if ( ! empty( $settings['show_copy_link'] ) ) {
                $copy_icon = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>';
                $icon_part = ( 'icon' === $style || 'icon_text' === $style ) ? '<span class="kas-btn-icon">' . wp_kses( $copy_icon, self::svg_kses() ) . '</span>' : '';
                $text_part = ( 'text' === $style || 'icon_text' === $style ) ? '<span class="kas-btn-label">Copy Link</span>' : '';
                $buttons .= sprintf(
                    '<button type="button" class="kas-btn kas-btn-style-%s %s kas-btn-copy" disabled title="Preview only">%s%s</button>',
                    esc_attr( $style ), esc_attr( $shape ), $icon_part, $text_part
                );
            }
            if ( '' !== $buttons ) {
                $output .= sprintf(
                    '<div class="kas-row kas-social-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
                    esc_attr( $shape ), esc_html( $settings['social_label'] ), $buttons
                );
            }
        }

        if ( '' === $output ) {
            $output = '<p class="description" style="color:#d63638;">⚠️ This row is currently disabled — enable it above to see a preview.</p>';
        }

        return $output;
    }
}
