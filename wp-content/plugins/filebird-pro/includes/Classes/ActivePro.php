<?php
namespace FileBird\Classes;

use FileBird\Classes\Helpers;

defined( 'ABSPATH' ) || exit;

class ActivePro {
	private $envato_login_url   = 'https://active.ninjateam.org/envato-login/';
	private $check_purchase_url = 'https://active.ninjateam.org/wp-admin/admin-ajax.php?action=njt_validate_code';

	public function __construct() {
		add_filter( 'fbv_data', array( $this, 'localize_fbv_data' ) );
		add_action( 'wp_ajax_fb_login_envato_success', array( $this, 'ajax_login_envato_success' ) );
		add_action( 'wp_ajax_fbv_deactivate_license', array( $this, 'ajax_fbv_deactivate_license' ) );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( ! class_exists( '\Puc_v4_Factory' ) ) {
			require_once NJFB_PLUGIN_PATH . '/includes/Lib/plugin-update-checker/plugin-update-checker.php';
		}

		$this->update_checker = \Puc_v4_Factory::buildUpdateChecker(
			'https://active.ninjateam.org/json/filebird.json',
			NJFB_PLUGIN_FILE, //Full path to the main plugin file or functions.php.
			'filebird_pro'
		);

		add_filter( 'puc_pre_inject_update-filebird_pro', array( $this, 'injectUpdate' ) );
		add_action( 'in_plugin_update_message-' . plugin_basename( NJFB_PLUGIN_FILE ), array( $this, 'in_plugin_update_message' ), 10, 2 );
	}
	public function in_plugin_update_message( $plugin_data, $version_info ) {
		if ( ! Helpers::isActivated() ) {
			echo '&nbsp;<strong><a href="' . esc_url(
				add_query_arg(
					array(
						'page' => 'filebird-settings',
						'tab'  => 'activation',
					),
					admin_url( 'options-general.php' )
				)
			) . '">' . esc_html__( 'Activate your license for automatic updates', 'filebird' ) . '</a></strong>.';
		}
	}
	public function ajax_login_envato_success() {
		$check_nonce = check_ajax_referer( 'njt_filebird_login_envato', 'nonce', false );
		if ( $check_nonce === false ) {
			exit( esc_html__( 'Validation failed (Nonce Errors), please try again later. Or you can <a href="https://ninjateam.org/support" target="_blank"><strong>contact support</strong></a>.', 'filebird' ) );
		}

		$purchase_code = isset( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';
		$email         = isset( $_GET['email'] ) ? sanitize_text_field( $_GET['email'] ) : '';
		$success       = isset( $_GET['success'] ) ? sanitize_text_field( $_GET['success'] ) : '';
		$error         = isset( $_GET['error'] ) ? sanitize_text_field( $_GET['error'] ) : '';
		$old_domain    = isset( $_GET['old_domain'] ) ? sanitize_text_field( $_GET['old_domain'] ) : '';

		if ( $success == true ) {
			$final_check = $this->remote_check_purchase_code( $purchase_code, $email );
			if ( $final_check['success'] ) {
				foreach ( $final_check['data'] as $k => $v ) {
					update_option( 'filebird_' . $k, $v );
				}
			}
		} else {
			update_option( 'filebird_activation_error', $error );
			update_option( 'filebird_activation_old_domain', $old_domain );
		}
		exit( '<script>window.close()</script>' );
	}
	public function ajax_fbv_deactivate_license() {
		check_ajax_referer( 'deactivate_license_nonce', 'nonce' );
		update_option( 'filebird_code', '' );
		update_option( 'filebird_email', '' );
		wp_send_json_success();
		exit;
	}
	public function injectUpdate( $update ) {
		if ( Helpers::isActivated() ) {
			$update->download_url = add_query_arg(
				array(
					'code'   => get_option( 'filebird_code', '' ),
					'email'  => get_option( 'filebird_email', '' ),
					'domain' => $this->get_domain(),
				),
				$update->download_url
			);
		} else {
			$update->download_url = null;
		}
		return $update;
	}
	private function remote_check_purchase_code( $code, $email, $plugin = 'filebird' ) {
		$domain   = $this->get_domain();
		$response = wp_remote_post(
			add_query_arg( array(), $this->check_purchase_url ),
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => array(
					'code'   => $code,
					'email'  => $email,
					'domain' => $domain,
					'plugin' => $plugin,
				),
			)
		);
		if ( ! is_wp_error( $response ) ) {
			$json = json_decode( $response['body'] );
			if ( $json->success ) {
				return array(
					'success' => true,
					'data'    => array(
						'code'            => $json->data->code,
						'email'           => $json->data->email,
						'supported_until' => $json->data->supported_until,
					),
				);
			}
			return array(
				'success' => false,
			);
		}
		return array(
			'success' => false,
		);
	}

	public function localize_fbv_data( $data ) {
		$return_args = array(
			'action' => 'fb_login_envato_success',
			'nonce'  => wp_create_nonce( 'njt_filebird_login_envato' ),
		);

		$return_url               = add_query_arg( $return_args, admin_url( 'admin-ajax.php' ) );
		$domain                   = $this->get_domain();
		$data['login_envato_url'] = esc_url(
			add_query_arg(
				array(
					'domain'     => $domain,
					'plugin'     => 'filebird',
					'return_url' => $return_url,
				),
				$this->envato_login_url
			)
		);

		$data['deactivate_license_nonce'] = wp_create_nonce( 'deactivate_license_nonce' );
		if ( ! isset( $data['i18n'] ) ) {
			$data['i18n'] = array();
		}
		$data['i18n']['active_to_update']                   = esc_html__( 'Please active license to update.', 'filebird' );
		$data['i18n']['deactivate_license_confirm_title']   = esc_html__( 'Deactivating license', 'filebird' );
		$data['i18n']['deactivate_license_confirm_content'] = esc_html__( 'Are you sure to deactivate the current license key? You will not get regular updates or any support for this site.', 'filebird' );
		$data['i18n']['deactivate_license_try_again']       = esc_html__( 'Please try again later!', 'filebird' );

		return $data;
	}
	public static function renderHtml() {
		$str = '';

		$filebird_activation_error = get_option( 'filebird_activation_error', '' );
		if ( $filebird_activation_error != '' ) {
			update_option( 'filebird_activation_error', '' );
		}

		$filebird_activation_old_domain = get_option( 'filebird_activation_old_domain', '' );
		if ( $filebird_activation_old_domain != '' ) {
			update_option( 'filebird_activation_old_domain', '' );
		}

		$str .= Helpers::view(
             'particle/activation_fail',
			array(
				'filebird_activation_error'      => $filebird_activation_error,
				'filebird_activation_old_domain' => $filebird_activation_old_domain,
			)
		);
		if ( ! Helpers::isActivated() ) {
			$str .= Helpers::view( 'pages/settings/tab-active' );
		} else {
			$str .= Helpers::view( 'pages/settings/tab-activated' );
		}

		return $str;
	}
	private function get_domain() {
		$url = get_bloginfo( 'url' );
		if ( $url == '' || $url == null ) {
			$url = home_url();
		}
		return preg_replace( '#https?:\/\/#', '', $url );
	}
}