<?php

/**
 * Plugin Name: Parking The Bus (BMI-ADAR)
 * Description: Bear Magpie Active Defense & Abuse Reporter. Uses "Invert, Always Invert" strategy to push back against attackers by automating upstream abuse reporting.
 * Version: 2.0.0
 * Author: Bear Magpie Intelligence & Antigravity
 * Text Domain: parking-the-bus
 */

if (! defined('ABSPATH')) {
    exit;
}

// Global Constants
define('BMI_ADAR_VERSION', '2.0.0');
define('BMI_ADAR_PATH', plugin_dir_path(__FILE__));
define('BMI_ADAR_URL', plugin_dir_url(__FILE__));
define('BMI_ADAR_DB_TABLE', 'bmi_abuse_reports'); // Prefix will be added by WP

// Autoload Classes (Manual for now)
require_once BMI_ADAR_PATH . 'includes/class-bmi-activator.php';
require_once BMI_ADAR_PATH . 'includes/class-bmi-threat-engine.php';

class Parking_The_Bus
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Activation Hook
        register_activation_hook(__FILE__, array('BMI_Activator', 'activate'));

        // Initialize Modules
        $threat_engine = new BMI_Threat_Engine();
        $threat_engine->run();

        // Dashboard Widget
        if (is_admin()) {
            require_once BMI_ADAR_PATH . 'includes/class-bmi-dashboard-widget.php';
            $dashboard = new BMI_Dashboard_Widget();
            $dashboard->run();

            // Settings Page
            require_once BMI_ADAR_PATH . 'includes/class-bmi-settings.php';
            $settings = new BMI_Settings();
            $settings->run();
        }
    }
}

// Let's Park The Bus!
Parking_The_Bus::get_instance();
