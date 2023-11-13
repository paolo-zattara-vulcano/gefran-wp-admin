<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_638dd571636b9',
	'title' => 'Template / Governance - Management',
	'fields' => array(
		array(
			'key' => 'field_638dd6172c8b1',
			'label' => 'Management',
			'name' => 'management',
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
			'collapsed' => 'field_638dd89b2ac73',
			'button_label' => 'Aggiungi Riga',
			'acfe_field_group_condition' => 0,
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_638dd89b2ac73',
					'label' => 'Person',
					'name' => 'person',
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
						0 => 'group_638dd774ab3a0',
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
					'parent_repeater' => 'field_638dd6172c8b1',
				),
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'page-templates/governance-management.php',
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
	'graphql_field_name' => 'acfPages_gov_management',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Page',
		1 => 'Template_GovernanceManagement',
	),
	'modified' => 1699877343,
));

endif;