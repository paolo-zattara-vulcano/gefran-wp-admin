<?php


class Page_Sync
{
    static function acf_ajax_page_handler()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'page_ajax_action')) {
            return;
        }

        $hero_video_solutions = get_field('hero_video_solutions', $_POST['post_id']);
        $contacts = get_field('contacts_block', $_POST['post_id']);
        $source_layouter = get_field('Layouter', $_POST['post_id']);
        error_log(print_r($hero_video_solutions, true));

        $translations = \Inpsyde\MultilingualPress\translationIds($_POST['post_id'], 'Post', 1);
        try {
            if (count($translations)) {
                foreach ($translations as $siteId => $postId) {
                    error_log('siteId:' . $siteId . ' postId:' . $postId);
                    switch_to_blog($siteId);

                    $post = get_post($postId);
                    if ($post != null) {
                        $local_hero_video_solutions = get_field('hero_video_solutions', $postId);

                        /*   NON FUNZIONA
                        if (have_rows('hero_video_solutions')) {
                            while (have_rows('hero_video_solutions')) {
                                the_row();

                                // Get the subfield value, which is the repeater field
                                $repeater_field = get_sub_field('Comp_video');

                                // Check if the repeater field has rows
                                if (have_rows($repeater_field)) {
                                    while (have_rows($repeater_field)) {
                                        the_row();

                                        error_log('HERE');
                                        $result = update_sub_field('video', $hero_video_solutions['Comp_video'][$index]['video'], $postId);
                                        if ($result === false) {
                                            throw new Exception('Failed to update the repeater subfield.');
                                        }
                                        $xx = get_field('hero_video_solutions', $postId);
                                        error_log('NEW:');
                                        error_log(print_r($xx, true));
                                    }
                                }
                            }
                        }
*/






                        foreach ($local_hero_video_solutions['Comp_video'] as $index => &$compVideo) {
                            if ($hero_video_solutions['Comp_video'][$index]) {
                                $compVideo['enable_content'] = $hero_video_solutions['Comp_video'][$index]['enable_content'];
                                $compVideo['type'] = $hero_video_solutions['Comp_video'][$index]['type'];

                                // Update "video" within the first row of "Comp_video repeater".
                                //update_sub_field(array('Comp_video', 1, 'video'), $hero_video_solutions['Comp_video'][$index]['video'], $post->ID);


                                $compVideo['video_vimeo'] = $hero_video_solutions['Comp_video'][$index]['video_vimeo'];
                                $compVideo['video_vimeo_mobile'] = $hero_video_solutions['Comp_video'][$index]['video_vimeo_mobile'];
                            }
                        }
                        update_field('hero_video_solutions', $local_hero_video_solutions, $post->ID);

                        /* NON FUNZIONA
                        $result = update_field('Comp_video' . '_0_' . 'video', $hero_video_solutions['Comp_video'][$index]['video'], $postId);

                        NON FUNZIONA
                        $result = update_sub_field(array('Comp_video', 0, 'video'), $hero_video_solutions['Comp_video'][$index]['video'], $post->ID);
                        */


                        // CONTACTS
                        $local_contacts = get_field('contacts_block', $postId);
                        $local_contacts['telephone'] = $contacts['telephone'];
                        $local_contacts['fax'] = $contacts['fax'];
                        $local_contacts['email'] = $contacts['email'];
                        $local_contacts['address'] = $contacts['address'];
                        $local_contacts['map_link'] = $contacts['map_link'];
                        update_field('contacts_block', $local_contacts, $post->ID);

                        // Layouter
                        $local_layouter = get_field('Layouter', $postId);
                        // [0] -> text_icons_grid_block
                        $local_layouter[0]['background'] = $source_layouter[0]['background'];
                        $local_layouter[0]['image'] = $source_layouter[0]['image'];
                        update_field('Layouter', $local_layouter, $post->ID);
                    }
                    restore_current_blog();
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            die('Error: ' . $e->getMessage());
        }

        /*foreach ($acf_fields as $field_name => $field_value) {
                error_log($field_name . ': ' . print_r($field_value['value'], true));
            }
            */


        die('Contenuti salvati');
    }
}
