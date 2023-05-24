<?php
/**
 * Plugin Name: Auto Assign Role by Email Domain
 * Description: Automatically assigns user role based on email domain at the time of user registration.
 * Version: 1.0.1
 * Author: Shahid Hussain
 * Author URI: https://hocien.com
 * Version: 1.0.2
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Add plugin settings page
add_action('admin_menu', 'aarbed_create_settings_page');

function aarbed_create_settings_page() {
    add_options_page(
        'Auto Assign Role by Email Domain',
        'Auto Assign Role by Email Domain',
        'manage_options',
        'aarbed_settings_page',
        'aarbed_render_settings_page'
    );
}

// Render settings page
function aarbed_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap">
        <h1>Auto Assign Role by Email Domain</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('aarbed_settings_group');
            do_settings_sections('aarbed_settings_page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'aarbed_register_plugin_settings');

function aarbed_register_plugin_settings() {
    register_setting('aarbed_settings_group', 'aarbed_email_domain_rules');
    
    add_settings_section(
        'aarbed_general_settings_section',
        'General Settings',
        '',
        'aarbed_settings_page'
    );

    add_settings_field(
        'aarbed_email_domain_rules',
        'Assign role based on email domain',
        'aarbed_email_domain_rules_callback',
        'aarbed_settings_page',
        'aarbed_general_settings_section'
    );
}

// Render email domain rules field
function aarbed_email_domain_rules_callback() {
    $rules = get_option('aarbed_email_domain_rules', '');
    ?>
    <textarea name="aarbed_email_domain_rules" rows="5" cols="60"><?php echo esc_textarea($rules); ?></textarea>
    <p class="description">Enter rules in the format: domain|role (one rule per line). Example:</p>
    <pre>example.com|editor
example.org|author</pre>
    <?php
}

// Update aarbed_auto_assign_role_by_email_domain function to use the settings
function aarbed_auto_assign_role_by_email_domain($user_id) {
    $user_email = get_userdata($user_id)->user_email;
    $email_domain = substr(strrchr($user_email, "@"), 1);

    $rules = get_option('aarbed_email_domain_rules', '');
    $rules = explode("\n", $rules);

    $user_role = 'subscriber';
    foreach ($rules as $rule) {
        list($domain, $role) = explode('|', $rule);
        if ($email_domain === trim($domain)) {
            $user_role = trim($role);
            break;
        }
    }

    $user = new WP_User($user_id);
    if (!empty($user) && !empty($user_role)) {
        $user->set_role($user_role);
    } else {
        error_log('Auto Assign Role by Email Domain: Unable to update user role. User ID: ' . $user_id);
    }
}

add_action( 'user_register', 'aarbed_auto_assign_role_by_email_domain' );
