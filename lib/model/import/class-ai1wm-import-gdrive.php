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

class Ai1wm_Import_Gdrive {

	public static function execute( $params ) {
		// Check if this is a Google Drive import
		if ( ! isset( $params['gdrive_url'] ) ) {
			return $params;
		}

		// Extract file ID from Google Drive URL
		$file_id = self::extract_file_id( $params['gdrive_url'] );
		if ( ! $file_id ) {
			throw new Ai1wm_Import_Retry_Exception(
				__( 'Invalid Google Drive URL format.', AI1WM_PLUGIN_NAME ),
				400
			);
		}

		// Get API key from WordPress options
		$api_key = get_option( AI1WM_GDRIVE_API_KEY );
		if ( empty( $api_key ) ) {
			throw new Ai1wm_Import_Retry_Exception(
				__( 'Please configure Google Drive API key in Settings.', AI1WM_PLUGIN_NAME ),
				400
			);
		}

		// Get archive path
		$archive = ai1wm_archive_path( $params );

		// Download file from Google Drive
		try {
			self::download_file( $file_id, $api_key, $archive );
		} catch ( Exception $e ) {
			throw new Ai1wm_Import_Retry_Exception(
				sprintf(
					__( 'Unable to download from Google Drive: %s', AI1WM_PLUGIN_NAME ),
					$e->getMessage()
				),
				400
			);
		}

		// Remove gdrive_url from params to prevent re-processing
		unset( $params['gdrive_url'] );

		return $params;
	}

	/**
	 * Extract Google Drive file ID from various URL formats
	 *
	 * @param string $url Google Drive URL
	 * @return string|false File ID or false if not found
	 */
	private static function extract_file_id( $url ) {
		// Validate that this is a Google Drive URL
		if ( strpos( $url, 'drive.google.com' ) === false ) {
			return false;
		}

		// Extract file ID from various URL formats
		// Format 1: https://drive.google.com/file/d/FILE_ID/view
		// Format 2: https://drive.google.com/open?id=FILE_ID
		// Format 3: https://drive.google.com/uc?id=FILE_ID
		if ( preg_match( '/(?:\/d\/|id=)([a-zA-Z0-9_-]+)/', $url, $matches ) ) {
			return $matches[1];
		}

		return false;
	}

	/**
	 * Download file from Google Drive using API key
	 *
	 * @param string $file_id Google Drive file ID
	 * @param string $api_key Google Drive API key
	 * @param string $archive_path Local path to save the file
	 * @return void
	 * @throws Exception
	 */
	private static function download_file( $file_id, $api_key, $archive_path ) {
		// First, get file metadata to check if file exists and get size
		$metadata_url = sprintf(
			'%s/files/%s?key=%s&fields=id,name,size,mimeType',
			AI1WM_GDRIVE_API_URL,
			$file_id,
			$api_key
		);

		$metadata_response = wp_remote_get( $metadata_url, array(
			'timeout' => 30,
		) );

		if ( is_wp_error( $metadata_response ) ) {
			throw new Exception( $metadata_response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $metadata_response );
		if ( $response_code !== 200 ) {
			$body = wp_remote_retrieve_body( $metadata_response );
			$error_data = json_decode( $body, true );

			if ( $response_code === 403 ) {
				throw new Exception( __( 'API quota exceeded or file not accessible. Make sure the file is shared as "Anyone with the link".', AI1WM_PLUGIN_NAME ) );
			} elseif ( $response_code === 404 ) {
				throw new Exception( __( 'File not found. Please check the Google Drive URL.', AI1WM_PLUGIN_NAME ) );
			} elseif ( isset( $error_data['error']['message'] ) ) {
				throw new Exception( $error_data['error']['message'] );
			} else {
				throw new Exception( sprintf( __( 'HTTP error %d', AI1WM_PLUGIN_NAME ), $response_code ) );
			}
		}

		// Download the file
		$download_url = sprintf(
			'%s/files/%s?alt=media&key=%s',
			AI1WM_GDRIVE_API_URL,
			$file_id,
			$api_key
		);

		$download_response = wp_remote_get( $download_url, array(
			'timeout' => 300,
			'stream' => true,
			'filename' => $archive_path,
		) );

		if ( is_wp_error( $download_response ) ) {
			throw new Exception( $download_response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $download_response );
		if ( $response_code !== 200 ) {
			// Clean up partial download
			if ( file_exists( $archive_path ) ) {
				ai1wm_unlink( $archive_path );
			}

			if ( $response_code === 403 ) {
				throw new Exception( __( 'File not accessible. Make sure it is shared as "Anyone with the link".', AI1WM_PLUGIN_NAME ) );
			} else {
				throw new Exception( sprintf( __( 'Download failed with HTTP error %d', AI1WM_PLUGIN_NAME ), $response_code ) );
			}
		}

		// Verify file was downloaded
		if ( ! file_exists( $archive_path ) || filesize( $archive_path ) === 0 ) {
			throw new Exception( __( 'File download failed or file is empty.', AI1WM_PLUGIN_NAME ) );
		}
	}
}
