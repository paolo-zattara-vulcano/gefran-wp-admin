<?php
/*
Plugin Name: Gefran Custom Post Order
Description: A plugin to handle the order of posts and custom post types, allowing rearrangement by drag and drop in the admin post list, while keeping the original order displayed in the admin product list when activated.
Version: 1.2
Author: Paolo Zattara
License: GPLv2 or later
Text Domain: custom-post-order
*/

// Prevent direct access to the file.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Edit_Products_Order')) {

  class Edit_Products_Order {



      public function __construct() {
          add_action('wp_ajax_update_menu_order', array($this, 'update_menu_order'));
          add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
      }

      public function enqueue_admin_scripts() {

          $screen = get_current_screen();
          if ('edit' === $screen->base && 'product' === $screen->post_type) {
            error_log('Edit_Products_Order: enqueue_admin_scripts');

              wp_enqueue_script('custom-post-order-admin-js', get_stylesheet_directory_uri() . '/inc/plugins/multisite-global-sync/assets/js/edit.products.order.js', array('jquery', 'jquery-ui-sortable'), '1.0', true);
              wp_localize_script('custom-post-order-admin-js', 'customPostOrder', array(
                  'ajax_url' => admin_url('admin-ajax.php'),
                  'nonce'    => wp_create_nonce('custom-post-order-nonce'),
                  'orderby'  => isset($_GET['orderby']) ? $_GET['orderby'] : 'menu_order',
                  'order'    => isset($_GET['order']) ? $_GET['order'] : 'ASC',
              ));
          }
      }




      public function update_menu_order() {
          check_ajax_referer('custom-post-order-nonce', 'security');

          if (!current_user_can('edit_posts')) {
              wp_send_json_error('You do not have permission to edit posts.');
          }

          $order = isset($_POST['order']) ? (array) $_POST['order'] : array();

          // Retrieve all products and their menu_order values
          $all_products = get_posts(array(
              'post_type' => 'product',
              'posts_per_page' => -1,
              'fields' => 'ids',
              'orderby' => 'menu_order',
              'order' => 'ASC',
          ));

          // Create a dictionary with product ID as key and menu_order as value
          $menu_order_dict = array_combine($all_products, range(0, count($all_products) - 1));

          // Prepare the updated menu_order values for changed products
          $menu_order_updates = array();
          foreach ($order as $item) {
              $post_id = intval($item['id']);
              $position = intval($item['new_position']);

              if (isset($menu_order_dict[$post_id])) {
                  $menu_order_updates[$post_id] = $position;
              }
          }

          // Update the menu_order values in the database for changed products using a single query
          if (!empty($menu_order_updates)) {
              global $wpdb;
              $case_sql = '';
              $post_ids = array();

              foreach ($menu_order_updates as $post_id => $menu_order) {
                  $post_ids[] = $post_id;
                  $case_sql .= "WHEN " . $post_id . " THEN " . $menu_order . " ";
              }

              $post_ids = implode(', ', $post_ids);

              $wpdb->query("UPDATE {$wpdb->posts} SET menu_order = CASE ID {$case_sql}END WHERE ID IN ({$post_ids})");
          }

          wp_send_json_success('Menu order updated.');
      }

  }
}
