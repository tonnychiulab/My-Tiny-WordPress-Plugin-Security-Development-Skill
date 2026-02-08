<?php

if (! defined('ABSPATH')) {
    exit;
}

class BMI_Activator
{

    public static function activate()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . BMI_ADAR_DB_TABLE;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			source_ip varchar(45) NOT NULL,
			asn varchar(20) DEFAULT '',
			isp_name varchar(100) DEFAULT '',
			report_method varchar(20) DEFAULT 'PENDING', -- EMAIL, WEB_FORM, PENDING
			target_contact varchar(255) DEFAULT '',
			evidence_blob text,
			status tinyint(1) DEFAULT 0, -- 0:Pending, 1:Sent, 2:Failed
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY source_ip (source_ip),
			KEY status (status)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
