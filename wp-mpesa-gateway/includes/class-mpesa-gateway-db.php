<?php

class WP_Mpesa_Gateway_DB {

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'mpesa_gateway_transactions';
	}

	public static function create_table() {
		global $wpdb;
		$table_name = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			checkout_request_id varchar(100) NOT NULL,
			merchant_request_id varchar(100) NOT NULL,
			phone_number varchar(20) NOT NULL,
			amount decimal(10,2) NOT NULL,
			mpesa_receipt_number varchar(50) DEFAULT NULL,
			result_code varchar(10) DEFAULT NULL,
			result_desc text DEFAULT NULL,
			status varchar(20) DEFAULT 'PENDING',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY checkout_request_id (checkout_request_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

    public static function insert_transaction($checkout_id, $merchant_id, $phone, $amount) {
        global $wpdb;
        $wpdb->insert(
            self::table_name(),
            array(
                'checkout_request_id' => $checkout_id,
                'merchant_request_id' => $merchant_id,
                'phone_number' => $phone,
                'amount' => $amount,
                'status' => 'PENDING'
            )
        );
    }

    public static function update_transaction($checkout_id, $data) {
        global $wpdb;
        $wpdb->update(
            self::table_name(),
            $data,
            array('checkout_request_id' => $checkout_id)
        );
    }

    public static function get_stats() {
		global $wpdb;
		$table_name = self::table_name();

        $total_revenue = $wpdb->get_var("SELECT SUM(amount) FROM $table_name WHERE status = 'COMPLETED'");
        $total_transactions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $successful_transactions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'COMPLETED'");
        $failed_transactions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'FAILED'");

        return array(
            'revenue' => $total_revenue ? $total_revenue : 0,
            'total' => $total_transactions,
            'success' => $successful_transactions,
            'failed' => $failed_transactions
        );
	}

    public static function get_transactions($limit = 50, $offset = 0) {
        global $wpdb;
        $table_name = self::table_name();
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    }
}
