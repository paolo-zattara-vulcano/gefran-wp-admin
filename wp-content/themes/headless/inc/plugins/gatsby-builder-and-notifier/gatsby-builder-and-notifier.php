<?php
// Include the admin bar menu functionality
require_once(__DIR__ . '/admin-bar-menu.php');

// Include the notifications functionality
require_once(__DIR__ . '/notifications.php');

// Include the frontend builder functionality
require_once(__DIR__ . '/frontend-builder.php');

// Remove Gatsby Notifications and Frontend Builder from admin sidebar
add_action('admin_menu', 'remove_gatsby_and_frontend_links');
function remove_gatsby_and_frontend_links() {
    remove_menu_page('gatsby_notifications');
    remove_menu_page('frontend_builder');
}
