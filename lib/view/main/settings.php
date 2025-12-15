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
?>

<div class="ai1wm-container">
	<div class="ai1wm-row">
		<div class="ai1wm-left">
			<div class="ai1wm-holder">
				<h1>
					<i class="ai1wm-icon-gear"></i>
					<?php _e( 'Settings', AI1WM_PLUGIN_NAME ); ?>
				</h1>

				<div class="ai1wm-segment">
					<h2><?php _e( 'Google Drive Configuration', AI1WM_PLUGIN_NAME ); ?></h2>

					<div class="ai1wm-field">
						<label for="ai1wm-gdrive-api-key"><?php _e( 'Google Drive API Key', AI1WM_PLUGIN_NAME ); ?></label>
						<input
							type="text"
							id="ai1wm-gdrive-api-key"
							name="ai1wm_gdrive_api_key"
							class="ai1wm-gdrive-api-key"
							value="<?php echo esc_attr( get_option( AI1WM_GDRIVE_API_KEY, '' ) ); ?>"
							placeholder="<?php _e( 'Enter your Google Drive API key', AI1WM_PLUGIN_NAME ); ?>"
						/>
					</div>

					<div class="ai1wm-field">
						<div class="ai1wm-buttons">
							<button type="button" id="ai1wm-save-gdrive-key" class="ai1wm-button-green">
								<i class="ai1wm-icon-save"></i>
								<?php _e( 'Save API Key', AI1WM_PLUGIN_NAME ); ?>
							</button>
							<button type="button" id="ai1wm-test-gdrive-key" class="ai1wm-button-blue">
								<i class="ai1wm-icon-notification"></i>
								<?php _e( 'Test API Key', AI1WM_PLUGIN_NAME ); ?>
							</button>
						</div>
					</div>

					<div class="ai1wm-field">
						<div id="ai1wm-gdrive-messages"></div>
					</div>
				</div>

				<div class="ai1wm-segment">
					<h2><?php _e( 'How to Obtain a Google Drive API Key', AI1WM_PLUGIN_NAME ); ?></h2>

					<p><?php _e( 'Follow these steps to create your Google Drive API key:', AI1WM_PLUGIN_NAME ); ?></p>

					<ol>
						<li>
							<strong><?php _e( 'Go to Google Cloud Console', AI1WM_PLUGIN_NAME ); ?></strong><br />
							<?php _e( 'Visit', AI1WM_PLUGIN_NAME ); ?>
							<a href="https://console.cloud.google.com" target="_blank">https://console.cloud.google.com</a>
						</li>
						<li>
							<strong><?php _e( 'Create or Select a Project', AI1WM_PLUGIN_NAME ); ?></strong><br />
							<?php _e( 'Click on the project dropdown in the top navigation bar and create a new project, or select an existing one.', AI1WM_PLUGIN_NAME ); ?>
						</li>
						<li>
							<strong><?php _e( 'Enable Google Drive API', AI1WM_PLUGIN_NAME ); ?></strong><br />
							<?php _e( 'Go to "APIs & Services" > "Library", search for "Google Drive API", and click "Enable".', AI1WM_PLUGIN_NAME ); ?>
						</li>
						<li>
							<strong><?php _e( 'Create API Key', AI1WM_PLUGIN_NAME ); ?></strong><br />
							<?php _e( 'Go to "APIs & Services" > "Credentials", click "Create Credentials", and select "API Key".', AI1WM_PLUGIN_NAME ); ?>
						</li>
						<li>
							<strong><?php _e( 'Restrict Your API Key (Recommended)', AI1WM_PLUGIN_NAME ); ?></strong><br />
							<?php _e( 'Click on your new API key to edit it. Under "API restrictions", select "Restrict key" and choose "Google Drive API".', AI1WM_PLUGIN_NAME ); ?>
						</li>
						<li>
							<strong><?php _e( 'Copy and Save', AI1WM_PLUGIN_NAME ); ?></strong><br />
							<?php _e( 'Copy the API key and paste it in the field above, then click "Save API Key".', AI1WM_PLUGIN_NAME ); ?>
						</li>
					</ol>
				</div>

				<div class="ai1wm-segment">
					<h2><?php _e( 'How to Share a Google Drive File', AI1WM_PLUGIN_NAME ); ?></h2>

					<p><?php _e( 'To import a file from Google Drive, it must be shared properly:', AI1WM_PLUGIN_NAME ); ?></p>

					<ol>
						<li><?php _e( 'Upload your backup file (.wpress) to Google Drive.', AI1WM_PLUGIN_NAME ); ?></li>
						<li><?php _e( 'Right-click on the file and select "Share" or "Get link".', AI1WM_PLUGIN_NAME ); ?></li>
						<li><?php _e( 'Under "General access", select "Anyone with the link".', AI1WM_PLUGIN_NAME ); ?></li>
						<li><?php _e( 'Set the permission to "Viewer".', AI1WM_PLUGIN_NAME ); ?></li>
						<li><?php _e( 'Click "Copy link" to get the shareable URL.', AI1WM_PLUGIN_NAME ); ?></li>
						<li><?php _e( 'Use this URL on the Import page to import your backup.', AI1WM_PLUGIN_NAME ); ?></li>
					</ol>

					<p>
						<strong><?php _e( 'Note:', AI1WM_PLUGIN_NAME ); ?></strong>
						<?php _e( 'The file must be shared as "Anyone with the link" for the API key authentication to work.', AI1WM_PLUGIN_NAME ); ?>
					</p>
				</div>

				<div class="ai1wm-segment">
					<h2><?php _e( 'Troubleshooting', AI1WM_PLUGIN_NAME ); ?></h2>

					<h3><?php _e( 'API Key Validation Failed', AI1WM_PLUGIN_NAME ); ?></h3>
					<p><?php _e( 'Make sure you have enabled the Google Drive API in your Google Cloud Console project and that your API key is not restricted to other APIs.', AI1WM_PLUGIN_NAME ); ?></p>

					<h3><?php _e( 'File Not Accessible', AI1WM_PLUGIN_NAME ); ?></h3>
					<p><?php _e( 'Ensure the file is shared as "Anyone with the link" and has at least "Viewer" permission.', AI1WM_PLUGIN_NAME ); ?></p>

					<h3><?php _e( 'API Quota Exceeded', AI1WM_PLUGIN_NAME ); ?></h3>
					<p><?php _e( 'Google Drive API has daily quotas. If you exceed these limits, you will need to wait until the quota resets (usually 24 hours) or request a quota increase in Google Cloud Console.', AI1WM_PLUGIN_NAME ); ?></p>
				</div>

			</div>
		</div>
	</div>
</div>
