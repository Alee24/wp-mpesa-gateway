<?php
// Mock WordPress functions
function get_option($name) {
    return array(
        'consumer_key' => 'test_key',
        'consumer_secret' => 'test_secret',
        'shortcode' => '174379',
        'passkey' => 'test_passkey',
        'environment' => 'sandbox',
        'callback_url' => 'http://example.com/callback'
    );
}

function home_url($path = '') {
    return 'http://example.com' . $path;
}

function is_wp_error($thing) {
    return false;
}

function wp_remote_get($url, $args) {
    echo "GET Request to: $url\n";
    return array('body' => json_encode(array('access_token' => 'mock_access_token')));
}

function wp_remote_post($url, $args) {
    echo "POST Request to: $url\n";
    echo "Payload: " . $args['body'] . "\n";
    return array('body' => json_encode(array(
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success. Request accepted for processing',
        'CheckoutRequestID' => 'ws_CO_DMZ_12345',
        'MerchantRequestID' => '12345-67890'
    )));
}

function wp_remote_retrieve_body($response) {
    return $response['body'];
}
class WP_Error { public function __construct($c,$m){} }

// Mock DB
class wpdb {
    public $prefix = 'wp_';
    public function insert($table, $data) {
        echo "DB Insert into $table: " . json_encode($data) . "\n";
    }
}
$wpdb = new wpdb();

// Include the class
require_once 'wp-stk-push/includes/class-stk-push-api.php';

// Test
echo "Testing STK Push API Logic...\n";
$api = new STK_Push_API();
$response = $api->initiate_stk_push('0712345678', 100);

print_r($response);
