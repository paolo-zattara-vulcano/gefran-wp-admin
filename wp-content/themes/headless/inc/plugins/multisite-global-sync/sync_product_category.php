<?php

class Product_Category_Sync
{
    // AJAX handler function. This function will receive the data from the AJAX call and process it
    static function acf_ajax_product_category_handler()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'product_category_tax')) {
            die(-1);
        }
        $source_term = get_term_by('id', $_POST['tag_ID'], $_POST['taxonomy']);
        $source_category_image = get_field('category_image', $source_term->taxonomy . '_' . $source_term->term_id);
        $source_term_selector = get_field('selector', $source_term->taxonomy . '_' . $source_term->term_id);
        $parent_term_translations = \Inpsyde\MultilingualPress\translationIds($source_term->parent, 'term', 1);
        //error_log(print_r($parent_term_translations, true));
        //error_log(print_r($source_term, true));
        //$fields = get_fields($source_term);
        //error_log('fields:');
        //error_log(print_r($fields, true));
        if (!$source_term) {
            die('Taxonomy not found');
        }

        // Get an array of all site IDs in the network
        $translations = \Inpsyde\MultilingualPress\translationIds($_POST['tag_ID'], 'term', 1);
        //error_log(print_r($translations, true));
        if (count($translations)) {
            foreach ($translations as $siteId => $termId) {
                //error_log('siteId:' . $siteId . ' termId:' . $termId);
                switch_to_blog($siteId);
                $term = get_term_by('id', $termId, $_POST['taxonomy']);
                //error_log(print_r($term, true));
                if ($term) {
                    if (isset($parent_term_translations[$siteId])) {
                        $result = wp_update_term(
                            $termId,
                            $_POST['taxonomy'],
                            array(
                                'parent' => $parent_term_translations[$siteId],
                            )
                        );
                    }
                    update_field('selector', $source_term_selector, $term->taxonomy . '_' . $term->term_id);

                    $new_id = attachment_url_to_postid($source_category_image['url']);
                    error_log('site imageId: ' . $new_id);
                    $new_image = get_post($new_id);
                    error_log(print_r($new_image, true));

                    update_field('category_image', $new_image, $source_term->taxonomy . '_' . $source_term->term_id);

                    // Check if the taxonomy term was updated successfully
                    if (is_wp_error($result)) {
                        $error_string = $result->get_error_message();
                        error_log(print_r($error_string, true));
                        die('Error updating custom taxonomy term: ' . $error_string);
                    }
                }
                restore_current_blog();
            }
        }

        die('Contenuti salvati');
    }
}
