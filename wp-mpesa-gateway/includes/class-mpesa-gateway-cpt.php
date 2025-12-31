<?php

class WP_Mpesa_Gateway_CPT {

    public function init() {
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
    }

    public function register_post_types() {
        // Product CPT
        register_post_type( 'mpesa_product', array(
            'labels' => array(
                'name' => 'Products',
                'singular_name' => 'Product',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Product',
                'edit_item' => 'Edit Product',
                'all_items' => 'All Products',
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_menu' => 'mpesa-gateway-dashboard', // Matched to Admin slug
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'menu_icon' => 'dashicons-cart',
            'rewrite' => array( 'slug' => 'product' ),
        ));

        // Order CPT
        register_post_type( 'mpesa_order', array(
            'labels' => array(
                'name' => 'Orders',
                'singular_name' => 'Order',
                'edit_item' => 'View Order',
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'mpesa-gateway-dashboard',
            'supports' => array( 'title' ),
            'menu_icon' => 'dashicons-clipboard',
            'capabilities' => array( 'create_posts' => false ), // Orders are created programmatically (usually)
            'map_meta_cap' => true,
        ));
    }

    public function add_meta_boxes() {
        add_meta_box( 'mpesa_product_data', 'Product Data', array( $this, 'render_product_meta' ), 'mpesa_product', 'normal', 'high' );
        add_meta_box( 'mpesa_order_data', 'Order Details', array( $this, 'render_order_meta' ), 'mpesa_order', 'normal', 'high' );
    }

    public function render_product_meta( $post ) {
        wp_nonce_field( 'mpesa_product_meta', 'mpesa_product_meta_nonce' );

        $price = get_post_meta( $post->ID, '_price', true );
        $stock = get_post_meta( $post->ID, '_stock', true );
        $type  = get_post_meta( $post->ID, '_product_type', true );
        $file  = get_post_meta( $post->ID, '_download_url', true );
        ?>
        <div class="mpesa-field-group">
            <p>
                <label for="mpesa_product_price"><strong>Price (KES):</strong></label><br>
                <input type="number" name="mpesa_product_price" id="mpesa_product_price" value="<?php echo esc_attr( $price ); ?>" class="widefat" step="0.01">
            </p>
            <p>
                <label for="mpesa_product_stock"><strong>Stock Quantity:</strong></label><br>
                <input type="number" name="mpesa_product_stock" id="mpesa_product_stock" value="<?php echo esc_attr( $stock ); ?>" class="widefat">
            </p>
            <p>
                <label for="mpesa_product_type"><strong>Product Type:</strong></label><br>
                <select name="mpesa_product_type" id="mpesa_product_type" class="widefat">
                    <option value="physical" <?php selected( $type, 'physical' ); ?>>Physical Product</option>
                    <option value="virtual" <?php selected( $type, 'virtual' ); ?>>Virtual / Service</option>
                    <option value="downloadable" <?php selected( $type, 'downloadable' ); ?>>Downloadable File</option>
                </select>
            </p>
            <p id="mpesa-download-field" style="<?php echo ( $type === 'downloadable' ) ? '' : 'display:none;'; ?>">
                <label for="mpesa_download_url"><strong>Download File URL:</strong></label><br>
                <input type="text" name="mpesa_download_url" id="mpesa_download_url" value="<?php echo esc_attr( $file ); ?>" class="widefat" placeholder="https://...">
                <span class="description">Enter the direct URL to the file. For security, ensure this is a protected link if needed.</span>
            </p>
        </div>
        <script>
            jQuery(document).ready(function($){
                $('#mpesa_product_type').change(function(){
                    if($(this).val() === 'downloadable'){
                        $('#mpesa-download-field').slideDown();
                    } else {
                        $('#mpesa-download-field').slideUp();
                    }
                });
            });
        </script>
        <?php
    }

    public function render_order_meta( $post ) {
        $phone = get_post_meta( $post->ID, '_customer_phone', true );
        $amount = get_post_meta( $post->ID, '_total_amount', true );
        $items = get_post_meta( $post->ID, '_cart_items', true );
        $status = get_post_meta( $post->ID, '_payment_status', true );
        $receipt = get_post_meta( $post->ID, '_mpesa_receipt', true );
        ?>
        <table class="form-table">
            <tr>
                <th>Customer Phone:</th>
                <td><?php echo esc_html( $phone ); ?></td>
            </tr>
            <tr>
                <th>Total Amount:</th>
                <td>KES <?php echo number_format( (float)$amount, 2 ); ?></td>
            </tr>
            <tr>
                <th>Payment Status:</th>
                <td><strong><?php echo esc_html( $status ); ?></strong></td>
            </tr>
            <tr>
                <th>M-Pesa Receipt:</th>
                <td><?php echo esc_html( $receipt ); ?></td>
            </tr>
        </table>
        <h3>Order Items</h3>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( is_array( $items ) && ! empty( $items ) ) : ?>
                    <?php foreach ( $items as $item ) : ?>
                        <tr>
                            <td><?php echo esc_html( $item['name'] ); ?></td>
                            <td><?php echo esc_html( $item['qty'] ); ?></td>
                            <td><?php echo esc_html( $item['price'] ); ?></td>
                            <td><?php echo number_format( $item['price'] * $item['qty'], 2 ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="4">No items data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    public function save_meta_boxes( $post_id ) {
        if ( ! isset( $_POST['mpesa_product_meta_nonce'] ) || ! wp_verify_nonce( $_POST['mpesa_product_meta_nonce'], 'mpesa_product_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if ( isset( $_POST['mpesa_product_price'] ) ) update_post_meta( $post_id, '_price', sanitize_text_field( $_POST['mpesa_product_price'] ) );
        if ( isset( $_POST['mpesa_product_stock'] ) ) update_post_meta( $post_id, '_stock', sanitize_text_field( $_POST['mpesa_product_stock'] ) );
        if ( isset( $_POST['mpesa_product_type'] ) ) update_post_meta( $post_id, '_product_type', sanitize_text_field( $_POST['mpesa_product_type'] ) );
        if ( isset( $_POST['mpesa_download_url'] ) ) update_post_meta( $post_id, '_download_url', esc_url_raw( $_POST['mpesa_download_url'] ) );
    }
}
