<?php

class WP_Mpesa_Gateway_API {

    private $settings;

    public function __construct() {
        $this->settings = get_option( 'mpesa_gateway_settings' );
    }

    public function get_api_url( $endpoint ) {
        $env = isset( $this->settings['environment'] ) ? $this->settings['environment'] : 'sandbox';
        $base_url = ( $env === 'live' ) ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
        return $base_url . $endpoint;
    }

    public function generate_access_token() {
        $consumer_key = isset( $this->settings['consumer_key'] ) ? $this->settings['consumer_key'] : '';
        $consumer_secret = isset( $this->settings['consumer_secret'] ) ? $this->settings['consumer_secret'] : '';

        if ( empty( $consumer_key ) || empty( $consumer_secret ) ) {
            return new WP_Error( 'missing_credentials', 'Consumer Key or Secret is missing.' );
        }

        $credentials = base64_encode( $consumer_key . ':' . $consumer_secret );
        $url = $this->get_api_url( '/oauth/v1/generate?grant_type=client_credentials' );

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . $credentials,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'M-Pesa API Error (Token): ' . $response->get_error_message() );
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );

        if ( isset( $data->access_token ) ) {
            return $data->access_token;
        }

        error_log( 'M-Pesa API Token Failed: ' . print_r( $data, true ) );
        return new WP_Error( 'auth_failed', 'Failed to generate access token', $data );
    }

    public function initiate_stk_push( $phone, $amount ) {
        $access_token = $this->generate_access_token();
        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $shortcode = isset( $this->settings['shortcode'] ) ? $this->settings['shortcode'] : '';
        $passkey = isset( $this->settings['passkey'] ) ? $this->settings['passkey'] : '';
        $callback_url = isset( $this->settings['callback_url'] ) ? $this->settings['callback_url'] : home_url( '/wp-json/mpesa/v1/callback' );

        // Sanitize phone (254...)
        $phone = preg_replace( '/^0/', '254', $phone );
        $phone = preg_replace( '/^\+254/', '254', $phone );

        $timestamp = date( 'YmdHis' );
        $password = base64_encode( $shortcode . $passkey . $timestamp );

        $payload = array(
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$amount,
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callback_url,
            'AccountReference' => 'Payment',
            'TransactionDesc' => 'Payment for Order'
        );

        $url = $this->get_api_url( '/mpesa/stkpush/v1/processrequest' );

        $response = wp_remote_post( $url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( $payload ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'M-Pesa STK Request Error: ' . $response->get_error_message() );
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Log
        error_log( 'M-Pesa STK Response: ' . $body );

        if ( isset( $data['ResponseCode'] ) && $data['ResponseCode'] == '0' ) {
            // Log to DB
            WP_Mpesa_Gateway_DB::insert_transaction(
                $data['CheckoutRequestID'],
                $data['MerchantRequestID'],
                $phone,
                $amount
            );
        }

        return $data;
    }

    public function register_callback_route() {
        register_rest_route( 'mpesa/v1', '/callback', array(
            'methods' => 'POST',
            'callback' => array( $this, 'handle_callback' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public function handle_callback( $request ) {
        $body = $request->get_body();
        error_log( 'M-Pesa Callback: ' . $body );
        $data = json_decode( $body, true );

        if ( isset( $data['Body']['stkCallback'] ) ) {
            $callback = $data['Body']['stkCallback'];
            $checkout_id = $callback['CheckoutRequestID'];
            $result_code = $callback['ResultCode'];
            $result_desc = $callback['ResultDesc'];

            $update_data = array(
                'result_code' => $result_code,
                'result_desc' => $result_desc,
                'status' => ( $result_code == 0 ) ? 'COMPLETED' : 'FAILED',
            );

            if ( isset( $callback['CallbackMetadata']['Item'] ) ) {
                foreach ( $callback['CallbackMetadata']['Item'] as $item ) {
                    if ( $item['Name'] === 'MpesaReceiptNumber' ) {
                        $update_data['mpesa_receipt_number'] = $item['Value'];
                    }
                }
            }

            WP_Mpesa_Gateway_DB::update_transaction( $checkout_id, $update_data );
        }

        return new WP_REST_Response( array( 'status' => 'success' ), 200 );
    }
}
