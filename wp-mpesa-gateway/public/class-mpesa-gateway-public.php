<?php

class WP_Mpesa_Gateway_Public {

    public function enqueue_scripts() {
        wp_enqueue_style( 'mpesa-gateway-css', WP_MPESA_GATEWAY_URL . 'assets/css/style.css', array(), WP_MPESA_GATEWAY_VERSION );
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
}
