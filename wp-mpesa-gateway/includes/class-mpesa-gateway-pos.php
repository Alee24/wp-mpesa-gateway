<?php

class WP_Mpesa_Gateway_POS {

    public function init() {
        add_action( 'add_meta_boxes', array( $this, 'add_product_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_product_meta' ) );
        add_action( 'admin_menu', array( $this, 'add_pos_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pos_assets' ) );
        add_action( 'wp_ajax_mpesa_pos_checkout', array( $this, 'process_pos_checkout' ) );
    }

    // --- Meta Boxes for Product ---
    public function add_product_meta_boxes() {
        add_meta_box( 'mpesa_product_data', 'Product Data', array( $this, 'render_product_meta_box' ), 'mpesa_product', 'normal', 'high' );
    }

    public function render_product_meta_box( $post ) {
        $price = get_post_meta( $post->ID, '_price', true );
        $stock = get_post_meta( $post->ID, '_stock', true );
        ?>
        <p>
            <label for="mpesa_product_price">Price (KES):</label>
            <input type="number" name="mpesa_product_price" id="mpesa_product_price" value="<?php echo esc_attr( $price ); ?>" class="widefat">
        </p>
        <p>
            <label for="mpesa_product_stock">Stock:</label>
            <input type="number" name="mpesa_product_stock" id="mpesa_product_stock" value="<?php echo esc_attr( $stock ); ?>" class="widefat">
        </p>
        <?php
    }

    public function save_product_meta( $post_id ) {
        if ( isset( $_POST['mpesa_product_price'] ) ) {
            update_post_meta( $post_id, '_price', sanitize_text_field( $_POST['mpesa_product_price'] ) );
        }
        if ( isset( $_POST['mpesa_product_stock'] ) ) {
            update_post_meta( $post_id, '_stock', sanitize_text_field( $_POST['mpesa_product_stock'] ) );
        }
    }

    // --- POS Menu & Page ---
    public function add_pos_menu() {
        add_menu_page( 'POS System', 'POS System', 'manage_options', 'mpesa-pos', array( $this, 'render_pos_page' ), 'dashicons-store', 57 );
        add_submenu_page( 'mpesa-pos', 'POS Terminal', 'POS Terminal', 'manage_options', 'mpesa-pos', array( $this, 'render_pos_page' ) );
        add_submenu_page( 'mpesa-pos', 'Products', 'Products', 'manage_options', 'edit.php?post_type=mpesa_product' );
        add_submenu_page( 'mpesa-pos', 'Orders', 'Orders', 'manage_options', 'edit.php?post_type=mpesa_order' );
    }

    public function enqueue_pos_assets( $hook ) {
        if ( $hook === 'toplevel_page_mpesa-pos' ) {
            wp_enqueue_style( 'mpesa-pos-css', WP_MPESA_GATEWAY_URL . 'assets/css/pos.css', array(), WP_MPESA_GATEWAY_VERSION );
            wp_enqueue_script( 'mpesa-pos-js', WP_MPESA_GATEWAY_URL . 'assets/js/pos.js', array( 'jquery' ), WP_MPESA_GATEWAY_VERSION, true );
            wp_localize_script( 'mpesa-pos-js', 'mpesa_pos_vars', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'mpesa_pos_nonce' ),
            ));
        }
    }

    public function render_pos_page() {
        // Fetch products
        $products = get_posts( array( 'post_type' => 'mpesa_product', 'numberposts' => -1 ) );
        ?>
        <div class="wrap mpesa-pos-wrap">
            <h1 class="wp-heading-inline">POS Terminal</h1>
            
            <div class="pos-container">
                <!-- Products Grid -->
                <div class="pos-products">
                    <input type="text" id="pos-search" placeholder="Search products...">
                    <div class="products-grid">
                        <?php foreach ( $products as $product ) : 
                            $price = get_post_meta( $product->ID, '_price', true );
                            $img = get_the_post_thumbnail_url( $product->ID, 'medium' ) ?: WP_MPESA_GATEWAY_URL . 'assets/images/placeholder.png';
                        ?>
                            <div class="pos-product-card" data-id="<?php echo $product->ID; ?>" data-name="<?php echo $product->post_title; ?>" data-price="<?php echo $price; ?>">
                                <div class="product-img" style="background-image: url('<?php echo $img; ?>');"></div>
                                <div class="product-info">
                                    <h4><?php echo $product->post_title; ?></h4>
                                    <span class="price">KES <?php echo number_format( (float)$price, 2 ); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cart Sidebar -->
                <div class="pos-cart">
                    <h3>Current Order</h3>
                    <div class="cart-items" id="cart-items">
                        <!-- JS wlll populate this -->
                        <p class="empty-cart">Cart is empty</p>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="row"><span>Total:</span> <span id="cart-total">KES 0.00</span></div>
                        <div class="form-group">
                            <input type="text" id="customer-phone" placeholder="Customer Phone (07...)" class="widefat">
                        </div>
                        <button id="pos-pay-btn" class="button button-primary button-large">Pay with M-Pesa</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function process_pos_checkout() {
        check_ajax_referer( 'mpesa_pos_nonce', 'nonce' );

        $phone = sanitize_text_field( $_POST['phone'] );
        $cart = isset( $_POST['cart'] ) ? $_POST['cart'] : array();
        
        if ( empty( $phone ) || empty( $cart ) ) {
            wp_send_json_error( 'Invalid data' );
        }

        $total = 0;
        foreach ( $cart as $item ) {
            $total += $item['price'] * $item['qty'];
        }

        // Initiate Payment
        $api = new WP_Mpesa_Gateway_API();
        $response = $api->initiate_stk_push( $phone, $total );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        if ( isset( $response['ResponseCode'] ) && $response['ResponseCode'] == '0' ) {
            // Create Order Post
            $order_id = wp_insert_post( array(
                'post_type' => 'mpesa_order',
                'post_title' => 'Order #' . time() . ' - ' . $phone,
                'post_status' => 'publish',
            ));

            update_post_meta( $order_id, '_customer_phone', $phone );
            update_post_meta( $order_id, '_total_amount', $total );
            update_post_meta( $order_id, '_cart_items', $cart );
            update_post_meta( $order_id, '_checkout_request_id', $response['CheckoutRequestID'] );
            update_post_meta( $order_id, '_payment_status', 'PENDING' );

            wp_send_json_success( 'Payment pushed to phone!' );
        } else {
            wp_send_json_error( 'Payment failed to initiate.' );
        }
    }
}
