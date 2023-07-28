<?php

function pmxe_gform_pre_handle_confirmation($lead)
{

    if(!class_exists('GF_Export_Add_On')) {
        return;
    }

    $entry_id = $lead['id'];

    $list = new PMXE_Export_List();

    $exportList = $list->setColumns($list->getTable() . '.*')->getBy();

    foreach ($exportList as $export) {
        if (
            isset($export['options']['enable_real_time_exports']) &&
            $export['options']['enable_real_time_exports'] &&
            isset($export['options']['enable_real_time_exports_running']) &&
            $export['options']['enable_real_time_exports_running']
        ) {
            if (strpos($export['options']['cpt'][0], 'custom_') === 0) {

                if ($entry_id) {

                    $exportRecord = new PMXE_Export_Record();
                    $exportRecord->getById($export['id']);
                    $exportRecord->execute(false, true, $entry_id);
                }
            }
        }
    }


}