<?php
namespace FileBird\Admin;

use FileBird\Controller\UserSettings;

defined( 'ABSPATH' ) || exit;
/**
 * Settings Page
 */
class Settings {
	private $pageId = null;

	public function __construct() {
		add_filter( 'plugin_action_links_' . NJFB_PLUGIN_BASE_NAME, array( $this, 'addActionLinks' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'settingsMenu' ) );
		add_action( 'wp_ajax_fbv_save_settings', array( $this, 'saveSettings' ) );
	}

	public function settingsMenu() {
		$GLOBALS['fbv_settings_screen_id'] = add_submenu_page(
			'options-general.php',
			__( 'FileBird', 'filebird' ),
			__( 'FileBird', 'filebird' ),
			'manage_options',
			$this->getPageId(),
			array( $this, 'settingsPage' )
		);
	}

	public function settingsPage() {
		include_once NJFB_PLUGIN_PATH . 'views/pages/html-settings.php';
	}

	public function plugin_row_meta( $links, $file ) {
		if ( strpos( $file, 'filebird.php' ) !== false ) {
			$new_links = array(
				'doc' => '<a href="https://ninjateam.gitbook.io/filebird/" target="_blank">' . __( 'Documentation', 'filebird' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	public function addActionLinks( $links ) {
		$settingsLinks = array(
			'<a href="' . admin_url( 'options-general.php?page=' . $this->getPageId() ) . '">Settings</a>',
		);

		return array_merge( $settingsLinks, $links );
	}

	public function getPageId() {
		if ( null == $this->pageId ) {
			$this->pageId = NJFB_PREFIX . '-settings';
		}

		return $this->pageId;
	}

	public function saveSettings() {
		check_ajax_referer( 'fbv_nonce', 'nonce', true );

		$theme             = sanitize_key( $_POST['theme'] );
		$folderCounterType = sanitize_key( $_POST['folderCounterType'] );
		$folderPerUser     = rest_sanitize_boolean( $_POST['folderPerUser'] );
		$showBreadcrumb    = rest_sanitize_boolean( $_POST['showBreadCrumb'] );

		if ( 'default' === $theme ) {
			$theme = '';
        }

		$userSettings = UserSettings::getInstance();

		$userSettings->setTheme( $theme );
		$userSettings->setFolderCounterType( $folderCounterType );
		$userSettings->setDisplayBreadCrumb( $showBreadcrumb );
		update_option( 'njt_fbv_folder_per_user', $folderPerUser );

		return wp_send_json_success(
			array( 'mess' => __( 'Settings saved!', 'filebird' ) )
		);
	}
}