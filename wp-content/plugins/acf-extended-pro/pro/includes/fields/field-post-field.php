<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_post_field')):

class acfe_field_post_field extends acfe_field{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_post_field';
        $this->label = __('Post Field', 'acfe');
        $this->category = 'layout';
        $this->defaults = array(
            'field_type' => 'title'
        );
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // Type
        acf_render_field_setting($field, array(
            'label'         => __('Field Type', 'acfe'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'field_type',
            'choices'       => array(
                'attributes'        => __('Attributes'),
                'author'            => __('Author'),
                'comments'          => __('Comments'),
                'content'           => __('Content'),
                'date'              => __('Date'),
                'discussion'        => __('Discussion'),
                'excerpt'           => __('Excerpt'),
                'featured_image'    => __('Featured Image'),
                'name'              => __('Name'),
                'permalink'         => __('Permalink'),
                'preview'           => __('Preview'),
                'revisions'         => __('Revisions'),
                'revisions_list'    => __('Revisions List'),
                'status'            => __('Status'),
                'taxonomy'          => __('Taxonomy'),
                'title'             => __('Title'),
                'trackbacks'        => __('Trackbacks'),
                'visibility'        => __('Visibility'),
            )
        ));
    
        // Taxonomy
        acf_render_field_setting($field, array(
            'label'         => __('Taxonomy', 'acfe'),
            'instructions'  => '',
            'type'          => 'acfe_taxonomies',
            'name'          => 'taxonomy',
            'field_type'    => 'select',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'taxonomy',
                    ),
                )
            )
        ));
        
    }
    
    
    /**
     * field_wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function field_wrapper_attributes($wrapper, $field){
        
        $wrapper['data-field-type'] = $field['field_type'];
        
        if($field['field_type'] === 'taxonomy'){
            
            $taxonomy = $field['taxonomy'];
            $taxonomy_obj = get_taxonomy($taxonomy);
            
            if($taxonomy_obj){
                
                $selector = $taxonomy_obj->hierarchical ? "{$taxonomy}div" : "tagsdiv-{$taxonomy}";
                
                $wrapper['data-taxonomy-hierarchical'] = $taxonomy_obj->hierarchical;
                $wrapper['data-taxonomy-selector'] = $selector;
                
            }
            
        }
        
        return $wrapper;
        
    }
    
    
    /**
     * load_field
     *
     * @param $field
     *
     * @return mixed
     */
    function load_field($field){
        
        $field['name'] = '';
        $field['required'] = 0;
        $field['value'] = false;
        
        return $field;
        
    }
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return false
     */
    function prepare_field($field){
        
        // get post id
        $post_id = acf_get_valid_post_id();
        
        // hide field
        if(!$post_id){
            return false;
        }
        
        $data = acf_get_post_id_info($post_id);
        
        // hide field on screens that are not post edit
        if($data['type'] !== 'post'){
            return false;
        }
        
        // return
        return $field;
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_post_field');

endif;