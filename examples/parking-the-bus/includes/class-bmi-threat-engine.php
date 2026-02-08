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
    private function log_incident($ip, $reason)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . BMI_ADAR_DB_TABLE;

        // 檢查是否已經有這個 IP 的 Pending 報告，避免重複刷
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE source_ip = %s AND status = 0",
            $ip
        ));

        if (! $exists) {
            $wpdb->insert(
                $table_name,
                array(
                    'source_ip' => $ip,
                    'evidence_blob' => $reason, // 暫時存原因，之後存完整 Log
                    'status' => 0 // Pending
                ),
                array('%s', '%s', '%d')
            );
        }
    }
}
