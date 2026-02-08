<?php
/**
 * Plugin Name: 💀 Doomed Diary (註定毀滅的日記)
 * Plugin URI:  https://github.com/Tonny-Lab/My-Tiny-WordPress-Plugin-Security-Development-Skill
 * Description: 這是錯誤示範！這個外掛包含多種嚴重的安全漏洞 (SQLi, XSS, CSRF, 等)。僅供教學使用，絕對不要在正式環境啟用！
 * Version:     1.0.0
 * Author:      Tonny Lab (Bad Example)
 * License:     GPL v2 or later
 */

// ❌ 錯誤 1: 沒有防止直接存取檔案
// 正確做法應該要有: if ( ! defined( 'ABSPATH' ) ) exit;

class Doomed_Diary {

    public function __construct() {
        // 建立資料表
        register_activation_hook( __FILE__, array( $this, 'install' ) );
        
        // 加入選單
        add_action( 'admin_menu', array( $this, 'add_menu' ) );

        // 處理表單提交 (沒有 nonce 檢查!)
        add_action( 'admin_init', array( $this, 'handle_submissions' ) );
    }

    public function install() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doomed_diary';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title tinytext NOT NULL,
            content text NOT NULL,
            PRIMARY KEY  (id)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function add_menu() {
        // ❌ 錯誤 2: 權限設定太寬鬆，甚至是 'read'
        add_menu_page(
            'Doomed Diary',
            '💀 毀滅日記',
            'read', // 任何人只要能讀取就能存取? 危險!
            'doomed-diary',
            array( $this, 'render_page' ),
            'dashicons-warning',
            6
        );
    }

    public function handle_submissions() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doomed_diary';

        // ❌ 錯誤 3: 沒有檢查 Nonce (CSRF 漏洞)
        // 駭客可以做一個假網頁，讓管理者點擊後自動發布或刪除日記

        // ❌ 錯誤 4: 沒有檢查權限 (Broken Access Control)
        // 任何登入使用者 (甚至訂閱者) 觸發這個 init hook 都能執行

        // 新增日記
        if ( isset( $_POST['doomed_action'] ) && $_POST['doomed_action'] == 'add' ) {
            $title   = $_POST['title'];
            $content = $_POST['content'];

            // ❌ 錯誤 5: SQL Injection (SQL 注入)
            // 直接把 $_POST 的內容拼接到 SQL 字串中
            // 駭客輸入 title 為: "Test', 'Content'); DROP TABLE wp_users; --" 就完蛋了
            $sql = "INSERT INTO $table_name (title, content) VALUES ('$title', '$content')";
            $wpdb->query( $sql );
        }

        // 刪除日記
        if ( isset( $_GET['doomed_action'] ) && $_GET['doomed_action'] == 'delete' ) {
            $id = $_GET['id'];

            // ❌ 錯誤 5 (再次): SQL Injection
            // 網址輸入: ?page=doomed-diary&doomed_action=delete&id=1 OR 1=1
            // 這會刪除所有日記!
            $sql = "DELETE FROM $table_name WHERE id = $id";
            $wpdb->query( $sql );
        }
    }

    public function render_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doomed_diary';

        // ❌ 錯誤 6: Self-Reflected XSS
        // 直接從網址取得參數並顯示，沒有任何過濾
        if ( isset( $_GET['msg'] ) ) {
            echo '<div class="notice notice-success"><p>' . $_GET['msg'] . '</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1>💀 註定毀滅的日記 (不安全示範)</h1>
            <p style="color: red; font-weight: bold;">⚠️ 警告：此為教學用外掛，包含大量安全漏洞。請勿在生產環境啟用！</p>

            <!-- 新增日記表單 -->
            <h2>寫下你的秘密...</h2>
            <form method="post" action="">
                <input type="hidden" name="doomed_action" value="add">
                <table class="form-table">
                    <tr>
                        <th>標題</th>
                        <td><input type="text" name="title" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>內容</th>
                        <td><textarea name="content" class="large-text" rows="5"></textarea></td>
                    </tr>
                </table>
                <!-- ❌ 錯誤 3: 表單沒有 wp_nonce_field -->
                <?php submit_button( '發布日記 (危險)' ); ?>
            </form>

            <hr>

            <!-- 日記列表 -->
            <h2>秘密列表</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>標題</th>
                        <th>內容</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $results = $wpdb->get_results( "SELECT * FROM $table_name" );
                    
                    foreach ( $results as $row ) {
                        echo '<tr>';
                        echo '<td>' . $row->id . '</td>';
                        
                        // ❌ 錯誤 7: Stored XSS (儲存型 XSS)
                        // 若資料庫裡有 <script>alert(1)</script>，這裡會直接執行
                        // 因為沒有使用 esc_html()
                        echo '<td>' . $row->title . '</td>';
                        echo '<td>' . $row->content . '</td>';
                        
                        // ❌ 錯誤 3 (再次): 連結沒有 Nonce
                        echo '<td><a href="?page=doomed-diary&doomed_action=delete&id=' . $row->id . '" style="color:red;">刪除 (無保護)</a></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

new Doomed_Diary();
