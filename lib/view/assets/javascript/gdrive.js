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
 */

jQuery(document).ready(function($) {
	'use strict';

	// Open Google Drive import modal
	$(document).on('click', '#ai1wm-import-gdrive', function(e) {
		e.preventDefault();

		// Show the modal
		$('.ai1wm-gdrive-import-dialog').fadeIn(300);

		// Clear previous input and messages
		$('#ai1wm-gdrive-url').val('');
		$('#ai1wm-gdrive-messages').hide().html('');
	});

	// Close modal
	$(document).on('click', '#ai1wm-gdrive-cancel, .ai1wm-overlay', function(e) {
		e.preventDefault();
		$('.ai1wm-gdrive-import-dialog').fadeOut(300);
	});

	// Submit Google Drive import
	$(document).on('click', '#ai1wm-gdrive-submit', function(e) {
		e.preventDefault();

		var button = $(this);
		var url = $('#ai1wm-gdrive-url').val().trim();
		var messageContainer = $('#ai1wm-gdrive-messages');

		// Clear messages
		messageContainer.hide().html('');

		// Validate URL
		if (!url) {
			messageContainer.html(
				'<div class="ai1wm-message ai1wm-error-message">' +
				'<p>Please enter a Google Drive URL.</p>' +
				'</div>'
			).show();
			return;
		}

		// Basic URL validation
		if (url.indexOf('drive.google.com') === -1) {
			messageContainer.html(
				'<div class="ai1wm-message ai1wm-error-message">' +
				'<p>Please enter a valid Google Drive URL.</p>' +
				'</div>'
			).show();
			return;
		}

		// Disable button
		button.prop('disabled', true);

		// Show loading message
		messageContainer.html(
			'<div class="ai1wm-message ai1wm-info-message">' +
			'<p>Checking API key configuration...</p>' +
			'</div>'
		).show();

		// First, check if API key is configured
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'ai1wm_check_gdrive_key'
			},
			dataType: 'json',
			success: function(response) {
				if (!response.success) {
					messageContainer.html(
						'<div class="ai1wm-message ai1wm-error-message">' +
						'<p>' + response.data.message + ' ' +
						'Please configure your API key in <a href="' +
						ai1wm_locale.settings_url + '">Settings</a>.</p>' +
						'</div>'
					).show();
					button.prop('disabled', false);
					return;
				}

				// API key is configured, proceed with import
				startGdriveImport(url, button, messageContainer);
			},
			error: function(xhr, status, error) {
				messageContainer.html(
					'<div class="ai1wm-message ai1wm-error-message">' +
					'<p>Error checking API key: ' + error + '</p>' +
					'</div>'
				).show();
				button.prop('disabled', false);
			}
		});
	});

	function startGdriveImport(url, button, messageContainer) {
		// Show loading message
		messageContainer.html(
			'<div class="ai1wm-message ai1wm-info-message">' +
			'<p>Starting Google Drive import...</p>' +
			'</div>'
		).show();

		// Get secret key
		var secretKey = ai1wm_import.secret_key || '';

		// Start the import process
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'ai1wm_gdrive_import',
				gdrive_url: url,
				secret_key: secretKey
			},
			dataType: 'json',
			success: function(response) {
				// Close modal
				$('.ai1wm-gdrive-import-dialog').fadeOut(300);

				// If the import started successfully, continue with normal import flow
				if (response && !response.errors) {
					// Trigger import progress monitoring
					if (typeof ai1wm_import !== 'undefined' && typeof ai1wm_import.poll_status === 'function') {
						ai1wm_import.poll_status();
					}
				} else if (response.errors && response.errors.length > 0) {
					// Show error in the import page instead of modal
					var errorMessage = response.errors[0].message || 'Unknown error occurred';
					messageContainer.html(
						'<div class="ai1wm-message ai1wm-error-message">' +
						'<p>' + errorMessage + '</p>' +
						'</div>'
					).show();

					// Re-open modal to show error
					$('.ai1wm-gdrive-import-dialog').fadeIn(300);
				}
			},
			error: function(xhr, status, error) {
				messageContainer.html(
					'<div class="ai1wm-message ai1wm-error-message">' +
					'<p>Import failed: ' + error + '</p>' +
					'</div>'
				).show();
			},
			complete: function() {
				button.prop('disabled', false);
			}
		});
	}
});
