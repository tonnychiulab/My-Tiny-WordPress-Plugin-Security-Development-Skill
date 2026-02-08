<?php

/**
 * Core Logic for Quantum Time Capsule
 *
 * @package    Quantum_Time_Capsule
 * @subpackage Quantum_Time_Capsule/includes
 * @author     Tonny Lab
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * The core plugin class.
 */
class Quantum_Time_Capsule
{

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'handle_form_submission'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    /**
     * Create the database table on installation.
     */
    public function install()
    {
        global $wpdb;
        $table_name      = $wpdb->prefix . 'quantum_capsules';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			title tinytext NOT NULL,
			encrypted_content text NOT NULL,
			iv text NOT NULL,
			reveal_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Add CSS for the admin page.
     */
    public function enqueue_styles()
    {
        wp_enqueue_style('dashicons');
        // Simple inline styles to make it look "cool" without extra file request for now
        wp_add_inline_style('common', '
			.qtc-card {
				background: #fff;
				border: 1px solid #ccd0d4;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
				padding: 20px;
				margin-top: 20px;
				max-width: 800px;
				border-radius: 8px;
			}
			.qtc-locked {
				color: #d63638;
				font-weight: bold;
			}
			.qtc-unlocked {
				color: #00a32a;
				font-weight: bold;
			}
			.qtc-header {
				display: flex;
				align-items: center;
				gap: 10px;
				margin-bottom: 20px;
			}
			.qtc-header h1 {
				margin: 0;
			}
		');
    }

    /**
     * Add the admin menu.
     */
    public function add_menu()
    {
        // Capability Check: Only admins should see this
        add_menu_page(
            __('Quantum Time Capsule', 'quantum-time-capsule'),
            __('Time Capsules', 'quantum-time-capsule'),
            'manage_options',
            'quantum-time-capsule',
            array($this, 'render_page'),
            'dashicons-lock',
            6
        );
    }

    /**
     * Securely encrypt data using AES-256-CBC.
     * 
     * @param string $data The data to encrypt.
     * @return array|false Encrypted data and IV, or false on failure.
     */
    private function encrypt_data($data)
    {
        // Use WordPress salts for key derivation
        $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true);
        $iv  = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

        if ($encrypted === false) {
            return false;
        }

        return array(
            'content' => base64_encode($encrypted),
            'iv'      => base64_encode($iv),
        );
    }

    /**
     * Securely decrypt data.
     *
     * @param string $encrypted_data The encrypted string (base64).
     * @param string $iv             The IV (base64).
     * @return string|false Decrypted data or false on failure.
     */
    private function decrypt_data($encrypted_data, $iv)
    {
        $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true);

        $decrypted = openssl_decrypt(
            base64_decode($encrypted_data),
            'aes-256-CBC',
            $key,
            0,
            base64_decode($iv)
        );

        return $decrypted;
    }

    /**
     * Handle form submissions securely.
     */
    public function handle_form_submission()
    {
        // 1. Check if form is submitted
        if (! isset($_POST['qtc_action']) && ! isset($_GET['qtc_action'])) {
            return;
        }

        // 2. AUTH: Verify User Capability
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you do not have permission to access this page.', 'quantum-time-capsule'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'quantum_capsules';

        // --- ADD CAPSULE ---
        if (isset($_POST['qtc_action']) && $_POST['qtc_action'] === 'add_capsule') {

            // 3. CSRF: Verify Nonce
            check_admin_referer('qtc_add_action', 'qtc_nonce');

            // 4. Input Sanitization
            // Fix: MissingUnslash warning - Use wp_unslash before sanitization
            $title       = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
            $raw_content = isset($_POST['content']) ? sanitize_textarea_field(wp_unslash($_POST['content'])) : '';
            $reveal_date = isset($_POST['reveal_date']) ? sanitize_text_field(wp_unslash($_POST['reveal_date'])) : '';

            // Validation
            if (empty($title) || empty($raw_content) || empty($reveal_date)) {
                // Redirect with error
                wp_safe_redirect(add_query_arg('msg', 'empty_fields', admin_url('admin.php?page=quantum-time-capsule')));
                exit;
            }

            // 5. Encryption (Sensitive Data Exposure Protection)
            $encrypted_package = $this->encrypt_data($raw_content);

            if (! $encrypted_package) {
                wp_die(esc_html__('Encryption failed.', 'quantum-time-capsule'));
            }

            // 6. SQL Injection Prevention ($wpdb->prepare)
            // 6. SQL Injection Prevention ($wpdb->prepare)
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->query(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                    "INSERT INTO $table_name (title, encrypted_content, iv, reveal_date, created_at) VALUES (%s, %s, %s, %s, %s)",
                    $title,
                    $encrypted_package['content'],
                    $encrypted_package['iv'],
                    $reveal_date,
                    current_time('mysql')
                )
            );

            // Clear cache
            wp_cache_delete('qtc_all_capsules', 'quantum_time_capsule');

            // Redirect Success
            wp_safe_redirect(add_query_arg('msg', 'added', admin_url('admin.php?page=quantum-time-capsule')));
            exit;
        }

        // --- DELETE CAPSULE ---
        if (isset($_GET['qtc_action']) && $_GET['qtc_action'] === 'delete_capsule') {

            // 3. CSRF: Verify Nonce (GET Request)
            $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
            check_admin_referer('qtc_delete_' . $id);

            if ($id > 0) {
                // 6. SQL Injection Prevention
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $wpdb->query(
                    $wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                        "DELETE FROM $table_name WHERE id = %d",
                        $id
                    )
                );
                // Clear cache
                wp_cache_delete('qtc_all_capsules', 'quantum_time_capsule');
            }

            wp_safe_redirect(add_query_arg('msg', 'deleted', admin_url('admin.php?page=quantum-time-capsule')));
            exit;
        }
    }

    /**
     * Render the admin page.
     */
    public function render_page()
    {
        // Output Escaping: Always escape output
        // Fix: MissingUnslash warning
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $msg = isset($_GET['msg']) ? sanitize_text_field(wp_unslash($_GET['msg'])) : '';
?>
        <div class="wrap">
            <div class="qtc-header">
                <h1><?php esc_html_e('🌌 Quantum Time Capsule', 'quantum-time-capsule'); ?></h1>
            </div>

            <?php if ('added' === $msg) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Time Capsule sealed successfully!', 'quantum-time-capsule'); ?></p>
                </div>
            <?php elseif ('deleted' === $msg) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Time Capsule destroyed.', 'quantum-time-capsule'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Add New Capsule Form -->
            <div class="qtc-card">
                <h2><?php esc_html_e('Seal a New Capsule', 'quantum-time-capsule'); ?></h2>
                <form method="post" action="">
                    <?php wp_nonce_field('qtc_add_action', 'qtc_nonce'); ?>
                    <input type="hidden" name="qtc_action" value="add_capsule">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="title"><?php esc_html_e('Title', 'quantum-time-capsule'); ?></label></th>
                            <td><input name="title" type="text" id="title" value="" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="content"><?php esc_html_e('Secret Message', 'quantum-time-capsule'); ?></label></th>
                            <td>
                                <textarea name="content" id="content" rows="4" class="large-text" required></textarea>
                                <p class="description"><?php esc_html_e('This content will be encrypted in the database.', 'quantum-time-capsule'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="reveal_date"><?php esc_html_e('Reveal Date', 'quantum-time-capsule'); ?></label></th>
                            <td><input name="reveal_date" type="datetime-local" id="reveal_date" class="regular-text" required></td>
                        </tr>
                    </table>
                    <?php submit_button(__('Seal Capsule', 'quantum-time-capsule'), 'primary'); ?>
                </form>
            </div>

            <hr>

            <!-- List Capsules -->
            <h2><?php esc_html_e('Your Time Capsules', 'quantum-time-capsule'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'quantum-time-capsule'); ?></th>
                        <th><?php esc_html_e('Title', 'quantum-time-capsule'); ?></th>
                        <th><?php esc_html_e('Status', 'quantum-time-capsule'); ?></th>
                        <th><?php esc_html_e('Reveal Date', 'quantum-time-capsule'); ?></th>
                        <th><?php esc_html_e('Content', 'quantum-time-capsule'); ?></th>
                        <th><?php esc_html_e('Actions', 'quantum-time-capsule'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'quantum_capsules';

                    // Implement Object Caching to Fix: DirectDatabaseQuery.NoCaching
                    $capsules = wp_cache_get('qtc_all_capsules', 'quantum_time_capsule');

                    if (false === $capsules) {
                        // Using get_results implies read query, but output must still be escaped
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
                        $capsules = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
                        wp_cache_set('qtc_all_capsules', $capsules, 'quantum_time_capsule', 3600);
                    }

                    if ($capsules) {
                        foreach ($capsules as $capsule) {
                            $now = current_time('mysql');
                            $is_locked = $now < $capsule->reveal_date;
                            // Use unique nonce for each delete link
                            $delete_url = wp_nonce_url(
                                admin_url('admin.php?page=quantum-time-capsule&qtc_action=delete_capsule&id=' . $capsule->id),
                                'qtc_delete_' . $capsule->id
                            );
                    ?>
                            <tr>
                                <td><?php echo absint($capsule->id); ?></td>
                                <td><?php echo esc_html($capsule->title); ?></td>
                                <td>
                                    <?php if ($is_locked) : ?>
                                        <span class="qtc-locked dashicons-before dashicons-lock"><?php esc_html_e(' Locked', 'quantum-time-capsule'); ?></span>
                                    <?php else : ?>
                                        <span class="qtc-unlocked dashicons-before dashicons-unlock"><?php esc_html_e(' Unlocked', 'quantum-time-capsule'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($capsule->reveal_date); ?></td>
                                <td>
                                    <?php
                                    if ($is_locked) {
                                        echo '<code>' . esc_html(substr($capsule->encrypted_content, 0, 20)) . '...</code> (Encrypted)';
                                    } else {
                                        // TIME UNLOCKED! Decrypt content
                                        $decrypted = $this->decrypt_data($capsule->encrypted_content, $capsule->iv);
                                        if ($decrypted) {
                                            echo nl2br(esc_html($decrypted));
                                        } else {
                                            echo '<span style="color:red;">Decrypt Error! Salt changed?</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($delete_url); ?>" class="button button-small delete" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'quantum-time-capsule'); ?>');">
                                        <?php esc_html_e('Destroy', 'quantum-time-capsule'); ?>
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="6">' . esc_html__('No capsules found.', 'quantum-time-capsule') . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
<?php
    }
}
