<?php

/**
 * Plugin Name: 🛡️ Guardian Diary (守護者日記)
 * Plugin URI:  https://github.com/Tonny-Lab/My-Tiny-WordPress-Plugin-Security-Development-Skill
 * Description: 這是正確示範！這個外掛展示了如何安全地開發 WordPress 外掛，包含防止 SQLi, XSS, CSRF, 和權限控制的最佳實踐。
 * Version:     1.0.0
 * Author:      Tonny Lab (Good Example)
 * License:     GPL v2 or later
 */

// ✅ 安全控制 1: 防止直接存取檔案
if (! defined('ABSPATH')) {
    exit; // Silence is golden
}

class Guardian_Diary
{

    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'install'));
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'handle_form_submission'));
    }

    public function install()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'guardian_diary';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title tinytext NOT NULL,
            content text NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_menu()
    {
        // ✅ 安全控制 2:設定正確的權限 (Capability)
        // 只有擁有 'manage_options' 權限的使用者 (通常是管理員) 才能看到此選單
        add_menu_page(
            'Guardian Diary',
            '🛡️ 守護日記',
            'manage_options', // 嚴格的權限
            'guardian-diary',
            array($this, 'render_page'),
            'dashicons-shield',
            6
        );
    }

    public function handle_form_submission()
    {
        // 1. 檢查是否是表單提交
        if (! isset($_POST['guardian_action']) && ! isset($_GET['guardian_action'])) {
            return;
        }

        // 2. ✅ 安全控制 3: 嚴格的權限檢查 (Broken Access Control 防護)
        // 再次確認當前使用者是否有權限執行此操作
        if (! current_user_can('manage_options')) {
            wp_die(__('抱歉，您沒有執行此操作的權限。'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'guardian_diary';

        // 處理新增資料
        if (isset($_POST['guardian_action']) && $_POST['guardian_action'] == 'add_diary') {

            // 3. ✅ 安全控制 4: CSRF 防護 (使用 Nonce)
            // 驗證隨機產生的 nonce，確保請求來自合法的表單頁面
            check_admin_referer('guardian_add_diary_action', 'guardian_nonce_field');

            // 4. ✅ 安全控制 5: 資料驗證與淨化 (Sanitization)
            $title   = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : ''; // 移除 HTML 標籤
            $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : ''; // 保留換行但移除危險標籤

            if (empty($title) || empty($content)) {
                // 處理錯誤...
                return;
            }

            // 5. ✅ 安全控制 6: SQL Injection 防護 (使用 prepare)
            // 使用 $wpdb->prepare() 預處理 SQL 語句
            // %s 代表字串，%d 代表整數，%f 代表浮點數
            // 這樣做可以確保輸入的內容被視為資料，而不會被解釋為 SQL 指令
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO $table_name (title, content, created_at) VALUES (%s, %s, %s)",
                    $title,
                    $content,
                    current_time('mysql')
                )
            );

            // 轉址避免重複提交
            wp_redirect(admin_url('admin.php?page=guardian-diary&msg=added'));
            exit;
        }

        // 處理刪除資料
        if (isset($_GET['guardian_action']) && $_GET['guardian_action'] == 'delete_diary') {

            // 3. ✅ 安全控制 4 (再次): CSRF 防護 (GET 請求版)
            // 驗證網址中的 nonce
            // 注意：刪除操作是危險動作，必須嚴格檢查 nonce
            check_admin_referer('guardian_delete_diary_' . $_GET['id']);

            // 4. ✅ 安全控制 5 (再次): 資料驗證
            $id = isset($_GET['id']) ? absint($_GET['id']) : 0; // 強制轉為正整數

            if ($id > 0) {
                // 5. ✅ 安全控制 6 (再次): SQL Injection 防護
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM $table_name WHERE id = %d",
                        $id
                    )
                );
            }

            wp_redirect(admin_url('admin.php?page=guardian-diary&msg=deleted'));
            exit;
        }
    }

    public function render_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'guardian_diary';

?>
        <div class="wrap">
            <h1>🛡️ 守護者日記 (安全示範)</h1>

            <?php
            // ✅ 安全控制 7: XSS 防護 (Escaping)
            // 顯示訊息時，使用 esc_html() 確保內容不會被瀏覽器解釋為程式碼
            if (isset($_GET['msg'])) {
                $msg = sanitize_text_field($_GET['msg']); // 先淨化
                if ($msg === 'added') {
                    echo '<div class="notice notice-success"><p>' . esc_html__('日記已安全發布！', 'guardian-diary') . '</p></div>';
                } elseif ($msg === 'deleted') {
                    echo '<div class="notice notice-success"><p>' . esc_html__('日記已安全刪除！', 'guardian-diary') . '</p></div>';
                }
            }
            ?>

            <h2>寫下你的秘密...</h2>
            <!-- 表單指向當前頁面 -->
            <form method="post" action="">
                <!-- ✅ 安全控制 4: 加入 Nonce 欄位 (CSRF 防護) -->
                <?php wp_nonce_field('guardian_add_diary_action', 'guardian_nonce_field'); ?>

                <input type="hidden" name="guardian_action" value="add_diary">

                <table class="form-table">
                    <tr>
                        <th><label for="title">標題</label></th>
                        <td><input type="text" name="title" id="title" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="content">內容</label></th>
                        <td><textarea name="content" id="content" class="large-text" rows="5" required></textarea></td>
                    </tr>
                </table>
                <?php submit_button('發布日記 (安全)'); ?>
            </form>

            <hr>

            <h2>秘密列表</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>標題</th>
                        <th>內容</th>
                        <th>建立時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 取出資料 (雖然我們 trust db content, 但輸出時還是要轉義)
                    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

                    if ($results) {
                        foreach ($results as $row) {
                            // ✅ 安全控制 4 (再次): 產生帶有 Nonce 的刪除連結
                            // 每個刪除連結都有獨一無二的 nonce，防止被猜測
                            $delete_url = wp_nonce_url(
                                admin_url('admin.php?page=guardian-diary&guardian_action=delete_diary&id=' . $row->id),
                                'guardian_delete_diary_' . $row->id
                            );

                            echo '<tr>';
                            echo '<td>' . absint($row->id) . '</td>';

                            // ✅ 安全控制 7 (再次): XSS 防護 (嚴格轉義)
                            // esc_html(): 將 <script> 轉成 &lt;script&gt;
                            echo '<td>' . esc_html($row->title) . '</td>';

                            // 內容部分使用 esc_html() 或 wp_kses_post() (若允許部分 HTML)
                            // 這裡我們假設也是純文字，所以用 esc_html()
                            echo '<td>' . esc_html($row->content) . '</td>';

                            echo '<td>' . esc_html($row->created_at) . '</td>';

                            echo '<td><a href="' . esc_url($delete_url) . '" class="button button-small delete" onclick="return confirm(\'確定要刪除嗎？\');">刪除 (安全)</a></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">目前沒有日記。</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
<?php
    }
}

new Guardian_Diary();
