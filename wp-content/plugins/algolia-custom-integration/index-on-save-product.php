<?php

require_once __DIR__ . '/utils.php';

function save_to_index($post_id, $post, $update)
{
    error_log('called save_to_index status=' . $post->post_status);
    $options = get_option('algolia_custom_integration_plugin_options');
    if (empty($options)) {
        add_flash_notice(__("Algolia appId and apyKey not configured. "), "error", true);
        return $post;
    }

    $siteLocale = get_locale();

    if (is_multisite() && !ms_is_switched()) {
        $algolia = Algolia::instance($options);

        error_log(print_r('post:' . $post_id, true));
        error_log(print_r('siteLocale:' . $siteLocale, true));
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
            acf_setup_meta($_POST['acf'], 'my_data', true);

            $record['objectID'] = base64_encode("post:" . $post_id);
            $record['uri'] = updateFrontedDomain($siteLocale, get_permalink($post_id));
            $record['title'] = get_the_title($post_id);
            $record['menuOrder'] = get_post_field('menu_order', $post_id);
            $record['badge'] = get_field('badge');
            $record['configuratorLink'] = get_field('configurator_link');
            $record['subTitle'] = get_field('sub_title');
            $record['mainFeatures'] = get_field('main_features');
            $record['node'] = array(
                'mediaItemUrl' => get_the_post_thumbnail_url($post_id)
            );
            $record['algoliaExluded'] = get_field('algolia_exluded');

            acf_reset_meta('my_data');

            if ($post_status == 'publish') {
                try {
                    $index->saveObject($record);
                } catch (\Exception $e) {
                    error_log('Error saving to algolia index ' . $e->getMessage());
                    add_flash_notice(__("Error saving to algolia index. " . $e->getMessage()), "error", true);
                }
            }
        }
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

add_action('save_post_product', 'save_to_index', 15, 3);

add_action('delete_post_product', 'delete_from_index', 15);

add_action('wp_trash_product', 'delete_from_index', 15);
