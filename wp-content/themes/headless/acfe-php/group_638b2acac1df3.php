<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_638b2acac1df3',
	'title' => 'Comp / Text Icons',
	'fields' => array(
		array(
			'key' => 'field_638b2bb7a073d',
			'label' => 'Background',
			'name' => 'background',
			'aria-label' => '',
			'type' => 'clone',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'clone' => array(
				0 => 'group_638b2c2cd27c9',
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
			'key' => 'field_645e59d2fef3b',
			'label' => 'Block ID',
			'name' => 'block_id',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
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
			'key' => 'field_638b2acac8691',
			'label' => 'Text Icons',
			'name' => 'text_icons',
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
			'collapsed' => '',
			'button_label' => 'Aggiungi Riga',
			'acfe_field_group_condition' => 0,
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_638b2b66a073c',
					'label' => 'Title',
					'name' => 'text_icons_title',
					'aria-label' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '100',
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
					'parent_repeater' => 'field_638b2acac8691',
				),
				array(
					'key' => 'field_638b359366778',
					'label' => 'Text',
					'name' => 'text_icons_text',
					'aria-label' => '',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'default_value' => '',
					'acfe_textarea_code' => 0,
					'maxlength' => '',
					'rows' => '',
					'placeholder' => '',
					'new_lines' => '',
					'acfe_field_group_condition' => 0,
					'parent_repeater' => 'field_638b2acac8691',
				),
				array(
					'key' => 'field_638b2b3ba073b',
					'label' => 'Icons',
					'name' => 'text_icons_icon',
					'aria-label' => '',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'acfe_repeater_stylised_button' => 0,
					'layout' => 'table',
					'min' => 0,
					'max' => 0,
					'collapsed' => '',
					'button_label' => 'Aggiungi Riga',
					'acfe_field_group_condition' => 0,
					'rows_per_page' => 20,
					'sub_fields' => array(
						array(
							'key' => 'field_638b3727f4d56',
							'label' => 'Icon',
							'name' => 'icon',
							'aria-label' => '',
							'type' => 'image',
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
							'library' => 'all',
							'parent_repeater' => 'field_638b2b3ba073b',
						),
					),
					'parent_repeater' => 'field_638b2acac8691',
				),
			),
		),
		array(
			'key' => 'field_645e718b09e9d',
			'label' => 'Column 1 Main Title',
			'name' => 'column_1_title',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '33',
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
			'key' => 'field_645e739e09ea7',
			'label' => 'Column 2 Main Title',
			'name' => 'column_2_title',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '33',
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
			'key' => 'field_645e72fa09ea5',
			'label' => 'Mobile Main title',
			'name' => 'mobile_title',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '33',
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
			'key' => 'field_645e71cb09e9f',
			'label' => 'List',
			'name' => 'list',
			'aria-label' => '',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'acfe_repeater_stylised_button' => 0,
			'layout' => 'table',
			'pagination' => 0,
			'min' => 0,
			'max' => 0,
			'collapsed' => '',
			'button_label' => 'Aggiungi Riga',
			'acfe_field_group_condition' => 0,
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_645e737409ea6',
					'label' => 'Column 1: Title',
					'name' => 'title',
					'aria-label' => '',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'default_value' => '',
					'acfe_textarea_code' => 0,
					'maxlength' => '',
					'rows' => 3,
					'placeholder' => '',
					'new_lines' => '',
					'acfe_field_group_condition' => 0,
					'parent_repeater' => 'field_645e71cb09e9f',
				),
				array(
					'key' => 'field_645e720509ea0',
					'label' => 'Column 2: Content',
					'name' => 'content',
					'aria-label' => '',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'default_value' => '',
					'acfe_textarea_code' => 0,
					'maxlength' => '',
					'rows' => 3,
					'placeholder' => '',
					'new_lines' => '',
					'acfe_field_group_condition' => 0,
					'parent_repeater' => 'field_645e71cb09e9f',
				),
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'post',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'seamless',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => false,
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
	'show_in_graphql' => 0,
	'graphql_field_name' => 'Comp_Hero',
	'map_graphql_types_from_location_rules' => 0,
	'graphql_types' => '',
	'modified' => 1683955391,
));

endif;