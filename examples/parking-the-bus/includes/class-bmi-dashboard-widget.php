<?php

if (! defined('ABSPATH')) {
    exit;
}

class BMI_Dashboard_Widget
{

    public function run()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }

    public function add_dashboard_widgets()
    {
        // 只有管理員才看得到
        if (! current_user_can('manage_options')) {
            return;
        }

        wp_add_dashboard_widget(
            'bmi_dashboard_widget',                 // Widget slug
            '🚌 Parking The Bus - 戰情室 (最新紀錄)', // Title
            array($this, 'dashboard_widget_function') // Callback function
        );
    }

    public function dashboard_widget_function()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . BMI_ADAR_DB_TABLE;

        // 查詢最近 5 筆紀錄
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 5");

        if ($logs) {
            echo '<table class="widefat striped">';
            echo '<thead><tr><th>時間</th><th>來源 IP</th><th>狀態</th><th>證據</th></tr></thead>';
            echo '<tbody>';
            foreach ($logs as $log) {
                $status_text = 'Pending';
                if ($log->status == 1) $status_text = 'Sent';
                if ($log->status == 2) $status_text = 'Failed';

                echo '<tr>';
                echo '<td>' . esc_html($log->created_at) . '</td>';
                echo '<td><a href="https://www.abuseipdb.com/check/' . esc_attr($log->source_ip) . '" target="_blank">' . esc_html($log->source_ip) . '</a></td>';
                echo '<td>' . esc_html($status_text) . '</td>';
                echo '<td>' . esc_html(mb_strimwidth($log->evidence_blob, 0, 20, '...')) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>目前沒有發現攻擊紀錄。很好！(Bus Parked Successfully)</p>';
        }

        echo '<div style="margin-top:10px; text-align:right;"><a href="#" class="button button-small">查看完整報告 (Coming Soon)</a></div>';
    }
}
