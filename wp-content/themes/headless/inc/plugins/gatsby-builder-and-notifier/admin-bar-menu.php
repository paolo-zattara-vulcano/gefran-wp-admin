<?php
// GATSBY MENU
add_action('admin_bar_menu', 'gatsby_admin_bar_menu', 100);
function gatsby_admin_bar_menu($wp_admin_bar) {
    $favicon_url = 'https://gefran.kinsta.cloud/wp-content/themes/headless/dist/images/favicon/gefran-favicon.svg';

    $args = array(
        'id'    => 'gatsby_amb',
        'title' => '<img src="' . $favicon_url . '" style="width: 18px; height: 18px; vertical-align: middle; margin: 0 3px 3px 0; filter: brightness(0) saturate(100%) invert(38%) sepia(84%) saturate(2517%) hue-rotate(245deg) brightness(99%) contrast(86%)"> Gatsby Menu',
        'meta'  => array('class' => 'switch-area-dropdown')
    );
    $wp_admin_bar->add_node($args);

    // POINT ALWAYS TO MAIN BLOG OPTION PAGE
    // Get the admin URL of the blog with ID 1
    $blog1_admin_url = get_admin_url(1);

    // Add the "Builds monitor" link in the admin bar menu
    $builds_monitor_link = array(
        'id'     => 'gatsby_link_monitor',
        'title'  => 'Builds monitor',
        'href'   => $blog1_admin_url . 'admin.php?page=gatsby_notifications',
        'parent' => 'gatsby_amb',
        'meta'   => array('class' => 'switch-area-link1'),
        'target' => '_blank', // Add this line to open the link in a new tab
    );
    $wp_admin_bar->add_node($builds_monitor_link);

    // Add the "Frontend Builder" link in the admin bar menu
    $frontend_builder_link = array(
        'id'     => 'frontend_builder_link',
        'title'  => 'Frontend Builder',
        'href'   => $blog1_admin_url . 'admin.php?page=frontend_builder',
        'parent' => 'gatsby_amb',
        'meta'   => array('class' => 'switch-area-link2'),
        'target' => '_blank', // Add this line to open the link in a new tab
    );
    $wp_admin_bar->add_node($frontend_builder_link);
}


// Add action to modify the admin bar
add_action('wp_before_admin_bar_render', 'customize_gatsby_admin_bar');
function customize_gatsby_admin_bar() {
    global $wp_admin_bar;

    // Get the node corresponding to the "Builds monitor" link
    $node = $wp_admin_bar->get_node('gatsby_link_monitor');

    // If the node exists, modify its target to '_blank'
    if ($node) {
        $node->meta['target'] = '_blank';
        $wp_admin_bar->add_node($node);
    }
}
