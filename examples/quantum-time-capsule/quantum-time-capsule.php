<?php

/**
 * Plugin Name:       Quantum Time Capsule (量子時光膠囊)
 * Plugin URI:        https://github.com/Tonny-Lab/My-Tiny-WordPress-Plugin-Security-Development-Skill
 * Description:       A secure WordPress plugin to encrypt and store messages that can only be unlocked after a specific date.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Tested up to:      6.9
 * Requires PHP:      7.4
 * Author:            Tonny Lab
 * Author URI:        https://github.com/Tonny-Lab
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quantum-time-capsule
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('QUANTUM_TIME_CAPSULE_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function quantum_time_capsule_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-quantum-time-capsule.php';
    $plugin = new Quantum_Time_Capsule();
    $plugin->install();
}

/**
 * The code that runs during plugin deactivation.
 */
function quantum_time_capsule_deactivate()
{
    // Flush rewrite rules if necessary
}

register_activation_hook(__FILE__, 'quantum_time_capsule_activate');
register_deactivation_hook(__FILE__, 'quantum_time_capsule_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-quantum-time-capsule.php';

/**
 * Begins execution of the plugin.
 */
function quantum_time_capsule_run()
{
    $plugin = new Quantum_Time_Capsule();
    $plugin->run();
}

quantum_time_capsule_run();
