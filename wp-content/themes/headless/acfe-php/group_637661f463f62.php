<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_637661f463f62',
	'title' => 'Atom / Download',
	'fields' => array(
		array(
			'key' => 'field_638dfa2249a4f',
			'label' => 'Orientation',
			'name' => 'orientation',
			'aria-label' => '',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_field' => '',
			'hide_label' => '',
			'hide_instructions' => '',
			'hide_required' => '',
			'show_in_graphql' => 1,
			'choices' => array(
				'vertical' => 'vertical',
				'horiziontal' => 'horizontal',
				'fullwidth' => 'fullwidth',
			),
			'default_value' => false,
			'return_format' => 'value',
			'multiple' => 0,
			'max' => '',
			'prepend' => '',
			'append' => '',
			'acfe_settings' => '',
			'acfe_validate' => '',
			'allow_null' => 0,
			'instruction_placement' => '',
			'acfe_permissions' => '',
			'ui' => 0,
			'acfe_field_group_condition' => 0,
			'ajax' => 0,
			'placeholder' => '',
			'allow_custom' => 0,
			'search_placeholder' => '',
		),
		array(
			'key' => 'field_6376645927961',
			'label' => 'Download Files',
			'name' => 'download_files',
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
			'hide_field' => '',
			'hide_label' => '',
			'hide_instructions' => '',
			'hide_required' => '',
			'show_in_graphql' => 1,
			'acfe_repeater_stylised_button' => 0,
			'layout' => 'block',
			'pagination' => 0,
			'acfe_settings' => '',
			'min' => 0,
			'max' => 0,
			'instruction_placement' => '',
			'acfe_permissions' => '',
			'collapsed' => 'field_637661f466749',
			'button_label' => 'Aggiungi Download',
			'acfe_field_group_condition' => 0,
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_637661f466749',
					'label' => 'Title',
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
					'hide_field' => '',
					'hide_label' => '',
					'hide_instructions' => '',
					'hide_required' => '',
					'show_in_graphql' => 1,
					'default_value' => '',
					'acfe_textarea_code' => 0,
					'acfe_settings' => '',
					'acfe_validate' => '',
					'maxlength' => '',
					'instruction_placement' => '',
					'acfe_permissions' => '',
					'rows' => 4,
					'placeholder' => '',
					'new_lines' => '',
					'acfe_field_group_condition' => 0,
					'parent_repeater' => 'field_6376645927961',
				),
				array(
					'key' => 'field_6376642b27960',
					'label' => 'File',
					'name' => 'file',
					'aria-label' => '',
					'type' => 'file',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'hide_field' => '',
					'hide_label' => '',
					'hide_instructions' => '',
					'hide_required' => '',
					'show_in_graphql' => 1,
					'uploader' => '',
					'return_format' => '',
					'upload_folder' => '',
					'multiple' => 0,
					'max' => '',
					'acfe_settings' => '',
					'acfe_validate' => '',
					'min_size' => '',
					'max_size' => '',
					'mime_types' => '',
					'instruction_placement' => '',
					'acfe_permissions' => '',
					'acfe_field_group_condition' => 0,
					'library' => 'all',
					'parent_repeater' => 'field_6376645927961',
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
	'acfe_form' => 1,
	'acfe_display_title' => '',
	'acfe_permissions' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 0,
	'graphql_field_name' => 'Comp_Hero',
	'map_graphql_types_from_location_rules' => 0,
	'graphql_types' => '',
	'modified' => 1699876257,
));

endif;