<?php

class Application_Sync
{
    // AJAX handler function. This function will receive the data from the AJAX call and process it
    static function acf_ajax_application_handler()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'application_ajax_action')) {
            die(-1);
        }
        $source_post_id = $_POST['post_id'];
        $source_page_template_name = get_post_meta($source_post_id, '_wp_page_template', true);
        $source_parent_post_id = wp_get_post_parent_id($source_post_id);
        $translations_parent_post = \Inpsyde\MultilingualPress\translationIds($source_parent_post_id, 'Post', 1);
        //if (str_contains($page_template_name, 'application.php')) {

        // Taxonomies
        $taxonomies = [
            'application_content', 'application_category'
        ];
        $source_taxonimies_terms = array();
        foreach ($taxonomies as $tax) {
            $source_taxonimies_terms[$tax] = wp_get_object_terms($source_post_id, $tax);
        }

        // Get the ID of the featured image of the original post
        $original_feature_id = get_post_meta($source_post_id, '_thumbnail_id', true);


        // Get an array of all site IDs in the network
        $translations = \Inpsyde\MultilingualPress\translationIds($_POST['post_id'], 'Post', 1);
        /** SYNC */
        if (count($translations)) {
            foreach ($translations as $siteId => $postId) {
                error_log('siteId:' . $siteId . ' postId:' . $postId);
                switch_to_blog($siteId);

                $post = get_post($postId);
                if ($post != null) {

                    if (isset($translations_parent_post[$siteId])) {
                        // update post parent
                        wp_update_post(
                            array(
                                'ID'          => $post->ID,
                                'post_parent' => $translations_parent_post[$siteId],
                            )
                        );
                    }

                    // update template
                    update_post_meta($post->ID, '_wp_page_template', $source_page_template_name);

                    foreach ($source_taxonimies_terms as $tax => $terms) {
                        $slugs_to_add = array();
                        foreach ($terms as $term) {
                            $translated_term_ids = \Inpsyde\MultilingualPress\translationIds($term->term_id, 'term', 1);
                            $trans_term = get_term_by('id', $translated_term_ids[$siteId], $tax);
                            //error_log(print_r($term, true));
                            if ($trans_term) {
                                array_push($slugs_to_add, $trans_term->slug);
                            }
                        }
                        if (!empty($slugs_to_add)) {
                            // Delete the existing taxonomy terms for the target post
                            wp_delete_object_term_relationships($post->ID, $tax);
                            // the terms and add them to the target post
                            wp_set_object_terms($post->ID, $slugs_to_add, $tax, true);
                        }
                    }
                    // Set the ID of the featured image for the translation
                    update_post_meta($post->ID, '_thumbnail_id', $original_feature_id);
                }
                restore_current_blog();
            }
        }

        die('Contenuti salvati');
    }
}
