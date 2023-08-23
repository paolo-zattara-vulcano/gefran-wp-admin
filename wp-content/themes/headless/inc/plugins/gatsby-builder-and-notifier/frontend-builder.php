<?php
// Include the settings tab functionality
require_once(__DIR__ . '/frontend-builder-settings-tab.php');

// Add action to handle the "Frontend Builder" admin page
add_action('admin_menu', 'register_frontend_builder_page');
function register_frontend_builder_page() {
    add_menu_page(
        'Frontend Builder',
        'Frontend Builder',
        'manage_options',
        'frontend_builder',
        'display_frontend_builder_page'
    );
}

function display_frontend_builder_page() {
    ?>
    <div class="wrap">
        <h1>Frontend Builder</h1>
        <?php
        // Check if a specific tab is requested, default to 'build' tab
        $active_tab = isset($_GET['action']) && $_GET['action'] === 'settings' ? 'settings' : 'build';

        // Display tabs
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=frontend_builder&amp;action=build" class="nav-tab ' . ($active_tab === 'build' ? 'nav-tab-active' : '') . '">Build</a>';
        echo '<a href="?page=frontend_builder&amp;action=settings" class="nav-tab ' . ($active_tab === 'settings' ? 'nav-tab-active' : '') . '">Settings</a>';
        echo '</h2>';

        // Display tab content
        if ($active_tab === 'build') {
            // Include the build tab content
            require_once(__DIR__ . '/frontend-builder-build-tab.php');
        } elseif ($active_tab === 'settings') {
            display_and_save_webhooks(); // Display the settings tab content
        }
        ?>
    </div>
    <?php
}
