<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_6361435033ce9',
	'title' => 'CPT / Event',
	'fields' => array(
		array(
			'key' => 'field_6361435023a6e',
			'label' => 'Date',
			'name' => 'date',
			'aria-label' => '',
			'type' => 'acfe_date_range_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'display_format' => 'd/m/Y',
			'return_format' => 'd/m/Y',
			'first_day' => 1,
			'placeholder' => '',
			'separator' => '-',
			'default_start' => '',
			'default_end' => '',
			'min_days' => '',
			'max_days' => '',
			'min_date' => '',
			'max_date' => '',
			'custom_ranges' => '',
			'show_dropdowns' => 0,
			'no_weekends' => 0,
			'auto_close' => 0,
			'allow_null' => 0,
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_6361441223a6f',
			'label' => 'Location',
			'name' => 'location',
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
			'rows' => 4,
			'placeholder' => '',
			'new_lines' => 'br',
			'acfe_field_group_condition' => 0,
		),
		array(
			'key' => 'field_63d549d043ee9',
			'label' => 'Link',
			'name' => 'link',
			'aria-label' => '',
			'type' => 'acfe_advanced_link',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'post_type' => '',
			'taxonomy' => '',
			'acfe_field_group_condition' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'event',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'seamless',
	'label_placement' => 'left',
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
	'graphql_field_name' => 'cptEvent',
	'map_graphql_types_from_location_rules' => 0,
	'graphql_types' => '',
	'modified' => 1674926350,
));

endif;