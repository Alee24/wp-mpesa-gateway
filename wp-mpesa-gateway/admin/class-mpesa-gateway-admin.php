<?php

class WP_Mpesa_Gateway_Admin {

    private $option_name = 'mpesa_gateway_settings';

    public function add_plugin_page() {
        add_menu_page(
            'Mpesa Gateway',
            'Mpesa Gateway',
            'manage_options',
            'mpesa-gateway-dashboard',
            array( $this, 'render_dashboard_page' ),
            'dashicons-money',
            56
        );

        add_submenu_page(
            'mpesa-gateway-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'mpesa-gateway-dashboard',
            array( $this, 'render_dashboard_page' )
        );

        add_submenu_page(
            'mpesa-gateway-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'mpesa-gateway-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function enqueue_styles( $hook ) {
        if ( strpos( $hook, 'mpesa-gateway' ) !== false ) {
            wp_enqueue_style( 'mpesa-gateway-admin', WP_MPESA_GATEWAY_URL . 'assets/css/admin.css', array(), WP_MPESA_GATEWAY_VERSION );
        }
    }

    public function render_dashboard_page() {
        require_once WP_MPESA_GATEWAY_PATH . 'admin/class-mpesa-gateway-dashboard.php';
        $dashboard = new WP_Mpesa_Gateway_Dashboard();
        $dashboard->render();
    }

    public function render_settings_page() {
        ?>
        <div class="wrap mpesa-settings-wrap">
            <h1>M-Pesa Gateway Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'mpesa_gateway_group' );
                do_settings_sections( 'mpesa-gateway-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting( 'mpesa_gateway_group', $this->option_name );
        
        add_settings_section( 'mpesa_api_creds', 'API Credentials', null, 'mpesa-gateway-settings' );

        $fields = array(
            'environment' => array('label' => 'Environment', 'type' => 'select', 'options' => array('sandbox' => 'Sandbox', 'live' => 'Live')),
            'consumer_key' => array('label' => 'Consumer Key', 'type' => 'text'),
            'consumer_secret' => array('label' => 'Consumer Secret', 'type' => 'password'),
            'shortcode' => array('label' => 'Business Shortcode', 'type' => 'text'),
            'passkey' => array('label' => 'Passkey', 'type' => 'password'),
            'callback_url' => array('label' => 'Callback URL', 'type' => 'text', 'desc' => 'Default: ' . home_url('/wp-json/mpesa/v1/callback')),
        );

        foreach($fields as $id => $field) {
            add_settings_field(
                $id,
                $field['label'],
                array( $this, 'render_field' ),
                'mpesa-gateway-settings',
                'mpesa_api_creds',
                array( 'id' => $id, 'field' => $field )
            );
        }
    }

    public function render_field( $args ) {
        $options = get_option( $this->option_name );
        $id = $args['id'];
        $field = $args['field'];
        $val = isset( $options[$id] ) ? $options[$id] : '';

        if( $field['type'] == 'select' ) {
            echo '<select name="'.$this->option_name.'['.$id.']">';
            foreach($field['options'] as $k => $v) {
                $selected = ($val == $k) ? 'selected' : '';
                echo "<option value='$k' $selected>$v</option>";
            }
            echo '</select>';
        } else {
            $type = $field['type'];
            echo "<input type='$type' name='".$this->option_name."[$id]' value='".esc_attr($val)."' class='regular-text'>";
        }

        if(isset($field['desc'])) {
            echo "<p class='description'>{$field['desc']}</p>";
        }
    }
}
