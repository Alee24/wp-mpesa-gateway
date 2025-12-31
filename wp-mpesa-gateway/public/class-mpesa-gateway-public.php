<?php

class WP_Mpesa_Gateway_Public {

    public function enqueue_scripts() {
        wp_enqueue_style( 'mpesa-gateway-css', WP_MPESA_GATEWAY_URL . 'assets/css/style.css', array(), WP_MPESA_GATEWAY_VERSION );
        wp_enqueue_style( 'mpesa-checkout-css', WP_MPESA_GATEWAY_URL . 'assets/css/checkout.css', array(), WP_MPESA_GATEWAY_VERSION );
        wp_enqueue_script( 'mpesa-gateway-js', WP_MPESA_GATEWAY_URL . 'assets/js/script.js', array( 'jquery' ), WP_MPESA_GATEWAY_VERSION, true );
        wp_localize_script( 'mpesa-gateway-js', 'mpesa_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'mpesa_pay_nonce' )
        ) );
    }

    public function render_form( $atts ) {
        ob_start();
        ?>
        <div class="mpesa-gateway-wrapper">
            <div class="mpesa-pay-card">
                <div class="mpesa-header">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/M-PESA_LOGO-01.svg/1200px-M-PESA_LOGO-01.svg.png" alt="M-Pesa" class="mpesa-logo">
                    <h3>Secure Payment</h3>
                </div>
                <form id="mpesa-payment-form">
                    <div class="mpesa-input-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="07XX XXX XXX" required>
                    </div>
                    <div class="mpesa-input-group">
                        <label>Amount (KES)</label>
                        <input type="number" name="amount" placeholder="e.g 100" required>
                    </div>
                    <button type="submit" class="mpesa-btn">
                        <span class="btn-text">Pay Now</span>
                        <span class="btn-loader"></span>
                    </button>
                    <div class="mpesa-message"></div>
                </form>
                <div class="mpesa-footer">
                    Powered by KKDynamic ENterprise solutions
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function initiate_payment() {
        check_ajax_referer( 'mpesa_pay_nonce', 'nonce' );

        $phone = sanitize_text_field( $_POST['phone'] );
        $amount = (float) $_POST['amount'];

        if ( empty( $phone ) || empty( $amount ) ) {
            wp_send_json_error( 'Please fill in all fields.' );
        }

        $api = new WP_Mpesa_Gateway_API();
        $response = $api->initiate_stk_push( $phone, $amount );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        if ( isset( $response['ResponseCode'] ) && $response['ResponseCode'] == '0' ) {
            wp_send_json_success( 'Please check your phone to complete the payment.' );
        } else {
            $msg = isset( $response['errorMessage'] ) ? $response['errorMessage'] : 'Payment initiation failed.';
            wp_send_json_error( $msg );
        }
    }
    public function render_cart() {
        ob_start();
        $cart = new WP_Mpesa_Gateway_Cart();
        $items = $cart->get_cart();
        ?>
        <div class="mpesa-cart-wrapper">
            <h2>Your Cart</h2>
            <?php if ( empty( $items ) ) : ?>
                <p>Your cart is empty.</p>
                <a href="<?php echo get_permalink( get_option( 'mpesa_shop_page_id' ) ); ?>" class="button">Go to Shop</a>
            <?php else : ?>
                <table class="mpesa-cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $items as $id => $item ) : ?>
                            <tr>
                                <td><?php echo esc_html( $item['name'] ); ?></td>
                                <td><?php echo number_format( $item['price'], 2 ); ?></td>
                                <td><?php echo $item['qty']; ?></td>
                                <td><?php echo number_format( $item['price'] * $item['qty'], 2 ); ?></td>
                                <td><a href="#" class="remove-cart-item" data-id="<?php echo $id; ?>">Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-total">
                    <strong>Total: KES <?php echo number_format( $cart->get_total(), 2 ); ?></strong>
                </div>
                <a href="<?php echo get_permalink( get_option( 'mpesa_checkout_page_id' ) ); ?>" class="button primary">Proceed to Checkout</a>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_checkout() {
        ob_start();
        $cart = new WP_Mpesa_Gateway_Cart();
        $total = $cart->get_total();
        $items = $cart->get_cart();
        
        if ( $total <= 0 ) {
            echo '<div class="mpesa-empty-cart"><p>Your cart is empty.</p><a href="'.get_permalink( get_option( 'mpesa_shop_page_id' ) ).'" class="mpesa-btn">Return to Shop</a></div>';
            return ob_get_clean();
        }
        ?>
        <div class="mpesa-checkout-container">
            
            <div class="mpesa-checkout-details">
                <div class="mpesa-card">
                    <div class="mpesa-card-header">
                        <h3><span class="dashicons dashicons-lock"></span> Secure Checkout</h3>
                        <p>Complete your purchase via M-Pesa</p>
                    </div>
                    
                    <form id="mpesa-checkout-form">
                        <div class="mpesa-form-row">
                            <div class="mpesa-input-group">
                                <label>M-Pesa Phone Number</label>
                                <div class="input-with-icon">
                                    <span class="dashicons dashicons-smartphone"></span>
                                    <input type="text" name="phone" placeholder="07XX XXX XXX" required>
                                </div>
                                <small>Format: 07XX or 2547XX</small>
                            </div>
                        </div>

                        <div class="mpesa-form-row">
                            <div class="mpesa-input-group">
                                <label>Email Address</label>
                                <div class="input-with-icon">
                                    <span class="dashicons dashicons-email"></span>
                                    <input type="email" name="email" placeholder="you@example.com" required>
                                </div>
                                <small>We'll send your receipt here.</small>
                            </div>
                        </div>

                        <div class="mpesa-payment-method">
                            <label>Payment Method</label>
                            <div class="method-card selected">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/M-PESA_LOGO-01.svg/300px-M-PESA_LOGO-01.svg.png" alt="M-Pesa">
                                <span>M-Pesa Express (STK Push)</span>
                                <span class="dashicons dashicons-saved"></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="mpesa-btn mpesa-btn-block mpesa-pay-btn">
                            Pay KES <?php echo number_format( $total, 2 ); ?>
                            <span class="btn-arrow">&rarr;</span>
                        </button>
                        
                        <div class="mpesa-secure-badge">
                            <span class="dashicons dashicons-shield"></span> Secured by M-Pesa
                        </div>
                        <div class="mpesa-message"></div>
                    </form>
                </div>
            </div>

            <div class="mpesa-checkout-summary">
                <div class="mpesa-card summary-card">
                    <h3>Order Summary</h3>
                    <ul class="summary-items">
                        <?php foreach($items as $item): ?>
                            <li>
                                <span class="item-name"><?php echo esc_html($item['name']); ?> <small>x<?php echo $item['qty']; ?></small></span>
                                <span class="item-price">KES <?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="summary-total">
                        <span>Total to Pay</span>
                        <span>KES <?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>

        </div>
        <div class="mpesa-checkout-footer" style="text-align: center; margin-top: 30px; color: #888; font-size: 0.85rem; padding-bottom: 20px;">
            Powered by <a href="#" style="color: #4CAF50; text-decoration: none; font-weight: 600;">KK Dynamic Enterprise Solutions</a>
        </div>
        <?php
        return ob_get_clean();
    }

    public function ajax_add_to_cart() {
        error_log("M-Pesa: ajax_add_to_cart called");
        check_ajax_referer( 'mpesa_pay_nonce', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $qty = intval( $_POST['qty'] );
        
        error_log("M-Pesa: Adding Product $product_id, Qty $qty");

        $cart = new WP_Mpesa_Gateway_Cart();
        $cart->add_to_cart( $product_id, $qty );
        
        $count = count( $cart->get_cart() );
        error_log("M-Pesa: Cart count now $count");

        wp_send_json_success( array( 'count' => $count, 'message' => 'Added to cart' ) );
    }

    public function ajax_process_checkout() {
        check_ajax_referer( 'mpesa_pay_nonce', 'nonce' );
        
        $phone = sanitize_text_field( $_POST['phone'] );
        $email = sanitize_email( $_POST['email'] );
        
        $cart = new WP_Mpesa_Gateway_Cart();
        $items = $cart->get_cart();
        $total = $cart->get_total();

        if ( $total <= 0 ) wp_send_json_error( 'Cart is empty' );

        // Init Payment
        $api = new WP_Mpesa_Gateway_API();
        $response = $api->initiate_stk_push( $phone, $total );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        if ( isset( $response['ResponseCode'] ) && $response['ResponseCode'] == '0' ) {
            // Create Order
            $order_id = wp_insert_post( array(
                'post_type' => 'mpesa_order',
                'post_title' => 'Order #' . time() . ' - ' . $phone,
                'post_status' => 'publish'
            ));
            
            update_post_meta( $order_id, '_customer_phone', $phone );
            update_post_meta( $order_id, '_customer_email', $email );
            update_post_meta( $order_id, '_total_amount', $total );
            update_post_meta( $order_id, '_cart_items', $items );
            update_post_meta( $order_id, '_checkout_request_id', $response['CheckoutRequestID'] );
            update_post_meta( $order_id, '_payment_status', 'PENDING' );

            $cart->clear_cart();
            
            wp_send_json_success( array( 'redirect' => get_permalink( get_option( 'mpesa_thankyou_page_id' ) ) . '?order=' . $order_id ) );
        } else {
             wp_send_json_error( isset($response['errorMessage']) ? $response['errorMessage'] : 'Payment failed' );
        }
    }
}
