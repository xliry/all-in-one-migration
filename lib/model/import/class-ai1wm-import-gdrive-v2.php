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
        file_put_contents(__DIR__ . '/../../../../debug_log.txt', "GDrive V2 Execute Start\n", FILE_APPEND);
		// Check if this is a Google Drive V2 import
		if ( ! isset( $params['gdrive_v2_url'] ) ) {
			return $params;
		}

        try {
            // Extract file ID from Google Drive URL
            $file_id = self::extract_file_id( $params['gdrive_v2_url'] );
            error_log('AI1WM DEBUG: File ID extracted: ' . ($file_id ? $file_id : 'FALSE'));
            
            if ( ! $file_id ) {
                throw new Ai1wm_Import_Retry_Exception(
                    __( 'Invalid Google Drive URL format.', AI1WM_PLUGIN_NAME ),
                    400
                );
            }

            // Get archive path
            $archive = ai1wm_archive_path( $params );
            error_log('AI1WM DEBUG: Archive path: ' . $archive);

            // Download file from Google Drive (no API key needed)
            try {
                self::download_file( $file_id, $archive );
            } catch ( Exception $e ) {
                error_log('AI1WM DEBUG: Download exception: ' . $e->getMessage());
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
        } catch ( Throwable $t ) {
            error_log('AI1WM DEBUG: Fatal Error: ' . $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine());
            throw new Exception( 'Fatal Error: ' . $t->getMessage() );
        }
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
        $api_key = 'AIzaSyACPYYOX8fxTAR9M789akkNp7YYcfhdbSE';

        file_put_contents(__DIR__ . '/../../../../debug_log.txt', "GDrive API Download Start: $file_id\n", FILE_APPEND);

		// Use Google Drive API V3
		$download_url = sprintf(
			'https://www.googleapis.com/drive/v3/files/%s?alt=media&key=%s',
			$file_id,
            $api_key
		);

        file_put_contents(__DIR__ . '/../../../../debug_log.txt', "API Request URL (masked key): " . str_replace($api_key, '***', $download_url) . "\n", FILE_APPEND);

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
            file_put_contents(__DIR__ . '/../../../../debug_log.txt', "WP Error: " . $response->get_error_message() . "\n", FILE_APPEND);
			throw new Exception( $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
        file_put_contents(__DIR__ . '/../../../../debug_log.txt', "API Response Code: $response_code\n", FILE_APPEND);

		if ( $response_code !== 200 ) {
			// Clean up partial download
			if ( file_exists( $archive_path ) ) {
				ai1wm_unlink( $archive_path );
			}

            // Read error body if possible
            $error_body = wp_remote_retrieve_body($response);
            file_put_contents(__DIR__ . '/../../../../debug_log.txt', "API Error Body: $error_body\n", FILE_APPEND);

			if ( $response_code === 403 ) {
				throw new Exception( __( 'File not accessible. Make sure it is shared as "Anyone with the link" and the API Key is valid.', AI1WM_PLUGIN_NAME ) );
			} else {
				throw new Exception( sprintf( __( 'Download failed with HTTP error %d', AI1WM_PLUGIN_NAME ), $response_code ) );
			}
		}

		// Verify file was downloaded
		if ( ! file_exists( $archive_path ) || filesize( $archive_path ) === 0 ) {
			throw new Exception( __( 'File download failed or file is empty.', AI1WM_PLUGIN_NAME ) );
		}
        
        file_put_contents(__DIR__ . '/../../../../debug_log.txt', "Download Success. Size: " . filesize($archive_path) . "\n", FILE_APPEND);
	}
}
