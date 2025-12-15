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

jQuery(document).ready(function ($) {
	console.log('Google Drive V2 Script Loaded');
	'use strict';

	// Open Google Drive V2 import modal
	$(document).on('click', '#ai1wm-import-gdrive-v2', function (e) {
		console.log('Google Drive V2 button clicked');
		e.preventDefault();


		// Show the modal
		var modal = $('.ai1wm-gdrive-v2-import-dialog');

		// Move to body to prevent clipping and ensure top-level stacking
		if (modal.parent('body').length === 0) {
			modal.appendTo('body');
		}

		modal.css({
			'display': 'block',
			'position': 'fixed',
			'top': '0',
			'left': '0',
			'width': '100%',
			'height': '100%',
			'z-index': '2147483647', // Max safe integer for z-index
			'visibility': 'visible',
			'opacity': '1'
		});

		modal.find('.ai1wm-overlay').css({
			'position': 'fixed',
			'top': '0',
			'left': '0',
			'width': '100%',
			'height': '100%',
			'background': 'rgba(0,0,0,0.7)',
			'z-index': '2147483646',
			'display': 'block'
		});

		modal.find('.ai1wm-modal-container').css({
			'position': 'fixed',
			'top': '50%',
			'left': '50%',
			'transform': 'translate(-50%, -50%)',
			'z-index': '2147483647',
			'background': '#fff',
			'padding': '20px',
			'border-radius': '4px',
			'box-shadow': '0 0 50px rgba(0,0,0,0.5)',
			'max-width': '600px',
			'width': '90%',
			'display': 'block'
		});

		console.log('Modal moved to body and display forced.');

		// Clear previous input and messages
		$('#ai1wm-gdrive-v2-url').val('');
		$('#ai1wm-gdrive-v2-messages').hide().html('');
	});

	// Close modal
	$(document).on('click', '#ai1wm-gdrive-v2-cancel, .ai1wm-gdrive-v2-import-dialog .ai1wm-overlay', function (e) {
		e.preventDefault();
		$('.ai1wm-gdrive-v2-import-dialog').fadeOut(300);
	});

	// Submit Google Drive V2 import
	$(document).on('click', '#ai1wm-gdrive-v2-submit', function (e) {
		e.preventDefault();

		var button = $(this);
		var url = $('#ai1wm-gdrive-v2-url').val().trim();
		var messageContainer = $('#ai1wm-gdrive-v2-messages');

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
			'<p>Starting Google Drive import (V2 - No API Key)...</p>' +
			'</div>'
		).show();

		// Get secret key
		var secretKey = ai1wm_import.secret_key || '';

		// Start the import process
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'ai1wm_gdrive_v2_import',
				gdrive_v2_url: url,
				secret_key: secretKey,
				ai1wm_manual_import: 1
			},
			success: function (rawResponse) {
				var response = {};
				try {
					if (typeof rawResponse === 'string' && rawResponse.trim() !== '') {
						response = JSON.parse(rawResponse);
					} else if (typeof rawResponse === 'object') {
						response = rawResponse;
					}
				} catch (e) {
					// Start of non-JSON response (likely HTML error or empty)
					// If empty, it's likely success (wp_remote_post fired and exited)
					if (!rawResponse) {
						response = {};
					} else {
						// Parse error or PHP fatal error outputting HTML
						console.error('JSON Parse Error', e);
						response = { errors: [{ message: 'Invalid server response' }] };
					}
				}

				// Close modal
				$('.ai1wm-gdrive-v2-import-dialog').fadeOut(300);

				// If the import started successfully, continue with normal import flow
				if (response && !response.errors) {
					console.log('GDrive V2 Import Started. Handoff to main import logic.', response);
					// Trigger import progress monitoring
					if (typeof Ai1wm !== 'undefined' && Ai1wm.Import) {
						console.log('Handing off to Ai1wm.Import');

						// CRITICAL: Override action to generic import to prevent re-triggering GDrive V2 logic
						response.action = 'ai1wm_import';

						var importer = new Ai1wm.Import();
						// Set params (storage, archive) received from backend
						importer.setParams(response);
						// Start the run loop with the new params
						importer.run(response);
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
					$('.ai1wm-gdrive-v2-import-dialog').fadeIn(300);
				}
			},
			error: function (xhr, status, error) {
				var serverMsg = '';
				if (xhr.responseText) {
					serverMsg = '<br><strong>Server Error Details:</strong><br>' + xhr.responseText.substring(0, 500);
				}
				messageContainer.html(
					'<div class="ai1wm-message ai1wm-error-message">' +
					'<p>Import failed: ' + error + serverMsg + '</p>' +
					'</div>'
				).show();
			},
			complete: function () {
				button.prop('disabled', false);
			}
		});
	});
});
