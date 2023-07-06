<?php
/*
Plugin Name: Activity Tracker
Description: A plugin to track admin and user activity in the WordPress dashboard
Version: 1.0
Author: Byron Jacobs
Author URI: https://byronjacobs.co.za
License: GPLv2 or later
Text Domain: activity-tracker
*/

if (!defined('WPINC')) {
    die;
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'activity-tracker-db.php';
require_once plugin_dir_path(__FILE__) . 'log-actions.php';
require_once plugin_dir_path(__FILE__) . 'hooks/wp-hooks.php';
require_once plugin_dir_path(__FILE__) . 'hooks/wc-hooks.php';
require_once plugin_dir_path(__FILE__) . 'activity-tracker-data-table.php';
require_once plugin_dir_path(__FILE__) . 'settings.php';

// Create an admin menu item for the logs page
function at_add_activity_logs_page()
{
    $allowed_users = get_option('at_allowed_users', array());
    $default_allowed_user = get_option('at_default_allowed_user', 0);
    if (is_array($allowed_users) && count($allowed_users) > 0) {
        $user_id = get_current_user_id();
        if (!in_array($user_id, $allowed_users) && $user_id !== $default_allowed_user) {
            return;
        }
    }

    add_menu_page('Activity Logs', 'Activity Logs', 'manage_options', 'activity-logs', 'at_display_activity_logs', 'dashicons-list-view',100);
    add_submenu_page('activity-logs', 'All Activity Logs', 'All Activity Logs', 'manage_options', 'activity-logs', 'at_display_activity_logs', 'dashicons-list-view',100);
    // Check if password and email options are set
    $password = get_option('at_password');
    $email = get_option('at_notification_email');
    
        add_submenu_page('activity-logs', 'Activity Tracker Settings', 'Settings', 'manage_options', 'at_settings', 'at_settings_page');
   
}

add_action('admin_menu', 'at_add_activity_logs_page');


// Prevent deactivation without a password
function at_prevent_deactivation($actions, $plugin_file, $plugin_data, $context)
{
    if (strpos($plugin_file, 'activity-tracker.php') !== false) {
        $password = get_option('at_password');
        $deactivation_url = $actions['deactivate'];

        $deactivation_nonce = wp_create_nonce('deactivate-plugin_' . $plugin_file);
        $actions['deactivate'] = '<a href="#" onclick="promptDeactivation(\'' . esc_js($deactivation_nonce) . '\', \'' . esc_js($plugin_file) . '\')">Deactivate</a>';

        echo '<script>
            function promptDeactivation(deactivationNonce, pluginFile) {
                var password = prompt("Please enter the password to deactivate the plugin:");
                if (password === "' . esc_js($password) . '") {
                    var url = "' . esc_url(admin_url('plugins.php')) . '?action=deactivate&plugin=" + encodeURIComponent(pluginFile) + "&_wpnonce=" + deactivationNonce;
                    window.location.href = url;
                } else {
                    alert("Incorrect password.");
                }
            }
        </script>';
    }

    return $actions;
}

add_filter('plugin_action_links', 'at_prevent_deactivation', 10, 4);

register_activation_hook(__FILE__, 'at_create_logs_table');
register_activation_hook(__FILE__, 'at_send_notification_email');

function at_send_notification_email($action)
{
    $email = get_option('at_notification_email');
    if ($email) {
        $subject = 'Activity Tracker Notification';
        $message = "Activity Tracker plugin: {$action}";
        wp_mail($email, $subject, $message);
    }
}

function at_deactivate_plugin()
{
    at_send_notification_email('Plugin deactivated');
}
register_deactivation_hook(__FILE__, 'at_deactivate_plugin');

function at_drop_activity_logs_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'at_activity_logs';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    at_send_notification_email('Activity logs table deleted');
}

function at_user_can_view_logs()
{
    $current_user = wp_get_current_user();
    $allowed_users = get_option('at_allowed_users', array());
    $default_allowed_user = get_option('at_default_allowed_user', 0);

    if (!is_array($allowed_users)) {
        $allowed_users = array();
    }

    if (in_array($current_user->ID, $allowed_users) || $current_user->ID == $default_allowed_user) {
        return true;
    }

    return false;
}

function at_send_test_email()
{
    $email_content = isset($_POST['email_content']) ? $_POST['email_content'] : '';
    // update_option('at_deactivation_email_content', $email_content); 

    $email = wp_get_current_user()->user_email;

    if (wp_mail($email, 'Test Deactivation Email', $email_content)) {
        echo 'Test email sent successfully.';
    } else {
        echo 'Failed to send test email.';
    }

    wp_die();
}
add_action('wp_ajax_at_send_test_email', 'at_send_test_email');

function at_send_deactivation_email()
{
    $email = get_option('at_notification_email');
    $email_content = get_option('at_deactivation_email_content', '');

    if (!empty($email)) {
        wp_mail($email, 'Activity Tracker Deactivation', $email_content);
    }
}
