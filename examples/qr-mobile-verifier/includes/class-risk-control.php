<?php

/**
 * Class Risk_Control
 * 負責執行所有「阻擋惡意申請」的邏輯
 */

if (! defined('ABSPATH')) {
    exit;
}

class QRMV_Risk_Control
{

    private $table_name;

    // 設定風控規則 (閾值)
    const LIMIT_IP_DAILY = 5;      // 同一個 IP 一天最多 5 次
    const LIMIT_PHONE_MONTHLY = 3; // 同一支手機 一個月最多 3 次

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'qrmv_history';
    }

    /**
     * 檢查 IP 信譽
     * @param string $ip
     * @return bool|string True if pass, Error message if blocked
     */
    public function check_ip($ip)
    {
        global $wpdb;

        // 查詢過去 24 小時內，這個 IP 成功驗證的次數
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name 
			 WHERE ip_address = %s 
			 AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
			 AND status = 'success'", // 只計算成功的，或者計算所有嘗試的？通常计算成功的绑定数
            $ip
        ));

        if ($count >= self::LIMIT_IP_DAILY) {
            return "此 IP位置 ($ip) 今日申請次數已達上限，請明天再試。";
        }

        return true;
    }

    /**
     * 檢查手機號碼使用頻率
     * @param string $phone
     * @return bool|string True if pass, Error message if blocked
     */
    public function check_phone($phone)
    {
        global $wpdb;

        // 查詢過去 30 天內，這支手機成功驗證的次數
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name 
			 WHERE phone_number = %s 
			 AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
			 AND status = 'success'",
            $phone
        ));

        if ($count >= self::LIMIT_PHONE_MONTHLY) {
            return "此手機號碼 ($phone) 近期驗證次數過多，為保障安全，請使用其他號碼。";
        }

        return true;
    }
}
