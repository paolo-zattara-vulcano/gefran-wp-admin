<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_6382477cdc0aa',
	'title' => 'Comp / Text Tabs',
	'fields' => array(
		array(
			'key' => 'field_6382477ce0a5b',
			'label' => 'Text Tabs',
			'name' => 'text_tabs',
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
			'collapsed' => 'field_638248123c68a',
			'button_label' => 'Aggiungi Riga',
			'acfe_field_group_condition' => 0,
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_638248123c68a',
					'label' => 'Text Tabs Block',
					'name' => 'text_tabs_block',
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
						0 => 'group_637251c2553a9',
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
					'parent_repeater' => 'field_6382477ce0a5b',
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
	'modified' => 1699876680,
));

endif;