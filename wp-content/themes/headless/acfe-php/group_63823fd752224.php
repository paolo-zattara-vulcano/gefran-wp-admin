<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_63823fd752224',
	'title' => 'Template / Blank',
	'fields' => array(
		array(
			'key' => 'field_63d1465e3d4c1',
			'label' => 'Enable Title',
			'name' => 'enable_title',
			'aria-label' => '',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'message' => '',
			'default_value' => 0,
			'ui_on_text' => '',
			'ui_off_text' => '',
			'ui' => 1,
			'style' => '',
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63dc48328f41a',
			'label' => 'Breadcrumbs type',
			'name' => 'breadcrumbs_type',
			'aria-label' => '',
			'type' => 'radio',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'choices' => array(
				'default' => 'Default',
				'no-parents-links' => 'Remove parents\' links',
				'support-downloads' => 'Support / Downloads subpages',
			),
			'default_value' => 'default : Default',
			'return_format' => 'value',
			'allow_null' => 0,
			'other_choice' => 0,
			'layout' => 'horizontal',
			'acfe_field_group_condition' => 0,
			'save_other_choice' => 0,
		),
		array(
			'key' => 'field_63d3e3aa95603',
			'label' => 'Layouter',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 0,
			'placement' => 'top',
			'endpoint' => 0,
			'no_preference' => 0,
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63823fd7555f0',
			'label' => 'Layouter',
			'name' => 'layouter_block',
			'aria-label' => '',
			'type' => 'clone',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'clone' => array(
				0 => 'group_637738549a687',
			),
			'display' => 'seamless',
			'layout' => 'block',
			'prefix_label' => 0,
			'prefix_name' => 0,
			'acfe_field_group_condition' => 0,
			'acfe_seamless_style' => 0,
			'acfe_clone_modal' => 0,
			'acfe_clone_modal_close' => 0,
			'acfe_clone_modal_button' => '',
			'acfe_clone_modal_size' => 'large',
		),
		array(
			'key' => 'field_63d3e3c595604',
			'label' => 'Related',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 0,
			'placement' => 'top',
			'endpoint' => 0,
			'no_preference' => 0,
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63d3e47095606',
			'label' => 'Related Title',
			'name' => 'related_title',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63d3e3f495605',
			'label' => 'Related',
			'name' => 'related',
			'aria-label' => '',
			'type' => 'relationship',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'post_type' => array(
				0 => 'post',
				1 => 'page',
			),
			'taxonomy' => '',
			'filters' => array(
				0 => 'search',
				1 => 'post_type',
				2 => 'taxonomy',
			),
			'return_format' => 'object',
			'acfe_add_post' => 0,
			'acfe_edit_post' => 0,
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => '0',
			),
			'min' => '',
			'max' => '',
			'elements' => '',
			'acfe_field_group_condition' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'page-templates/blank.php',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => array(
		0 => 'the_content',
	),
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_display_title' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 1,
	'graphql_field_name' => 'acfPages_blank',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Page',
		1 => 'BlankPageTemplate',
	),
	'modified' => 1684118415,
));

endif;