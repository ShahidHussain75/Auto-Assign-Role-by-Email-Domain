<?php
/**
 * Plugin Name: Auto Assign Role by Email Domain
 * Plugin URI:  https://hocien.com/auto-assign-role/
 * Description: The "Auto Assign Role by Email Domain" plugin allows you to assign user roles based on their email addresses during registration. If a user's role has expired according to the specified time set in the plugin's settings, their role will be automatically changed to "Subscriber". Additionally, if the administrator deletes a domain rule in the plugin's settings page, all users with that specific domain name will have their roles changed to "Subscriber".
 * Version: 2.0.0
 * Author: Shahid Hussain
 * Author URI: https://www.upwork.com/freelancers/~01304587883757d540
 * Version: 1.0.0
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: Auto-Assign-Role
 */

// Add admin menu
add_action('admin_menu', 'domain_registration_menu');

function domain_registration_menu() {
    add_options_page('Domain Registration Settings', 'Domain Registration', 'manage_options', 'domain-registration', 'domain_registration_settings_page');
}

// Enqueue jQuery UI datepicker script and style
add_action('admin_enqueue_scripts', 'domain_registration_enqueue_scripts');

function domain_registration_enqueue_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-datepicker-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}

// Settings page
function domain_registration_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    domain_registration_delete();
    domain_registration_add();

    $domains = get_option('domain_registration_domains', array());
    ?>
    <div class="wrap">
        <h1>Domain Registration Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Add Domain</th>
                    <td>
                        <input type="text" name="add_domain" placeholder="example.com" required />
                        <select name="add_role">
                            <?php wp_dropdown_roles(); ?>
                        </select>
                        <input type="text" name="add_expiration" id="expiration_date" placeholder="Expiration date" required />
                        <input type="submit" class="button button-primary" value="Add" />
                    </td>
                </tr>
            </table>
        </form>
        <hr />
        <h2>Current Domains</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Domain</th>
                    <th>Role</th>
                    <th>Expiration</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $domain => $data) : ?>
                    <?php
                    $expiration_date = strtotime($data['expiration']);
                    $remaining_time = $expiration_date - time();
                    $days_remaining = floor($remaining_time / (24 * 60 * 60));
                    $hours_remaining = floor(($remaining_time % (24 * 60 * 60)) / (60 * 60));
                    $minutes_remaining = floor(($remaining_time % (60 * 60)) / 60);
                    $seconds_remaining = $remaining_time % 60;
                    ?>
                    <tr>
                        <td><?php echo $domain; ?></td>
                        <td><?php echo $data['role']; ?></td>
                        <td><?php echo "$days_remaining days $hours_remaining hours $minutes_remaining minutes $seconds_remaining seconds"; ?></td> <!-- Display countdown -->
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="delete_domain" value="<?php echo $domain; ?>" />
                                <input type="submit" class="button button-secondary" value="Delete" />
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#expiration_date').datepicker({
                minDate: 0, // Prevent selecting past dates
                dateFormat: 'yy-mm-dd' // Set the desired date format
            });
        });
    </script>
    <?php
}

// Add domain
function domain_registration_add() {
    if (isset($_POST['add_domain']) && isset($_POST['add_role']) && isset($_POST['add_expiration'])) {
        $domain = sanitize_text_field($_POST['add_domain']);
        $role = sanitize_text_field($_POST['add_role']);
        $expiration = sanitize_text_field($_POST['add_expiration']);

        if (empty($expiration)) {
            echo '<div class="error"><p><strong>' . __('Expiration date is required.', 'menu-test') . '</strong></p></div>';
            return;
        }

        $expiration_timestamp = strtotime($expiration);

        if ($expiration_timestamp === false || $expiration_timestamp < time()) {
            echo '<div class="error"><p><strong>' . __('Invalid expiration date. Please enter a future date.', 'menu-test') . '</strong></p></div>';
            return;
        }

        $domains = get_option('domain_registration_domains', array());

        if (isset($domains[$domain])) {
            echo '<div class="error"><p><strong>' . __('Domain already exists. Please use a different domain.', 'menu-test') . '</strong></p></div>';
            return;
        }

        $domains[$domain] = array('role' => $role, 'expiration' => $expiration);
        update_option('domain_registration_domains', $domains);

        // Update existing users with the specified domain
        update_existing_users_role_by_domain($domain, $role, $expiration);

        echo '<div class="updated"><p><strong>' . __('Domain added.', 'menu-test') . '</strong></p></div>';
    }
}
// Update existing users' role by domain
function update_existing_users_role_by_domain($domain, $role, $expiration) {
    $users = get_users();
    foreach ($users as $user) {
        $user_email_domain = substr(strrchr($user->user_email, "@"), 1);
        if ($user_email_domain === $domain) {
            $user->set_role($role);
            $expiration_date = strtotime($expiration);

            update_user_meta($user->ID, 'domain_registration_expiration', $expiration_date);
        }
    }
}

// Delete domain
function domain_registration_delete() {
    if (isset($_POST['delete_domain'])) {
        $domain = sanitize_text_field($_POST['delete_domain']);
        change_role_to_subscriber_by_domain($domain);
        $domains = get_option('domain_registration_domains', array());
        unset($domains[$domain]);
        update_option('domain_registration_domains', $domains);

        echo '<div class="updated"><p><strong>' . __('Domain deleted.', 'menu-test') . '</strong></p></div>';
    }
}

// Change role to subscriber by domain
function change_role_to_subscriber_by_domain($domain) {
    $users = get_users();
    foreach ($users as $user) {
        $user_email_domain = substr(strrchr($user->user_email, "@"), 1);
        if ($user_email_domain === $domain) {
            $user->set_role('subscriber');
        }
    }
}

// Restrict registration to specific domain names start
add_action('registration_errors', 'domain_registration_restrict_registration', 10, 3);

function domain_registration_restrict_registration($errors, $sanitized_user_login, $user_email) {
    $allowed_domains = array();
    $domains = get_option('domain_registration_domains', array());

    foreach ($domains as $domain => $data) {
        $allowed_domains[] = $domain;
    }

    $user_email_domain = substr(strrchr($user_email, "@"), 1);
    if (!in_array($user_email_domain, $allowed_domains)) {
        $errors->add('domain_registration_error', __('Registration is restricted to specific domain names.'));
    }

    return $errors;
}
// Restrict registration to specific domain names end
