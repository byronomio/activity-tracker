<?php

function at_log_action($user_id, $user_role, $user_login, $action_type, $item_id = null, $item_title = null)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'at_activity_logs';

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'user_role' => $user_role,
            'user_login' => $user_login,
            'action_type' => $action_type,
            'item_id' => $item_id,
            'item_title' => $item_title,
            'action_time' => current_time('mysql')
        )
    );

    // Check if file logging is enabled
    $file_logging_enabled = get_option('at_file_logging_enabled');
    if ($file_logging_enabled) {
        $file_format = get_option('at_file_format');
        at_write_to_file(array(
            'user_id' => $user_id,
            'user_name' => $user_login,
            'action' => $action_type,
            'object_type' => '',
            'object_name' => $item_title,
            'time' => current_time('mysql')
        ), $file_format);
    }
}

function at_track_actions($action_type, $item_id = null, $item_title = null)
{
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_role = implode(', ', $current_user->roles);
    $user_login = $current_user->user_login;

    if (user_can($current_user, 'manage_options')) {
        at_log_action($user_id, $user_role, $user_login, $action_type, $item_id, $item_title);
    }
}

function at_write_to_file($data, $file_format)
{
    $log_dir = WP_CONTENT_DIR . '/secure-activity-tracker/';
    if (!file_exists($log_dir)) {
        if (!mkdir($log_dir, 0755, true)) {
            error_log('Activity Tracker: Failed to create directory: ' . $log_dir);
            return;
        }
    }
    $file_name = $log_dir . 'secure-activity-tracker.' . $file_format;

    if ($file_format == 'csv') {
        $headers = array('User ID', 'User Name', 'Action', 'Object Type', 'Object Name', 'Time');
        $csv_data = array($data['user_id'], $data['user_name'], $data['action'], $data['object_type'], $data['object_name'], $data['time']);

        if (!file_exists($file_name)) {
            $file = fopen($file_name, 'w');
            if (!$file) {
                error_log('Activity Tracker: Failed to open file: ' . $file_name);
                return;
            }
            fputcsv($file, $headers);
            fclose($file);
        }

        $file = fopen($file_name, 'a');
        if (!$file) {
            error_log('Activity Tracker: Failed to open file: ' . $file_name);
            return;
        }
        fputcsv($file, $csv_data);
        fclose($file);
    } else {
        $txt_data = sprintf(
            "[%s] User ID: %d | User Name: %s | Action: %s | Object Type: %s | Object Name: %s\n",
            $data['time'],
            $data['user_id'],
            $data['user_name'],
            $data['action'],
            $data['object_type'],
            $data['object_name']
        );

        if (file_put_contents($file_name, $txt_data, FILE_APPEND) === false) {
            error_log('Activity Tracker: Failed to write to file: ' . $file_name);
        }
    }
}
