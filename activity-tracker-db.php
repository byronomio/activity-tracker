<?php

function at_create_logs_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'at_activity_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        user_role varchar(255) NOT NULL,
        user_login varchar(255) NOT NULL,
        action_type varchar(255) NOT NULL,
        item_id bigint(20) UNSIGNED NULL DEFAULT NULL,
        item_title varchar(255) NULL DEFAULT NULL,
        action_time datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(plugin_dir_path(__DIR__) . 'activity-tracker.php', 'at_create_logs_table');