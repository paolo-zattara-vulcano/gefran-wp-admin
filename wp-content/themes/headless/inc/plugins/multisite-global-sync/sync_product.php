<?php


class Product_Sync
{
    // AJAX handler function. This function will receive the data from the AJAX call and process it
    static function acf_ajax_product_handler()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'product_ajax_action')) {
            return;
        }

        //menu order
        //$currentSitePost = get_post($_POST['post_id']);
        //$menu_order = $currentSitePost->menu_order;

        $currentSiteOriginalId = get_field('original_id', $_POST['post_id']);
        $currentSiteBadge = get_field('badge', $_POST['post_id']);
        $currentSiteGallery = get_field('gallery', $_POST['post_id']);
        $currentSiteIoLink = get_field('io-link', $_POST['post_id']);
        $currentSiteAlgoliaEx = get_field('algolia_exluded', $_POST['post_id']);
        $currentSiteEnableC = get_field('enable_configurator', $_POST['post_id']);
        // $currentSiteConfLink = get_field('configurator_link', $_POST['post_id']);
        // $currentSiteCadenasLink = get_field('cadenas_link', $_POST['post_id']);
        $currentSiteAppAvail = get_field('apps_available_on', $_POST['post_id']);
        $currentSiteMainVimeo = get_field('main_vimeo_video_url', $_POST['post_id']);
        $currentSiteVideo = get_field('video', $_POST['post_id']);

        $currentSiteRelProds = get_field('related_products', $_POST['post_id']);
        $currentSiteRelApps = get_field('related_applications', $_POST['post_id']);
        $currentSiteReplProd = get_field('replacement_product', $_POST['post_id']);

        function processID($post, $siteId) {
          $post->ID = \Inpsyde\MultilingualPress\translationIds($post->ID, 'Post', 1)[$siteId];
          return $post;
        }


        $source_post_id = $_POST['post_id'];
        $taxonomies = [
            'product_category', 'application_category', 'filter_application', 'filter_type', 'filter_mechanical_characteristic',
            'filter_mounting', 'filter_variable', 'filter_signal_output', 'filter_safety_conformity_cert', 'filter_ex_certification', 'filter_characteristic',
            'filter_stroke', 'filter_accuracy', 'filter_format_mounting', 'filter_loop_controller', 'filter_features', 'filter_fieldbus', 'filter_phase',
            'filter_current_rate', 'filter_heatsink', 'filter_type_control', 'filter_certification_norms', 'filter_measured_variable',
            'filter_series', 'filter_input_output', 'filter_system_and_accessories', 'filter_functional_modules'
        ];
        $source_taxonimies_terms = array();
        foreach ($taxonomies as $tax) {
            $source_taxonimies_terms[$tax] = wp_get_object_terms($source_post_id, $tax);
        }

        // Get the original size post thumbnail url
        $attachment_id = get_post_thumbnail_id($source_post_id); // Replace $post_id with the ID of your post
        $source_thumbnail_url = wp_get_attachment_url($attachment_id);

        $source_gallery_images = get_field('gallery', $source_post_id);
        // Get an array of all site IDs in the network
        $translations = \Inpsyde\MultilingualPress\translationIds($_POST['post_id'], 'Post', 1);
        try {
            if (count($translations)) {
                foreach ($translations as $siteId => $postId) {
                    // error_log('siteId:' . $siteId . ' postId:' . $postId);
                    switch_to_blog($siteId);

                    $post = get_post($postId);
                    if ($post != null) {
                        //$post->menu_order = $menu_order;

                        update_field('original_id', $currentSiteOriginalId, $post->ID);
                        update_field('badge', $currentSiteBadge, $post->ID);
                        update_field('gallery', $currentSiteGallery, $post->ID);
                        update_field('io-link', $currentSiteIoLink, $post->ID);
                        update_field('algolia_exluded', $currentSiteAlgoliaEx, $post->ID);
                        update_field('enable_configurator', $currentSiteEnableC, $post->ID);
                        // update_field('configurator_link', $currentSiteConfLink, $post->ID);
                        // update_field('cadenas_link', $currentSiteCadenasLink, $post->ID);
                        update_field('apps_available_on', $currentSiteAppAvail, $post->ID);
                        update_field('main_vimeo_video_url', $currentSiteMainVimeo, $post->ID);
                        update_field('video', $currentSiteVideo, $post->ID);


                        // RELATIONSHIP FIELDS
                        // Loop through each WP_Post object in $currentSiteReplProd and modify the ID
                        foreach ($currentSiteReplProd as $relation) {
                            $translations = \Inpsyde\MultilingualPress\translationIds($relation->ID, 'Post', 1);
                            $relation->ID = isset($translations[$siteId]) ?$translations[$siteId] : '0';
                        }
                        update_field('replacement_product', $currentSiteReplProd, $post->ID);

                        // Loop through each WP_Post object in $currentSiteRelProds and modify the ID
                        foreach ($currentSiteRelProds as $relation) {
                            $translations = \Inpsyde\MultilingualPress\translationIds($relation->ID, 'Post', 1);
                            $relation->ID = isset($translations[$siteId]) ?$translations[$siteId] : '0';
                        }
                        update_field('related_products', $currentSiteRelProds, $post->ID);

                        // Loop through each WP_Post object in $currentSiteRelApps and modify the ID
                        foreach ($currentSiteRelApps as $relation) {
                            $translations = \Inpsyde\MultilingualPress\translationIds($relation->ID, 'Post', 1);
                            $relation->ID = isset($translations[$siteId]) ?$translations[$siteId] : '0';
                        }
                        update_field('related_applications', $currentSiteRelApps, $post->ID);



                        // TAXONOMIES
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

                        $thumbnail_id = attachment_url_to_postid($source_thumbnail_url);
                        set_post_thumbnail($post->ID, $thumbnail_id);

                        $gallery_images = [];
                        foreach ($source_gallery_images as $source_image) {
                            $new_id = attachment_url_to_postid($source_image['url']);
                            $new_image = get_post($new_id);
                            $gallery_images[] = acf_get_attachment($new_image);
                        }
                        //error_log(print_r($gallery_images, true));
                        update_field('gallery', $gallery_images, $post->ID);
                    }
                    restore_current_blog();
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            die('Error: ' . $e->getMessage());
        }

        die('Contenuti salvati');
    }

    static function ajax_products_category_handler()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'sync-products-category-nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        self::update_products_category();
    }

    public static function update_products_category()
    {
        $orig_products = get_posts(array(
            'post_type' => 'product',
            'post_status' => array('publish', 'draft'),
            'posts_per_page' => -1,
        ));
        //$tax = 'product_category';

        $taxonomies = [
            'product_category', 'application_category', 'filter_application', 'filter_type', 'filter_mechanical_characteristic',
            'filter_mounting', 'filter_variable', 'filter_signal_output', 'filter_safety_conformity_cert', 'filter_ex_certification', 'filter_characteristic',
            'filter_stroke', 'filter_accuracy', 'filter_format_mounting', 'filter_loop_controller', 'filter_features', 'filter_fieldbus', 'filter_phase',
            'filter_current_rate', 'filter_heatsink', 'filter_type_control', 'filter_certification_norms', 'filter_measured_variable', 'filter_series',
            'filter_input_output', 'filter_system_and_accessories', 'filter_functional_modules'
        ];

        foreach ($orig_products as $orig_prod) {
            $translations = \Inpsyde\MultilingualPress\translationIds($orig_prod->ID, 'Post', 1);
            $source_taxonimies_terms = array();
            foreach ($taxonomies as $tax) {
                $source_taxonimies_terms[$tax] = wp_get_object_terms($orig_prod->ID, $tax);
            }

            //$prod_category_tax_terms = wp_get_object_terms($orig_prod->ID, $tax);
            if (count($translations)) {
                foreach ($translations as $siteId => $postId) {
                    // skip english master blog
                    if ($siteId != 1) {
                      switch_to_blog($siteId);
                      $post = get_post($postId);
                      if ($post != null) {

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
                                  wp_delete_object_term_relationships($postId, $tax);
                                  wp_set_object_terms($postId, $slugs_to_add, $tax, true);
                              }
                          }
                      }
                      restore_current_blog();
                    }
                }
            }
        }
        die('Contenuti salvati');
    }
}
