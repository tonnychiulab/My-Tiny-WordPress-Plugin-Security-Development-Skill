=== Quantum Time Capsule (量子時光膠囊) ===
Contributors: tonnylab
Tags: security, encryption, time-capsule, privacy
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A secure WordPress plugin to encrypt and store messages that can only be unlocked after a specific date.

== Description ==

Quantum Time Capsule is the ultimate demonstration of secure WordPress plugin development practices. It allows users to securely encrypt messages ("Time Capsules") and store them in the database. These capsules are locked until a specified future date, ensuring the contents remain private until the right time.

**Key Features:**

*   **Military-Grade Encryption**: Uses `openssl_encrypt` with AES-256-CBC algorithm to secure your messages.
*   **Time-Locked Access**: Messages are mathematically locked until the reveal date.
*   **Security First**: Built following the strictest security guidelines (My-Tiny-WordPress-Plugin-Security-Development-Skill).
*   **Standards Compliant**: Passes WordPress Plugin Check (PCP) requirements.

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/quantum-time-capsule` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Navigate to the "Quantum Time Capsule" menu to start creating your secure messages.

== Frequently Asked Questions ==

= Is my data really secure? =

Yes! We use AES-256-CBC encryption. The encryption key is derived from your WordPress installation's unique salts. Even if someone accesses your database, they cannot read the message content without the keys.

= What happens if I change my WordPress salts? =

**Warning:** If you change the security keys in your `wp-config.php` file, all existing time capsules will become permanently unreadable.

== Changelog ==

= 1.0.0 =
*   Initial release.
