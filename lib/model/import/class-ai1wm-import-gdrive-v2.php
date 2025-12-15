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

class Ai1wm_Import_Gdrive_V2 {

	public static function execute( $params ) {
		// Check if this is a Google Drive V2 import
		if ( ! isset( $params['gdrive_v2_url'] ) ) {
			return $params;
		}

		// Extract file ID from Google Drive URL
		$file_id = self::extract_file_id( $params['gdrive_v2_url'] );
		if ( ! $file_id ) {
			throw new Ai1wm_Import_Retry_Exception(
				__( 'Invalid Google Drive URL format.', AI1WM_PLUGIN_NAME ),
				400
			);
		}

		// Get archive path
		$archive = ai1wm_archive_path( $params );

		// Download file from Google Drive (no API key needed)
		try {
			self::download_file( $file_id, $archive );
		} catch ( Exception $e ) {
			throw new Ai1wm_Import_Retry_Exception(
				sprintf(
					__( 'Unable to download from Google Drive: %s', AI1WM_PLUGIN_NAME ),
					$e->getMessage()
				),
				400
			);
		}

		// Remove gdrive_v2_url from params to prevent re-processing
		unset( $params['gdrive_v2_url'] );

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
	 * Download file from Google Drive without API key (public files only)
	 *
	 * @param string $file_id Google Drive file ID
	 * @param string $archive_path Local path to save the file
	 * @return void
	 * @throws Exception
	 */
	private static function download_file( $file_id, $archive_path ) {
		// Use direct download URL (works for public files)
		$download_url = sprintf(
			'https://drive.google.com/uc?export=download&id=%s',
			$file_id
		);

		// Try to download the file
		$response = wp_remote_get( $download_url, array(
			'timeout' => 300,
			'stream' => true,
			'filename' => $archive_path,
			'headers' => array(
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			),
		) );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		// Check if we got a confirmation page (for large files)
		if ( $response_code === 200 && file_exists( $archive_path ) ) {
			// Check if we got the virus scan warning page
			$file_size = filesize( $archive_path );

			// If file is very small (< 1KB), it's probably the confirmation page HTML
			if ( $file_size < 1024 ) {
				$content = file_get_contents( $archive_path );

				// Check for confirmation token
				if ( preg_match( '/confirm=([^&"]+)/', $content, $matches ) ) {
					$confirm = $matches[1];

					// Clean up the HTML file
					ai1wm_unlink( $archive_path );

					// Download with confirmation
					$download_url_with_confirm = sprintf(
						'https://drive.google.com/uc?export=download&id=%s&confirm=%s',
						$file_id,
						$confirm
					);

					$response = wp_remote_get( $download_url_with_confirm, array(
						'timeout' => 300,
						'stream' => true,
						'filename' => $archive_path,
						'headers' => array(
							'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
						),
					) );

					if ( is_wp_error( $response ) ) {
						throw new Exception( $response->get_error_message() );
					}

					$response_code = wp_remote_retrieve_response_code( $response );
				}
			}
		}

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
