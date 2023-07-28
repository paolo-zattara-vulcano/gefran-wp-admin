<?php
/*
	Plugin Name: Bulk menu creator PRO
	Plugin URI: https://wp-speedup.eu/shop/wordpress-plugins/pro-plugins/bulk-menu-creator-pro/
	Description: Bulk menu creator PRO
	Version: 10.0
	Author: wp-speedup.eu
	Author URI: https://wp-speedup.eu/contact-us/
	Text Domain: bulk-menu-pro
	Domain Path: /languages
*/

require 'license.php';
new DomainLicense(
	454, // woo product ID - must be main product ID in case of variables
	'bulk-menu-creator-pro',
	'Bulk menu creator PRO'
);

// here we need to add more URL parameters
require 'plugin-update-checker/plugin-update-checker.php';
$bulk_menu_pro_updater = Puc_v4_Factory::buildUpdateChecker(
	'https://updates.wp-speedup.eu/?action=get_metadata&slug=bulk-menu-creator-pro&plugin=454&license_key=' . get_option( 'bulk-menu-creator-pro' . '_license', false ),
	__FILE__,
	'bulk-menu-creator-pro'
);

// and deactivate license if it returns error
$bulk_menu_pro_updater->addResultFilter(function( $plugin_info, $http_response = null ){
	if( ! isset( $plugin_info->download_url ) || ! trim( $plugin_info->download_url ) ){
		if( isset( $plugin_info->error ) && trim( $plugin_info->error ) ){
			update_option( 'bulk-menu-creator-pro' . '_license', '' );
		}
	}
	return $plugin_info;
});

if( ! get_option( 'bulk-menu-creator-pro' . '_license', false ) ) return;

if( ! class_exists('bulk_menu_pro') ){
	class bulk_menu_pro{
		var $menu_offset;
		var $current_level = 0;
		var $hidden_parent = array();
		
		function __construct(){
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_filter( 'wp_get_nav_menu_items', array( $this, 'wp_get_nav_menu_items' ), 10 );

			add_action( 'admin_footer', array( $this, 'add_duplicate_button' ) );
			add_action( 'wp_ajax_duplicate_menu', array( $this, 'duplicate_menu' ) );
		}

		function plugins_loaded(){
			load_plugin_textdomain( 'bulk-menu-pro', FALSE, basename( __DIR__ ) . '/languages/' );
		}

		function admin_init(){
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 2 );
			add_meta_box( 'bulk_menu_pro', __( 'Bulk menu creator', 'bulk-menu-pro' ), array( $this, 'bulk_menu_creator_meta_box' ), 'nav-menus', 'side', 'high' );
			add_meta_box( 'bulk_menu_pro_extra_items', __( 'Bulk menu extras', 'bulk-menu-pro' ), array( $this, 'bulk_menu_extras_meta_box' ), 'nav-menus', 'side', 'high' );
		}

		function admin_enqueue_scripts(){
			$screen = get_current_screen();
			if( empty( $screen ) || $screen->base !== 'nav-menus' ) return;

			wp_enqueue_script( 'bulk_menu_pro', plugins_url( '/js/nav-menu.js', __FILE__ ), array('jquery'), 1, 1 );
			wp_enqueue_script( 'bulk_menu_pro_extra_items', plugins_url( '/js/extra-items.js', __FILE__ ), array('jquery'), 1, 1 );
			wp_enqueue_script( 'bulk_menu_pro_quick_copy', plugins_url( '/js/quick-copy.js', __FILE__ ), array('jquery'), 1, 1 );
			wp_enqueue_script( 'bulk_menu_pro_quick_delete', plugins_url( '/js/quick-delete.js', __FILE__ ), array('jquery'), 1, 1 );
			echo '<style>#menu-to-edit :is(.quick-delete,.quick-copy){position:relative;display:inline-block;vertical-align:text-bottom;color:#a00;opacity:0}#menu-to-edit .menu-item-handle:hover :is(.quick-delete,.quick-copy){opacity:1}#menu-to-edit :is(.quick-delete,.quick-copy):before{content:"\f182";font:normal 20px/1 dashicons;speak:never;display:block;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;text-decoration:none}#menu-to-edit .quick-copy:before{content:"\f105";color:#2196f3;margin-right:10px}#menu-to-edit .quick-copy .spinner{position:absolute;top:-4px;left:-30px}</style>';


			$taxonomies = get_taxonomies( array( 'public' => 1 ), 'objects' );
			$taxonomies_data = array();
			foreach( $taxonomies as $taxonomy ){
				$taxonomies_data[ esc_attr( $taxonomy->name ) ] = esc_html( $taxonomy->label . ' (' . $taxonomy->name . ')' );
			}

			$taxonomies_values = array();
			$items = get_posts(array(
				'numberposts' => -1,
				'nopaging' => true,
				'post_type' => 'nav_menu_item',
				'fields' => 'ids',
				'meta_key' => '_emi_taxonomy_menu_item',
			));
			foreach( $items as $item ){
				$taxonomies_values[ intval( $item ) ] = array(
					'taxonomy' => esc_attr( get_post_meta( $item, '_emi_taxonomy_menu_item', true ) ),
					'limit' => intval( get_post_meta( $item, '_emi_menu_item_limit', true ) ),
					'exclude' => esc_attr( get_post_meta( $item, '_emi_menu_item_exclude', true ) ),
					'levels' => intval( get_post_meta( $item, '_emi_menu_item_levels', true ) ),
					'orderby' => esc_attr( get_post_meta( $item, '_emi_menu_item_orderby', true ) ),
					'order' => esc_attr( get_post_meta( $item, '_emi_menu_item_order', true ) ),
					'showempty' => intval( get_post_meta( $item, '_emi_menu_item_showempty', true ) ),
					'showcount' => intval( get_post_meta( $item, '_emi_menu_item_showcount', true ) ),
				);
			}


			$posttypes = get_post_types( array( 'public' => true ), 'objects' );
			$posttypes_data = array();
			foreach( $posttypes as $posttype_slug => $posttype ){
				$posttypes_data[ esc_attr( $posttype_slug ) ] = esc_html( $posttype->label . ' (' . $posttype_slug . ')' );
			}

			$posttypes_values = array();
			$items = get_posts(array(
				'numberposts' => -1,
				'nopaging' => true,
				'post_type' => 'nav_menu_item',
				'fields' => 'ids',
				'meta_key' => '_emi_posttype_menu_item',
			));
			foreach( $items as $item ){
				$posttypes_values[ intval( $item ) ] = array(
					'posttype' => esc_attr( get_post_meta( $item, '_emi_posttype_menu_item', true ) ),
					'limit' => intval( get_post_meta( $item, '_emi_menu_item_limit', true ) ),
					'exclude' => esc_attr( get_post_meta( $item, '_emi_menu_item_exclude', true ) ),
					'levels' => intval( get_post_meta( $item, '_emi_menu_item_levels', true ) ),
					'terms' => explode( ',', get_post_meta( $item, '_emi_menu_item_terms', true ) ),
					'orderby' => esc_attr( get_post_meta( $item, '_emi_menu_item_orderby', true ) ),
					'order' => esc_attr( get_post_meta( $item, '_emi_menu_item_order', true ) ),
				);
			}


			$profile_values = array();
			$items = get_posts(array(
				'numberposts' => -1,
				'nopaging' => true,
				'post_type' => 'nav_menu_item',
				'fields' => 'ids',
				'meta_key' => '_emi_profile_menu_item',
			));
			foreach( $items as $item ){
				$label = esc_attr( get_post_meta( $item, '_emi_profile_menu_item', true ) );
				do_action( 'wpml_register_single_string', 'Bulk menu', 'profile_menu_item', $label );
				$profile_values[ intval( $item ) ] = array(
					'link' => esc_attr( get_post_meta( $item, '_emi_profile_link_menu_item', true ) ),
					'label' => $label,
				);
			}


			$terms_data = array();
			$terms_data_taxonomies = get_taxonomies( array(), 'objects' );
			foreach( $terms_data_taxonomies as $terms_data_taxonomy ){
				if( ! in_array( $terms_data_taxonomy->name, array( 'wp_template_part_area', 'wp_theme', 'nav_menu' ) ) ){
					$terms_data_terms = get_terms(array( 'taxonomy' => $terms_data_taxonomy->name ));
					$terms_data_terms = wp_list_pluck( $terms_data_terms, 'name', 'term_id' );
					if( is_array( $terms_data_terms ) && count( $terms_data_terms ) ){
						$terms_data[ $terms_data_taxonomy->name ] = array(
							'label' => $terms_data_taxonomy->label,
							'terms' => $terms_data_terms,
						);
					}
				}
			}


			$loginout_values = array();
			$items = get_posts(array(
				'numberposts' => -1,
				'nopaging' => true,
				'post_type' => 'nav_menu_item',
				'fields' => 'ids',
				'meta_key' => '_emi_login_menu_item',
			));
			foreach( $items as $item ){
				$login_url = esc_attr( get_post_meta( $item, '_emi_login_url_menu_item', true ) );
				do_action( 'wpml_register_single_string', 'Bulk menu', 'login_url_menu_item', $login_url );

				$login_redirect_url = esc_attr( get_post_meta( $item, '_emi_login_redirect_url_menu_item', true ) );
				do_action( 'wpml_register_single_string', 'Bulk menu', 'login_redirect_url_menu_item', $login_redirect_url );

				$login_label = esc_attr( get_post_meta( $item, '_emi_login_menu_item', true ) );
				do_action( 'wpml_register_single_string', 'Bulk menu', 'login_menu_item', $login_label );

				$logout_redirect_url = esc_attr( get_post_meta( $item, '_emi_logout_redirect_url_menu_item', true ) );
				do_action( 'wpml_register_single_string', 'Bulk menu', 'logout_redirect_url_menu_item', $logout_redirect_url );

				$logout_label = esc_attr( get_post_meta( $item, '_emi_logout_menu_item', true ) );
				do_action( 'wpml_register_single_string', 'Bulk menu', 'logout_menu_item', $logout_label );

				$loginout_values[ intval( $item ) ] = array(
					'login_url' => $login_url,
					'login_redirect_url' => $login_redirect_url,
					'login_label' => $login_label,

					'logout_redirect_url' => $logout_redirect_url,
					'logout_label' => $logout_label,
				);
			}

			$profile_links = array(
				'author_posts_url' => __( 'Author posts URL', 'bulk-menu-pro' ),
				'edit_profile_url' => __( 'Edit profile URL', 'bulk-menu-pro' ),
				'none' => __( 'None (#)', 'bulk-menu-pro' ),
			);

			if( class_exists('woocommerce') ){
				$profile_links['woo_myaccount'] = __( 'WooCommerce My account', 'bulk-menu-pro' );
				$profile_links['woo_orders'] = __( 'WooCommerce My orders', 'bulk-menu-pro' );
				$profile_links['woo_downloads'] = __( 'WooCommerce My downloads', 'bulk-menu-pro' );
				$profile_links['woo_addresses'] = __( 'WooCommerce My addresses', 'bulk-menu-pro' );
				$profile_links['woo_edit_account'] = __( 'WooCommerce Edit account', 'bulk-menu-pro' );
			}

			wp_localize_script( 'bulk_menu_pro', 'emi_data', array(
				'bulk_copy_button' => __( 'Copy', 'bulk-menu-pro' ),

				'bulk_delete' => __( 'Do you also want to delete all subitems?', 'bulk-menu-pro' ),
				'bulk_delete_button' => __( 'Delete', 'bulk-menu-pro' ),

				'taxonomies_title' => __( 'Taxonomy terms', 'bulk-menu-pro' ),
				'taxonomies_label' => __( 'Taxonomy', 'bulk-menu-pro' ),
				'taxonomies' => $taxonomies_data,
				'taxonomies_values' => $taxonomies_values,

				'posttypes_title' => __( 'Post type posts', 'bulk-menu-pro' ),
				'posttypes_label' => __( 'Post type', 'bulk-menu-pro' ),
				'posttypes' => $posttypes_data,
				'posttypes_values' => $posttypes_values,

				'limit_label' => __( 'Limit', 'bulk-menu-pro' ),
				'limit_help' => __( 'number of items to show (use -1 to show all items)', 'bulk-menu-pro' ),

				'exclude_label' => __( 'Exclude', 'bulk-menu-pro' ),
				'exclude_help' => __( 'enter the comma-separated IDs of items you want to exclude', 'bulk-menu-pro' ),

				'levels_label' => __( 'Levels', 'bulk-menu-pro' ),
				'levels_help' => __( 'number of sublevels to show (use -1 to show all or use 0 to show only top-level items)', 'bulk-menu-pro' ),

				'terms_data' => $terms_data,
				'terms_label' => __( 'Taxonomy terms', 'bulk-menu-pro' ),
				'terms_help' => __( 'use Ctrl+click to (de)select any item', 'bulk-menu-pro' ),

				'orderby_label' => __( 'Order by', 'bulk-menu-pro' ),
				'order_label' => __( 'Order', 'bulk-menu-pro' ),

				'showempty_label' => __( 'Show empty', 'bulk-menu-pro' ),
				'showempty_help' => __( 'Whether to show terms not assigned to any posts', 'bulk-menu-pro' ),

				'showcount_label' => __( 'Show count', 'bulk-menu-pro' ),
				'showcount_help' => __( 'Display number of assigned posts next to the term name', 'bulk-menu-pro' ),

				'loginout_title' => __( 'Login / Logout', 'bulk-menu-pro' ),
				'loginout_values' => $loginout_values,
				
				'login_url_label' => __( 'Login URL (or page ID)', 'bulk-menu-pro' ),
				'login_url_help' => __( 'Leave empty to use default wp_login_url', 'bulk-menu-pro' ),
				'login_redirect_url_label' => __( 'Login Redirect URL (or page ID)', 'bulk-menu-pro' ),
				'login_redirect_url_help' => __( 'Custom redirect user to this URL after login (only for default wp_login_url)', 'bulk-menu-pro' ),
				'login_label' => __( 'Login Navigation Label', 'bulk-menu-pro' ),
				'login_help' => __( 'Leave empty to hide login link.', 'bulk-menu-pro' ),
				
				'logout_redirect_url_label' => __( 'Logout Redirect URL (or page ID)', 'bulk-menu-pro' ),
				'logout_redirect_url_help' => __( 'Custom redirect user to this URL after logout', 'bulk-menu-pro' ),
				'logout_label' => __( 'Logout Navigation Label', 'bulk-menu-pro' ),
				'logout_help' => __( 'Leave empty to hide logout link.', 'bulk-menu-pro' ) . '<br>' . __( 'You can also use:', 'bulk-menu-pro' ) . ' <span style="user-select:all">{' . implode( '}</span>, <span style="user-select:all">{', array( 'display_name', 'first_name', 'last_name', 'nickname', 'user_email' ) ) . '}</span>',

				'profile_title' => __( 'Profile', 'bulk-menu-pro' ),
				'profile_link_label' => __( 'URL', 'bulk-menu-pro' ),
				'profile_link' => $profile_links,
				'profile_label' => __( 'Navigation Label', 'bulk-menu-pro' ),
				'profile_help' => __( 'You can also use:', 'bulk-menu-pro' ) . ' <span style="user-select:all">{' . implode( '}</span>, <span style="user-select:all">{', array( 'display_name', 'first_name', 'last_name', 'nickname', 'user_email' ) ) . '}</span>',
				'profile_values' => $profile_values,
			));
		}

		function wp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0 ){
			if( isset( $_POST['menu-item-url'], $_POST['menu-item-url'][ $menu_item_db_id ] ) && current_user_can( 'edit_theme_options' ) ){
				check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );
				if( $_POST['menu-item-url'][ $menu_item_db_id ] == '#emi_taxonomy' && isset( $_POST['menu-item-taxonomy'], $_POST['menu-item-taxonomy'][ $menu_item_db_id ] ) ){
					update_post_meta( $menu_item_db_id, '_emi_taxonomy_menu_item', sanitize_text_field( $_POST['menu-item-taxonomy'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_limit', intval( $_POST['menu-item-limit'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_exclude', sanitize_text_field( $_POST['menu-item-exclude'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_levels', intval( $_POST['menu-item-levels'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_orderby', sanitize_text_field( $_POST['menu-item-orderby'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_order', sanitize_text_field( $_POST['menu-item-order'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_showempty', intval( $_POST['menu-item-showempty'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_showcount', intval( $_POST['menu-item-showcount'][ $menu_item_db_id ] ) );
				}elseif( $_POST['menu-item-url'][ $menu_item_db_id ] == '#emi_posttype' && isset( $_POST['menu-item-posttype'], $_POST['menu-item-posttype'][ $menu_item_db_id ] ) ){
					update_post_meta( $menu_item_db_id, '_emi_posttype_menu_item', sanitize_text_field( $_POST['menu-item-posttype'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_limit', intval( $_POST['menu-item-limit'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_exclude', sanitize_text_field( $_POST['menu-item-exclude'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_levels', intval( $_POST['menu-item-levels'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_terms', is_array( $_POST['menu-item-terms'][ $menu_item_db_id ] ) ? implode( ',', $_POST['menu-item-terms'][ $menu_item_db_id ] ) : '' );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_orderby', sanitize_text_field( $_POST['menu-item-orderby'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_menu_item_order', sanitize_text_field( $_POST['menu-item-order'][ $menu_item_db_id ] ) );
				}elseif( $_POST['menu-item-url'][ $menu_item_db_id ] == '#emi_profile' && isset( $_POST['menu-item-profile'], $_POST['menu-item-profile'][ $menu_item_db_id ] ) ){
					update_post_meta( $menu_item_db_id, '_emi_profile_menu_item', sanitize_text_field( $_POST['menu-item-profile'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_profile_link_menu_item', sanitize_text_field( $_POST['menu-item-profile-link'][ $menu_item_db_id ] ) );
				}elseif( $_POST['menu-item-url'][ $menu_item_db_id ] == '#emi_loginout' && isset( $_POST['menu-item-login'], $_POST['menu-item-login'][ $menu_item_db_id ] ) ){
					update_post_meta( $menu_item_db_id, '_emi_login_url_menu_item', intval( $_POST['menu-item-login-url'][ $menu_item_db_id ] ) == $_POST['menu-item-login-url'][ $menu_item_db_id ] ? intval( $_POST['menu-item-login-url'][ $menu_item_db_id ] ) : sanitize_url( $_POST['menu-item-login-url'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_login_redirect_url_menu_item', intval( $_POST['menu-item-login-redirect-url'][ $menu_item_db_id ] ) == $_POST['menu-item-login-redirect-url'][ $menu_item_db_id ] ? intval( $_POST['menu-item-login-redirect-url'][ $menu_item_db_id ] ) : sanitize_url( $_POST['menu-item-login-redirect-url'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_login_menu_item', sanitize_text_field( $_POST['menu-item-login'][ $menu_item_db_id ] ) );

					update_post_meta( $menu_item_db_id, '_emi_logout_redirect_url_menu_item', intval( $_POST['menu-item-logout-redirect-url'][ $menu_item_db_id ] ) == $_POST['menu-item-logout-redirect-url'][ $menu_item_db_id ] ? intval( $_POST['menu-item-logout-redirect-url'][ $menu_item_db_id ] ) : sanitize_url( $_POST['menu-item-logout-redirect-url'][ $menu_item_db_id ] ) );
					update_post_meta( $menu_item_db_id, '_emi_logout_menu_item', sanitize_text_field( $_POST['menu-item-logout'][ $menu_item_db_id ] ) );
				}
			}
		}

		public function bulk_menu_creator_meta_box(){
			global $_nav_menu_placeholder;
			$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1; ?>
			<div id="bulk_menu_fields" class="posttypediv">
				<label>
					<?php _e( 'Menu items labels', 'bulk-menu-pro' ) ?><br>
					<textarea id="bulk-menu-labels" class="numbered"></textarea>
				</label>
				<label>
					<?php _e( 'Menu items URLs', 'bulk-menu-pro' ) ?><br>
					<textarea id="bulk-menu-urls" class="numbered"></textarea>
				</label>
				<button type="button" class="button-secondary right" id="process_bulk_menu_fields"><?php _e( 'Generate menu items', 'bulk-menu-pro' ) ?></button>
				<span class="spinner"></span>
				<div style="display:none">
					<div class="tabs-panel tabs-panel-active">
						<ul class="categorychecklist form-no-clear">
							<li>
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-object-id]" value="-1" checked="checked">
								<input type="text" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-type]" value="custom">
								<input type="text" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-title]" value="TEST">
								<input type="text" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-url]" value="#test">
							</li>
						</ul>
					</div>
					<p class="button-controls wp-clearfix">
						<span class="add-to-menu">
							<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php _e( 'Generate menu items', 'bulk-menu-pro' ) ?>" name="add-post-type-menu-item" id="submit-bulk_menu_fields">
							<span class="spinner"></span>
						</span>
					</p>
				</div>
			</div>
			<style>
				textarea.numbered{
					width: 100%;
					min-height: 75px;
					margin-bottom: 10px;
					padding: 5px 10px 5px 34px;
					font-family: Consolas, monaco, monospace;
					font-size: 12px;
					line-height: 1.35;
					background: url(<?php echo plugins_url( '/assets/lines.png', __FILE__ ) ?>) 0 -6px no-repeat;
					background-attachment: local;
				}
			</style><?php
		}

		function bulk_menu_extras_meta_box(){
			global $_nav_menu_placeholder, $nav_menu_selected_id;
			$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1; ?>
			<div id="posttype-bulk-menu-pro" class="posttypediv">
				<div id="tabs-panel-bulk-menu-pro" class="tabs-panel tabs-panel-active">
					<ul id="bulk-menu-pro-checklist" class="categorychecklist form-no-clear">
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-object-id]" value="-1"> <?php esc_html_e( 'Taxonomy terms', 'bulk-menu-pro' ) ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-title]" value="<?php esc_attr_e( 'Taxonomy terms', 'bulk-menu-pro' ) ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-url]" value="#emi_taxonomy">
						</li>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-object-id]" value="-1"> <?php esc_html_e( 'Post type posts', 'bulk-menu-pro' ) ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-title]" value="<?php esc_attr_e( 'Post type posts', 'bulk-menu-pro' ) ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-url]" value="#emi_posttype">
						</li>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-object-id]" value="-1"> <?php esc_html_e( 'Profile', 'bulk-menu-pro' ) ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-title]" value="<?php esc_attr_e( 'Profile', 'bulk-menu-pro' ) ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-url]" value="#emi_profile">
						</li>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-object-id]" value="-1"> <?php esc_html_e( 'Login / Logout', 'bulk-menu-pro' ) ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-title]" value="<?php esc_attr_e( 'Login / Logout', 'bulk-menu-pro' ) ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder ?>][menu-item-url]" value="#emi_loginout">
						</li>
					</ul>
				</div>
				<p class="button-controls">
					<span class="add-to-menu">
						<input type="button" <?php disabled( $nav_menu_selected_id, 0 ) ?> class="button-secondary right" value="<?php esc_attr_e( 'Add to Menu', 'bulk-menu-pro' ); ?>" id="submit-extras-bulk-menu-pro">
						<span class="spinner"></span>
					</span>
				</p>
			</div><?php
		}

		function usort_menu_items( $a, $b ){
			return $a->menu_order < $b->menu_order ? -1 : 1;
		}

		function get_terms_hierarchy( $taxonomy, $parent = 0, $item = null ){
			global $scporder;
			$hierarchy = array();
			$limit = intval( get_post_meta( $item->ID, '_emi_menu_item_limit', true ) );
			if( $limit < 0 ){
				$limit = 0;
			}
			$exclude = esc_attr( get_post_meta( $item->ID, '_emi_menu_item_exclude', true ) );
			$levels = intval( get_post_meta( $item->ID, '_emi_menu_item_levels', true ) );
			$orderby = esc_attr( get_post_meta( $item->ID, '_emi_menu_item_orderby', true ) );
			$order = esc_attr( get_post_meta( $item->ID, '_emi_menu_item_order', true ) );
			$showempty = intval( get_post_meta( $item->ID, '_emi_menu_item_showempty', true ) );
			$showcount = intval( get_post_meta( $item->ID, '_emi_menu_item_showcount', true ) );
			if( $levels == -1 || $this->current_level <= $levels ){
				$args = array(
					'taxonomy' => $taxonomy,
					'parent' => $parent,
					'number' => $limit,
					'count' => true,
				);
				if( trim( $exclude ) ){
					$args['exclude'] = $exclude;
				}
				if( $showempty ){
					$args['hide_empty'] = false;
				}
				if( $orderby && $orderby != 'default' ){
					if( isset( $scporder ) ){
						remove_filter( 'get_terms_orderby', array( $scporder, 'scporder_get_terms_orderby'), 10 );
						remove_filter( 'wp_get_object_terms', array( $scporder, 'scporder_get_object_terms'), 10 );
						remove_filter( 'get_terms', array( $scporder, 'scporder_get_object_terms'), 10 );
					}
					$args['orderby'] = $orderby;
				}
				if( $order && $order != 'default' ){
					if( isset( $scporder ) ){
						remove_filter( 'get_terms_orderby', array( $scporder, 'scporder_get_terms_orderby'), 10 );
						remove_filter( 'wp_get_object_terms', array( $scporder, 'scporder_get_object_terms'), 10 );
						remove_filter( 'get_terms', array( $scporder, 'scporder_get_object_terms'), 10 );
					}
					$args['order'] = $order;
				}
				$terms = get_terms( $args );
				if( count( $terms ) ){
					foreach( $terms as $term ){
						$term_item = clone $item;
						$term_item->ID = $term->term_id;
						$term_item->db_id = $item->ID . $term->term_id;
						$term_item->title = $term->name . ( $showcount ? apply_filters( 'bulk_menu_pro_count', ' <span class="count">(' . $term->count . ')</span>', $term ) : '' );
						$term_item->attr_title = '';
						$term_item->url = get_term_link( $term );
						$term_item->menu_order += $this->menu_offset;
						if( $parent ){
							$term_item->menu_item_parent = $item->ID . $parent;
						}

						$this->menu_offset++;
						
						$this->current_level++;
						$children = $this->get_terms_hierarchy( $taxonomy, $term->term_id, $item );
						$this->current_level--;
						if( count( $children ) ){
							$term_item->classes[] = 'menu-item-has-children';
							$hierarchy = array_merge( $hierarchy, $children );
						}

						$hierarchy[] = $term_item;
					}
				}
			}
			if( isset( $scporder ) ){
				add_filter( 'get_terms_orderby', array( $scporder, 'scporder_get_terms_orderby'), 10, 3 );
				add_filter( 'wp_get_object_terms', array( $scporder, 'scporder_get_object_terms'), 10, 3 );
				add_filter( 'get_terms', array( $scporder, 'scporder_get_object_terms'), 10, 3 );
			}
			return $hierarchy;
		}

		function get_posts_hierarchy( $posttype, $parent = 0, $item = null ){
			global $scporder;
			$hierarchy = array();
			$limit = intval( get_post_meta( $item->ID, '_emi_menu_item_limit', true ) );
			$exclude = esc_attr( get_post_meta( $item->ID, '_emi_menu_item_exclude', true ) );
			$levels = intval( get_post_meta( $item->ID, '_emi_menu_item_levels', true ) );
			$terms = explode( ',', esc_attr( get_post_meta( $item->ID, '_emi_menu_item_terms', true ) ) );
			$orderby = esc_attr( get_post_meta( $item->ID, '_emi_menu_item_orderby', true ) );
			$order = esc_attr( get_post_meta( $item->ID, '_emi_menu_item_order', true ) );
			if( $levels == -1 || $this->current_level <= $levels ){
				$args = array(
					'post_type' => $posttype,
					'posts_per_page' => $limit,
					'no_found_rows' => 1,
					'post_parent' => $parent,
				);
				if( trim( $exclude ) ){
					$args['post__not_in'] = explode( ',', $exclude );
				}
				if( $terms ){
					$tax_query_data = array();
					foreach( $terms as $term_id ){
						$term = get_term( $term_id );
						if( is_object( $term ) ){
							if( ! isset( $tax_query_data[ $term->taxonomy ] ) ){
								$tax_query_data[ $term->taxonomy ] = array();
							}
							$tax_query_data[ $term->taxonomy ][] = $term->term_id;
						}
					}
					$args['tax_query'] = array();
					foreach( $tax_query_data as $taxonomy => $terms ){
						$args['tax_query'][] = array(
							'taxonomy' => $taxonomy,
							'terms' => $terms,
							'operator' => 'IN'
						);
					}
				}
				if( $orderby && $orderby != 'default' ){
					if( isset( $scporder ) ){
						remove_action( 'pre_get_posts', array( $scporder, 'scporder_pre_get_posts' ) );
					}
					$args['orderby'] = $orderby;
				}
				if( $order && $order != 'default' ){
					if( isset( $scporder ) ){
						remove_action( 'pre_get_posts', array( $scporder, 'scporder_pre_get_posts' ) );
					}
					$args['order'] = $order;
				}
				$args = apply_filters( 'bmp_posts_args', $args );
				$the_query = new WP_Query( $args );
				while( $the_query->have_posts() ){
					$the_query->the_post();

					$post_id = get_the_ID();
					$post_title = get_the_title();
					$post_link = get_permalink();
					
					$post_item = clone $item;
					$post_item->ID = $post_id;
					$post_item->db_id = $item->ID . $post_id;

					$post_item->auto_listed = true;
					if( ! is_array( $post_item->classes ) ){
						$post_item->classes = array();
					}
					$post_item->classes[] = 'auto-listed';
					$post_item->classes[] = 'auto-' . $posttype;
					
					$post_item->title = $post_title;
					$post_item->attr_title = '';
					$post_item->url = $post_link;
					$post_item->menu_order += $this->menu_offset;
					if( $parent ){
						$post_item->menu_item_parent = $item->ID . $parent;
					}

					$this->menu_offset++;
					
					$this->current_level++;
					$children = $this->get_posts_hierarchy( $posttype, $post_id, $item );
					$this->current_level--;
					if( count( $children ) ){
						$post_item->classes[] = 'menu-item-has-children';
						$hierarchy = array_merge( $hierarchy, $children );
					}
					
					$hierarchy[] = $post_item;
				}
				wp_reset_postdata();
			}
			if( isset( $scporder ) ){
				add_action( 'pre_get_posts', array( $scporder, 'scporder_pre_get_posts' ) );
			}
			return $hierarchy;
		}

		function replace_user_info( $profile ){
			$user = wp_get_current_user();
			preg_match_all( '/\{([a-z_]+)\}/', $profile, $matches );
			if( isset( $matches[1], $matches[1][0] ) ){
				foreach( $matches[1] as $key ){
					if( isset( $user->{ $key } ) ){
						$profile = str_replace( '{' . $key . '}', $user->{ $key }, $profile );
					}
				}
			}
			return $profile;
		}

		function get_profile_item( $profile, $item ){
			// get translated user label string
			$profile = apply_filters( 'wpml_translate_single_string', $profile, 'Bulk menu', 'profile_menu_item' );
			// handle the user link
			$link = get_post_meta( $item->ID, '_emi_profile_link_menu_item', true );
			switch( $link ){
				case 'author_posts_url':
					$link = get_author_posts_url( get_current_user_id() );
					break;
				case 'edit_profile_url':
					$link = get_edit_profile_url( get_current_user_id() );
					break;
				case 'woo_myaccount':
					$link = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '#';
					break;
				case 'woo_orders':
					$link = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('orders') : '#';
					break;
				case 'woo_downloads':
					$link = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('downloads') : '#';
					break;
				case 'woo_addresses':
					$link = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('addresses') : '#';
					break;
				case 'woo_edit_account':
					$link = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('edit-account') : '#';
					break;
				default:
					$link = '#';
					break;
			}
			// create new menu item
			$profile_item = clone $item;
			$profile_item->menu_order += $this->menu_offset;
			$profile_item->auto_listed = true;
			if( ! is_array( $profile_item->classes ) ){
				$profile_item->classes = array();
			}
			$profile_item->classes[] = 'auto-listed';
			$profile_item->classes[] = 'auto-profile';
			$profile_item->title = $this->replace_user_info( $profile );
			$profile_item->attr_title = '';
			$profile_item->url = $link;
			
			$this->menu_offset++;

			return $profile_item;
		}

		function get_login_item( $login_label, $item ){
			// get translated user label string
			$login_url = get_post_meta( $item->ID, '_emi_login_url_menu_item', true );
			if( intval( $login_url ) == $login_url ){
				$login_url = apply_filters( 'wpml_object_id', $login_url, 'page', true );
				$login_url = get_permalink( $login_url );
			}else{
				$login_url = apply_filters( 'wpml_translate_single_string', $login_url, 'Bulk menu', 'login_url_menu_item' );
			}

			$login_redirect_url = get_post_meta( $item->ID, '_emi_login_redirect_url_menu_item', true );
			if( intval( $login_redirect_url ) == $login_redirect_url ){
				$login_redirect_url = apply_filters( 'wpml_object_id', $login_redirect_url, 'page', true );
				$login_redirect_url = get_permalink( $login_redirect_url );
			}else{
				$login_redirect_url = apply_filters( 'wpml_translate_single_string', $login_redirect_url, 'Bulk menu', 'login_redirect_url_menu_item' );
			}

			$login_label = apply_filters( 'wpml_translate_single_string', $login_label, 'Bulk menu', 'login_menu_item' );
			// create new menu item
			$login_item = clone $item;
			$login_item->menu_order += $this->menu_offset;
			$login_item->auto_listed = true;
			if( ! is_array( $login_item->classes ) ){
				$login_item->classes = array();
			}
			$login_item->classes[] = 'auto-listed';
			$login_item->classes[] = 'auto-login';
			$login_item->title = $login_label;
			$login_item->attr_title = '';
			// handle URLs
			if( $login_url ){
				$login_item->url = $login_url;
			}else{
				if( $login_redirect_url ){
					$login_item->url = wp_login_url( $login_redirect_url );
				}else{
					$login_item->url = wp_login_url();
				}
			}
			
			$this->menu_offset++;

			return $login_item;
		}

		function get_logout_item( $logout_label, $item ){
			// get translated user label string
			$logout_redirect_url = get_post_meta( $item->ID, '_emi_logout_redirect_url_menu_item', true );
			if( intval( $logout_redirect_url ) == $logout_redirect_url ){
				$logout_redirect_url = apply_filters( 'wpml_object_id', $logout_redirect_url, 'page', true );
				$logout_redirect_url = get_permalink( $logout_redirect_url );
			}else{
				$logout_redirect_url = apply_filters( 'wpml_translate_single_string', $logout_redirect_url, 'Bulk menu', 'logout_redirect_url_menu_item' );
			}
			$logout_label = apply_filters( 'wpml_translate_single_string', $logout_label, 'Bulk menu', 'logout_menu_item' );
			// create new menu item
			$logout_item = clone $item;
			$logout_item->menu_order += $this->menu_offset;
			$logout_item->auto_listed = true;
			if( ! is_array( $logout_item->classes ) ){
				$logout_item->classes = array();
			}
			$logout_item->classes[] = 'auto-listed';
			$logout_item->classes[] = 'auto-logout';
			$logout_item->title = $this->replace_user_info( $logout_label );
			$logout_item->attr_title = '';
			// handle URLs
			if( $logout_redirect_url ){
				$logout_item->url = wp_logout_url( $logout_redirect_url );
			}else{
				$logout_item->url = wp_logout_url();
			}
			
			$this->menu_offset++;

			return $logout_item;
		}

		function wp_get_nav_menu_items( $items ){

			if( is_admin() || doing_action( 'customize_register' ) ) return $items;

			usort( $items, array( $this, 'usort_menu_items' ) );

			$new_items = array();
			$this->menu_offset = 0;
			foreach( $items as $item ){
				if( ! in_array( $item->menu_item_parent, $this->hidden_parent ) ){
					if( $taxonomy = get_post_meta( $item->ID, '_emi_taxonomy_menu_item', true ) ){
						$terms = $this->get_terms_hierarchy( $taxonomy, 0, $item );
						$new_items = array_merge( $new_items, $terms );
					}elseif( $posttype = get_post_meta( $item->ID, '_emi_posttype_menu_item', true ) ){
						$posts = $this->get_posts_hierarchy( $posttype, 0, $item );
						$new_items = array_merge( $new_items, $posts );
					}elseif( $profile = get_post_meta( $item->ID, '_emi_profile_menu_item', true ) ){
						if( is_user_logged_in() ){
							$new_items[] = $this->get_profile_item( $profile, $item );
						}else{
							$this->hidden_parent[] = $item->ID;
						}
					}elseif( get_post_meta( $item->ID, '_emi_login_menu_item', true ) || get_post_meta( $item->ID, '_emi_logout_menu_item', true ) ){
						if( is_user_logged_in() ){
							$logout_label = get_post_meta( $item->ID, '_emi_logout_menu_item', true );
							if( $logout_label ){
								$new_items[] = $this->get_logout_item( $logout_label, $item );
							}else{
								$this->hidden_parent[] = $item->ID;
							}
						}else{
							$login_label = get_post_meta( $item->ID, '_emi_login_menu_item', true );
							if( $login_label ){
								$new_items[] = $this->get_login_item( $login_label, $item );
							}else{
								$this->hidden_parent[] = $item->ID;
							}
						}
					}else{
						$item->menu_order += $this->menu_offset;
						$new_items[] = $item;
					}
				}else{
					$this->hidden_parent[] = $item->ID;
				}
			}
			return $new_items;
		}

		function add_duplicate_button(){
			if( get_current_screen() && get_current_screen()->base == 'nav-menus' ){ ?>
				<script>
				jQuery(document).ready(function($){
					if( $('#menu-name').length && parseInt( $('#menu').val() ) ){
						$('#menu-name').after('&emsp;<button type="button" id="duplicate_menu" class="button button-secondary" style="vertical-align:middle"><?php _e( 'Duplicate this menu', 'bulk-menu-pro' ) ?></button><span class="spinner" style="float:none"></span>');

						$(document).on('click', '#duplicate_menu', function(e){
							e.preventDefault();
							let new_menu_name = '';
							if( new_menu_name = prompt('<?php _e( 'Duplicated Menu Name', 'bulk-menu-pro' ) ?>') ){
								$('#duplicate_menu').next('.spinner').addClass('is-active');
								$.post( ajaxurl, {
									action: 'duplicate_menu',
									menu_id: $('#menu').val(),
									menu_name: new_menu_name,
									nonce: '<?php echo wp_create_nonce( 'duplicate_menu_' . get_current_user_id() ) ?>'
								}, function( response ){
									if( response.substr(0,9) == 'SUCCESS::' ){
										window.location.href = response.substr(9);
									}else{
										alert( response );
										$('#duplicate_menu').next('.spinner').removeClass('is-active');
									}
								});
							}
						});
					}
				});
				</script><?php
			}
		}

		function duplicate_menu(){
			if( ! defined('DOING_AJAX') || ! DOING_AJAX ) die();
			
			if( ! isset( $_POST['nonce'], $_POST['menu_id'], $_POST['menu_name'] ) ) die();

			if( ! wp_verify_nonce( $_POST['nonce'], 'duplicate_menu_' . get_current_user_id() ) ) die();
			
			$menu_id = intval( $_POST['menu_id'] );
			$menu_name = sanitize_text_field( $_POST['menu_name'] );

			$menu_exists = wp_get_nav_menu_object( $menu_name );
			if( $menu_exists ) die( sprintf( __( 'Menu with name %s already exists!', 'bulk-menu-pro' ), $menu_name ) );

			$new_id = wp_create_nav_menu( $menu_name );
			if( is_wp_error( $new_id ) ){
				die( $new_id->get_error_message() );
			}

			$rel = array();
			$source_items = wp_get_nav_menu_items( $menu_id );
			foreach( $source_items as $menu_item ){
				$args = array(
					'menu-item-db-id' => $menu_item->db_id,
					'menu-item-object-id' => $menu_item->object_id,
					'menu-item-object' => $menu_item->object,
					'menu-item-parent-id' => $menu_item->menu_item_parent ? $rel[ $menu_item->menu_item_parent ] : 0,
					'menu-item-position' => $menu_item->menu_order,
					'menu-item-type' => $menu_item->type,
					'menu-item-title' => $menu_item->title,
					'menu-item-url' => $menu_item->url,
					'menu-item-description' => $menu_item->description,
					'menu-item-attr-title' => $menu_item->attr_title,
					'menu-item-target' => $menu_item->target,
					'menu-item-classes' => implode( ' ', $menu_item->classes ),
					'menu-item-xfn' => $menu_item->xfn,
					'menu-item-status' => $menu_item->post_status
				);

				$new_menu_item = wp_update_nav_menu_item( $new_id, 0, $args );
				$rel[ $menu_item->db_id ] = $new_menu_item;

				$metas = get_post_meta( $menu_item->object_id );
				unset( $metas['_wp_old_date'] );
				unset( $metas['_menu_item_type'] );
				unset( $metas['_menu_item_menu_item_parent'] );
				unset( $metas['_menu_item_object_id'] );
				unset( $metas['_menu_item_object'] );
				unset( $metas['_menu_item_target'] );
				unset( $metas['_menu_item_classes'] );
				unset( $metas['_menu_item_xfn'] );
				unset( $metas['_menu_item_url'] );
				unset( $metas['_menu_item_content'] );
				foreach( $metas as $key => $value ){
					update_post_meta( $new_menu_item, $key, maybe_unserialize( $value[0] ) );
				}
			}

			die( 'SUCCESS::' . admin_url( 'nav-menus.php?action=edit&menu=' . $new_id ) );
		}
	}

	$bulk_menu_pro_var = new bulk_menu_pro();
}