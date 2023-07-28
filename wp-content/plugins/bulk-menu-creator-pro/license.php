<?php

if( ! class_exists('DomainLicense') ){
	class DomainLicense{

		var $plugin_id, $plugin_slug, $plugin_name;

		public function __construct( $plugin_id = 0, $plugin_slug = '', $plugin_name = '' ){
			$this->plugin_id = intval( $plugin_id );
			$this->plugin_slug = sanitize_title( $plugin_slug );
			$this->plugin_name = sanitize_text_field( $plugin_name );

			add_action( 'admin_notices', array( $this, 'license_notice' ) );
			add_action( 'wp_ajax_verify_license', array( $this, 'verify_license' ) );
		}

		function license_notice(){
			// $is_licensed = get_option( $this->plugin_slug . '_license', false );
			$is_licensed = true;
			if( ! $is_licensed ){ ?>
				<div class="domain_license_key_notice error">
					<p><?php printf( __( 'Please enter your license key for %s', 'domainlicense' ), esc_html( $this->plugin_name ) ) ?></p>
					<form action="/" method="post" style="margin-bottom:12px">
						<input type="hidden" name="action" value="verify_license">
						<input type="hidden" name="plugin_id" value="<?php echo intval( $this->plugin_id ) ?>">
						<input type="hidden" name="plugin_slug" value="<?php echo esc_attr( $this->plugin_slug ) ?>">
						<?php wp_nonce_field( 'verify_license_' . get_current_user_id(), 'verify_license_nonce' ) ?>
						<input type="text" name="license_key" placeholder="<?php _e( 'Your license key', 'domainlicense' ) ?>" required>
						&nbsp;
						<button type="submit" class="button button-primary"><?php _e( 'Verify license', 'domainlicense' ) ?></button>
						<span class="spinner" style="float:none;margin:0 0 0 6px"></span>
					</form>
					<div class="error-message" style="margin-bottom:12px"></div>
				</div>
				<script>
				jQuery(document).ready(function($){
					$('.domain_license_key_notice form').on('submit', function(e){
						e.preventDefault();
						let $notice = $(this).closest('.domain_license_key_notice');
						let $spinner = $notice.find('.spinner');
						let $message = $notice.find('.error-message');
						$notice.css('pointer-events', 'none');
						$spinner.addClass('is-active');
						$.post( wp.ajax.settings.url, $(this).serialize(), function( response ){
							$notice.css('pointer-events', 'all');
							$spinner.removeClass('is-active');
							if( response.code == 'dlmfwc_rest_data_error' ){
								$message.text( response.message );
							}else if( response.code == 'dlmfwc_rest_data_success' ){
								$notice.removeClass('error').addClass('updated').html( '<p>' + response.message + '</p>' );
							}else{
								alert( response );
							}
						});
					});
				});
				</script><?php
			}
		}

		function verify_license(){
			if( defined('DOING_AJAX') && DOING_AJAX && check_ajax_referer( 'verify_license_' . get_current_user_id(), 'verify_license_nonce' ) ){
				if( isset( $_POST['license_key'] ) && $_POST['license_key'] ){
					$response = wp_remote_post(
						'https://wp-speedup.eu/wp-json/domain-license/activate',
						array(
							'method' => 'POST',
							'timeout' => 100,
							'redirection' => 1,
							'sslverify' => false,
							'body' => array(
								'plugin' => intval( $_POST['plugin_id'] ),
								'domain' => $_SERVER['SERVER_NAME'],
								'license_key' => sanitize_text_field( $_POST['license_key'] ),
							)
						)
					);

					$response_code = wp_remote_retrieve_response_code( $response );
					$response_body = wp_remote_retrieve_body( $response );
					if( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) == 200 ){
						update_option( sanitize_text_field( $_POST['plugin_slug'] ) . '_license', sanitize_text_field( $_POST['license_key'] ) );
					}
					wp_send_json( json_decode( $response_body ) );
				}
			}
			exit();
		}
	}
}