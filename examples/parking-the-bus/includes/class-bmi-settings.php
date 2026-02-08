<?php

if (! defined('ABSPATH')) {
    exit;
}

class BMI_Settings
{

    public function run()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function add_plugin_page()
    {
        add_options_page(
            'Parking The Bus Settings',
            'Parking The Bus',
            'manage_options',
            'bmi-parking-the-bus',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page()
    {
?>
        <div class="wrap">
            <h1>🚌 Parking The Bus - 設定</h1>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('bmi_option_group');
                do_settings_sections('bmi-parking-the-bus');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public function page_init()
    {
        register_setting(
            'bmi_option_group', // Option group
            'bmi_abuseipdb_key', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'bmi_setting_section_id', // ID
            'API 設定', // Title
            array($this, 'print_section_info'), // Callback
            'bmi-parking-the-bus' // Page
        );

        add_settings_field(
            'bmi_abuseipdb_key', // ID
            'AbuseIPDB API Key', // Title 
            array($this, 'api_key_callback'), // Callback
            'bmi-parking-the-bus', // Page
            'bmi_setting_section_id' // Section           
        );
    }

    public function sanitize($input)
    {
        $new_input = sanitize_text_field($input);

        if (! empty($new_input)) {
            // 驗證 API Key 是否有效
            require_once BMI_ADAR_PATH . 'includes/class-bmi-abuseipdb.php';
            $abuse_client = new BMI_AbuseIPDB($new_input);

            // 測試查詢 Google DNS (8.8.8.8)
            // 強制略過快取，確保是真的去連 API 驗證 Key
            $response = $abuse_client->check_ip('8.8.8.8', true);

            if (is_wp_error($response)) {
                add_settings_error(
                    'bmi_abuseipdb_key',
                    'bmi_api_error',
                    '❌ API Key 驗證失敗：' . $response->get_error_message(),
                    'error'
                );
                return get_option('bmi_abuseipdb_key'); // Reject change
            }

            if (! isset($response['isp'])) {
                add_settings_error(
                    'bmi_abuseipdb_key',
                    'bmi_api_invalid',
                    '❌ API Key 驗證失敗：回應格式不正確。',
                    'error'
                );
                return get_option('bmi_abuseipdb_key'); // Reject change
            }

            // 驗證成功
            add_settings_error(
                'bmi_abuseipdb_key',
                'bmi_api_success',
                '✅ API Key 驗證成功！已成功連線 (Test IP: 8.8.8.8 -> ' . $response['isp'] . ')',
                'success'
            );
        }

        return $new_input;
    }

    public function print_section_info()
    {
        print '請輸入您的 AbuseIPDB API Key 以啟用情資查詢功能：';
    }

    public function api_key_callback()
    {
        $value = get_option('bmi_abuseipdb_key', '');
        printf(
            '<input type="text" id="bmi_abuseipdb_key" name="bmi_abuseipdb_key" value="%s" style="width: 400px;" />',
            esc_attr($value)
        );
        echo '<br><small>還沒有 Key 嗎？<a href="https://www.abuseipdb.com/" target="_blank">點此免費申請</a></small>';
    }
}
