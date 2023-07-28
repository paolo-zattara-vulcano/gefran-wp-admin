<?php

namespace FileBird\Controller\Import;

defined( 'ABSPATH' ) || exit;

class ImportController {
    const NAMESPACE = 'import';

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        $this->add(
            'happyfiles',
            array(
				'name'     => 'HappyFiles',
				'author'   => 'Codeer',
				'taxonomy' => 'happyfiles_category',
			)
        );
        $this->add(
            'enhanced',
            array(
				'name'     => 'Enhanced Media Library',
				'author'   => 'wpUXsolutions',
				'taxonomy' => 'media_category',
			)
        );
        $this->add(
            'wpmf',
            array(
				'name'     => 'WP Media folder',
				'author'   => 'Joomunited',
				'taxonomy' => 'wpmf-category',
			)
        );
        $this->add(
            'premio',
            array(
				'name'     => 'Folders',
				'author'   => 'Premio',
				'taxonomy' => 'media_folder',
			)
        );
        $this->add(
            'feml',
            array(
				'name'     => 'WP Media Folders',
				'author'   => 'Damien Barrère',
				'taxonomy' => 'feml-folder',
			)
        );
        $this->add(
            'wpmlf',
            array(
                'name'   => 'WordPress Media Library Folders',
                'author' => 'Max Foundry',
            )
        );
        $this->add(
            'mla',
            array(
                'name'     => 'Media Library Assistant',
                'author'   => 'David Lingren',
                'taxonomy' => 'attachment_category',
            )
        );

        $this->add(
            'realmedia',
            array(
				'name'   => 'WP Real Media Library',
				'author' => 'devowl.io GmbH',
			)
        );
    }

    public function add( $prefix, $attributes ) {
        new DataImport( $prefix, $attributes );
    }

    public function get_all() {
        return DataImport::get();
    }

    public static function get_all_plugins_import() {
        $data_import         = DataImport::get();
        $total_folder_import = 0;
		foreach ( $data_import as $prefix => $data ) {
			$data_import[ $prefix ]->counter     = self::get_counters( $prefix );
            $data_import[ $prefix ]->completed   = get_option( 'njt_fb_updated_from_' . $data->prefix ) === '1';
            $data_import[ $prefix ]->noThanks    = get_option( "njt_fb_{$data->prefix}_no_thanks" ) === '1';
            $data_import[ $prefix ]->description = sprintf( esc_html__( 'We found you have %1$s categories you created from %2$s plugin. Would you like to import it to %3$s?', 'filebird' ), '<strong>(' . esc_html( $data_import[ $prefix ]->counter ) . ')</strong>', '<strong>' . esc_html( $data->name ) . '</strong>', '<strong>FileBird</strong>' );

            $total_folder_import += $data_import[ $prefix ]->counter;
		}
        return (object) array(
			'data'                => $data_import,
			'total_folder_import' => $total_folder_import,
		);
    }

    public static function get_notice_import( $screen ) {
        if ( $screen !== 'upload.php' ) {
            return (object) array( 'data' => new \stdClass() );
        }

        return self::get_all_plugins_import();
    }

    public function register_rest_routes() {
        register_rest_route(
			NJFB_REST_URL,
			self::NAMESPACE . '/get-folders/(?P<prefix>[a-zA-Z]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_folders' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

        register_rest_route(
			NJFB_REST_URL,
			self::NAMESPACE . '/get-attachments/(?P<prefix>[a-zA-Z]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_attachments' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

        register_rest_route(
			NJFB_REST_URL,
			self::NAMESPACE . '/run/(?P<prefix>[a-zA-Z]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'run_import' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
    }

    public function permission_callback() {
		return current_user_can( 'upload_files' );
    }

    public static function get_counters( $prefix ) {
        $import_method = ImportFactory::getImportMethod( $prefix );
        $data          = DataImport::get( $prefix );

        return $import_method->get_counters( $data );
    }

    public function get_folders( $request ) {
		$prefix = sanitize_key( $request->get_param( 'prefix' ) );

        $import_method = ImportFactory::getImportMethod( $prefix );
        $data          = DataImport::get( $prefix );

        return $import_method->get_folders( $data );
    }

    public function get_attachments( $request ) {
        $prefix = sanitize_key( $request->get_param( 'prefix' ) );

        $import_method = ImportFactory::getImportMethod( $prefix );
        $data          = DataImport::get( $prefix );

        return $import_method->get_attachments( $data );
    }

    public function run_import( $request ) {
        $prefix = sanitize_key( $request->get_param( 'prefix' ) );

        $import_method = ImportFactory::getImportMethod( $prefix );
        $data          = DataImport::get( $prefix );

        return $import_method->run( $data );
    }
}