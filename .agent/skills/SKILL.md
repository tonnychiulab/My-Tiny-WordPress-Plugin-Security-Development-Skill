# WordPress Plugin Security Development Skill

## 概述
這個 skill 提供 WordPress 外掛開發的安全最佳實踐,基於 WPScan Vulnerability Database 和 Patchstack Database 的真實漏洞案例分析。幫助開發者在撰寫程式碼時預防常見的安全漏洞。

## 資料來源
- **WPScan Vulnerability Database**: https://wpscan.com/api/
  - 超過 21,000+ 已知安全漏洞
  - 包含 WordPress 核心、外掛和主題漏洞
  - 由 WordPress 安全專家手動驗證
  - 可透過 API 免費存取 (每日 25 次請求)

- **Patchstack Database**: https://patchstack.com/database/
  - 由安全專家手工策劃和驗證的漏洞資訊
  - 2024 年披露超過 5,000+ 個漏洞
  - 提供詳細的漏洞細節和修復建議
  - 主動漏洞披露計劃 (VDP)

## WordPress 外掛常見漏洞類型統計

根據 Patchstack 2024-2025 年度統計數據:
1. **Cross-Site Scripting (XSS)** - 42.69%
2. **其他漏洞** - 16.58%
3. **Cross-Site Request Forgery (CSRF)** - 14.79%
4. **Broken Access Control** - 11.36%
5. **SQL Injection** - 6.29%
6. **Sensitive Data Exposure** - 5.51%
7. **Arbitrary File Upload** - 2.77%

---

## 核心安全原則

### 永遠不要信任外部輸入
所有來自以下來源的資料都必須視為不可信任:
- `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`
- `$_SERVER` (特別是 `HTTP_*` 標頭)
- 資料庫查詢結果 (可能被之前的攻擊污染)
- 檔案上傳
- REST API 請求
- AJAX 請求

### 三層防護策略
1. **輸入驗證 (Validation)** - 確保資料格式正確
2. **資料淨化 (Sanitization)** - 清理不安全的內容
3. **輸出轉義 (Escaping)** - 防止資料被解釋為程式碼

---

## 1. SQL Injection 防護

### 漏洞成因
未經淨化的使用者輸入被直接放入 SQL 查詢中,允許攻擊者執行任意 SQL 指令。

### ❌ 危險寫法

```php
// 絕對不要這樣做!
function get_user_by_id() {
    global $wpdb;
    $user_id = $_GET['id'];
    
    // 危險:直接拼接 SQL
    $sql = "SELECT * FROM {$wpdb->prefix}users WHERE id = $user_id";
    $user = $wpdb->get_row($sql);
    
    return $user;
}
```

```php
// 同樣危險
function search_posts() {
    global $wpdb;
    $keyword = $_POST['keyword'];
    
    // 危險:字串拼接
    $results = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}posts WHERE post_title LIKE '%{$keyword}%'"
    );
    
    return $results;
}
```

### ✅ 安全寫法

#### 使用 wpdb::prepare() (推薦)

```php
function get_user_by_id_safe() {
    global $wpdb;
    
    // 1. 驗證輸入
    $user_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
    
    if ($user_id === 0) {
        return null;
    }
    
    // 2. 使用 prepare 防止 SQL Injection
    $sql = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}users WHERE id = %d",
        $user_id
    );
    
    $user = $wpdb->get_row($sql);
    
    return $user;
}
```

#### prepare() 佔位符說明

```php
// %d - 整數
// %f - 浮點數
// %s - 字串

function search_posts_safe() {
    global $wpdb;
    
    // 驗證並淨化輸入
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
    
    if (empty($keyword)) {
        return array();
    }
    
    // 使用 %s 佔位符處理字串
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}posts 
            WHERE post_title LIKE %s 
            AND post_status = %s",
            '%' . $wpdb->esc_like($keyword) . '%',
            'publish'
        )
    );
    
    return $results;
}
```

#### 使用 WordPress 內建函數 (更安全)

```php
function insert_form_submission_safe() {
    global $wpdb;
    
    // 淨化輸入
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    
    // 驗證 email
    if (!is_email($email)) {
        return false;
    }
    
    // 使用 wpdb::insert() 自動處理轉義
    $result = $wpdb->insert(
        $wpdb->prefix . 'form_submissions',
        array(
            'name' => $name,
            'email' => $email,
            'submitted_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s') // 資料格式
    );
    
    return $result !== false;
}
```

```php
function update_user_meta_safe($user_id, $meta_key, $meta_value) {
    // 驗證輸入
    $user_id = absint($user_id);
    $meta_key = sanitize_key($meta_key);
    $meta_value = sanitize_text_field($meta_value);
    
    // 使用 wpdb::update() 
    global $wpdb;
    $result = $wpdb->update(
        $wpdb->prefix . 'usermeta',
        array('meta_value' => $meta_value), // 資料
        array(
            'user_id' => $user_id,
            'meta_key' => $meta_key
        ), // WHERE 條件
        array('%s'), // 資料格式
        array('%d', '%s') // WHERE 格式
    );
    
    return $result !== false;
}
```

#### 刪除操作

```php
function delete_submission_safe() {
    global $wpdb;
    
    // 型別轉換確保為整數
    $id = (int) $_POST['id'];
    
    if ($id <= 0) {
        return false;
    }
    
    // 使用 wpdb::delete()
    $rows_deleted = $wpdb->delete(
        $wpdb->prefix . 'form_submissions',
        array('id' => $id),
        array('%d')
    );
    
    return $rows_deleted > 0;
}
```

### 關鍵點
- **永遠使用** `$wpdb->prepare()`
- **優先使用** WordPress 內建函數: `insert()`, `update()`, `delete()`, `replace()`
- **LIKE 查詢**必須使用 `$wpdb->esc_like()` 轉義通配符
- **型別轉換**: 整數用 `absint()` 或 `(int)`, email 用 `sanitize_email()`

---

## 2. Cross-Site Scripting (XSS) 防護

### 漏洞成因
未經轉義的使用者輸入被輸出到 HTML 中,允許攻擊者注入惡意 JavaScript。

### XSS 類型
1. **Reflected XSS** - 惡意腳本來自當前請求
2. **Stored XSS** - 惡意腳本存儲在資料庫中
3. **DOM-based XSS** - 漏洞存在於客戶端 JavaScript

### ❌ 危險寫法

```php
// 危險:直接輸出未轉義的資料
function display_user_comment() {
    $comment = $_GET['comment'];
    echo '<div class="comment">' . $comment . '</div>';
}
```

```php
// 危險:在屬性中直接使用
function display_user_avatar() {
    $avatar_url = $_GET['avatar'];
    echo '<img src="' . $avatar_url . '" />';
}
```

```php
// 危險:輸出 JSON 未轉義
function output_settings() {
    $settings = get_option('my_settings');
    ?>
    <script>
        var settings = <?php echo json_encode($settings); ?>;
    </script>
    <?php
}
```

### ✅ 安全寫法

#### 根據上下文選擇正確的轉義函數

```php
// 1. HTML 內容 - 使用 esc_html()
function display_user_name_safe() {
    $name = get_user_meta(get_current_user_id(), 'display_name', true);
    echo '<div class="username">' . esc_html($name) . '</div>';
}

// 2. HTML 屬性 - 使用 esc_attr()
function display_user_input_safe() {
    $default_value = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    ?>
    <input type="text" 
           name="search" 
           value="<?php echo esc_attr($default_value); ?>" 
           placeholder="<?php echo esc_attr__('Search...', 'textdomain'); ?>" />
    <?php
}

// 3. URL - 使用 esc_url()
function display_user_website_safe() {
    $website = get_user_meta(get_current_user_id(), 'user_url', true);
    echo '<a href="' . esc_url($website) . '">' . esc_html__('Visit Website', 'textdomain') . '</a>';
}

// 4. JavaScript - 使用 esc_js()
function output_user_data_safe() {
    $user_name = get_user_meta(get_current_user_id(), 'display_name', true);
    ?>
    <script>
        var userName = '<?php echo esc_js($user_name); ?>';
        console.log(userName);
    </script>
    <?php
}

// 5. Textarea - 使用 esc_textarea()
function display_bio_field_safe() {
    $bio = get_user_meta(get_current_user_id(), 'description', true);
    ?>
    <textarea name="bio"><?php echo esc_textarea($bio); ?></textarea>
    <?php
}
```

#### 允許特定 HTML 標籤 - 使用 wp_kses()

```php
function display_user_content_safe() {
    $content = get_post_meta(get_the_ID(), 'custom_content', true);
    
    // 定義允許的 HTML 標籤和屬性
    $allowed_html = array(
        'a' => array(
            'href' => array(),
            'title' => array(),
            'target' => array()
        ),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
        'p' => array(
            'class' => array()
        ),
        'img' => array(
            'src' => array(),
            'alt' => array(),
            'width' => array(),
            'height' => array()
        )
    );
    
    echo wp_kses($content, $allowed_html);
}
```

```php
// 使用預定義的規則集
function display_post_content_safe() {
    $content = get_post_meta(get_the_ID(), 'custom_content', true);
    
    // wp_kses_post() 允許文章中安全的 HTML
    echo wp_kses_post($content);
}
```

#### 輸出 JSON 資料

```php
function output_settings_safe() {
    $settings = get_option('my_settings');
    
    // 使用 wp_json_encode() 並轉義
    ?>
    <script>
        var settings = <?php echo wp_json_encode($settings); ?>;
    </script>
    <?php
}

// 或者使用 wp_localize_script() (推薦)
function enqueue_scripts_with_data() {
    wp_enqueue_script('my-script', plugins_url('js/script.js', __FILE__), array('jquery'));
    
    $settings = get_option('my_settings');
    wp_localize_script('my-script', 'myPluginSettings', $settings);
}
```

### 轉義函數速查表

| 上下文 | 函數 | 用途 |
|--------|------|------|
| HTML 內容 | `esc_html()` | 普通文字內容 |
| HTML 屬性 | `esc_attr()` | input value, title, alt 等 |
| URL | `esc_url()` | href, src 等 URL |
| JavaScript | `esc_js()` | JavaScript 字串 |
| Textarea | `esc_textarea()` | textarea 內容 |
| 允許部分 HTML | `wp_kses()` | 需要保留某些 HTML 標籤時 |
| 文章內容 | `wp_kses_post()` | 允許文章中的安全 HTML |
| SQL | `esc_sql()` | 不推薦,應使用 prepare() |

### 關鍵點
- **輸出時轉義,而非輸入時** - 在顯示資料時才轉義,保持資料原始性
- **根據上下文選擇** - HTML、屬性、URL、JS 各有不同的轉義函數
- **國際化也要轉義** - `esc_html__()`, `esc_attr__()`, `esc_html_e()`

---

## 3. Cross-Site Request Forgery (CSRF) 防護

### 漏洞成因
惡意網站誘導已登入的使用者執行非預期的操作,如刪除資料、修改設定等。

### ❌ 危險寫法

```php
// 危險:沒有 nonce 驗證
function handle_delete_post() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $post_id = absint($_GET['post_id']);
        wp_delete_post($post_id, true);
        wp_redirect(admin_url('edit.php'));
        exit;
    }
}
add_action('admin_init', 'handle_delete_post');
```

### ✅ 安全寫法

#### 使用 WordPress Nonces

```php
// 1. 建立表單時產生 nonce
function render_delete_form($post_id) {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('delete_post_' . $post_id, 'delete_post_nonce'); ?>
        <input type="hidden" name="post_id" value="<?php echo absint($post_id); ?>" />
        <input type="hidden" name="action" value="delete_post" />
        <button type="submit"><?php esc_html_e('Delete', 'textdomain'); ?></button>
    </form>
    <?php
}

// 2. 處理表單時驗證 nonce
function handle_delete_post_safe() {
    if (!isset($_POST['action']) || $_POST['action'] !== 'delete_post') {
        return;
    }
    
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    // 驗證 nonce
    if (!isset($_POST['delete_post_nonce']) || 
        !wp_verify_nonce($_POST['delete_post_nonce'], 'delete_post_' . $post_id)) {
        wp_die(__('Security check failed', 'textdomain'));
    }
    
    // 檢查權限
    if (!current_user_can('delete_post', $post_id)) {
        wp_die(__('You do not have permission to delete this post', 'textdomain'));
    }
    
    // 執行刪除
    wp_delete_post($post_id, true);
    
    wp_redirect(admin_url('edit.php'));
    exit;
}
add_action('admin_init', 'handle_delete_post_safe');
```

#### URL 中的 nonce (GET 請求)

```php
// 建立帶 nonce 的 URL
function get_delete_link($post_id) {
    $url = add_query_arg(
        array(
            'action' => 'delete_post',
            'post_id' => $post_id
        ),
        admin_url('admin.php')
    );
    
    // 加入 nonce
    $url = wp_nonce_url($url, 'delete_post_' . $post_id, 'delete_nonce');
    
    return $url;
}

// 驗證 URL 中的 nonce
function handle_delete_via_url() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'delete_post') {
        return;
    }
    
    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
    
    // 驗證 nonce (注意參數名稱 'delete_nonce')
    if (!isset($_GET['delete_nonce']) || 
        !wp_verify_nonce($_GET['delete_nonce'], 'delete_post_' . $post_id)) {
        wp_die(__('Security check failed', 'textdomain'));
    }
    
    // 檢查權限
    if (!current_user_can('delete_post', $post_id)) {
        wp_die(__('Permission denied', 'textdomain'));
    }
    
    wp_delete_post($post_id, true);
    wp_redirect(admin_url('edit.php'));
    exit;
}
add_action('admin_init', 'handle_delete_via_url');
```

#### AJAX 請求中的 nonce

```php
// 1. 將 nonce 傳遞給 JavaScript
function enqueue_ajax_script() {
    wp_enqueue_script(
        'my-ajax-script',
        plugins_url('js/ajax-handler.js', __FILE__),
        array('jquery')
    );
    
    // 使用 wp_localize_script 傳遞 nonce
    wp_localize_script('my-ajax-script', 'myAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('my_ajax_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_ajax_script');

// 2. JavaScript 發送 AJAX 請求
// js/ajax-handler.js
/*
jQuery(document).ready(function($) {
    $('#delete-button').on('click', function() {
        $.ajax({
            url: myAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_item',
                item_id: 123,
                nonce: myAjax.nonce
            },
            success: function(response) {
                console.log(response);
            }
        });
    });
});
*/

// 3. PHP 處理 AJAX 請求
function handle_ajax_delete() {
    // 驗證 nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_ajax_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // 驗證權限
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        wp_die();
    }
    
    $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
    
    // 執行刪除操作
    $result = delete_item($item_id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Item deleted'));
    } else {
        wp_send_json_error(array('message' => 'Delete failed'));
    }
    
    wp_die();
}
add_action('wp_ajax_delete_item', 'handle_ajax_delete');
```

#### REST API 請求中的 nonce

```php
function register_rest_route_with_nonce() {
    register_rest_route('myplugin/v1', '/delete/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'handle_rest_delete',
        'permission_callback' => function() {
            // REST API 自動驗證 nonce (當使用 cookie 認證時)
            return current_user_can('manage_options');
        }
    ));
}
add_action('rest_api_init', 'register_rest_route_with_nonce');

function handle_rest_delete($request) {
    $id = $request['id'];
    
    // REST API 會自動處理 nonce 驗證 (透過 cookie)
    // 只需檢查權限
    if (!current_user_can('delete_post', $id)) {
        return new WP_Error(
            'permission_denied',
            __('You do not have permission', 'textdomain'),
            array('status' => 403)
        );
    }
    
    $result = wp_delete_post($id, true);
    
    if ($result) {
        return new WP_REST_Response(array('deleted' => true), 200);
    }
    
    return new WP_Error('delete_failed', __('Failed to delete', 'textdomain'));
}
```

### Nonce 函數速查表

| 函數 | 用途 |
|------|------|
| `wp_nonce_field($action, $name)` | 在表單中產生 nonce 隱藏欄位 |
| `wp_verify_nonce($nonce, $action)` | 驗證 nonce |
| `wp_create_nonce($action)` | 產生 nonce 值 |
| `wp_nonce_url($url, $action, $name)` | 在 URL 中加入 nonce |
| `check_admin_referer($action, $name)` | 驗證並在失敗時終止執行 |
| `check_ajax_referer($action, $name, $die)` | AJAX 專用驗證 |

### 關鍵點
- **所有狀態改變操作都需要 nonce** - 建立、更新、刪除
- **GET 請求**使用 `wp_nonce_url()`
- **POST 請求**使用 `wp_nonce_field()`
- **AJAX 請求**透過 `wp_localize_script()` 傳遞 nonce
- **nonce action 應該唯一** - 通常包含 ID: `'delete_post_' . $post_id`

---

## 4. Broken Access Control (權限控制漏洞)

### 漏洞成因
未正確檢查使用者權限,允許低權限使用者執行高權限操作。

### ❌ 危險寫法

```php
// 危險:沒有權限檢查
function delete_any_post() {
    $post_id = absint($_GET['post_id']);
    wp_delete_post($post_id, true);
}
add_action('admin_init', 'delete_any_post');
```

```php
// 危險:只檢查是否登入
function update_site_settings() {
    if (is_user_logged_in()) {
        update_option('site_settings', $_POST['settings']);
    }
}
```

### ✅ 安全寫法

#### 使用 current_user_can() 檢查權限

```php
function delete_post_with_permission_check() {
    // 驗證 nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_post')) {
        wp_die(__('Security check failed', 'textdomain'));
    }
    
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    
    // 檢查是否有刪除此文章的權限
    if (!current_user_can('delete_post', $post_id)) {
        wp_die(__('You do not have permission to delete this post', 'textdomain'));
    }
    
    wp_delete_post($post_id, true);
}
```

#### 常用權限檢查

```php
// 1. 管理員權限
function admin_only_function() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You need to be an administrator', 'textdomain'));
    }
    
    // 執行管理員操作
}

// 2. 編輯權限
function edit_posts_function() {
    if (!current_user_can('edit_posts')) {
        wp_die(__('You cannot edit posts', 'textdomain'));
    }
    
    // 執行編輯操作
}

// 3. 特定文章的權限
function edit_specific_post($post_id) {
    if (!current_user_can('edit_post', $post_id)) {
        return new WP_Error('permission_denied', __('Cannot edit this post', 'textdomain'));
    }
    
    // 編輯文章
}

// 4. 上傳檔案權限
function upload_file_function() {
    if (!current_user_can('upload_files')) {
        wp_die(__('You cannot upload files', 'textdomain'));
    }
    
    // 處理檔案上傳
}

// 5. 自訂權限
function custom_capability_check() {
    if (!current_user_can('my_custom_capability')) {
        wp_die(__('Permission denied', 'textdomain'));
    }
    
    // 執行自訂操作
}
```

#### AJAX 請求的權限檢查

```php
function handle_ajax_admin_action() {
    // 1. 驗證 nonce
    check_ajax_referer('admin_action_nonce', 'nonce');
    
    // 2. 檢查權限
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
    }
    
    // 執行操作
    wp_send_json_success(array('message' => 'Success'));
}
add_action('wp_ajax_admin_action', 'handle_ajax_admin_action');

// 前端也需要 AJAX (登入使用者)
add_action('wp_ajax_user_action', 'handle_user_action');

// 訪客也可用的 AJAX
add_action('wp_ajax_nopriv_public_action', 'handle_public_action');
```

#### REST API 的權限檢查

```php
function register_protected_endpoint() {
    register_rest_route('myplugin/v1', '/protected', array(
        'methods' => 'POST',
        'callback' => 'handle_protected_request',
        'permission_callback' => function() {
            // 檢查權限
            return current_user_can('edit_posts');
        }
    ));
}
add_action('rest_api_init', 'register_protected_endpoint');

// 更複雜的權限檢查
function register_complex_endpoint() {
    register_rest_route('myplugin/v1', '/posts/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_post_callback',
        'permission_callback' => function($request) {
            $post_id = $request['id'];
            
            // 檢查是否可以編輯特定文章
            return current_user_can('edit_post', $post_id);
        }
    ));
}
add_action('rest_api_init', 'register_complex_endpoint');
```

### WordPress 常用權限 (Capabilities)

| 權限 | 說明 | 預設角色 |
|------|------|----------|
| `manage_options` | 管理網站設定 | Administrator |
| `edit_posts` | 編輯文章 | Editor, Author, Contributor |
| `edit_published_posts` | 編輯已發布文章 | Editor, Author |
| `publish_posts` | 發布文章 | Editor, Author |
| `delete_posts` | 刪除文章 | Editor, Author, Contributor |
| `upload_files` | 上傳檔案 | Administrator, Editor, Author |
| `moderate_comments` | 管理留言 | Administrator, Editor |
| `manage_categories` | 管理分類 | Administrator, Editor |
| `edit_users` | 編輯使用者 | Administrator |
| `create_users` | 建立使用者 | Administrator |
| `delete_users` | 刪除使用者 | Administrator |

### 關鍵點
- **每個操作都檢查權限** - 不要假設使用者有權限
- **使用最小權限原則** - 只給予必要的權限
- **具體權限優於通用權限** - 如 `delete_post` 優於 `delete_posts`
- **前端和後端都要檢查** - JavaScript 驗證可被繞過

---

## 5. Sensitive Data Exposure (敏感資料洩露)

### 常見風險
- API 金鑰暴露在前端
- 資料庫憑證洩露
- 使用者個資未加密
- 錯誤訊息包含敏感資訊

### ✅ 安全實踐

#### 保護 API 金鑰

```php
// ❌ 危險:金鑰暴露在前端
function bad_api_key_usage() {
    ?>
    <script>
        const apiKey = '<?php echo get_option('my_api_key'); ?>';
        fetch('https://api.example.com/data', {
            headers: {'X-API-Key': apiKey}
        });
    </script>
    <?php
}

// ✅ 安全:透過 AJAX 由後端處理
function enqueue_safe_api_script() {
    wp_enqueue_script('my-api-script', plugins_url('js/api.js', __FILE__));
    wp_localize_script('my-api-script', 'myApi', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fetch_data_nonce')
    ));
}

// JavaScript (api.js)
/*
jQuery.ajax({
    url: myApi.ajax_url,
    type: 'POST',
    data: {
        action: 'fetch_api_data',
        nonce: myApi.nonce
    },
    success: function(response) {
        console.log(response.data);
    }
});
*/

// PHP 後端處理
function handle_api_request() {
    check_ajax_referer('fetch_data_nonce', 'nonce');
    
    // API 金鑰只在伺服器端使用
    $api_key = get_option('my_api_key');
    
    $response = wp_remote_get('https://api.example.com/data', array(
        'headers' => array('X-API-Key' => $api_key)
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'API request failed'));
    }
    
    $data = json_decode(wp_remote_retrieve_body($response), true);
    wp_send_json_success($data);
}
add_action('wp_ajax_fetch_api_data', 'handle_api_request');
```

#### 隱藏敏感檔案

```php
// .htaccess 保護設定檔
/*
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>
*/

// 在 PHP 中檢查直接存取
// config.php 開頭加入
if (!defined('ABSPATH')) {
    exit; // 防止直接存取
}
```

#### 安全的錯誤處理

```php
// ❌ 危險:洩露資料庫資訊
function bad_error_handling() {
    global $wpdb;
    $result = $wpdb->get_row("SELECT * FROM table WHERE id = 999");
    
    if (!$result) {
        // 洩露了資料庫結構
        die('Database error: ' . $wpdb->last_error);
    }
}

// ✅ 安全:通用錯誤訊息
function safe_error_handling() {
    global $wpdb;
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}table WHERE id = %d",
        999
    ));
    
    if (!$result) {
        // 記錄詳細錯誤到日誌
        error_log('Database error: ' . $wpdb->last_error);
        
        // 使用者看到通用訊息
        wp_die(__('An error occurred. Please try again later.', 'textdomain'));
    }
}
```

#### 加密敏感資料

```php
// 儲存加密資料
function save_encrypted_data($user_id, $sensitive_data) {
    // 使用 WordPress 的加密 salt
    $encrypted = base64_encode(
        openssl_encrypt(
            $sensitive_data,
            'AES-256-CBC',
            wp_salt('auth'),
            0,
            substr(wp_salt('secure_auth'), 0, 16)
        )
    );
    
    update_user_meta($user_id, 'encrypted_field', $encrypted);
}

// 讀取解密資料
function get_decrypted_data($user_id) {
    $encrypted = get_user_meta($user_id, 'encrypted_field', true);
    
    if (empty($encrypted)) {
        return '';
    }
    
    $decrypted = openssl_decrypt(
        base64_decode($encrypted),
        'AES-256-CBC',
        wp_salt('auth'),
        0,
        substr(wp_salt('secure_auth'), 0, 16)
    );
    
    return $decrypted;
}
```

### 關鍵點
- **API 金鑰永不暴露在前端**
- **使用環境變數**存儲敏感設定
- **錯誤訊息不包含系統資訊**
- **敏感資料加密存儲**
- **HTTPS 傳輸敏感資料**

---

## 6. File Upload 安全

### 漏洞成因
未驗證的檔案上傳可能導致遠端程式碼執行、XSS、或儲存空間耗盡。

### ✅ 安全實踐

#### 驗證檔案類型

```php
function handle_safe_file_upload() {
    // 檢查權限
    if (!current_user_can('upload_files')) {
        wp_die(__('You do not have permission to upload files', 'textdomain'));
    }
    
    // 驗證 nonce
    check_admin_referer('file_upload_nonce');
    
    // 檢查檔案是否上傳
    if (!isset($_FILES['uploaded_file'])) {
        return new WP_Error('no_file', __('No file uploaded', 'textdomain'));
    }
    
    $file = $_FILES['uploaded_file'];
    
    // 1. 檢查檔案錯誤
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', __('File upload error', 'textdomain'));
    }
    
    // 2. 驗證檔案大小 (例如:2MB)
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        return new WP_Error('file_too_large', __('File size exceeds 2MB', 'textdomain'));
    }
    
    // 3. 白名單驗證檔案類型
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
    $file_type = wp_check_filetype($file['name']);
    
    if (!in_array($file['type'], $allowed_types)) {
        return new WP_Error('invalid_type', __('File type not allowed', 'textdomain'));
    }
    
    // 4. 驗證副檔名
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
    if (!in_array($file_type['ext'], $allowed_extensions)) {
        return new WP_Error('invalid_extension', __('File extension not allowed', 'textdomain'));
    }
    
    // 5. 使用 WordPress 檔案上傳處理
    $upload = wp_handle_upload($file, array(
        'test_form' => false,
        'mimes' => array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        )
    ));
    
    if (isset($upload['error'])) {
        return new WP_Error('upload_failed', $upload['error']);
    }
    
    return $upload;
}
```

#### 圖片上傳的額外檢查

```php
function handle_safe_image_upload() {
    check_admin_referer('image_upload_nonce');
    
    if (!current_user_can('upload_files')) {
        wp_die(__('Permission denied', 'textdomain'));
    }
    
    $file = $_FILES['image'];
    
    // 基本驗證...
    
    // 6. 驗證是否真的是圖片
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return new WP_Error('not_image', __('File is not a valid image', 'textdomain'));
    }
    
    // 7. 檢查圖片 MIME 類型
    $allowed_image_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);
    if (!in_array($image_info[2], $allowed_image_types)) {
        return new WP_Error('invalid_image_type', __('Image type not allowed', 'textdomain'));
    }
    
    // 8. 重新處理圖片以移除潛在的惡意程式碼
    $temp_file = $file['tmp_name'];
    
    switch ($image_info[2]) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($temp_file);
            imagejpeg($image, $temp_file, 90);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($temp_file);
            imagepng($image, $temp_file);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($temp_file);
            imagegif($image, $temp_file);
            break;
    }
    
    if (isset($image)) {
        imagedestroy($image);
    }
    
    // 使用 WordPress 處理上傳
    $upload = wp_handle_upload($file, array('test_form' => false));
    
    return $upload;
}
```

#### 儲存上傳檔案的最佳實踐

```php
function save_uploaded_file_safely() {
    // ... 檔案驗證 ...
    
    $upload = wp_handle_upload($_FILES['file'], array('test_form' => false));
    
    if (isset($upload['error'])) {
        return $upload;
    }
    
    // 儲存到 WordPress 媒體庫
    $attachment = array(
        'post_mime_type' => $upload['type'],
        'post_title' => sanitize_file_name($upload['file']),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    
    $attach_id = wp_insert_attachment($attachment, $upload['file']);
    
    // 產生縮圖
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);
    
    return $attach_id;
}
```

### 檔案上傳檢查清單
- ✅ 檢查使用者權限
- ✅ 驗證 nonce
- ✅ 檢查檔案大小限制
- ✅ 白名單驗證 MIME 類型
- ✅ 白名單驗證副檔名
- ✅ 圖片需用 `getimagesize()` 二次驗證
- ✅ 使用 `wp_handle_upload()` 處理上傳
- ✅ 重新命名檔案避免覆蓋
- ✅ 儲存在 wp-content/uploads 目錄外

---

## 7. 資料淨化函數總覽

### 輸入淨化

```php
// 文字欄位
$text = sanitize_text_field($_POST['text']);

// 多行文字
$textarea = sanitize_textarea_field($_POST['textarea']);

// Email
$email = sanitize_email($_POST['email']);

// URL
$url = esc_url_raw($_POST['url']);

// 檔名
$filename = sanitize_file_name($_FILES['file']['name']);

// HTML class
$class = sanitize_html_class($_POST['class']);

// 標題/slug
$title = sanitize_title($_POST['title']);

// Meta key
$meta_key = sanitize_key($_POST['meta_key']);

// 整數
$int = absint($_POST['number']); // 絕對值整數
$int = intval($_POST['number']); // 整數轉換

// 浮點數
$float = floatval($_POST['price']);

// 布林值
$bool = (bool) $_POST['checkbox'];
$bool = rest_sanitize_boolean($_POST['checkbox']); // REST API

// 陣列
$array = array_map('sanitize_text_field', $_POST['items']);
$array = array_map('absint', $_POST['ids']);

// Hex color
$color = sanitize_hex_color($_POST['color']);
```

### 允許 HTML 的淨化

```php
// 允許文章中的 HTML
$content = wp_kses_post($_POST['content']);

// 自訂允許的標籤
$allowed_html = array(
    'a' => array('href' => array(), 'title' => array()),
    'br' => array(),
    'strong' => array(),
    'em' => array()
);
$content = wp_kses($_POST['content'], $allowed_html);

// 完全移除所有 HTML
$text = wp_strip_all_tags($_POST['content']);
```

---

## 8. 資料驗證實踐

### 驗證函數

```php
function validate_form_data($data) {
    $errors = new WP_Error();
    
    // 驗證必填欄位
    if (empty($data['name'])) {
        $errors->add('name_required', __('Name is required', 'textdomain'));
    }
    
    // 驗證 email
    if (!is_email($data['email'])) {
        $errors->add('invalid_email', __('Invalid email address', 'textdomain'));
    }
    
    // 驗證 URL
    if (!filter_var($data['website'], FILTER_VALIDATE_URL)) {
        $errors->add('invalid_url', __('Invalid URL', 'textdomain'));
    }
    
    // 驗證數值範圍
    $age = absint($data['age']);
    if ($age < 18 || $age > 120) {
        $errors->add('invalid_age', __('Age must be between 18 and 120', 'textdomain'));
    }
    
    // 驗證字串長度
    if (strlen($data['username']) < 3 || strlen($data['username']) > 20) {
        $errors->add('invalid_username', __('Username must be 3-20 characters', 'textdomain'));
    }
    
    // 驗證格式 (正規表達式)
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['username'])) {
        $errors->add('invalid_format', __('Username can only contain letters, numbers, - and _', 'textdomain'));
    }
    
    // 如果有錯誤,回傳 WP_Error
    if ($errors->has_errors()) {
        return $errors;
    }
    
    // 驗證通過,回傳清理後的資料
    return array(
        'name' => sanitize_text_field($data['name']),
        'email' => sanitize_email($data['email']),
        'website' => esc_url_raw($data['website']),
        'age' => $age,
        'username' => sanitize_text_field($data['username'])
    );
}

// 使用範例
$result = validate_form_data($_POST);

if (is_wp_error($result)) {
    // 處理錯誤
    foreach ($result->get_error_messages() as $error) {
        echo '<p>' . esc_html($error) . '</p>';
    }
} else {
    // 使用驗證後的資料
    save_user_data($result);
}
```

---

## 9. 安全開發檢查清單

### 資料處理
- [ ] 所有外部輸入都經過驗證
- [ ] 所有外部輸入都經過淨化
- [ ] 所有輸出都經過轉義
- [ ] 使用適當的淨化函數
- [ ] 使用適當的轉義函數

### SQL 安全
- [ ] 使用 `$wpdb->prepare()` 處理所有 SQL 查詢
- [ ] 優先使用 WordPress 內建函數 (`insert()`, `update()`, `delete()`)
- [ ] LIKE 查詢使用 `$wpdb->esc_like()`
- [ ] 不使用 `$wpdb->query()` 進行資料查詢

### XSS 防護
- [ ] HTML 內容使用 `esc_html()`
- [ ] HTML 屬性使用 `esc_attr()`
- [ ] URL 使用 `esc_url()`
- [ ] JavaScript 使用 `esc_js()` 或 `wp_json_encode()`
- [ ] 允許 HTML 時使用 `wp_kses()` 或 `wp_kses_post()`

### CSRF 防護
- [ ] 所有表單包含 nonce (`wp_nonce_field()`)
- [ ] 所有表單處理驗證 nonce (`wp_verify_nonce()`)
- [ ] AJAX 請求傳遞 nonce
- [ ] AJAX 處理驗證 nonce (`check_ajax_referer()`)
- [ ] URL 操作使用 `wp_nonce_url()`

### 權限控制
- [ ] 所有操作檢查使用者權限 (`current_user_can()`)
- [ ] AJAX 動作檢查權限
- [ ] REST API 端點定義 `permission_callback`
- [ ] 使用最小權限原則
- [ ] 不依賴前端權限檢查

### 檔案上傳
- [ ] 檢查使用者權限
- [ ] 驗證檔案大小
- [ ] 白名單驗證 MIME 類型
- [ ] 白名單驗證副檔名
- [ ] 圖片使用 `getimagesize()` 驗證
- [ ] 使用 `wp_handle_upload()` 處理

### 其他安全措施
- [ ] API 金鑰不暴露在前端
- [ ] 敏感資料加密存儲
- [ ] 使用 HTTPS 傳輸敏感資料
- [ ] 錯誤訊息不洩露系統資訊
- [ ] 限制登入嘗試次數
- [ ] 定期更新依賴套件

---

## 10. 常見漏洞案例參考

### XSS 案例:Stored XSS in User Profile

**漏洞描述** (基於真實 WPScan 案例):
某外掛允許使用者在個人資料中輸入自訂欄位,但未正確轉義輸出,導致存儲型 XSS。

**修復方案**:
```php
// ❌ 漏洞版本
function display_custom_field() {
    $custom_field = get_user_meta(get_current_user_id(), 'custom_field', true);
    echo '<div>' . $custom_field . '</div>'; // 危險!
}

// ✅ 修復版本
function display_custom_field_safe() {
    $custom_field = get_user_meta(get_current_user_id(), 'custom_field', true);
    echo '<div>' . esc_html($custom_field) . '</div>';
}

// 如果需要允許某些 HTML
function display_custom_field_with_html() {
    $custom_field = get_user_meta(get_current_user_id(), 'custom_field', true);
    echo '<div>' . wp_kses_post($custom_field) . '</div>';
}
```

### SQL Injection 案例:Search Function

**漏洞描述** (基於 Patchstack 案例):
搜尋功能未使用 prepare(),允許 SQL 注入。

**修復方案**:
```php
// ❌ 漏洞版本
function search_products() {
    global $wpdb;
    $search = $_GET['s'];
    $sql = "SELECT * FROM {$wpdb->prefix}products WHERE name LIKE '%{$search}%'";
    return $wpdb->get_results($sql);
}

// ✅ 修復版本
function search_products_safe() {
    global $wpdb;
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    
    if (empty($search)) {
        return array();
    }
    
    $sql = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}products WHERE name LIKE %s",
        '%' . $wpdb->esc_like($search) . '%'
    );
    
    return $wpdb->get_results($sql);
}
```

### CSRF 案例:Admin Settings Update

**漏洞描述**:
管理設定頁面未驗證 nonce,允許 CSRF 攻擊。

**修復方案**:
```php
// ❌ 漏洞版本
function handle_settings_update() {
    if (isset($_POST['settings'])) {
        update_option('my_settings', $_POST['settings']);
    }
}

// ✅ 修復版本
function handle_settings_update_safe() {
    // 1. 檢查是否提交
    if (!isset($_POST['settings'])) {
        return;
    }
    
    // 2. 驗證 nonce
    if (!isset($_POST['settings_nonce']) || 
        !wp_verify_nonce($_POST['settings_nonce'], 'update_settings')) {
        wp_die(__('Security check failed', 'textdomain'));
    }
    
    // 3. 檢查權限
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission', 'textdomain'));
    }
    
    // 4. 淨化資料
    $settings = array_map('sanitize_text_field', $_POST['settings']);
    
    // 5. 更新選項
    update_option('my_settings', $settings);
}
```

### Broken Access Control 案例:Delete Post Without Permission

**漏洞描述**:
刪除功能未檢查使用者是否有權限刪除特定文章。

**修復方案**:
```php
// ❌ 漏洞版本
function delete_post_endpoint() {
    $post_id = absint($_GET['post_id']);
    wp_delete_post($post_id);
}

// ✅ 修復版本
function delete_post_endpoint_safe() {
    // 1. 驗證 nonce
    if (!isset($_GET['nonce']) || 
        !wp_verify_nonce($_GET['nonce'], 'delete_post')) {
        wp_die(__('Security check failed', 'textdomain'));
    }
    
    // 2. 取得文章 ID
    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
    
    if ($post_id === 0) {
        wp_die(__('Invalid post ID', 'textdomain'));
    }
    
    // 3. 檢查是否有刪除此文章的權限
    if (!current_user_can('delete_post', $post_id)) {
        wp_die(__('You do not have permission to delete this post', 'textdomain'));
    }
    
    // 4. 檢查文章是否存在
    $post = get_post($post_id);
    if (!$post) {
        wp_die(__('Post not found', 'textdomain'));
    }
    
    // 5. 執行刪除
    $result = wp_delete_post($post_id, true);
    
    if ($result) {
        wp_redirect(admin_url('edit.php'));
        exit;
    } else {
        wp_die(__('Failed to delete post', 'textdomain'));
    }
}
```

---

## 11. 如何使用 WPScan API 檢查外掛漏洞

### 取得 API Token
1. 註冊帳號: https://wpscan.com/register
2. 在個人頁面取得 API Token
3. 免費版每日 25 次請求

### 檢查特定外掛

```bash
# 使用 cURL 檢查外掛漏洞
curl -H "Authorization: Token YOUR_API_TOKEN" \
  "https://wpscan.com/api/v3/plugins/contact-form-7"
```

```php
// 在 WordPress 中檢查外掛漏洞
function check_plugin_vulnerabilities($plugin_slug) {
    $api_token = 'YOUR_API_TOKEN';
    $url = "https://wpscan.com/api/v3/plugins/{$plugin_slug}";
    
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Token ' . $api_token
        )
    ));
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data[$plugin_slug]['vulnerabilities'])) {
        return $data[$plugin_slug]['vulnerabilities'];
    }
    
    return array();
}

// 使用範例
$vulnerabilities = check_plugin_vulnerabilities('contact-form-7');

foreach ($vulnerabilities as $vuln) {
    echo "Title: " . esc_html($vuln['title']) . "\n";
    echo "Type: " . esc_html($vuln['vuln_type']) . "\n";
    echo "Fixed in: " . esc_html($vuln['fixed_in']) . "\n";
}
```

---

## 12. 開發工具與資源

### 線上檢測工具
- **WPScan**: https://wpscan.com/ (CLI 掃描工具)
- **Patchstack**: https://patchstack.com/ (即時防護與掃描)
- **Wordfence**: https://www.wordfence.com/ (防火牆與掃描)
- **Sucuri SiteCheck**: https://sitecheck.sucuri.net/ (線上掃描)

### 本地開發工具

```bash
# 安裝 WPScan CLI
gem install wpscan

# 掃描 WordPress 網站
wpscan --url https://example.com --api-token YOUR_TOKEN

# 掃描已安裝的外掛
wpscan --url https://example.com --enumerate vp --api-token YOUR_TOKEN

# 掃描主題漏洞
wpscan --url https://example.com --enumerate vt --api-token YOUR_TOKEN
```

### 程式碼檢查工具

```bash
# PHP_CodeSniffer with WordPress Coding Standards
composer require --dev squizlabs/php_codesniffer
composer require --dev wp-coding-standards/wpcs

# 檢查程式碼
./vendor/bin/phpcs --standard=WordPress my-plugin/

# PHP Security Checker
composer require --dev sensiolabs/security-checker

# 檢查依賴套件安全性
./vendor/bin/security-checker security:check
```

### 學習資源
- **WordPress Plugin Security**: https://developer.wordpress.org/plugins/security/
- **WordPress Theme Security**: https://developer.wordpress.org/themes/advanced-topics/security/
- **OWASP Top 10**: https://owasp.org/www-project-top-ten/
- **WordPress.tv Security**: https://wordpress.tv/?s=security

---

## 13. 持續安全實踐

### 開發階段
1. **使用安全的開發環境**
   - 定期更新 PHP、MySQL
   - 使用最新版 WordPress
   - 啟用 WP_DEBUG 除錯模式

2. **程式碼審查**
   - 每次提交前自我審查
   - 使用 phpcs 檢查程式碼標準
   - 團隊進行 code review

3. **自動化測試**
   - 撰寫單元測試
   - 整合安全掃描到 CI/CD
   - 定期執行漏洞掃描

### 部署階段
1. **版本控制**
   - 使用 Git 追蹤變更
   - 不將敏感資訊提交到 repository
   - 使用 .gitignore 排除設定檔

2. **環境隔離**
   - 開發、測試、正式環境分離
   - 使用環境變數管理設定
   - 正式環境關閉除錯模式

3. **監控與日誌**
   - 啟用 WordPress 除錯日誌
   - 監控異常登入嘗試
   - 定期檢查錯誤日誌

### 維護階段
1. **定期更新**
   - WordPress 核心即時更新
   - 外掛與主題定期更新
   - PHP 版本保持在支援版本

2. **安全掃描**
   - 每週執行 WPScan 掃描
   - 使用 Patchstack 即時監控
   - 訂閱漏洞通知

3. **備份策略**
   - 每日自動備份資料庫
   - 每週完整備份檔案
   - 異地存儲備份

---

## 14. AI 程式開發時的使用建議

### 在 Prompt 中引用此 Skill

當使用 AI 協助開發 WordPress 外掛時,可以在 prompt 中明確要求遵循此安全準則:

```
請幫我建立一個 WordPress 外掛的使用者表單處理功能,
需要遵循 WordPress Plugin Security Development Skill 中的所有安全最佳實踐:
1. 使用 wpdb::prepare() 防止 SQL Injection
2. 使用 esc_html() 等函數防止 XSS
3. 加入 wp_nonce_field() 防止 CSRF
4. 使用 current_user_can() 檢查權限
5. 所有輸入都要進行淨化與驗證
```

### 程式碼審查提示詞

```
請審查以下 WordPress 外掛程式碼的安全性,
特別檢查:
1. SQL 查詢是否使用 prepare()
2. 所有輸出是否正確轉義
3. 是否有 nonce 驗證
4. 是否檢查使用者權限
5. 檔案上傳是否安全
6. 是否有敏感資料洩露風險

[貼上程式碼]
```

### 重構請求

```
請將以下不安全的程式碼重構為符合 WordPress 安全標準的版本,
參考 WordPress Plugin Security Development Skill 的最佳實踐。

[貼上不安全的程式碼]
```

---

## 15. 總結

WordPress 外掛安全開發的核心原則:

1. **永遠不要信任外部輸入** - 所有資料都需驗證與淨化
2. **輸出時轉義** - 防止 XSS 攻擊
3. **使用 prepare()** - 防止 SQL Injection
4. **加入 nonce** - 防止 CSRF 攻擊
5. **檢查權限** - 防止未授權存取
6. **安全的檔案處理** - 驗證檔案類型與大小
7. **保護敏感資料** - 加密存儲,不暴露在前端
8. **持續學習** - 關注最新的安全漏洞與修復方案

遵循這些原則,參考 WPScan 和 Patchstack 的真實案例,可以大幅降低外掛的安全風險,保護使用者的網站安全。

---

## 版本資訊
- **版本**: 1.0.0
- **最後更新**: 2025-02-08
- **資料來源**: WPScan Vulnerability Database, Patchstack Database
- **作者**: Tonny Chiu (Tonny Lab)
- **授權**: MIT License

## 相關連結
- WPScan API: https://wpscan.com/api/
- Patchstack Database: https://patchstack.com/database/
- WordPress Plugin Handbook: https://developer.wordpress.org/plugins/
- WordPress Security Whitepaper: https://wordpress.org/about/security/
