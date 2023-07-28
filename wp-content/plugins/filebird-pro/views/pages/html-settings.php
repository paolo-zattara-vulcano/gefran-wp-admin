<?php
defined( 'ABSPATH' ) || exit;

use FileBird\Classes\ActivePro;
use FileBird\Classes\Helpers;
use FileBird\Controller\Convert;
use FileBird\Controller\Import\ImportController;

$countFBOldFolder = apply_filters( 'fbv_update_database_notice', false ) ? 1 : Convert::countOldFolders();

$data_import = ImportController::get_all_plugins_import();

$tabs = array(
	array(
		'id'      => 'activation',
		'name'    => esc_html__( 'Activation', 'filebird' ),
		'content' => ActivePro::renderHtml(),
	),
	array(
		'id'      => 'settings',
		'name'    => esc_html__( 'Settings', 'filebird' ),
		'content' => Helpers::view(
			'pages/settings/tab-settings',
			array()
		),
	),
	array(
		'id'      => 'tools',
		'name'    => esc_html__( 'Tools', 'filebird' ),
		'content' => Helpers::view(
            'pages/settings/tab-tools',
			array( 'oldFolders' => $countFBOldFolder )
		),
	),
	array(
		'id'      => 'import',
		'name'    => esc_html__( 'Import/Export', 'filebird' ),
		'content' => Helpers::view(
			'pages/settings/tab-import',
			array(
				'data_import' => $data_import,
			)
		),
	),
);

$current_tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : $tabs[0]['id'] );
$tabs        = apply_filters( 'fbv_settings_tabs', $tabs );
?>
<div class="wrap">
    <h1><?php esc_html_e( 'FileBird Settings', 'filebird' ); ?></h1>
    <form action="options.php" method="POST" id="fbv-setting-form" autocomplete="off">
        <?php settings_fields( 'njt_fbv' ); ?>
        <?php do_settings_sections( 'njt_fbv' ); ?>
        <nav class="nav-tab-wrapper">
            <?php
			foreach ( $tabs as $k => $tab ) {
				$active = ( $tab['id'] == $current_tab ) ? 'nav-tab-active' : '';
				echo sprintf( '<a data-id="%s" href="#" class="nav-tab fbv-tab-name %s">%s</a>', esc_attr( $tab['id'] ), esc_attr( $active ), esc_html( $tab['name'] ) );
			}
			?>
        </nav>
        <?php
		foreach ( $tabs as $k => $tab ) {
			$class = ( $tab['id'] == $current_tab ) ? '' : 'hidden';
			echo sprintf( '<div id="fbv-settings-tab-%s" class="fbv-tab-content %s">%s</div>', esc_attr( $tab['id'] ), esc_attr( $class ), $tab['content'] );
		}
		?>
    </form>
</div>