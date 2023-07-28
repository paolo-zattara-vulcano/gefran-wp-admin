<?php

function pmxe_admin_init()
{
    wp_enqueue_script('pmxe-script', PMXE_ROOT_URL . '/static/js/pmxe.js', array('jquery'), PMXE_VERSION);

    $custom_types = get_post_types(array('_builtin' => true), 'objects') + get_post_types(array('_builtin' => false, 'show_ui' => true), 'objects') + get_post_types(array('_builtin' => false, 'show_ui' => false), 'objects');

    foreach ($custom_types as $key => $ct) {
        if (in_array($key, array('attachment', 'revision', 'nav_menu_item', 'import_users', 'shop_webhook', 'acf-field', 'acf-field-group'))) unset($custom_types[$key]);
    }
    $custom_types = apply_filters('wpallexport_custom_types', $custom_types);

    foreach ($custom_types as $slug => $type) {

        if ($slug) {
            add_action('publish_' . $slug, function ($post_id) {

                if (wp_is_post_revision($post_id)) {
                    return;
                }

                $post = get_post($post_id);

                if ($post->post_type === 'shop_order' || ($post->post_type === 'property' && class_exists('Easy_Real_Estate'))) {
                    return;
                }

                if ($post->post_type === 'product' || $post->post_type === 'product_variation') {
                    $addonsService = new \Wpae\App\Service\Addons\AddonService();

                    if(!$addonsService->isWooCommerceProductAddonActive() && !$addonsService->isWooCommerceAddonActive()) {
                        return;
                    }
                }

                if (defined('REST_REQUEST') && REST_REQUEST // (#1)
                    || isset($_GET['rest_route']) // (#2)
                    && strpos($_GET['rest_route'], '/', 0) === 0) {
                    return;
                }

                $list = new PMXE_Export_List();

                $exportList = $list->setColumns($list->getTable() . '.*')->getBy();

                foreach ($exportList as $export) {
                    if (
                        isset($export['options']['enable_real_time_exports']) &&
                        $export['options']['enable_real_time_exports'] &&
                        isset($export['options']['enable_real_time_exports_running']) &&
                        $export['options']['enable_real_time_exports_running']
                    ) {
                        if (in_array($post->post_type, $export['options']['cpt'])) {

                            if ($post_id) {

                                $exportRecord = new PMXE_Export_Record();
                                $exportRecord->getById($export['id']);
                                $exportRecord->execute(false, true, $post_id);
                            }
                        }
                    }
                }

            });
        }
    }
}