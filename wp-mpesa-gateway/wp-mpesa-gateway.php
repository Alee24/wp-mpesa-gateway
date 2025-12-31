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
	}

	private function load_dependencies() {
		require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-db.php';
		require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-api.php';
		require_once WP_MPESA_GATEWAY_PATH . 'admin/class-mpesa-gateway-admin.php';
		require_once WP_MPESA_GATEWAY_PATH . 'public/class-mpesa-gateway-public.php';
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
        add_shortcode( 'stk_push_form', array( $plugin_public, 'render_form' ) ); // Constructive alias
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_mpesa_initiate', array( $plugin_public, 'initiate_payment' ) );
		add_action( 'wp_ajax_nopriv_mpesa_initiate', array( $plugin_public, 'initiate_payment' ) );
        
        // Callback handler
        add_action( 'rest_api_init', array( new WP_Mpesa_Gateway_API(), 'register_callback_route' ) );
	}
}

// Activation Hook
register_activation_hook( __FILE__, array( 'WP_Mpesa_Gateway_Activator', 'activate' ) );

class WP_Mpesa_Gateway_Activator {
	public static function activate() {
		require_once WP_MPESA_GATEWAY_PATH . 'includes/class-mpesa-gateway-db.php';
		WP_Mpesa_Gateway_DB::create_table();
	}
}

// Initialize
$plugin = new WP_Mpesa_Gateway();
$plugin->run();
