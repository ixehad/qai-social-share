<?php
/**
 * Builds the HTML output for the AI and Social button rows.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KAS_Render {

    /**
     * Build a single network's share/summarize URL from its pattern.
     */
    private static function build_url( $pattern, $url_raw, $title_raw, $prompt_raw ) {
        $replacements = array(
            '{url}'    => rawurlencode( $url_raw ),
            '{title}'  => rawurlencode( $title_raw ),
            '{prompt}' => rawurlencode( $prompt_raw ),
        );
        return strtr( $pattern, $replacements );
    }

    /**
     * Render the AI "Summarize with" row.
     */
    public static function ai_row( $post_id = null ) {
        $settings = KAS_Settings::instance()->get_settings();

        if ( empty( $settings['show_ai'] ) ) {
            return '';
        }

        $post_id = $post_id ? $post_id : get_the_ID();
        if ( ! $post_id ) {
            return '';
        }

        $url_raw   = get_permalink( $post_id );
        $title_raw = get_the_title( $post_id );
        $prompt    = str_replace( '{url}', $url_raw, $settings['ai_prompt_template'] );

        $networks = KAS_Settings::get_network_definitions();
        $target   = ! empty( $settings['open_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $style    = 'kas-style-' . sanitize_html_class( $settings['button_style'] );

        $buttons = '';
        foreach ( $networks as $key => $def ) {
            if ( 'ai' !== $def['group'] ) continue;
            if ( empty( $settings['enabled_networks'][ $key ] ) ) continue;

            $href = self::build_url( $def['url_pattern'], $url_raw, $title_raw, $prompt );
            $buttons .= sprintf(
                '<a href="%s" class="kas-btn kas-btn-%s"%s style="background:%s;">%s</a>',
                esc_url( $href ),
                esc_attr( $key ),
                $target,
                esc_attr( $def['color'] ),
                esc_html( $def['label'] )
            );
        }

        if ( '' === $buttons ) {
            return '';
        }

        return sprintf(
            '<div class="kas-row kas-ai-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
            esc_attr( $style ),
            esc_html( $settings['ai_label'] ),
            $buttons
        );
    }

    /**
     * Render the social "Share this article" row.
     */
    public static function social_row( $post_id = null ) {
        $settings = KAS_Settings::instance()->get_settings();

        if ( empty( $settings['show_social'] ) ) {
            return '';
        }

        $post_id = $post_id ? $post_id : get_the_ID();
        if ( ! $post_id ) {
            return '';
        }

        $url_raw   = get_permalink( $post_id );
        $title_raw = get_the_title( $post_id );

        $networks = KAS_Settings::get_network_definitions();
        $target   = ! empty( $settings['open_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $style    = 'kas-style-' . sanitize_html_class( $settings['button_style'] );

        $buttons = '';
        foreach ( $networks as $key => $def ) {
            if ( 'social' !== $def['group'] ) continue;
            if ( empty( $settings['enabled_networks'][ $key ] ) ) continue;

            $href = self::build_url( $def['url_pattern'], $url_raw, $title_raw, '' );
            $buttons .= sprintf(
                '<a href="%s" class="kas-btn kas-btn-%s"%s style="background:%s;">%s</a>',
                esc_url( $href ),
                esc_attr( $key ),
                $target,
                esc_attr( $def['color'] ),
                esc_html( $def['label'] )
            );
        }

        if ( ! empty( $settings['show_copy_link'] ) ) {
            $buttons .= sprintf(
                '<button type="button" class="kas-btn kas-btn-copy" data-kas-copy-url="%s">Copy Link</button>',
                esc_url( $url_raw )
            );
        }

        if ( '' === $buttons ) {
            return '';
        }

        return sprintf(
            '<div class="kas-row kas-social-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
            esc_attr( $style ),
            esc_html( $settings['social_label'] ),
            $buttons
        );
    }

    /**
     * Both rows together, in AI-then-social order.
     */
    public static function both_rows( $post_id = null ) {
        return self::ai_row( $post_id ) . self::social_row( $post_id );
    }

    /**
     * Renders a sample version of both rows for the admin settings page
     * "Live Preview" block, using placeholder data instead of a real post
     * (the settings screen has no post context to pull from).
     */
    public static function preview_rows( $settings ) {
        $sample_url   = home_url( '/sample-blog-post/' );
        $sample_title = 'Sample Blog Post Title';
        $networks     = KAS_Settings::get_network_definitions();
        $target       = ! empty( $settings['open_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $style        = 'kas-style-' . sanitize_html_class( $settings['button_style'] );
        $output       = '';

        if ( ! empty( $settings['show_ai'] ) ) {
            $prompt  = str_replace( '{url}', $sample_url, $settings['ai_prompt_template'] );
            $buttons = '';
            foreach ( $networks as $key => $def ) {
                if ( 'ai' !== $def['group'] || empty( $settings['enabled_networks'][ $key ] ) ) continue;
                $href = self::build_url( $def['url_pattern'], $sample_url, $sample_title, $prompt );
                $buttons .= sprintf(
                    '<a href="%s" class="kas-btn kas-btn-%s"%s style="background:%s;">%s</a>',
                    esc_url( $href ), esc_attr( $key ), $target, esc_attr( $def['color'] ), esc_html( $def['label'] )
                );
            }
            if ( '' !== $buttons ) {
                $output .= sprintf(
                    '<div class="kas-row kas-ai-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
                    esc_attr( $style ), esc_html( $settings['ai_label'] ), $buttons
                );
            }
        }

        if ( ! empty( $settings['show_social'] ) ) {
            $buttons = '';
            foreach ( $networks as $key => $def ) {
                if ( 'social' !== $def['group'] || empty( $settings['enabled_networks'][ $key ] ) ) continue;
                $href = self::build_url( $def['url_pattern'], $sample_url, $sample_title, '' );
                $buttons .= sprintf(
                    '<a href="%s" class="kas-btn kas-btn-%s"%s style="background:%s;">%s</a>',
                    esc_url( $href ), esc_attr( $key ), $target, esc_attr( $def['color'] ), esc_html( $def['label'] )
                );
            }
            if ( ! empty( $settings['show_copy_link'] ) ) {
                $buttons .= sprintf(
                    '<button type="button" class="kas-btn kas-btn-copy" disabled title="Disabled in preview" data-kas-copy-url="%s">Copy Link</button>',
                    esc_url( $sample_url )
                );
            }
            if ( '' !== $buttons ) {
                $output .= sprintf(
                    '<div class="kas-row kas-social-row %s"><span class="kas-row-label">%s</span><div class="kas-buttons">%s</div></div>',
                    esc_attr( $style ), esc_html( $settings['social_label'] ), $buttons
                );
            }
        }

        if ( '' === $output ) {
            $output = '<p class="description">Nothing to preview &mdash; both rows are currently turned off below.</p>';
        }

        return $output;
    }
}
