<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_pro_compatibility')):

class acfe_pro_compatibility{
    
    /**
     * construct
     */
    function __construct(){
        
        // global
        add_action('acf/init',                              array($this, 'init'), 98);
        add_filter('acfe/form_field_type_category',         array($this, 'form_field_type_category'));
    
        // fields compatibility
        add_filter('acf/validate_field/type=acfe_image_selector', array($this, 'field_image_selector'), 20);
        
    }
    
    
    /**
     * init
     *
     * Renamed modules
     *
     * acf/init:98
     *
     * @since 0.8.8 (20/03/2021)
     */
    function init(){
    
        // settings list
        $settings = array(
            'acfe/modules/dynamic_templates' => 'acfe/modules/templates',
        );
    
        // loop settings
        foreach($settings as $old => $new){
        
            if(acf_get_setting($old) !== null){
                acf_update_setting($new, acf_get_setting($old));
            }
        
        }
        
    }
    
    
    /**
     * form_field_type_category
     *
     * acfe/form_field_type_category
     *
     * Change Forms field category to 'ACF'
     *
     * @param $category
     *
     * @since 0.8.8.1 (25/03/2021)
     *
     * @return string
     */
    function form_field_type_category($category){
        return 'ACF';
    }
    
    
    /**
     * field_image_selector
     *
     * acf/validate_field/type=acfe_image_selector:20
     *
     * Removed images and use choices only
     *
     * @since 0.8.8.4 (14/06/2021)
     */
    function field_image_selector($field){
    
        // compatibility: removed 'images' setting, use choices instead
        if(isset($field['images'])){
        
            // vars
            $choices = $field['choices'];
            $images = acf_maybe_get($field, 'images');
            $images = acf_get_array($images);
        
            // merge & combine images
            $choices = array_merge($images, $choices);
            $choices = array_combine($choices, $choices);
        
            // assign choices
            $field['choices'] = $choices;
        
        }
    
        // return
        return $field;
        
    }
    
}

new acfe_pro_compatibility();

endif;