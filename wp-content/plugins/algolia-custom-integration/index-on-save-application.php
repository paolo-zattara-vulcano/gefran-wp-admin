<?php

require_once __DIR__ . '/utils.php';

function save_application_to_index($post_id, $post, $update)
{
    error_log('called save_application_to_index status=' . $post->post_status);
    $options = get_option('algolia_custom_integration_plugin_options');
    if (empty($options)) {
        add_flash_notice(__("Algolia appId and apyKey not configured. "), "error", true);
        return $post;
    }

    // Get the page template name
    $page_template_name = get_post_meta($post_id, '_wp_page_template', true);

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
            if (str_contains($page_template_name, 'application-ch.php')) {
                $index = $algolia->getIndex($siteLocale, AlgoliaIndex::case);
            } else {
                $index = $algolia->getIndex($siteLocale, AlgoliaIndex::applications);
            }
            $record = array();

            /*
            acf_setup_meta load the current ACF fields and let me use the get_field function.
            This is necessary because here, in the on_save_post , current acf fields's post hasn't been updated into DB
            */

            acf_setup_meta($_POST['acf'], 'my_data', true);

            $record['objectID'] = base64_encode("post:" . $post_id);
            $record['uri'] = updateFrontedDomain($siteLocale, get_permalink($post_id));
            $record['title'] = get_the_title($post_id);
            $record['cptApplication'] = array(
                'preTitle' => get_field('pre_title'),
                'title' => get_field('title'),
                'content' => get_field('content')
            );
            $textModules = array();
            if (have_rows('text_modules')) {
                while (have_rows('text_modules')) : the_row();
                    array_push($textModules, array(
                        'content' => get_sub_field('content'),
                        'preTitle' => get_sub_field('preTitle'),
                        'title' => get_sub_field('title')
                    ));
                endwhile;
            }

            $layouters = array();
            if (have_rows('blocks')) {
                while (have_rows('blocks')) : the_row();
                    if (have_rows('Layouter')) {
                        while (have_rows('Layouter')) : the_row();
                            if (get_row_layout() == 'text_module_block') :
                                array_push($layouters, array(
                                    'content' => get_sub_field('content'),
                                    'preTitle' => get_sub_field('preTitle'),
                                    'title' => get_sub_field('title')
                                ));
                            endif;
                        endwhile;
                    }
                endwhile;
            }

            $record['cptApplicationChildrens'] = array(
                'subtitle' => get_field('subtitle') ? get_field('subtitle') : '',
                'textModules' => $textModules,
                'layouter' => $layouters
            );

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

function delete_application_from_index($postId)
{
    error_log('called delete_application_from_index ');
    $options = get_option('algolia_custom_integration_plugin_options');
    if (empty($options)) {
        return;
    }

    $siteLocale = get_locale();

    if (is_multisite() && !ms_is_switched()) {
        try {
            $algolia = Algolia::instance($options);
            // Get the page template name
            $page_template_name = get_post_meta($postId, '_wp_page_template', true);
            if (str_contains($page_template_name, 'application-ch.php')) {
                $index = $algolia->getIndex($siteLocale, AlgoliaIndex::case);
            } else {
                $index = $algolia->getIndex($siteLocale, AlgoliaIndex::applications);
            }
            $objectID = base64_encode("post:" . $postId);
            error_log('deleting ' . $objectID);
            $index->deleteObject($objectID);
        } catch (\Exception $e) {
            error_log('Error deleting to algolia index ' . $e->getMessage());
            add_flash_notice(__("Error saving to algolia index. " . $e->getMessage()), "error", true);
        }
    }
}

add_action('save_post_application', 'save_application_to_index', 15, 3);

add_action('delete_post_application', 'delete_application_from_index', 15);

add_action('wp_trash_application', 'delete_application_from_index', 15);
