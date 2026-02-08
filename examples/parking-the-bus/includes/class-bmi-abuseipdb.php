<?php

if (! defined('ABSPATH')) {
    exit;
}

class BMI_AbuseIPDB
{

    private $api_key;
    private $api_url = 'https://api.abuseipdb.com/api/v2/check';

    public function __construct($api_key = '')
    {
        $this->api_key = $api_key;
    }

    /**
     * Check IP Reputation
     * 優化策略:
     * 1. 檢查 Cache (Transient)
     * 2. 若無 Cache，呼叫 API
     * 3. 寫入 Cache (TTL 24h)
     * 
     * @param string $ip
     * @return array|WP_Error Response data or error
     */
    public function check_ip($ip)
    {
        // 1. Check Cycle (Cache)
        $cache_key = 'bmi_ip_reputation_' . md5($ip);
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        if (empty($this->api_key)) {
            return new WP_Error('missing_key', 'AbuseIPDB API Key not configured');
        }

        // 2. Call API
        $response = wp_remote_get($this->api_url . '?ipAddress=' . $ip . '&maxAgeInDays=90', array(
            'headers' => array(
                'Key' => $this->api_key,
                'Accept' => 'application/json'
            )
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['data'])) {
            // 3. Save Cache
            set_transient($cache_key, $data['data'], DAY_IN_SECONDS);
            return $data['data'];
        }

        return new WP_Error('api_error', 'Invalid response from AbuseIPDB');
    }
}
