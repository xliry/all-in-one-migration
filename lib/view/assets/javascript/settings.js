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

	// Save API Key
	$('#ai1wm-save-gdrive-key').on('click', function(e) {
		e.preventDefault();

		var button = $(this);
		var apiKey = $('#ai1wm-gdrive-api-key').val().trim();
		var messageContainer = $('#ai1wm-gdrive-messages');

		// Disable button
		button.prop('disabled', true);

		// Clear messages
		messageContainer.html('');

		// Send AJAX request
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'ai1wm_save_gdrive_key',
				api_key: apiKey,
				ai1wm_nonce: $('<input>').attr('type', 'hidden').attr('name', '_wpnonce').val(
					(function() {
						var nonce = '';
						try {
							nonce = wp.ajax.settings.nonce;
						} catch(e) {
							// Fallback to generating nonce
							nonce = 'ai1wm_gdrive_settings';
						}
						return nonce;
					})()
				).val() || 'ai1wm_gdrive_settings'
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					messageContainer.html(
						'<div class="ai1wm-message ai1wm-success-message">' +
						'<p>' + response.data.message + '</p>' +
						'</div>'
					);
				} else {
					messageContainer.html(
						'<div class="ai1wm-message ai1wm-error-message">' +
						'<p>' + response.data.message + '</p>' +
						'</div>'
					);
				}
			},
			error: function(xhr, status, error) {
				messageContainer.html(
					'<div class="ai1wm-message ai1wm-error-message">' +
					'<p>Error: ' + error + '</p>' +
					'</div>'
				);
			},
			complete: function() {
				// Re-enable button
				button.prop('disabled', false);
			}
		});
	});

	// Test API Key
	$('#ai1wm-test-gdrive-key').on('click', function(e) {
		e.preventDefault();

		var button = $(this);
		var apiKey = $('#ai1wm-gdrive-api-key').val().trim();
		var messageContainer = $('#ai1wm-gdrive-messages');

		// Disable button
		button.prop('disabled', true);

		// Clear messages
		messageContainer.html('');

		if (!apiKey) {
			messageContainer.html(
				'<div class="ai1wm-message ai1wm-error-message">' +
				'<p>Please enter an API key first.</p>' +
				'</div>'
			);
			button.prop('disabled', false);
			return;
		}

		// Show testing message
		messageContainer.html(
			'<div class="ai1wm-message ai1wm-info-message">' +
			'<p>Testing API key...</p>' +
			'</div>'
		);

		// Send AJAX request
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'ai1wm_validate_gdrive_key',
				api_key: apiKey,
				ai1wm_nonce: 'ai1wm_gdrive_settings'
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					messageContainer.html(
						'<div class="ai1wm-message ai1wm-success-message">' +
						'<p>' + response.data.message + '</p>' +
						'</div>'
					);
				} else {
					messageContainer.html(
						'<div class="ai1wm-message ai1wm-error-message">' +
						'<p>' + response.data.message + '</p>' +
						'</div>'
					);
				}
			},
			error: function(xhr, status, error) {
				messageContainer.html(
					'<div class="ai1wm-message ai1wm-error-message">' +
					'<p>Error: ' + error + '</p>' +
					'</div>'
				);
			},
			complete: function() {
				// Re-enable button
				button.prop('disabled', false);
			}
		});
	});
});
