<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_63c5861f07250',
	'title' => 'Template / Support - Downloads',
	'fields' => array(
		array(
			'key' => 'field_63c587151b935',
			'label' => 'Type',
			'name' => 'type',
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
			'show_in_graphql' => 1,
			'choices' => array(
				'certifications' => 'Certifications',
				'downloads' => 'Downloads',
			),
			'default_value' => 'downloads',
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
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'page-templates/support-downloads.php',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
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
	'graphql_field_name' => 'acfPages_support_downloads',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Page',
		1 => 'Template_SupportDownloads',
	),
	'modified' => 1674392512,
));

endif;