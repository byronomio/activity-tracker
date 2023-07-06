<?php
// Hooks for WordPress core actions

function at_hook_into_wp_actions() {
    // Track post-related actions
    add_action('save_post', function ($post_id, $post) {
        if ($post->post_status === 'publish') {
            at_track_actions('post_saved');
        }
    }, 10, 2);

    add_action('transition_post_status', function ($new_status, $old_status, $post) {
        at_track_actions('post_status_transition', $post->ID, "Status changed from $old_status to $new_status");
    }, 10, 3);

    add_action('deleted_post', function ($post_id) {
        at_track_actions('post_permanently_deleted', $post_id);
    });

    add_action('wp_trash_post', function ($post_id) {
        at_track_actions('post_sent_to_trash', $post_id);
    });

    add_action('untrashed_post', function ($post_id) {
        at_track_actions('post_restored_from_trash', $post_id);
    });

    // Category/Taxonomy-related actions
    add_action('created_category', function ($term_id, $tt_id) {
        at_track_actions('category_created', $term_id);
    });

    add_action('edited_category', function ($term_id, $tt_id) {
        at_track_actions('category_edited', $term_id);
    });

    add_action('delete_category', function ($term_id, $tt_id, $deleted_term) {
        at_track_actions('category_deleted', $term_id);
    });

    // User-related actions
    add_action('user_register', function () {
        at_track_actions('user_registered');
    });

    add_action('delete_user', function () {
        at_track_actions('user_deleted');
    });

    add_action('profile_update', function () {
        at_track_actions('profile_updated');
    });

    add_action('password_reset', function ($user, $new_pass) {
        at_track_actions('user_password_reset', $user->ID);
    }, 10, 2);

    add_action('wp_login_failed', function ($username) {
        at_track_actions('user_login_failed', null, $username);
    });

    // Media-related actions
    add_action('add_attachment', function ($post_ID) {
        at_track_actions('attachment_added', $post_ID);
    });

    add_action('edit_attachment', function ($post_ID) {
        at_track_actions('attachment_updated', $post_ID);
    });

    add_action('delete_attachment', function ($post_ID) {
        at_track_actions('attachment_deleted', $post_ID);
    });

    // Comment-related actions
    add_action('comment_post', function ($comment_ID, $comment_approved, $commentdata) {
        at_track_actions('comment_created', $comment_ID, "Comment status: $comment_approved");
    }, 10, 3);

    add_action('edit_comment', function ($comment_ID) {
        at_track_actions('comment_updated', $comment_ID);
    });

    add_action('trash_comment', function ($comment_ID) {
        at_track_actions('comment_trashed', $comment_ID);
    });

    add_action('untrash_comment', function ($comment_ID) {
        at_track_actions('comment_untrashed', $comment_ID);
    });

    add_action('deleted_comment', function ($comment_ID) {
        at_track_actions('comment_deleted', $comment_ID);
    });

    add_action('wp_set_comment_status', function ($comment_ID, $comment_status) {
        at_track_actions('comment_status_changed', $comment_ID, "Status: $comment_status");
    }, 10, 2);

    add_action('upgrader_process_complete', function ($upgrader, $hook_extra) {
        if (isset($hook_extra['type']) && $hook_extra['type'] === 'plugin') {
            at_track_actions('plugin_updated');
        }
    }, 10, 2);

    // Plugin-related actions
    add_action('activated_plugin', function ($plugin, $network_wide) {
        at_track_actions('plugin_activated', null, $plugin);
    }, 10, 2);

    add_action('deactivated_plugin', function ($plugin, $network_wide) {
        at_track_actions('plugin_deactivated', null, $plugin);
    }, 10, 2);

    // Theme-related actions
    add_action('after_switch_theme', function () {
        at_track_actions('theme_switched');
    });
}

add_action('init', 'at_hook_into_wp_actions');
