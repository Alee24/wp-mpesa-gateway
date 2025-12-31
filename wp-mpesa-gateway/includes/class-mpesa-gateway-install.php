<?php

class WP_Mpesa_Gateway_Install {

    public static function install() {
        self::create_pages();
        flush_rewrite_rules();
    }

    private static function create_pages() {
        $pages = array(
            'mpesa_shop_page_id' => array(
                'title'   => 'Shop',
                'content' => '', // Archive template will handle this
                'slug'    => 'shop'
            ),
            'mpesa_cart_page_id' => array(
                'title'   => 'Cart',
                'content' => '[mpesa_cart]', // Shortcode for cart
                'slug'    => 'cart'
            ),
            'mpesa_checkout_page_id' => array(
                'title'   => 'Checkout',
                'content' => '[mpesa_checkout]', // Shortcode for checkout
                'slug'    => 'checkout'
            ),
            'mpesa_thankyou_page_id' => array(
                'title'   => 'Order Received',
                'content' => '',
                'slug'    => 'order-received'
            )
        );

        foreach ( $pages as $option_name => $page_data ) {
            $page_id = get_option( $option_name );
            
            // Check if page exists
            if ( $page_id && get_post( $page_id ) ) {
                continue; 
            }

            // Create page
            $page_id = wp_insert_post( array(
                'post_title'     => $page_data['title'],
                'post_content'   => $page_data['content'],
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_name'      => $page_data['slug'],
                'comment_status' => 'closed'
            ));

            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_option( $option_name, $page_id );
            }
        }
    }
}
