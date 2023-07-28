<?php

require_once __DIR__ . '/utils.php';

function save_related_posts_on_product_category_save($term_id, $tt_id)
{
    error_log('called edited_product_category');
    $options = get_option('algolia_custom_integration_plugin_options');
    if (empty($options)) {
        add_flash_notice(__("Algolia appId and apyKey not configured. "), "error", true);
        return;
    }

    $siteLocale = get_locale();

    if (is_multisite() && !ms_is_switched()) {
        $algolia = Algolia::instance($options);
        // Get all the posts that belong to this category
        $posts = get_posts(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_category',
                    'field' => 'term_id',
                    'terms' => $term_id,
                ),
            ),
            'fields' => 'ids',
        ));

        $index = $algolia->getIndex($siteLocale, AlgoliaIndex::products);

        // Loop through the posts and update the meta field
        foreach ($posts as $post_id) {
            reindex($post_id, $index, $siteLocale);
        }
    }
}

function reindex($post_id, $index, $siteLocale)
{
    error_log('Reindexing postID:' . $post_id);
    $record = array();
    $record['objectID'] = base64_encode("post:" . $post_id);
    $record['uri'] = updateFrontedDomain($siteLocale, get_permalink($post_id));
    $record['title'] = get_the_title($post_id);
    $record['menuOrder'] = get_post_field('menu_order', $post_id);
    $record['badge'] = get_field('badge', $post_id);
    $record['configuratorLink'] = get_field('configurator_link', $post_id);
    $record['subTitle'] = get_field('sub_title', $post_id);
    $record['mainFeatures'] = get_field('main_features', $post_id);
    $record['node'] = array(
        'mediaItemUrl' => get_the_post_thumbnail_url($post_id)
    );
    $record['algoliaExluded'] = get_field('algolia_exluded', $post_id);

    try {
        $index->saveObject($record);
    } catch (\Exception $e) {
        error_log('Error saving to algolia index ' . $e->getMessage());
        add_flash_notice(__("Error saving to algolia index. " . $e->getMessage()), "error", true);
    }
}

add_action('edited_product_category', 'save_related_posts_on_product_category_save', 10, 2);
