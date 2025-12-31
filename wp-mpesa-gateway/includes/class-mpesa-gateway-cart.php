<?php

class WP_Mpesa_Gateway_Cart {

    private $cart = array();

    public function __construct() {
        if ( isset( $_COOKIE['mpesa_cart'] ) ) {
            $this->cart = json_decode( stripslashes( $_COOKIE['mpesa_cart'] ), true );
            if ( ! is_array( $this->cart ) ) {
                $this->cart = array();
            }
        }
    }

    private function save() {
        // expire in 7 days
        setcookie( 'mpesa_cart', json_encode( $this->cart ), time() + 604800, '/' );
    }

    public function add_to_cart( $product_id, $qty = 1 ) {
        if ( isset( $this->cart[$product_id] ) ) {
            $this->cart[$product_id]['qty'] += $qty;
        } else {
            $this->cart[$product_id] = array(
                'qty' => $qty,
                'price' => get_post_meta( $product_id, '_price', true ),
                'name' => get_the_title( $product_id )
            );
        }
        $this->save();
    }

    public function remove_from_cart( $product_id ) {
        if ( isset( $this->cart[$product_id] ) ) {
            unset( $this->cart[$product_id] );
            $this->save();
        }
    }

    public function get_cart() {
        return $this->cart;
    }

    public function get_total() {
        $total = 0;
        foreach ( $this->cart as $item ) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }

    public function clear_cart() {
        $this->cart = array();
        $this->save();
        // Also unset cookie immediately for this request if needed, 
        // but setcookie with past time is better for client.
        setcookie( 'mpesa_cart', '', time() - 3600, '/' );
    }
}
