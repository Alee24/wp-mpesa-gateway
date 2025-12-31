<?php

class WP_Mpesa_Gateway_Template_Loader {

    public function init() {
        add_filter( 'template_include', array( $this, 'load_template' ) );
    }

    public function load_template( $template ) {
        if ( is_post_type_archive( 'mpesa_product' ) || is_page( get_option( 'mpesa_shop_page_id' ) ) ) {
            $new_template = $this->get_template_path( 'archive-mpesa_product.php' );
            if ( $new_template ) return $new_template;
        }

        if ( is_singular( 'mpesa_product' ) ) {
            $new_template = $this->get_template_path( 'single-mpesa_product.php' );
            if ( $new_template ) return $new_template;
        }

        // Cart and Checkout pages functionality checks
        // We will likely handle these via Shortcodes in the content for better theme compatibility,
        // but if we wanted full page takeovers, we'd do it here. 
        // For now, let's stick to shortcodes for Cart/Checkout as per the install class, 
        // OR we can force a template if we want a strict layout.
        // Let's use the shortcode approach for Cart/Checkout content, but maybe we want a specific wrapper?
        // Let's rely on the theme's page.php for now, but if the user wants "creates checkout pages on its own",
        // we already made pages with shortcodes.
        
        return $template;
    }

    private function get_template_path( $filename ) {
        // Check theme override first: your-theme/mpesa-gateway/filename.php
        $theme_path = locate_template( array( 'mpesa-gateway/' . $filename ) );
        if ( $theme_path ) {
            return $theme_path;
        }

        // Check plugin templates
        $plugin_path = WP_MPESA_GATEWAY_PATH . 'templates/' . $filename;
        if ( file_exists( $plugin_path ) ) {
            return $plugin_path;
        }

        return false;
    }
}
