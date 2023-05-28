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

// Create table on plugin activation
function aarbed_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aarbed_email_rules';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        return;
    }

    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        domain VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL,
        expiration_days INT(11) DEFAULT NULL,
        expiration_date DATE DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'aarbed_create_table');

// Enqueue plugin styles and scripts
function aarbed_enqueue_scripts()
{
    wp_enqueue_style('aarbed-style', plugin_dir_url(__FILE__) . 'css/style.css');
}
add_action('admin_enqueue_scripts', 'aarbed_enqueue_scripts');

// Add plugin settings page
function aarbed_settings_page()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'aarbed_email_rules';
    $roles = wp_roles()->get_names();

    if (isset($_POST['aarbed_submit'])) {
        $domain = sanitize_text_field($_POST['aarbed_domain']);
        $role = sanitize_text_field($_POST['aarbed_role']);
        $expiration_days = isset($_POST['aarbed_expiration_days']) ? intval($_POST['aarbed_expiration_days']) : null;

        $existing_rule = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE domain = %s", $domain));
        if ($existing_rule) {
            echo '<div class="notice notice-error"><p>Duplicate entry. This domain rule already exists.</p></div>';
        } elseif ($expiration_days !== null && $expiration_days < 0) {
            echo '<div class="notice notice-error"><p>Invalid expiration days. Please enter a non-negative value.</p></div>';
        } else {
            $expiration_date = null;
            if ($expiration_days) {
                $expiration_date = date('Y-m-d', strtotime("+{$expiration_days} days"));
            }

            $wpdb->insert(
                $table_name,
                array(
                    'domain' => $domain,
                    'role' => $role,
                    'expiration_days' => $expiration_days,
                    'expiration_date' => $expiration_date,
                )
            );
        }
    }

    if (isset($_GET['aarbed_delete_id'])) {
        $delete_id = intval($_GET['aarbed_delete_id']);
        $wpdb->delete($table_name, array('id' => $delete_id));
    }

    $rules = $wpdb->get_results("SELECT * FROM $table_name");

    // Check and remove expired role assignments
$current_date = date('Y-m-d');
$expired_assignments = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $table_name WHERE expiration_days IS NOT NULL AND expiration_date IS NOT NULL AND expiration_date <= %s", $current_date)
);

foreach ($expired_assignments as $assignment) {
    $user_query_args = array(
        'role' => $assignment->role,
        'meta_key' => 'aarbed_email_domain',
        'meta_value' => $assignment->domain,
    );
    $users = get_users($user_query_args);
    foreach ($users as $user) {
        $user->remove_role($assignment->role);
    }
    $wpdb->delete($table_name, array('id' => $assignment->id));
}

  // Update role for existing users based on domain rules
$users_to_update = get_users();
foreach ($users_to_update as $user_to_update) {
    $user_id = $user_to_update->ID;
    $user_email = $user_to_update->user_email;
    $email_parts = explode('@', $user_email);
    $domain = strtolower($email_parts[1]);

    $user_role = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE domain = %s", $domain));

    if ($user_role) {
        $user = new WP_User($user_id);

        // Remove all existing roles
        $user->set_role('');

        // Assign the new role based on the domain rule
        $user->add_role($user_role->role);
    }
}

    ?>
    <div class="wrap">
    
    <h1>Auto Assign Role by Email Domain</h1>
        <br />
        <div class="description">
            <h3>Instructions to Auto Assign Role by Email Domain rules:</h3>
            <ol>
                <li>In the "Existing Rules" section, you will find a table displaying existing rules.</li>
                <li>To add a new rule, locate the "Add New Rule" section below the table.</li>
                <li>Enter the email domain and the desired role for the domain.</li>
                <li>Optionally, you can specify expiration days for the role assignment. Leave it blank for no expiration.</li>
                <li>Click on the "Add Rule" button to save the rule.</li>
                <li>You can delete existing rules by using the respective actions in the table.</li>
            </ol>
        </div>
        <br />
        
        
        <h1>Add New Rule</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="aarbed_domain">Domain:</label>
                    </th>
                    <td>
                        <input type="text" id="aarbed_domain" name="aarbed_domain" class="regular-text" required>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="aarbed_role">Role:</label>
                    </th>
                    <td>
                        <select id="aarbed_role" name="aarbed_role" required>
                            <?php foreach ($roles as $role_slug => $role_name) : ?>
                                <option value="<?php echo esc_attr($role_slug); ?>"><?php echo esc_html($role_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="aarbed_expiration_days">Expiration (optional):</label>
                    </th>
                    <td>
                        <input type="number" id="aarbed_expiration_days" name="aarbed_expiration_days" class="small-text" min="0">
                        <span class="description">days</span>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="aarbed_submit" id="aarbed_submit" class="button button-primary" value="Add Rule">
            </p>
        </form>


<br />
        
        
        <h1>Existing Rules </h1>
        <?php if (!empty($rules)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Role</th>
                        <th>Expiration Days</th>
                        <th>Expiration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule) : ?>
                        <tr>
                            <td><?php echo $rule->domain; ?></td>
                            <td><?php echo $roles[$rule->role]; ?></td>
                            <td><?php echo $rule->expiration_days ? $rule->expiration_days . ' days' : 'Never'; ?></td>
                            <td><?php echo esc_html($rule->expiration_date); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg('aarbed_delete_id', $rule->id)); ?>" class="delete" onclick="return confirm('Are you sure you want to delete this rule?');" style="color:#FF0004">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No rules found.</p>
        <?php endif; ?>
    </div>
       <?php
}

// Add plugin settings page to the admin menu
function aarbed_add_settings_page()
{
    add_menu_page(
        'Email Role Rules',
        'Email Role Rules',
        'manage_options',
        'aarbed-settings',
        'aarbed_settings_page',
        'dashicons-email-alt'
    );
}
add_action('admin_menu', 'aarbed_add_settings_page');

