<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_6359f1c8a8b71',
	'title' => 'Comp / Video',
	'fields' => array(
		array(
			'key' => 'field_6359f1c8ae56f',
			'label' => 'Comp Video',
			'name' => 'Comp_video',
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
			'layout' => 'block',
			'pagination' => 0,
			'min' => 0,
			'max' => 0,
			'collapsed' => 'field_6359f1c8b3312',
			'button_label' => 'Aggiungi Riga',
			'acfe_field_group_condition' => 0,
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_636903e0c9368',
					'label' => 'Enable content and background img',
					'name' => 'enable_content',
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
					'ui_on_text' => 'Enable',
					'ui_off_text' => 'Video only',
					'ui' => 1,
					'style' => '',
					'acfe_field_group_condition' => 0,
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_636908e03b4ff',
					'label' => 'Type',
					'name' => 'type',
					'aria-label' => '',
					'type' => 'select',
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
						'bg-loop' => 'Background loop autoplay',
						'with-controls' => 'With controls',
					),
					'default_value' => 'bg-loop',
					'return_format' => 'value',
					'multiple' => 0,
					'max' => '',
					'prepend' => '',
					'append' => '',
					'allow_null' => 0,
					'ui' => 0,
					'acfe_field_group_condition' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'allow_custom' => 0,
					'search_placeholder' => '',
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_6359f1c8b3312',
					'label' => 'content',
					'name' => 'content',
					'aria-label' => '',
					'type' => 'clone',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '100',
						'class' => '',
						'id' => '',
					),
					'clone' => array(
						0 => 'group_63595bc5c09db',
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
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_636909b43b501',
					'label' => '(Column Endpoint)',
					'name' => '',
					'aria-label' => '',
					'type' => 'acfe_column',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'endpoint' => 1,
					'acfe_field_group_condition' => 0,
					'columns' => '6/12',
					'border' => '',
					'border_endpoint' => array(
						0 => 'endpoint',
					),
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_636909a93b500',
					'label' => '(Column 12/12)',
					'name' => '',
					'aria-label' => '',
					'type' => 'acfe_column',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'columns' => '12/12',
					'endpoint' => 0,
					'border' => '',
					'acfe_field_group_condition' => 0,
					'border_endpoint' => array(
						0 => 'endpoint',
					),
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_6359f27e5a8b1',
					'label' => 'Video',
					'name' => 'video',
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
					'show_in_graphql' => 1,
					'uploader' => '',
					'return_format' => 'array',
					'upload_folder' => '',
					'multiple' => 0,
					'max' => '',
					'min_size' => '',
					'max_size' => '',
					'mime_types' => '',
					'acfe_field_group_condition' => 0,
					'library' => 'all',
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_6359f2cf5a8b2',
					'label' => 'Video mobile',
					'name' => 'video_mobile',
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
					'show_in_graphql' => 1,
					'acfe_field_group_condition' => 0,
					'return_format' => 'array',
					'library' => 'all',
					'min_size' => 0,
					'max_size' => 0,
					'mime_types' => '',
					'uploader' => '',
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_636903b8c9366',
					'label' => 'Video Vimeo',
					'name' => 'video_vimeo',
					'aria-label' => '',
					'type' => 'url',
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
					'placeholder' => '',
					'acfe_field_group_condition' => 0,
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_636903cdc9367',
					'label' => 'Video Vimeo Mobile',
					'name' => 'video_vimeo_mobile',
					'aria-label' => '',
					'type' => 'url',
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
					'placeholder' => '',
					'acfe_field_group_condition' => 0,
					'parent_repeater' => 'field_6359f1c8ae56f',
				),
				array(
					'key' => 'field_636909b43b501',
					'label' => '(Column Endpoint)',
					'name' => '',
					'aria-label' => '',
					'type' => 'acfe_column',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'endpoint' => 1,
					'acfe_field_group_condition' => 0,
					'columns' => '6/12',
					'border' => '',
					'border_endpoint' => array(
						0 => 'endpoint',
					),
					'parent_repeater' => 'field_6359f1c8ae56f',
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
	'show_in_graphql' => 1,
	'graphql_field_name' => 'Comp_Hero',
	'map_graphql_types_from_location_rules' => 0,
	'graphql_types' => '',
	'modified' => 1699876756,
));

endif;