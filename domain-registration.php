<?php
/*
Plugin Name: Auto Assign Role by Email Domain
Description: Automatically assigns a user role based on their email domain during registration and handles expiration.
Version: 1.0
Author: Your Name
*/

// Add admin menu
add_action('admin_menu', 'domain_registration_menu');

function domain_registration_menu() {
    add_options_page('Domain Registration Settings', 'Domain Registration', 'manage_options', 'domain-registration', 'domain_registration_settings_page');
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
                        <input type="text" name="add_domain" placeholder="example.com" />
                        <select name="add_role">
                            <?php wp_dropdown_roles(); ?>
                        </select>
                        <input type="number" name="add_expiration" placeholder="Expiration days" min="1" />
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
                    <th>Expiration (days)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $domain => $data) : ?>
                    <tr>
                        <td><?php echo $domain; ?></td>
                        <td><?php echo $data['role']; ?></td>
                        <td><?php echo $data['expiration']; ?></td>
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
    <?php
}

// Add domain
function domain_registration_add() {
    if (isset($_POST['add_domain']) && isset($_POST['add_role']) && isset($_POST['add_expiration'])) {
        $domain = sanitize_text_field($_POST['add_domain']);
        $role = sanitize_text_field($_POST['add_role']);
        $expiration = intval($_POST['add_expiration']);

        if ($expiration < 1) {
            echo '<div class="error"><p><strong>' . __('Invalid expiration value. Please enter a positive number.', 'menu-test') . '</strong></p></div>';
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
            $expiration_date = time() + ($expiration * 24 * 60 * 60);
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

// Assign role on registration
add_action('user_register', 'domain_registration_assign_role', 10, 1);

function domain_registration_assign_role($user_id) {
    $user = get_userdata($user_id);
    $user_email_domain = substr(strrchr($user->user_email, "@"), 1);

    $domains = get_option('domain_registration_domains', array());

    if (isset($domains[$user_email_domain])) {
        $user->set_role($domains[$user_email_domain]['role']);
        $expiration_date = time() + ($domains[$user_email_domain]['expiration'] * 24 * 60 * 60);
        update_user_meta($user_id, 'domain_registration_expiration', $expiration_date);
    }
}

// Check for expired roles
add_action('init', 'domain_registration_check_expirations');

function domain_registration_check_expirations() {
    $users = get_users();
    foreach ($users as $user) {
        $expiration_date = get_user_meta($user->ID, 'domain_registration_expiration', true);
        if ($expiration_date && time() > $expiration_date) {
            $user->set_role('subscriber');
            delete_user_meta($user->ID, 'domain_registration_expiration');
        }
    }
}
?>
