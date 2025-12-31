<?php
/**
 * Plugin Name: WP Mpesa Gateway
 * Plugin URI:  https://kkdes.co.ke/wp-mpesa-gateway/
 * Description: A complete, professional M-Pesa STK Push payment solution for WordPress.
 * Version:     1.0.1
 * Author:      KK Dynamic Enterprise Solutions Ltd
 * Text Domain: wp-mpesa-gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'WP_MPESA_GATEWAY_VERSION', '1.0.1' );
define( 'WP_MPESA_GATEWAY_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_MPESA_GATEWAY_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class WP_Mpesa_Gateway {

	public function run() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
        add_action( 'init', array( $this, 'start_session' ), 1 );
	}

    public function start_session() {
        if ( ! session_id() ) {
            session_start();
        }
    }

	private function load_dependencies() {
		require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-db.php';
		require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-api.php';
        require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-cpt.php'; // New CPT Class
        require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-install.php'; // Install Class
		require_once WP_MPESA_GATEWAY_PATH . 'admin/class-mpesa-gateway-admin.php';
		require_once WP_MPESA_GATEWAY_PATH . 'public/class-mpesa-gateway-public.php';
        require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-cart.php'; // Cart Class
        require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-pos.php';

        require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-template-loader.php'; // Template Loader

        $cpt = new WP_Mpesa_Gateway_CPT();
        $cpt->init();

        $loader = new WP_Mpesa_Gateway_Template_Loader();
        $loader->init();

        
        $pos = new WP_Mpesa_Gateway_POS();
        $pos->init();
	}

	private function define_admin_hooks() {
		$plugin_admin = new WP_Mpesa_Gateway_Admin();
		add_action( 'admin_menu', array( $plugin_admin, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $plugin_admin, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
	}

	private function define_public_hooks() {
		$plugin_public = new WP_Mpesa_Gateway_Public();
		add_shortcode( 'mpesa_stk_push', array( $plugin_public, 'render_form' ) );
        add_shortcode( 'stk_push_form', array( $plugin_public, 'render_form' ) ); 
        add_shortcode( 'mpesa_cart', array( $plugin_public, 'render_cart' ) );
        add_shortcode( 'mpesa_checkout', array( $plugin_public, 'render_checkout' ) );

		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_mpesa_initiate', array( $plugin_public, 'initiate_payment' ) );
		add_action( 'wp_ajax_nopriv_mpesa_initiate', array( $plugin_public, 'initiate_payment' ) );

        add_action( 'wp_ajax_mpesa_add_to_cart', array( $plugin_public, 'ajax_add_to_cart' ) );
        add_action( 'wp_ajax_nopriv_mpesa_add_to_cart', array( $plugin_public, 'ajax_add_to_cart' ) );

        add_action( 'wp_ajax_mpesa_process_checkout', array( $plugin_public, 'ajax_process_checkout' ) );
        add_action( 'wp_ajax_nopriv_mpesa_process_checkout', array( $plugin_public, 'ajax_process_checkout' ) );
        
        // Callback handler
        add_action( 'rest_api_init', array( new WP_Mpesa_Gateway_API(), 'register_callback_route' ) );
	}
}

// Activation Hook
register_activation_hook( __FILE__, array( 'WP_Mpesa_Gateway_Activator', 'activate' ) );

class WP_Mpesa_Gateway_Activator {
	public static function activate() {
		require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-db.php';
        require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-install.php';
		WP_Mpesa_Gateway_DB::create_table();
        WP_Mpesa_Gateway_Install::install();
	}
}

// Initialize
$plugin = new WP_Mpesa_Gateway();
$plugin->run();
