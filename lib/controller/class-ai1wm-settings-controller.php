<?php
/**
 * Copyright (C) 2014-2018 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

class Ai1wm_Settings_Controller {

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public static function index() {
		Ai1wm_Template::render( 'main/settings' );
	}

	/**
	 * Save Google Drive API key via AJAX
	 *
	 * @return void
	 */
	public static function save_api_key() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You do not have permission to perform this action.', AI1WM_PLUGIN_NAME ),
			) );
		}

		// Check nonce
		if ( ! isset( $_POST['ai1wm_nonce'] ) || ! wp_verify_nonce( $_POST['ai1wm_nonce'], 'ai1wm_gdrive_settings' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security check failed.', AI1WM_PLUGIN_NAME ),
			) );
		}

		// Get API key from POST data
		$api_key = isset( $_POST['api_key'] ) ? trim( stripslashes( $_POST['api_key'] ) ) : '';

		// Save API key
		if ( empty( $api_key ) ) {
			delete_option( AI1WM_GDRIVE_API_KEY );
			wp_send_json_success( array(
				'message' => __( 'API key has been removed.', AI1WM_PLUGIN_NAME ),
			) );
		} else {
			update_option( AI1WM_GDRIVE_API_KEY, $api_key );
			wp_send_json_success( array(
				'message' => __( 'API key has been saved successfully.', AI1WM_PLUGIN_NAME ),
			) );
		}
	}

	/**
	 * Validate Google Drive API key via AJAX
	 *
	 * @return void
	 */
	public static function validate_api_key() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You do not have permission to perform this action.', AI1WM_PLUGIN_NAME ),
			) );
		}

		// Check nonce
		if ( ! isset( $_POST['ai1wm_nonce'] ) || ! wp_verify_nonce( $_POST['ai1wm_nonce'], 'ai1wm_gdrive_settings' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security check failed.', AI1WM_PLUGIN_NAME ),
			) );
		}

		// Get API key
		$api_key = isset( $_POST['api_key'] ) ? trim( stripslashes( $_POST['api_key'] ) ) : '';

		if ( empty( $api_key ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please enter an API key.', AI1WM_PLUGIN_NAME ),
			) );
		}

		// Test API key by making a simple request to Google Drive API
		// We'll use the 'about' endpoint which is lightweight
		$test_url = sprintf(
			'%s/about?key=%s&fields=user',
			AI1WM_GDRIVE_API_URL,
			$api_key
		);

		$response = wp_remote_get( $test_url, array(
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array(
				'message' => sprintf(
					__( 'Network error: %s', AI1WM_PLUGIN_NAME ),
					$response->get_error_message()
				),
			) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( $response_code === 200 ) {
			wp_send_json_success( array(
				'message' => __( 'API key is valid!', AI1WM_PLUGIN_NAME ),
			) );
		} elseif ( $response_code === 400 || $response_code === 403 ) {
			$body = wp_remote_retrieve_body( $response );
			$error_data = json_decode( $body, true );

			$error_message = isset( $error_data['error']['message'] )
				? $error_data['error']['message']
				: __( 'Invalid API key or API access denied.', AI1WM_PLUGIN_NAME );

			wp_send_json_error( array(
				'message' => $error_message,
			) );
		} else {
			wp_send_json_error( array(
				'message' => sprintf(
					__( 'Validation failed with HTTP error %d', AI1WM_PLUGIN_NAME ),
					$response_code
				),
			) );
		}
	}

	/**
	 * Check if Google Drive API key is configured via AJAX
	 *
	 * @return void
	 */
	public static function check_api_key() {
		$api_key = get_option( AI1WM_GDRIVE_API_KEY );

		if ( empty( $api_key ) ) {
			wp_send_json_error( array(
				'message' => __( 'Google Drive API key is not configured.', AI1WM_PLUGIN_NAME ),
			) );
		} else {
			wp_send_json_success( array(
				'message' => __( 'API key is configured.', AI1WM_PLUGIN_NAME ),
			) );
		}
	}
}
