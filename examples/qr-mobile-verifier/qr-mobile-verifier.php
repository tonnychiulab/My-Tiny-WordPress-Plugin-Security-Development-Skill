<?php

/**
 * Plugin Name: QR Mobile Verifier (Simulation)
 * Description: A "Gmail-style" mobile verification plugin logic demonstration. Uses QR codes, AJAX polling, and a simulated risk control system (IP/Device/Phone limits) to verify users.
 * Version: 1.0.0
 * Author: Tonny & Antigravity
 * Text Domain: qr-mobile-verifier
 */

if (! defined('ABSPATH')) {
    exit;
}

class QR_Mobile_Verifier
{

    private static $instance = null;
    private $table_name;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'qrmv_history';

        // Hooks
        register_activation_hook(__FILE__, array($this, 'install_db'));
        add_shortcode('qr_verifier', array($this, 'render_shortcode'));

        // Load Assets (CSS/JS)
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // Load Includes
        require_once plugin_dir_path(__FILE__) . 'includes/class-risk-control.php';

        // Mobile Endpoint Logic
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_mobile_endpoint'));

        // AJAX Handler
        add_action('wp_ajax_qrmv_check_status', array($this, 'ajax_check_status'));
        add_action('wp_ajax_nopriv_qrmv_check_status', array($this, 'ajax_check_status'));
    }

    /**
     * Register 'qrmv_action' and 'token' as public query vars
     */
    public function add_query_vars($vars)
    {
        $vars[] = 'qrmv_action';
        $vars[] = 'token';
        return $vars;
    }

    /**
     * Handle the mobile page rendering
     */
    public function handle_mobile_endpoint()
    {
        if (get_query_var('qrmv_action') === 'verify' && get_query_var('token')) {
            // Load the mobile template
            $template = plugin_dir_path(__FILE__) . 'templates/mobile-verify.php';
            if (file_exists($template)) {
                include $template;
                exit; // Stop WP from loading the normal theme
            }
        }
    }

    public function ajax_check_status()
    {
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';

        if (! $token) {
            wp_send_json_error(array('message' => 'No token provided'));
        }

        $status = get_transient('qrmv_' . $token);

        wp_send_json_success(array('status' => $status));
    }

    /**
     * 1. Install DB Table for Risk Control History
     * 我們需要記錄每一次的 "嘗試"，用來判斷這個人是不是 "洗帳號"
     */
    public function install_db()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			token varchar(64) NOT NULL, -- 此次驗證的唯一代碼
			phone_number varchar(20),   -- 使用者填寫的手機 (模擬)
			ip_address varchar(45) NOT NULL, -- 來源 IP (風控核心)
			user_agent text,            -- 裝置指紋 (簡單版)
			status varchar(20) DEFAULT 'pending', -- pending, verified, blocked
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY token (token),
			KEY phone_ip (phone_number, ip_address)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * 2. Render Shortcode [qr_verifier]
     * 這是顯示在電腦端的 "鎖"
     */
    public function render_shortcode($atts)
    {
        // 生成一個臨時的 Token (這就是 "鑰匙孔")
        $token = wp_generate_password(32, false);

        // 將這個 Token 存入 Transient，有效期 10 分鐘
        // 我們暫時還不需要存入 DB，因為還沒開始驗證
        set_transient('qrmv_' . $token, 'pending', 10 * MINUTE_IN_SECONDS);

        // 生成 QR Code 的連結 (這裡假設我們有一個 verify 端點)
        // 為了模擬，我們先用一個簡單的 URL 參數
        $verify_url = home_url('/?qrmv_action=verify&token=' . $token);

        // 使用 QRServer API (替代已棄用的 Google Charts)
        $qr_image = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($verify_url);

        ob_start();
?>
        <div id="qrmv-container" style="text-align:center; border:1px solid #ddd; padding:20px; max-width:400px; margin:0 auto; border-radius:8px;">
            <h3>🔐 安全驗證</h3>
            <p>為了保護您的帳戶安全，請使用手機掃描下方 QR Code 進行驗證。</p>

            <div class="qrmv-qr-code">
                <img src="<?php echo esc_url($qr_image); ?>" alt="Scan to Verify" style="max-width:100%; height:auto;" />
            </div>

            <div style="margin-top:10px; font-size:12px; color:#888;">
                <p>看不到 QR Code 嗎？</p>
                <a href="<?php echo esc_url($verify_url); ?>" target="_blank" style="text-decoration:underline;">👉 點擊這裡開啟模擬手機驗證頁面 (測試用)</a>
            </div>

            <div id="qrmv-status" style="margin-top:15px; font-weight:bold; color:#666;">
                等待掃描中... <span class="spinner">⏳</span>
            </div>

            <!-- 隱藏欄位供 JS 使用 -->
            <input type="hidden" id="qrmv_token" value="<?php echo esc_attr($token); ?>">
            <input type="hidden" id="qrmv_ajax_url" value="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        </div>

        <style>
            #qrmv-container {
                background: #fff;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
        </style>
<?php
        return ob_get_clean();
    }

    public function enqueue_assets()
    {
        // 載入 jQuery (WordPress 內建)
        wp_enqueue_script('jquery');

        // 載入 polling.js
        wp_enqueue_script('qrmv-polling', plugin_dir_url(__FILE__) . 'assets/polling.js', array('jquery'), '1.0', true);
    }
}

// Initialize
QR_Mobile_Verifier::get_instance();
