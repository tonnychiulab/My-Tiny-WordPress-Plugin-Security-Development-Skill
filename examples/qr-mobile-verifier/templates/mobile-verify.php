<?php

/**
 * Mobile Verification Page Template
 * 這裡是手機掃碼後看到的頁面
 */

if (! defined('ABSPATH')) {
    exit;
}

$token = get_query_var('token');
$status = get_transient('qrmv_' . $token);
$error_message = '';
$step = isset($_POST['step']) ? intval($_POST['step']) : 1;

// 0. 基本檢查：Token 是否有效
if (! $status || $status !== 'pending') {
    wp_die('<h1>無效或過期的請求</h1><p>請重新掃描 QR Code。</p>', 'Error');
}

// 0.5 風控檢查 (IP Level)
// 在顯示任何畫面之前，先檢查 IP 是否異常
$risk_control = new QRMV_Risk_Control();
$ip_check = $risk_control->check_ip($_SERVER['REMOTE_ADDR']);

if ($ip_check !== true) {
    // IP 被封鎖
    wp_die("<h1>⛔ 存取被拒絕</h1><p>$ip_check</p>", 'Risk Blocked');
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STEP 1: 提交手機號碼 (執行風控檢查)
    if ($step === 2 && isset($_POST['phone'])) {
        // 強制過濾非數字字符
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);

        if (! $phone) {
            $error_message = "❌ 請輸入有效的手機號碼 (僅限數字)";
            $step = 1;
        } else {
            // 風控檢查 (Phone Level)
            $phone_check = $risk_control->check_phone($phone);
            if ($phone_check !== true) {
                $error_message = "❌ " . $phone_check;
                $step = 1; // 回到輸入手機號碼
            } else {
                // 通過風控，產生模擬驗證碼 888888
                $sim_code = '888888';

                // 🛡️ 保護使用者隱私：遮蔽手機號碼後 5 碼
                // 例如：0912345678 -> 09123*****
                $masked_phone = strlen($phone) > 5
                    ? substr($phone, 0, -5) . '*****'
                    : '*****';

                $step = 2; // 進入輸入驗證碼的畫面
            }
        }
    }

    // STEP 2: 提交驗證碼 (完成驗證)
    if ($step === 3 && isset($_POST['sms_code'])) {
        // 強制過濾非數字字符
        $code = preg_replace('/[^0-9]/', '', $_POST['sms_code']);

        if ($code === '888888') {
            // A. 更新 Transient 狀態 -> 'verified'
            set_transient('qrmv_' . $token, 'verified', 10 * MINUTE_IN_SECONDS);

            // B. 寫入 DB 歷史紀錄 (風控用)
            global $wpdb;
            $table = $wpdb->prefix . 'qrmv_history';
            $wpdb->insert(
                $table,
                array(
                    'token' => $token,
                    'phone_number' => isset($_POST['phone_hidden']) ? sanitize_text_field($_POST['phone_hidden']) : 'unknown',
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'status' => 'success'
                )
            );

            // C. 顯示完成畫面
            $step = 99; // Success
        } else {
            $error_message = "驗證碼錯誤，請輸入 888888";
            // 重新計算遮蔽號碼 (因為我們要留在 Step 2)
            $phone_hidden = isset($_POST['phone_hidden']) ? $_POST['phone_hidden'] : '';
            $masked_phone = strlen($phone_hidden) > 5 ? substr($phone_hidden, 0, -5) . '*****' : '*****';
            $step = 2; // 回到輸入驗證碼
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>手機安全驗證</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-top: 0;
            color: #1a73e8;
        }

        input[type="tel"],
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1557b0;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .sim-sms-popup {
            background: #333;
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
            font-family: monospace;
        }
    </style>
</head>

<body>

    <div class="card">
        <?php if ($step === 1) : ?>
            <!-- 畫面 1: 輸入手機號碼 -->
            <h2>📱 手機驗證</h2>
            <p>為了確保帳戶安全，請輸入您的手機號碼以接收驗證碼。</p>

            <?php if ($error_message) : ?>
                <div class="error"><?php echo esc_html($error_message); ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="tel" name="phone" placeholder="0912345678" required
                    pattern="[0-9]*" inputmode="numeric"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                <input type="hidden" name="step" value="2">
                <button type="submit">發送驗證碼</button>
            </form>

        <?php elseif ($step === 2) : ?>
            <!-- 畫面 2: 輸入驗證碼 -->
            <h2>🔒 輸入驗證碼</h2>
            <p>
                驗證碼已傳送至：<br>
                <strong style="font-size: 1.2em; color: #333; letter-spacing: 1px;">
                    <?php echo esc_html($masked_phone); ?>
                </strong>
            </p>

            <?php if ($error_message) : ?>
                <div class="error"><?php echo esc_html($error_message); ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="sms_code" placeholder="888888" maxlength="6" required
                    pattern="[0-9]*" inputmode="numeric" autocomplete="one-time-code"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                <input type="hidden" name="step" value="3">
                <!-- 保存手機號碼供最後寫入 DB -->
                <input type="hidden" name="phone_hidden" value="<?php echo esc_attr(isset($_POST['phone']) ? $_POST['phone'] : (isset($_POST['phone_hidden']) ? $_POST['phone_hidden'] : '')); ?>">
                <button type="submit">驗證</button>
            </form>

            <!-- 模擬簡訊彈窗 -->
            <div class="sim-sms-popup">
                🔔 [模擬簡訊] <br>
                您的驗證碼是：<strong>888888</strong>
            <?php elseif ($step === 99) : ?>
                <!-- 畫面 3: 成功 -->
                <h2 style="color: green;">✅ 驗證成功</h2>
                <p>您的身份已確認！<br>現在您可以查看電腦螢幕。</p>
                <p style="font-size: 12px; color: #999;">(此頁面將自動關閉...)</p>

            <?php endif; ?>
            </div>

</body>

</html>