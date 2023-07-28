<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_637652c5961fd',
	'title' => 'CPT / Application - Childrens',
	'fields' => array(
		array(
			'key' => 'field_637652c59a77a',
			'label' => 'Subtitle',
			'name' => 'subtitle',
			'aria-label' => '',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'acfe_textarea_code' => 0,
			'maxlength' => '',
			'rows' => 4,
			'placeholder' => '',
			'new_lines' => 'br',
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63765eb05019b',
			'label' => 'Main Image & Related products',
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
			'show_in_graphql' => 1,
			'placement' => 'top',
			'endpoint' => 0,
			'no_preference' => 0,
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63766017501a2',
			'label' => 'Main Image',
			'name' => 'main_image',
			'aria-label' => '',
			'type' => 'image',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'uploader' => '',
			'acfe_thumbnail' => 0,
			'return_format' => 'array',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
			'preview_size' => 'medium',
			'acfe_field_group_condition' => 0,
			'library' => 'all',
		),
		array(
			'key' => 'field_6376603c501a3',
			'label' => 'Related products',
			'name' => 'related_products',
			'aria-label' => '',
			'type' => 'relationship',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'post_type' => array(
				0 => 'product',
			),
			'taxonomy' => '',
			'filters' => array(
				0 => 'search',
				1 => 'post_type',
				2 => 'taxonomy',
			),
			'return_format' => 'object',
			'acfe_add_post' => 0,
			'acfe_edit_post' => 1,
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => true,
				'acfe_bidirectional_related' => array(
					0 => 'field_6381084b0f380',
				),
			),
			'min' => '',
			'max' => '',
			'elements' => '',
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63765f5c5019c',
			'label' => 'Main content',
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
			'show_in_graphql' => 1,
			'placement' => 'top',
			'endpoint' => 0,
			'no_preference' => 0,
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63766002501a1',
			'label' => 'Main Text Module',
			'name' => 'text_modules',
			'aria-label' => '',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '100',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'acfe_repeater_stylised_button' => 0,
			'layout' => 'block',
			'pagination' => 0,
			'min' => 0,
			'max' => 0,
			'collapsed' => 'field_637665703ce27',
			'button_label' => 'Aggiungi Text Module',
			'acfe_field_group_condition' => 0,
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_637665703ce27',
					'label' => 'Text Module',
					'name' => 'text_module',
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
					'show_in_graphql' => 1,
					'acfe_field_group_condition' => 0,
					'clone' => array(
						0 => 'group_637251c2553a9',
					),
					'display' => 'seamless',
					'layout' => 'block',
					'prefix_label' => 0,
					'prefix_name' => 0,
					'acfe_seamless_style' => 0,
					'acfe_clone_modal' => 0,
					'acfe_clone_modal_close' => 0,
					'acfe_clone_modal_button' => '',
					'acfe_clone_modal_size' => 'large',
					'parent_repeater' => 'field_63766002501a1',
				),
			),
		),
		array(
			'key' => 'field_63765f755019d',
			'label' => 'Blocks Content',
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
			'show_in_graphql' => 1,
			'placement' => 'top',
			'endpoint' => 0,
			'no_preference' => 0,
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63773c68b9c15',
			'label' => 'Blocks',
			'name' => 'blocks',
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
			'show_in_graphql' => 1,
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
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'application',
			),
			array(
				'param' => 'post_template',
				'operator' => '!=',
				'value' => 'page-templates/application.php',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 1,
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_display_title' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 1,
	'graphql_field_name' => 'cptApplicationChildrens',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Application',
		1 => 'Template_Application',
	),
	'modified' => 1679906819,
));

endif;