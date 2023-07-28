<?php

namespace FileBird\Controller;

defined( 'ABSPATH' ) || exit;

use FileBird\Model\Folder as FolderModel;

class UserSettings {
	protected static $instance = null;

	private $userId  = '';
	public $settings = array();

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

    public function __construct() {
		$this->userId   = get_current_user_id();
		$this->settings = $this->getAllSettings();
	}

	public function doHooks() {
		add_filter( 'fbv_data', array( $this, 'addUserSettingsData' ), 10, 1 );
	}

	public function getAllSettings() {
		return array(
			'default_folder'      => $this->getDefaultSelectedFolder(),
			'default_sort_files'  => $this->getDefaultSortFiles(),
			'folder_counter_type' => $this->getFolderCounterType(),
			'theme'               => $this->getCurrentTheme(),
			'showBreadCrumb'      => $this->isShowBreadCrumb(),
			// 'show_breadcrumb' =
		);
	}

	public function addUserSettingsData( $data ) {
		$data['user_settings'] = $this->settings;
		return $data;
	}

    public function getDefaultSortFiles() {
		return get_user_meta( $this->userId, '_njt_fbv_default_sort_files', true );
	}

    public function getCurrentTheme() {
		$theme = get_user_meta( get_current_user_id(), 'fbv_theme', true );

		if ( empty( $theme ) ) {
			$color = '#8f8f8f';
		}
		if ( 'windows' === $theme ) {
			$color = '#F3C73E';
		}
		if ( 'dropbox' === $theme ) {
			$color = '#88C1FC';
		}

		return array(
			'themeName'  => $theme,
			'themeColor' => $color,
		);
	}

    public function getDefaultSelectedFolder() {
		$folder_id = get_user_meta( $this->userId, '_njt_fbv_default_folder', true );
		$folder_id = intval( $folder_id );

		if ( $folder_id > 0 ) {
			if ( is_null( FolderModel::findById( $folder_id ) ) ) {
				$folder_id = -1;
			}
		}
		return $folder_id;
	}

    public function getFolderCounterType() {
        $type = get_user_meta( $this->userId, 'fbv_counter_type', true );
		return empty( $type ) ? 'counter_file_in_folder' : $type;
    }

    public function setDefaultSelectedFolder( $value ) {
		$value = (int) $value;
		update_user_meta( $this->userId, '_njt_fbv_default_folder', $value );
	}

    public function setDefaultSortFiles( $value ) {
		update_user_meta( $this->userId, '_njt_fbv_default_sort_files', $value );
	}

    public function setTheme( $theme ) {
		update_user_meta( $this->userId, 'fbv_theme', $theme );
    }

    public function setFolderCounterType( $type ) {
        update_user_meta( $this->userId, 'fbv_counter_type', $type );
    }

	public function isShowBreadCrumb() {
		return get_user_meta( $this->userId, 'fbv_show_breadcrumb', true );
	}

	public function setDisplayBreadCrumb( $value ) {
		update_user_meta( $this->userId, 'fbv_show_breadcrumb', $value );
	}
}