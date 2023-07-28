<?php

namespace FileBird\Controller;

use FileBird\Controller\Convert as ConvertController;
use FileBird\Controller\Exclude;
use FileBird\Controller\UserSettings;
use FileBird\Controller\Attachment\SizeMeta;
use FileBird\Controller\FolderUser;
use FileBird\Controller\Api;

use FileBird\Model\Folder as FolderModel;
use FileBird\Classes\Helpers as Helpers;
use FileBird\Classes\Tree;
use FileBird\Controller\Import\ImportController;
use FileBird\I18n as I18n;

defined( 'ABSPATH' ) || exit;
class Folder extends Controller {
	protected static $instance = null;
	private $userSettings      = null;
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public function __construct() {
	}

	private function doHooks() {
		add_filter( 'media_library_infinite_scrolling', '__return_true' );
		add_filter( 'ajax_query_attachments_args', array( $this, 'ajaxQueryAttachmentsArgs' ), 20 );
		add_filter( 'mla_media_modal_query_final_terms', array( $this, 'ajaxQueryAttachmentsArgs' ), 20 );
		add_filter( 'restrict_manage_posts', array( $this, 'restrictManagePosts' ) );
		add_filter( 'posts_clauses', array( $this, 'postsClauses' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), 10, 2 );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
		add_action( 'rest_api_init', array( $this, 'registerRestFields' ) );
		add_action( 'add_attachment', array( $this, 'addAttachment' ) );
		add_action( 'delete_attachment', array( $this, 'deleteAttachment' ) );
		add_action( 'pre-upload-ui', array( $this, 'actionPluploadUi' ) );
		add_action( 'wp_ajax_fbv_first_folder_notice', array( $this, 'ajax_first_folder_notice' ) );
		if( class_exists( '\ZipStream\ZipStream' ) ) {
			add_action( 'wp_ajax_fbv_download_folder', array( $this, 'ajaxDownloadFolder' ) );
		} else {
			add_action( 'wp_ajax_fbv_download_folder', array( $this, 'ajaxDownloadFolderO' ) );
		}
		
		add_action( 'admin_notices', array( $this, 'adminNotices' ) );
		add_action( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );

		$this->userSettings = UserSettings::getInstance();

		SizeMeta::getInstance()->doHooks();
		new Exclude();
		new FolderUser();
		new Api();

		$this->check_update_database();
		// MailPoet plugin support
		add_filter( 'mailpoet_conflict_resolver_whitelist_script', array( $this, 'mailpoet_conflict_resolver_whitelist_script' ), 10, 1 );
		add_filter( 'mailpoet_conflict_resolver_whitelist_style', array( $this, 'mailpoet_conflict_resolver_whitelist_style' ), 10, 1 );

		add_filter( 'users_have_additional_content', array( $this, 'users_have_additional_content' ), 10, 2 );
		add_action( 'deleted_user', array( $this, 'deleted_user' ), 10, 3 );
	}

	public function admin_body_class( $classes ) {
		global $pagenow;

		$theme = $this->userSettings->getCurrentTheme();

		if ( ! empty( $theme['themeName'] ) && $pagenow === 'upload.php' ) {
			$classes .= ' filebird-custom-theme';
		}

		return $classes;
	}

	public function mailpoet_conflict_resolver_whitelist_script( $scripts ) {
		$scripts[] = 'filebird';
		$scripts[] = 'filebird-pro';
		return $scripts;
	}

	public function mailpoet_conflict_resolver_whitelist_style( $styles ) {
		$styles[] = 'filebird';
		$styles[] = 'filebird-pro';
		return $styles;
	}

	public function adminNotices() {
		global $pagenow;

		$optionFirstFolder = get_option( 'fbv_first_folder_notice' );
		if ( FolderModel::countFolder() === 0 && $pagenow !== 'upload.php' &&
		( $optionFirstFolder === false || time() >= intval( $optionFirstFolder ) ) ) {
			include NJFB_PLUGIN_PATH . '/views/notices/html-notice-first-folder.php';
		}

		if ( $pagenow !== 'upload.php' && apply_filters( 'fbv_update_database_notice', false ) ) {
			include NJFB_PLUGIN_PATH . '/views/notices/html-notice-update-database.php';
		}
	}

	public function check_update_database() {
		$is_converted = get_option( 'fbv_old_data_updated_to_v4', '0' );
		if ( $is_converted !== '1' ) {
			if ( ConvertController::countOldFolders() > 0 && ! isset( $_GET['autorun'] ) ) {
				add_filter( 'fbv_update_database_notice', '__return_true' );
			}
		}
	}

	public function ajax_first_folder_notice() {
		check_ajax_referer( 'fbv_nonce', 'nonce', true );
		update_option( 'fbv_first_folder_notice', time() + 30 * 60 * 60 * 24 ); //After 3 months show
		wp_send_json_success();
	}

	public function registerRestFields() {
		register_rest_route(
			NJFB_REST_URL,
			'get-folders',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'ajaxGetFolder' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'gutenberg-get-folders',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'ajaxGutenbergGetFolder' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);

		register_rest_route(
			NJFB_REST_URL,
			'new-folder',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxNewFolder' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'update-folder',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxUpdateFolder' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'update-folder-color',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxUpdateFolderColor' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'update-folder-ord',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxUpdateFolderOrd' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'delete-folder',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxDeleteFolder' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'set-folder-attachments',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxSetFolder' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'generate-attachment-size',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( SizeMeta::getInstance(), 'apiCallback' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'update-tree',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxUpdateTree' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'get-relations',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxGetRelations' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'set-settings',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ajaxSetSettings' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);

		register_rest_route(
			NJFB_REST_URL,
			'export-csv',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'exportCSV' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);

		register_rest_route(
			NJFB_REST_URL,
			'import-csv',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'importCSV' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
	}
	public function resPermissionsCheck() {
		 return current_user_can( 'upload_files' );
	}

	public function enqueueAdminScripts( $screenId ) {
		if ( function_exists( 'get_current_screen' ) ) {
			if ( $screenId == 'upload.php' ) {
				wp_register_script( 'jquery-resizable', NJFB_PLUGIN_URL . 'assets/js/jquery-resizable.min.js', array(), NJFB_VERSION, true );
				wp_enqueue_script( 'jquery-resizable' );
			}
		}

		if ( $screenId === 'settings_page_filebird-settings' ) {
			wp_enqueue_script( 'wp-pointer' );
        	wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'fbv-setting', NJFB_PLUGIN_URL . 'assets/js/setting.js', array( 'jquery' ), NJFB_VERSION, false );
        	wp_enqueue_style( 'fbv-pro-tippy', NJFB_PLUGIN_URL . 'assets/css/tippy.min.css', array(), NJFB_VERSION );
			wp_enqueue_style( 'fbv-active', NJFB_PLUGIN_URL . 'assets/css/active.css', array(), NJFB_VERSION );
		}

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch-fixed', NJFB_PLUGIN_URL . 'assets/js/jquery.ui.touch-punch.js', array( 'jquery-ui-widget', 'jquery-ui-mouse' ), NJFB_VERSION, false );
		}

		$theme     = $this->userSettings->getCurrentTheme();
		$themeName = '';

		if ( ! empty( $theme['themeName'] ) ) {
			$themeName = '-' . $theme['themeName'];
        }

		wp_enqueue_script( 'fbv-folder', NJFB_PLUGIN_URL . 'assets/dist/js/app.js', array(), NJFB_VERSION, false );
		wp_enqueue_script( 'fbv-lib', NJFB_PLUGIN_URL . 'assets/js/jstree/jstree.min.js', array(), NJFB_VERSION, false );

		wp_enqueue_style( 'fbv-folder', NJFB_PLUGIN_URL . "assets/dist/css/app{$themeName}.css", array(), NJFB_VERSION );
		wp_style_add_data( 'fbv-folder', 'rtl', 'replace' );
		wp_localize_script(
			'fbv-folder',
			'fbv_data',
			apply_filters(
				'fbv_data',
				array(
					'nonce'                  => wp_create_nonce( 'fbv_nonce' ),
					'rest_nonce'             => wp_create_nonce( 'wp_rest' ),
					'nonce_error'            => __( 'Your request can\'t be processed.', 'filebird' ),
					'current_folder'         => ( ( isset( $_GET['fbv'] ) ) ? (int) sanitize_text_field( $_GET['fbv'] ) : -1 ), //-1: all files. 0: uncategorized
					'folders'                => FolderModel::allFolders( 'id as term_id, name as term_name, name', array( 'term_id', 'term_name' ) ),
					'relations'              => FolderModel::getRelations(),
					'is_upload_screen'       => 'upload.php' === $screenId ? '1' : '0',
					'i18n'                   => I18n::getTranslation(),
					'media_mode'             => get_user_option( 'media_library_mode', get_current_user_id() ),
					'json_url'               => apply_filters( 'filebird_json_url', rtrim( rest_url( NJFB_REST_URL ), '/' ) ),
					'media_url'              => admin_url( 'upload.php' ),
					'data_import'            => ImportController::get_notice_import( $screenId ),
					'data_import_url'        => esc_url(
						add_query_arg(
							array(
								'page' => 'filebird-settings',
								'tab'  => 'import',
							),
							admin_url( 'options-general.php' )
						)
					),
					'auto_import_url'        => esc_url(
						add_query_arg(
							array(
								'page'    => 'filebird-settings',
								'tab'     => 'tools',
								'autorun' => 'true',
							),
							admin_url( '/options-general.php' )
						)
					),
					'pll_lang'               => apply_filters( 'fbv_pll_lang', '' ),
					'is_new_user'            => get_option( 'fbv_is_new_user', false ),
					'sort_folder'            => get_option( 'njt_fb_sort_folder', 'reset' ),
					'theme'                  => $theme,
					'update_database_notice' => apply_filters( 'fbv_update_database_notice', false ),
					'utils'                  => new \stdClass(),
				)
			)
		);
	}

	public function restrictManagePosts() {
		$screen = get_current_screen();
		if ( $screen->id == 'upload' ) {
			$fbv     = ( ( isset( $_GET['fbv'] ) ) ? (int) sanitize_text_field( $_GET['fbv'] ) : -1 );
			$folders = FolderModel::allFolders();

			$all       = new \stdClass();
			$all->id   = -1;
			$all->name = __( 'All Folders', 'filebird' );

			$uncategorized       = new \stdClass();
			$uncategorized->id   = 0;
			$uncategorized->name = __( 'Uncategorized', 'filebird' );

			array_unshift( $folders, $all, $uncategorized );
			echo '<select name="fbv" id="filter-by-fbv" class="fbv-filter attachment-filters fbv">';
			foreach ( $folders as $k => $folder ) {
				echo sprintf( '<option value="%1$d" %3$s>%2$s</option>', esc_html( $folder->id ), esc_html( $folder->name ), selected( $folder->id, $fbv, false ) );
			}
			echo '</select>';
		}
	}

	public function postsClauses( $clauses, $query ) {
		global $wpdb;
		if ( $query->get( 'post_type' ) !== 'attachment' ) {
			return $clauses;
		}

		if ( $query->get( 'orderby' ) === 'fb_filename' ) {
			$clauses['fields'] .= ' ,SUBSTRING_INDEX(postmeta.meta_value, \'/\', -1) as postfilename ';
			$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} AS postmeta ON ({$wpdb->posts}.ID = postmeta.post_id AND postmeta.meta_key = '_wp_attached_file') ";
			$clauses['orderby'] = " CAST(postfilename AS UNSIGNED), postfilename {$query->query['order']} ";
		}

		if ( Helpers::isListMode() && ! isset( $_GET['fbv'] ) ) {
			return $clauses;
		}

		$sizeMeta    = SizeMeta::getInstance()->meta_key;
		$fbvProperty = $query->get( 'fbv' );

		if ( $query->get( 'orderby' ) === $sizeMeta ) {
			$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} AS fbmt ON ({$wpdb->posts}.ID = fbmt.post_id AND fbmt.meta_key = '{$sizeMeta}') ";
			$clauses['orderby'] = " fbmt.meta_value + 0 {$query->query['order']} ";
		}

		if ( isset( $_GET['fbv'] ) || $fbvProperty !== '' ) {
			$fbv = isset( $_GET['fbv'] ) ? (int) sanitize_text_field( $_GET['fbv'] ) : (int) $fbvProperty;

			if ( $fbv === -1 ) {
				return $clauses;
			} elseif ( $fbv === 0 ) {
				$clauses = FolderModel::getRelationsWithFolderUser( $clauses );
			} else {
				$clauses['join']  .= $wpdb->prepare( " LEFT JOIN {$wpdb->prefix}fbv_attachment_folder AS fbva ON fbva.attachment_id = {$wpdb->posts}.ID AND fbva.folder_id = %d ", $fbv );
				$clauses['where'] .= ' AND fbva.folder_id IS NOT NULL';
			}
		}
		return $clauses;
	}
	public function addAttachment( $post_id ) {
		$fbv = ( ( isset( $_REQUEST['fbv'] ) ) ? sanitize_text_field( $_REQUEST['fbv'] ) : '' );
		if ( $fbv != '' ) {
			if ( is_numeric( $fbv ) ) {
				$parent = $fbv;
			} else {
				$fbv    = explode( '/', ltrim( rtrim( $fbv, '/' ), '/' ) );
				$parent = (int) $fbv[0];
				if ( $parent < 0 ) {
					$parent = 0; //important
				}
				if ( apply_filters( 'fbv_auto_create_folders', true ) ) {
					unset( $fbv[0] );
					foreach ( $fbv as $k => $v ) {
						$parent = FolderModel::newOrGet( $v, $parent );
					}
				}
			}
			FolderModel::setFoldersForPosts( $post_id, $parent );
		}
	}
	public function deleteAttachment( $post_id ) {
		FolderModel::deleteFoldersOfPost( $post_id );
	}

	public function ajaxQueryAttachmentsArgs( $query ) {
		// phpcs:disable 
		if ( isset( $_REQUEST['query']['fbv'] ) ) {
			$fbv = $_REQUEST['query']['fbv'];
			if ( is_array( $fbv ) ) {
				$fbv = array_map( 'intval', $fbv );
			} else {
				$fbv = intval( $fbv );
			}
			$query['fbv'] = $fbv;
		}
		return $query;
	}
	public function ajaxGetFolder( $request ) {
		$icl_lang = $request->get_param( 'icl_lang' );
		$pll_lang = $request->get_param( 'pll_lang' );
		$sort     = sanitize_text_field( $request->get_param( 'sort' ) );

		$order_by    = null;
		$sort_option = 'reset';

		$lang = null;

		if ( ! is_null( $icl_lang ) ) {
			$lang = $icl_lang;
		}
		if ( ! is_null( $pll_lang ) ) {
			$lang = $pll_lang;
		}

		if ( \in_array( $sort, array( 'name_asc', 'name_desc', 'reset' ) ) ) {
			$order_by = $sort;
			$sort_option = $sort;
			update_option( 'njt_fb_sort_folder', $sort_option );
		} else {
			$njt_fb_sort_folder = get_option( 'njt_fb_sort_folder', 'reset' );
			if ( $njt_fb_sort_folder === 'reset' ) {
				$order_by = null;
			} elseif ( $njt_fb_sort_folder === 'name_asc' || $njt_fb_sort_folder === 'name_desc' ) {
				$order_by = $njt_fb_sort_folder;
			}
		}

		$tree = Tree::getFolders( $order_by );

		wp_send_json_success(
			array(
				'tree'         => $tree,
				'folder_count' => array(
					'total'   => Tree::getCount( -1, $lang ),
					'folders' => Tree::getAllFoldersAndCount( $lang ),
				),
			)
		);
	}
	public function ajaxGutenbergGetFolder() {
		$_folders = Tree::getFolders( null, true );
		$folders  = array(
			array(
				'value'    => 0,
				'label'    => __( 'Please choose folder', 'filebird' ),
				'disabled' => true,
			),
		);
		foreach ( $_folders as $k => $v ) {
			$folders[] = array(
				'value' => $v['id'],
				'label' => $v['text'],
			);
		}

		wp_send_json_success( $folders );
	}
	public function ajaxUpdateFolderColor( $request ) {
		$id    = sanitize_key( $request->get_param( 'folder_id' ) );
		$color = sanitize_hex_color( $request->get_param( 'color' ) );

		$option        = get_option( 'fbv_folder_colors', array() );
		$option[ $id ] = $color;

		update_option( 'fbv_folder_colors', $option );

		wp_send_json_success();
	}
	public function ajaxNewFolder( $request ) {
		$name   = $request->get_param( 'name' );
		$parent = $request->get_param( 'parent' );
		$name   = isset( $name ) ? sanitize_text_field( wp_unslash( $name ) ) : '';
		$parent = isset( $parent ) ? sanitize_text_field( $parent ) : '';
		if ( $name != '' && $parent != '' ) {
			$insert = FolderModel::newOrGet( $name, $parent, false );
			if ( $insert !== false ) {
				wp_send_json_success( array( 'id' => $insert ) );
			} else {
				wp_send_json_error( array( 'mess' => __( 'A folder with this name already exists. Please choose another one.', 'filebird' ) ) );
			}
		} else {
			wp_send_json_error(
				array(
					'mess' => __( 'Validation failed', 'filebird' ),
				)
			);
		}
	}
	public function ajaxUpdateFolder( $request ) {
		$id     = $request->get_param( 'id' );
		$parent = $request->get_param( 'parent' );
		$name   = $request->get_param( 'name' );

		$id     = isset( $id ) ? sanitize_text_field( $id ) : '';
		$parent = isset( $parent ) ? intval( sanitize_text_field( $parent ) ) : '';
		$name   = isset( $name ) ? sanitize_text_field( wp_unslash( $name ) ) : '';
		if ( is_numeric( $id ) && is_numeric( $parent ) && $name != '' ) {
			$update = FolderModel::updateFolderName( $name, $parent, $id );
			if ( $update === true ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( array( 'mess' => __( 'A folder with this name already exists. Please choose another one.', 'filebird' ) ) );
			}
		}
		wp_send_json_error();
	}
	public function ajaxUpdateFolderOrd( $request ) {
		$id     = $request->get_param( 'id' );
		$parent = $request->get_param( 'parent' );
		$ord    = $request->get_param( 'ord' );

		$id     = isset( $id ) ? sanitize_text_field( $id ) : '';
		$parent = isset( $parent ) ? sanitize_text_field( wp_unslash( $parent ) ) : '';
		$ord    = isset( $ord ) ? sanitize_text_field( wp_unslash( $ord ) ) : '';
		if ( is_numeric( $id ) && is_numeric( $parent ) && is_numeric( $ord ) ) {
			FolderModel::updateOrdAndParent( $id, $ord, $parent );
			wp_send_json_success();
		}
		wp_send_json_error();
	}
	public function ajaxDeleteFolder( $request ) {
		$ids = $request->get_param( 'ids' );
		$ids = isset( $ids ) ? Helpers::sanitize_array( $ids ) : '';
		if ( $ids != '' ) {
			if ( ! is_array( $ids ) ) {
				$ids = array( $ids );
			}
			$ids = array_map( 'intval', $ids );

			foreach ( $ids as $k => $v ) {
				if ( $v > 0 ) {
					FolderModel::deleteFolderAndItsChildren( $v );
				}
			}
			wp_send_json_success();
		}
		wp_send_json_error(
			array(
				'mess' => __( 'Can\'t delete folder, please try again later', 'filebird' ),
			)
		);
	}
	public function ajaxSetFolder( $request ) {
		$ids    = $request->get_param( 'ids' );
		$folder = $request->get_param( 'folder' );

		$ids    = isset( $ids ) ? Helpers::sanitize_array( $ids ) : '';
		$folder = isset( $folder ) ? sanitize_text_field( $folder ) : '';
		if ( $ids != '' && is_array( $ids ) && is_numeric( $folder ) ) {
			FolderModel::setFoldersForPosts( $ids, $folder );
			wp_send_json_success();
		}
		wp_send_json_error(
			array(
				'mess' => __( 'Validation failed', 'filebird' ),
			)
		);
	}
	public function ajaxUpdateTree( $request ) {
		$tree = $request->get_param( 'tree' );

		$tree = isset( $tree ) ? sanitize_text_field( $tree ) : '';
		if ( $tree != '' ) {
			$tree = preg_replace( '#[^0-9,()]#', '', $tree );
			FolderModel::rawInsert( '(id, ord, parent) VALUES ' . $tree . ' ON DUPLICATE KEY UPDATE ord=VALUES(ord),parent=VALUES(parent)' );
			wp_send_json_success(
				array(
					'mess' => __( 'Folder tree has been updated.', 'filebird' ),
				)
			);
		}
		wp_send_json_error(
			array(
				'mess' => __( 'Validation failed', 'filebird' ),
			)
		);
	}
	public function ajaxGetRelations() {
		wp_send_json_success(
			array(
				'relations' => FolderModel::getRelations(),
			)
		);
	}
	public function ajaxSetSettings( $request ) {
		$folderId   = $request->get_param( 'folderId' );
		$sortSetting = $request->get_param( 'sortSetting' );
		
		$folderId   = isset( $folderId ) ? intval( $folderId ) : -1;
		$sortSetting = isset( $sortSetting ) ? sanitize_text_field( $sortSetting ) : 'default';

		$this->userSettings->setDefaultSelectedFolder( $folderId );
		$this->userSettings->setDefaultSortFiles( $sortSetting );
		
		wp_send_json_success();
	}
	public function ajaxDownloadFolder() {
		global $wpdb;

		check_ajax_referer( 'fbv_nonce', 'nonce', true );
		try {
			if( isset($_GET['do-download']) ) {
				if ( function_exists( 'set_time_limit' ) ) {
					@set_time_limit( 0 );
				} else {
					if ( function_exists( 'ini_set' ) ) {
						@ini_set( 'max_execution_time', 0 );
					}
				}
				$folder_id = ( ( isset( $_GET['folder_id'] ) ) ? intval( $_GET['folder_id'] ) : 0 );
				if ( $folder_id > 0 ) {
					$folder = FolderModel::getChildrenOfFolder( $folder_id, 0 );

					$options = new \ZipStream\Option\Archive();
					$options->setSendHttpHeaders(true);
					$options->setEnableZip64(false);

					$zipname = $folder->name . '-' . uniqid() . '-' . time() . '.zip';
					$zipname = apply_filters( 'fbv_download_filename', $zipname, $folder );

					$zip = new \ZipStream\ZipStream( $zipname, $options);

					$attachment_ids = Helpers::getAttachmentIdsByFolderId( $folder_id );
					$files = array();
					if( count($attachment_ids) > 0 ) {
						$files = $wpdb->get_col("SELECT meta_value from ".$wpdb->postmeta." where meta_key = '_wp_attached_file' AND post_id IN (".implode(',', $attachment_ids).")");
					}
					
					$uploads = wp_get_upload_dir();
					if ( false === $uploads['error'] ) {
						foreach($files as $file) {
							if ( $file && 0 !== strpos( $file, '/' ) && ! preg_match( '|^.:\\\|', $file ) ) {
								$file = $uploads['basedir'] . "/$file";
								// $zip->addFile( $file, \basename( $file ) );
								$zip->addFileFromPath( \basename( $file ), $file );
							}
						}
					}
					
					self::addFolderToZip( $zip, $folder->children, '' );
					$zip->finish();
				}
			} else {
				$link = add_query_arg( array(
					'nonce' => $_GET['nonce'],
					'action' => 'fbv_download_folder',
					'folder_id' => (int)$_GET['folder_id'],
					'do-download' => true
				), admin_url( 'admin-ajax.php' ) );
				if( is_ssl() && substr( $link, 0, 7 ) == 'http://' ) {
					$link = str_replace( 'http://', 'https://', $link );
				}
				wp_send_json_success(
					array(
						'link' => $link
					)
				);
			}
		} catch ( \Exception $ex ) {
			wp_send_json_error(
				array(
					'errorCode'    => $ex->getCode(),
					'errorMessage' => $ex->getMessage(),
				)
			);
		} catch ( \Error $ex ) {
			wp_send_json_error(
				array(
					'errorCode'    => $ex->getCode(),
					'errorMessage' => $ex->getMessage(),
				)
			);
		}
	}
	public function ajaxDownloadFolderO() {
		check_ajax_referer( 'fbv_nonce', 'nonce', true );
		try {
			$wp_dir = wp_upload_dir();
			$upload_folder = $wp_dir['path'] . DIRECTORY_SEPARATOR;

			$folder_id = ( ( isset( $_GET['folder_id'] ) ) ? intval( $_GET['folder_id'] ) : 0 );
			if ( $folder_id > 0 ) {
				$folder = FolderModel::getChildrenOfFolder( $folder_id, 0 );

				$zip = new \ZipArchive();

				$zipname = $folder->name . '-' . uniqid() . '-' . time() . '.zip';
				$zipname = apply_filters( 'fbv_download_filename', $zipname, $folder );
				$zip->open( $upload_folder . $zipname, \ZipArchive::CREATE );
				$attachment_ids = Helpers::getAttachmentIdsByFolderId( $folder_id );
				foreach ( $attachment_ids as $k => $id ) {
					$file = get_attached_file( $id );
					if ( $file ) {
						$zip->addFile( $file, \basename( $file ) );
					}
				}
				
				self::addFolderToZipO( $zip, $folder->children, '' );

				if(count($attachment_ids) == 0) {
					$zip->addEmptyDir('.');
				}
				$zip->close();

				//save path to database
				$saved_downloads = get_option( 'filebird_saved_downloads', array() );
				if( ! is_array($saved_downloads) ) {
					$saved_downloads = array();
				}
				$saved_downloads[time()] = $wp_dir['subdir'] . '/' . $zipname;
				update_option( 'filebird_saved_downloads', $saved_downloads );
				// end saving
				$link = trailingslashit( $wp_dir['url'] ) . $zipname;
				if( is_ssl() && substr( $link, 0, 7 ) == 'http://' ) {
					$link = str_replace( 'http://', 'https://', $link );
				}
				wp_send_json_success(
					array(
						'link' => $link
					)
				);
			}
		} catch ( \Exception $ex ) {
			wp_send_json_error(
				array(
					'errorCode'    => $ex->getCode(),
					'errorMessage' => $ex->getMessage(),
				)
			);
		} catch ( \Error $ex ) {
			wp_send_json_error(
				array(
					'errorCode'    => $ex->getCode(),
					'errorMessage' => $ex->getMessage(),
				)
			);
		}
	}
	public function attachment_fields_to_edit( $form_fields, $post ) {
		$screen = null;
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			if ( ! is_null( $screen ) && 'attachment' === $screen->id ) {
				return $form_fields;
			}
		}

		$fbv_folder         = FolderModel::getFolderFromPostId( $post->ID );
		$fbv_folder         = count( $fbv_folder ) > 0 ? $fbv_folder[0] : (object) array(
			'folder_id' => 0,
			'name'      => __( 'Uncategorized', 'filebird' ),
		);
		$form_fields['fbv'] = array(
			'html'  => "<div class='fbv-attachment-edit-wrapper' data-folder-id='{$fbv_folder->folder_id}' data-attachment-id='{$post->ID}'><input readonly type='text' value='{$fbv_folder->name}'/></div>",
			'label' => esc_html__( 'FileBird folder:', 'filebird' ),
			'helps' => esc_html__( 'Click on the button to move this file to another folder', 'filebird' ),
			'input' => 'html',
		);

		return $form_fields;
	}

	public function attachment_fields_to_save( $post, $attachment ) {
		if ( isset( $attachment['fbv'] ) ) {
			FolderModel::setFoldersForPosts( $post['ID'], $attachment['fbv'] );
		}
		return $post;
	}

	private static function addFolderToZip( &$zip, $children, $parent_dir = '' ) {
		global $wpdb;
		foreach ( $children as $k => $v ) {
			$folder_name = $v->name;
			$folder_id   = $v->id;

			// $folder_name = sanitize_title( $folder_name );

			$attachment_ids = Helpers::getAttachmentIdsByFolderId( $folder_id );
			$empty_dir      = $parent_dir != '' ? $parent_dir . '/' . $folder_name : $folder_name;
			// $zip->addEmptyDir( $empty_dir );
			$files = array();
			if( count( $attachment_ids ) > 0 ) {
				$files = $wpdb->get_col("SELECT meta_value from ".$wpdb->postmeta." where meta_key = '_wp_attached_file' AND post_id IN (".implode(',', $attachment_ids).")");
			}

			$uploads = wp_get_upload_dir();
			if ( false === $uploads['error'] ) {
				foreach($files as $file) {
					if ( $file && 0 !== strpos( $file, '/' ) && ! preg_match( '|^.:\\\|', $file ) ) {
						$file = $uploads['basedir'] . "/$file";
						$zip->addFileFromPath( $empty_dir . '/' . \basename( $file ), $file );
						// $zip->addFile( $file, $empty_dir . '/' . \basename( $file ) );
					}
				}
			}
			if ( \is_array( $v->children ) ) {
				self::addFolderToZip( $zip, $v->children, $empty_dir );
			}
		}
	}
	private static function addFolderToZipO( &$zip, $children, $parent_dir = '' ) {
		foreach ( $children as $k => $v ) {
			$folder_name = $v->name;
			$folder_id   = $v->id;

			// $folder_name = sanitize_title( $folder_name );

			$attachment_ids = Helpers::getAttachmentIdsByFolderId( $folder_id );
			$empty_dir      = $parent_dir != '' ? $parent_dir . '/' . $folder_name : $folder_name;
			$zip->addEmptyDir( $empty_dir );

			foreach ( $attachment_ids as $k => $id ) {
				$file = get_attached_file( $id );
				if ( $file ) {
					$zip->addFile( $file, $empty_dir . '/' . \basename( $file ) );
				}
			}
			if ( \is_array( $v->children ) ) {
				self::addFolderToZipO( $zip, $v->children, $empty_dir );
			}
		}
	}
	public function actionPluploadUi() {
		$this->loadView( 'particle/folder_dropdown' );
	}
	private function _buildQuery( $tree, $parent ) {
		$results = array();
		$ord     = 0;
		foreach ( $tree as $k => $v ) {
			// if($v['key'] < 1) continue;
			$results[] = sprintf( '(%1$d, %2$d, %3$d)', $v['id'], $ord, $parent );
			if ( isset( $v['children'] ) && is_array( $v['children'] ) && count( $v['children'] ) > 0 ) {
				$children = $this->_buildQuery( $v['children'], $v['id'] );
				foreach ( $children as $k2 => $v2 ) {
					$results[] = $v2;
				}
			}
			$ord++;
		}
		return $results;
	}

	public function getFlatTree( $data = array(), $parent = 0, $default = null, $level = 0 ) {
		$tree = is_null( $default ) ? array() : $default;
		foreach ( $data as $k => $v ) {
			if ( $v->parent == $parent ) {
				$node     = array(
					'title' => str_repeat( '-', $level ) . $v->name,
					'value' => $v->id,
				);
				$tree[]   = $node;
				$children = $this->getFlatTree( $data, $v->id, null, $level + 1 );
				foreach ( $children as $k2 => $child ) {
					$tree[] = $child;
				}
			}
		}
		return $tree;
	}

	public function exportCSV() {
		global $wpdb;

		$folders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fbv", ARRAY_A );

		return new \WP_REST_Response( array( 'folders' => $folders ) );
	}

	public function restoreFolderStructure( $folders ) {
		global $wpdb;
		$currentUserId = get_current_user_id();
		try {
			foreach ( $folders as $k => $folder ) {
				$new_parent = $folder['parent'];
				if ( intval( $new_parent ) > 0 ) {
					$new_parent = get_option( 'njt_new_term_id_' . $new_parent );
				}
				$data = array(
					'name'       => sanitize_text_field( $folder['name'] ),
					'parent'     => intval( $new_parent ),
					'type'       => 0,
					'ord'        => intval( $folder['ord'] ),
					'created_by' => get_option( 'njt_fbv_folder_per_user', '0' ) === '1' ? $currentUserId : 0,
				);

				$table    = "{$wpdb->prefix}fbv";
				$inserted = $wpdb->insert( $table, $data );
				update_option( 'njt_new_term_id_' . $folder['id'], $wpdb->insert_id );
			}
			return true;
		} catch ( \Throwable $th ) {
			return false;
		}
	}

	public function importCSV( \WP_REST_Request $request ) {
		$params  = $request->get_file_params();
		$handle  = \fopen( $params['file']['tmp_name'], 'r' );
		$data    = array();
		$columns = array();
		if ( false !== $handle ) {
			$count = 1;
			while ( 1 ) {
				$row = fgetcsv( $handle, 0 );
				if ( 1 === $count ) {
					$columns = $row;
					$count++;
					continue;
				}
				if ( false === $row ) {
					break;
				}
				foreach ( $columns as $key => $col ) {
					$tmp[ $col ] = $row[ $key ];
				}
				$data[] = $tmp;
			}
		}
		\fclose( $handle );

		$check = array_diff(
			$columns,
			array(
				'id',
				'name',
				'parent',
				'type',
				'ord',
				'created_by',
			)
		);

		if ( count( $check ) > 0 ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'The uploaded file was not generated by FileBird. Please check again.', 'filebird' ),
				)
			);
		}

		$result = $this->restoreFolderStructure( $data );

		return new \WP_REST_Response( array( 'success' => $result ) );
	}
	public function deleted_user( $id, $reassign, $user ) {
		if( $reassign === null ) {
			FolderModel::deleteByAuthor( $id );
		} else {
			FolderModel::updateAuthor( $id, (int) $reassign );
		}
	}
	public function users_have_additional_content( $users_have_content, $userids ) {
		global $wpdb;
		if ( $userids && ! $users_have_content ) {
			if ( $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}fbv WHERE created_by IN( " . implode( ',', $userids ) . ' ) LIMIT 1' ) ) {
				$users_have_content = true;
			}
		}
		return $users_have_content;
	}
}
