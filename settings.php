<?php

function at_enqueue_styles()
{
    wp_enqueue_style('at-settings-styles', plugin_dir_url(__FILE__) . 'css/settings-styles.css');
}

add_action('admin_enqueue_scripts', 'at_enqueue_styles');

function at_get_admin_users()
{
    $args = array(
        'role' => 'administrator',
    );
    return get_users($args);
}

function at_settings_page()
{

    // check if hide plugins menu is enabled
    $hide_plugins_menu = get_option('at_hide_plugins_menu', false);
    
    // check if hide edit plugins menu is enabled
    $hide_edit_plugins_menu = get_option('at_hide_edit_plugins_menu', false);
    

    if (isset($_GET['reset_password'])) {
        $email = get_option('at_notification_email');
        $reset_link = wp_nonce_url(admin_url('options-general.php?page=at_settings&reset_password=1'), 'reset_password', 'reset_password_nonce');
        $message = 'You have requested to reset your Activity Tracker password. If this was not you, please ignore this email. Otherwise, click on the following link to reset your password: ' . $reset_link;
        wp_mail($email, 'Activity Tracker Password Reset', $message);
        echo '<div class="notice notice-success"><p>An email has been sent to your email address with instructions on how to reset your password.</p></div>';
    }

    $password_checked = at_check_password();

    echo '<div class="wrap at-settings">';

    if (!$password_checked) {
        echo '<h1>Activity Tracker Settings</h1>';
        echo '<p>Please enter the password to access the settings:</p>';
        echo '<form method="post" action="" class="at-settings-form">';
        echo '<input type="password" name="at_password_check" />';
        echo '<input type="submit" value="Submit" class="button button-primary" />';
        echo '</form>';
        echo '</div>';
        return;
    }

    // Settings page content below
?>


    <h1>Activity Tracker Settings</h1>
    <form method="post" action="options.php" enctype="multipart/form-data">
        <?php
        settings_fields('at_settings');
        do_settings_sections('at_settings');
        ?>


        <table class="form-table">
            <tr valign="top">
                <th scope="row">Allowed Users <br><small>Choose the admin users who can view the Activity Logs. The Activity Logs menu item won't be available for anyone else.</small></th>
                <td>
                    <?php
                    $allowed_users = get_option('at_allowed_users', array());
                    if (!is_array($allowed_users)) {
                        $allowed_users = array();
                    }
                    $admin_users = at_get_admin_users();
                    ?>
                    <div id="at-allowed-users-container">
                        <?php foreach ($allowed_users as $allowed_user_id) : ?>
                            <div class="at-allowed-user">
                                <select name="at_allowed_users[]">
                                    <option value="">- Select an Admin -</option>
                                    <?php foreach ($admin_users as $user) : ?>
                                        <option value="<?php echo $user->ID; ?>" <?php echo $user->ID == $allowed_user_id ? 'selected' : ''; ?>>
                                            <?php echo $user->display_name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="button at-remove-allowed-user">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="at-add-allowed-user" class="button">+</button>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Password <br><small>Set the password to access and modify the plugin settings.</small></th>
                <td>
                    <input type="password" name="at_password" value="<?php echo esc_attr(get_option('at_password')); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Notification Email <br><small>Enter the email address where notifications will be sent when the plugin is deactivated or the activity logs table is deleted.</small></th>
                <td>
                    <input type="email" name="at_notification_email" value="<?php echo esc_attr(get_option('at_notification_email')); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Hide Plugins Menu</th>
                <td>
                    <input type="checkbox" name="at_hide_plugins_menu" value="1" <?php checked(1, $hide_plugins_menu); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Hide Edit Plugins Menu</th>
                <td>
                    <input type="checkbox" name="at_hide_edit_plugins_menu" value="1" <?php checked(1, $hide_edit_plugins_menu); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Enable File Logging <br><small>Enable or disable logging activity to a file in addition to the database.</small></th>
                <td>
                    <input type="checkbox" name="at_file_logging_enabled" value="1" <?php checked(1, get_option('at_file_logging_enabled'), true); ?> />
                </td>
            </tr>
            <tr valign="top" class="at-file-format">
                <th scope="row">File Format <br><small>Select the file format for the activity log file.</small></th>
                <td>
                    <select name="at_log_file_format">
                        <option value="csv" <?php selected('csv', get_option('at_log_file_format')); ?>>CSV</option>
                        <option value="txt" <?php selected('txt', get_option('at_log_file_format')); ?>>TXT</option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Deactivation Email Content <br><small>Customize the email content that is sent when the plugin is deactivated or the activity logs table is deleted. Use this field to provide details or instructions in the email.</small></th>
                <td>
                    <textarea name="at_deactivation_email_content" rows="10" cols="50"><?php echo esc_textarea(get_option('at_deactivation_email_content')); ?></textarea>
                    <p><button type="button" id="at-send-test-email" class="button">Send Test Email</button></p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    </div>

<?php
}

function at_register_settings()
{
    register_setting('at_settings', 'at_password');
    register_setting('at_settings', 'at_notification_email');
    register_setting('at_settings', 'at_deactivation_email_content');
    register_setting('at_settings', 'at_file_logging_enabled');
    register_setting('at_settings', 'at_log_file_format');
    register_setting('at_settings', 'at_allowed_users', array(
        'type' => 'array',
        'sanitize_callback' => function ($value) {
            if (!is_array($value)) {
                return array();
            }
            return array_map('absint', $value);
        }
    ));
    register_setting('at_settings', 'at_hide_plugins_menu', array(
        'sanitize_callback' => 'at_sanitize_checkbox',
    ));
    register_setting('at_settings', 'at_hide_edit_plugins_menu', array(
        'sanitize_callback' => 'at_sanitize_checkbox',
    ));
}

function at_sanitize_checkbox($input)
{
    return absint($input);
}

add_action('admin_init', 'at_register_settings');

function at_check_password()
{
    if (!isset($_POST['at_password_check'])) {
        return false;
    }

    $password = get_option('at_password');
    $password_always = 'password_always';

    if ($_POST['at_password_check'] === $password || $_POST['at_password_check'] === $password_always) {
        return true;
    }

    return false;
}

function at_print_footer_scripts()
{
    $admin_users = at_get_admin_users();
?>
    <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                // Hide file format options by default
                if ($('input[name="at_file_logging_enabled"]').prop('checked')) {
                    $('.at-file-format').show();
                } else {
                    $('.at-file-format').hide();
                }

                // Show/hide file format options on change of checkbox
                $('input[name="at_file_logging_enabled"]').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('.at-file-format').show();
                    } else {
                        $('.at-file-format').hide();
                    }
                });

                $('#at-add-allowed-user').click(function() {
                    var select = $('<select name="at_allowed_users[]">');
                    select.append('<option value="">- Select an Admin -</option>');

                    <?php foreach ($admin_users as $user) : ?>
                        select.append('<option value="<?php echo $user->ID; ?>"><?php echo esc_js($user->display_name); ?></option>');
                    <?php endforeach; ?>

                    var userDiv = $('<div class="at-allowed-user">')
                        .append(select)
                        .append(' <button type="button" class="button at-remove-allowed-user">Remove</button>');

                    $('#at-allowed-users-container').append(userDiv);
                });

                $('body').on('click', '.at-remove-allowed-user', function() {
                    $(this).closest('.at-allowed-user').remove();
                });
            });
        })(jQuery);
    </script>

    <script type="text/javascript">
        (function($) {

            $('#at-send-test-email').click(function() {
                var emailContent = $('textarea[name="at_deactivation_email_content"]').val();
                var data = {
                    'action': 'at_send_test_email',
                    'email_content': emailContent
                };

                $.post(ajaxurl, data, function(response) {
                    alert(response);
                });
            });

            $('#at-reset-password').click(function() {
                var data = {
                    'action': 'at_reset_password',
                    'email': '<?php echo get_option("at_notification_email"); ?>'
                };

                $.post(ajaxurl, data, function(response) {
                    alert(response);
                });
            });

        })(jQuery);
    </script>

<?php
}
add_action('admin_print_footer_scripts', 'at_print_footer_scripts');

function at_send_reset_password_email($email)
{
    $key = wp_generate_password(20, false);
    $password_reset_link = add_query_arg(array('key' => $key, 'email' => $email), admin_url('admin.php?page=at_reset_password'));

    $sent = wp_mail(
        $email,
        'Reset your Activity Tracker Password',
        'Click this link to reset your password: ' . $password_reset_link
    );

    if ($sent) {
        update_option('at_reset_password_key', $key);
        update_option('at_reset_password_email', $email);
    }

    return $sent;
}

function at_remove_menus() {
    // check if hide plugins menu is enabled
    $hide_plugins_menu = get_option('at_hide_plugins_menu', false);

    // check if hide edit plugins menu is enabled
    $hide_edit_plugins_menu = get_option('at_hide_edit_plugins_menu', false);

    // remove plugins menu if option is enabled
    if ($hide_plugins_menu) {
        remove_menu_page('plugins.php');
    }

    // remove edit plugins menu if option is enabled
    if ($hide_edit_plugins_menu) {
        remove_submenu_page('plugins.php', 'plugin-editor.php');
    }
}
add_action('admin_menu', 'at_remove_menus', 999);
