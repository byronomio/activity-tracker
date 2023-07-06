<?php

function at_enqueue_data_table_assets() {
    wp_register_style('datatables-css', 'https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css', array(), '1.11.3');
    wp_register_script('datatables-js', 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js', array('jquery'), '1.11.3', true);

    wp_enqueue_style('datatables-css');
    wp_enqueue_script('datatables-js');
}
add_action('admin_enqueue_scripts', 'at_enqueue_data_table_assets');

function at_display_activity_logs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'at_activity_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY action_time DESC", ARRAY_A);
    ?>
    <div class="wrap">
        <h1>Activity Logs</h1>
        <table id="activity-logs-table" class="display">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>User Role</th>
                    <th>Action</th>                   
                    <th>Item</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log) : ?>
                    <tr>
                        <td><?php echo $log['user_id']; ?></td>
                        <td><?php echo $log['user_login']; ?></td>
                        <td><?php echo $log['user_role']; ?></td>
                        <td><?php echo $log['action_type']; ?></td>
                        <td><?php echo $log['item_title']; ?></td>
                        <td><?php echo $log['action_time']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        jQuery(document).ready(function() {
            jQuery('#activity-logs-table').DataTable();
        });
    </script>
    <?php
}
