<?php


add_action('delete_user', 'delete_user_related_form_entries_from_admin');
function delete_user_related_form_entries_from_admin($user_id)
{
    global $wpdb;

    // Get the user's email
    $user = get_userdata($user_id);
    if (!$user)
        return;

    $user_email = $user->user_email;

    // Table names
    $entry_table = $wpdb->prefix . 'frmt_form_entry';
    $meta_table = $wpdb->prefix . 'frmt_form_entry_meta';

    // Step 1: Find all entry_ids in wp_frmt_form_entry_meta for this email
    $entry_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT entry_id FROM $meta_table WHERE meta_key = %s AND meta_value = %s",
            'email-1',
            $user_email
        )
    );

    // Step 2: If entries found, delete them
    if (!empty($entry_ids)) {
        // Prepare placeholders dynamically for SQL
        $placeholders = implode(',', array_fill(0, count($entry_ids), '%d'));

        // Delete from meta table first
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $meta_table WHERE entry_id IN ($placeholders)",
                ...$entry_ids
            )
        );

        // Delete from main entry table
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $entry_table WHERE id IN ($placeholders)",
                ...$entry_ids
            )
        );
    }
}
