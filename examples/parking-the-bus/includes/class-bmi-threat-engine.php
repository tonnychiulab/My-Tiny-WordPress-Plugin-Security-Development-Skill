<?php

if (! defined('ABSPATH')) {
    exit;
}

class BMI_Threat_Engine
{

    public function run()
    {
        // 監聽登入失敗事件 (暴力破解)
        add_action('wp_login_failed', array($this, 'on_login_failed'));
    }

    /**
     * 當有人登入失敗時觸發
     * @param string $username
     */
    public function on_login_failed($username)
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        // TODO: 這裡之後要接 AbuseIPDB API 檢查信譽
        // 目前我們先假設它是壞人，並記錄下來

        $this->log_incident($ip, 'Login Failed: ' . $username);
    }

    /**
     * 記錄攻擊事件到資料庫
     */
    /**
     * 記錄攻擊事件到資料庫
     */
    private function log_incident($ip, $reason)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . BMI_ADAR_DB_TABLE;

        // 取得 API Key
        $api_key = get_option('bmi_abuseipdb_key');
        $asn = '';
        $isp = '';

        // 如果有 Key，就去查情資 (Intelligence Check)
        if (! empty($api_key)) {
            require_once BMI_ADAR_PATH . 'includes/class-bmi-abuseipdb.php';
            $abuse_client = new BMI_AbuseIPDB($api_key);
            $ip_data = $abuse_client->check_ip($ip);

            if (! is_wp_error($ip_data) && isset($ip_data['isp'])) {
                $isp = $ip_data['isp'];
                $asn = isset($ip_data['asn']) ? 'AS' . $ip_data['asn'] : '';
                // 未來可以在這裡由 ASN 判斷是否為 AWS/GCP，決定 status 或 report_method
            }
        }

        // 寫入資料庫 (含情資)
        $wpdb->insert(
            $table_name,
            array(
                'source_ip' => $ip,
                'asn' => $asn,
                'isp_name' => $isp,
                'evidence_blob' => $reason,
                'status' => 0 // Pending
            ),
            array('%s', '%s', '%s', '%s', '%d')
        );
    }
}
