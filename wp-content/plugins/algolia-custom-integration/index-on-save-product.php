<?php

require_once __DIR__ . '/utils.php';

function save_to_index($post_id, $post, $blog_id = null)
{
    if (is_multisite() && !ms_is_switched()) {

        // Switch to blog if argument is provided
        if($blog_id) switch_to_blog( $blog_id );
        error_log('called save_to_index status=' . $post->post_status . ' blog id: ' . $blog_id);

        // Init Algolia
        $options = get_option('algolia_custom_integration_plugin_options');
        if (empty($options)) {
            add_flash_notice(__("Algolia appId and apyKey not configured. "), "error", true);
            return $post;
        }
        $algolia = Algolia::instance($options);

        $siteLocale = $locale = get_option('WPLANG');
        $post_type = $post->post_type;
        $post_status = $post->post_status;

        $searchable_post_types = get_post_types(
            array(
                'public' => true,
                'exclude_from_search' => false
            )
        );

        if (in_array($post_type, $searchable_post_types)) {
            // Only reindex posts that have been published
            $is_invalid_status = $post_status != 'publish'; // && $post_status != 'trash';

            if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || $is_invalid_status) {
                delete_from_index($post_id);
                return $post;
            }

            $index = $algolia->getIndex($siteLocale, AlgoliaIndex::products);
            $record = array();

            /*
            acf_setup_meta load the current ACF fields and let me use the get_field function.
            This is necessary because here, in the on_save_post , current acf fields's post hasn't been updated into DB
            */
            if( isset( $_POST['acf'] ) ) acf_setup_meta($_POST['acf'], 'my_data', true);

            // Register cpts again, for some reason are not reachable
            cptui_register_my_cpts();

            $record['objectID'] = base64_encode("post:" . $post_id);
            $record['uri'] = updateFrontedDomain($siteLocale, get_permalink($post_id), $blog_id);
            $record['title'] = get_the_title($post_id);
            $record['menuOrder'] = get_post_field('menu_order', $post_id);
            $record['badge'] = get_field('badge', $post_id);
            $record['configuratorLink'] = get_field('configurator_link', $post_id);
            $record['subTitle'] = get_field('sub_title', $post_id);
            $record['mainFeatures'] = get_field('main_features', $post_id);
            $record['node'] = array(
                'mediaItemUrl' => get_the_post_thumbnail_url($post_id)
            );
            $record['algoliaExluded'] = get_field('algolia_exluded');

            if( isset( $_POST['acf'] ) ) acf_reset_meta('my_data');

            if ($post_status == 'publish') {
                try {
                    $index->saveObject($record);
                } catch (\Exception $e) {
                    error_log('Error saving to algolia index ' . $e->getMessage());
                    add_flash_notice(__("Error saving to algolia index. " . $e->getMessage()), "error", true);
                }
            }
        }

        if($blog_id) restore_current_blog();
    }

    return $post;
}


function my_top_bar_message_function()
{
    #    if (!isset($_GET['notice_key'])) {
    #        return;
    #    }
    echo '<div class="notice notice-success is-dismissible">
        <p>Your post has been saved successfully.</p>
    </div>';
}


function delete_from_index($postId)
{
    error_log('called delete_from_index ');
    $options = get_option('algolia_custom_integration_plugin_options');
    if (empty($options)) {
        return;
    }

    $siteLocale = get_locale();

    if (is_multisite() && !ms_is_switched()) {
        try {
            $algolia = Algolia::instance($options);
            $index = $algolia->getIndex($siteLocale, AlgoliaIndex::products);
            $objectID = base64_encode("post:" . $postId);
            error_log('deleting ' . $objectID);
            $index->deleteObject($objectID);
        } catch (\Exception $e) {
            error_log('Error deleting to algolia index ' . $e->getMessage());
            add_flash_notice(__("Error saving to algolia index. " . $e->getMessage()), "error", true);
        }
    }
}

// add_action('save_post_product', 'save_to_index', 15, 3);
// add_action('save_post_product',function($post_id, $post, $update){
//   // error_log('ACTION --- save_post_product');
//   save_to_index($post_id, $post, null);
// }, 15, 3);

// With save_post_product acf fields value is not yet saved
add_action('acf/save_post', function($post_id) {
    // At this point, all ACF fields have been saved.
    $post = get_post($post_id);
    // Make sure to check for your specific post type if necessary
    if ($post->post_type === 'product') {
        // Now you can safely access and use the updated ACF fields
        save_to_index($post_id, $post, null);
    }
}, 20);

add_action('mgs_product_synched', function($post_id, $post, $blog_id){
  // error_log('ACTION --- mgs_product_synched');
  save_to_index($post_id, $post, $blog_id);
}, 999, 3);

add_action('delete_post_product', 'delete_from_index', 15);

add_action('wp_trash_product', 'delete_from_index', 15);
